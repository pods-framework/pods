/**
 * External dependencies
 */
import React, { useEffect } from 'react';
import ReactDOM from 'react-dom';
import { omit } from 'lodash';

/**
 * WordPress dependencies
 */
import { createHooks, doAction, addAction } from '@wordpress/hooks';
import { select } from '@wordpress/data'
import { registerPlugin } from '@wordpress/plugins';

/**
 * Pods dependencies
 */
import {
	createStoreKey,
	initPodStore,
	initEditPodStore,
} from 'dfv/src/store/store';
import App from 'dfv/src/core/app';
import API from 'dfv/src/core/api';

/**
 * Pods helpers
 */
import isGutenbergEditorLoaded from 'dfv/src/helpers/isGutenbergEditorLoaded';
import isModalWindow from 'dfv/src/helpers/isModalWindow';
import isMediaModal from 'dfv/src/helpers/isMediaModal';
import isEditPodScreen from 'dfv/src/helpers/isEditPodScreen';

import initPodsGbModalListener from 'dfv/src/core/gb-modal-listener';

import { STORE_KEY_EDIT_POD, STORE_KEY_DFV } from 'dfv/src/store/constants';
import FIELD_MAP from 'dfv/src/fields/field-map';

// Loads data from an object in this script tag.
const SCRIPT_TARGET = 'script.pods-dfv-field-data';

window.PodsDFV = {
	/**
	 * Private Pods instance of hooks. Can be used such as:
	 *
	 * ```
	 * window.PodsDFV.hooks.addAction( 'pods_init_complete', 'pods/dfv', () => {} );
	 * ```
	 */
	hooks: createHooks(),

	/**
	 * Initialize Pod data.
	 *
	 * @param {string} selector Selector to target script tags. If empty, selects all DFV script tags from the document.
	 */
	init( selector = '' ) {
		// Find all in-line data scripts
		const scriptTagSelector = selector || SCRIPT_TARGET;
		const dataTags = [ ...document.querySelectorAll( scriptTagSelector ) ];

		const fieldsData = dataTags.map( ( tag ) => {
			const data = JSON.parse( tag.innerHTML );

			// Ignore anything malformed or that doesn't have the field type set
			if ( ! data?.fieldType ) {
				return undefined;
			}

			// Some fields are rendered directly, most are not.
			const directRender = FIELD_MAP[ data.fieldType ]?.directRender || false;

			// Clean up the field config.
			// Includes a kludge to disable the "Add New" button if we're inside a media modal.  This should
			// eventually be ironed out so we can use Add New from this context (see #4864)
			const cleanedFieldConfig = omit(
				( data.fieldConfig || {} ),
				[ '_field_object', 'output_options', 'item_id' ]
			);

			if ( tag.closest( '.media-modal-content' ) ) {
				if ( cleanedFieldConfig.fieldConfig ) {
					cleanedFieldConfig.fieldConfig.pick_allow_add_new = '0';
				} else {
					cleanedFieldConfig.pick_allow_add_new = '0';
				}
			}

			// Move other data into the field config so we have less to pass around.
			cleanedFieldConfig.htmlAttr = data.htmlAttr || {};
			cleanedFieldConfig.fieldEmbed = data.fieldEmbed || false;

			if ( data.fieldItemData ) {
				cleanedFieldConfig.fieldItemData = data.fieldItemData;
			}

			if ( false === data.fieldValue ) {
				data.fieldValue = null;
			}

			return {
				directRender,
				fieldComponent: FIELD_MAP[ data.fieldType ]?.fieldComponent || null,
				parentNode: tag.parentNode,
				pod: tag.dataset.pod || null,
				group: tag.dataset.group || null,
				itemId: tag.dataset.itemId || null,
				formCounter: tag.dataset.formCounter || null,
				fieldConfig: directRender ? undefined : cleanedFieldConfig,
				fieldItemData: data.fieldItemData || null,
				fieldValue: data.fieldValue ?? null,
			};
		} );

		// Filter out any that we skipped.
		const validFieldsData = fieldsData.filter( ( fieldData ) => !! fieldData );

		// We may need to create multiple DFV instances and multiple stores, if either
		// the pod, item ID, or group are different between the tags.
		//
		// Loop through all of the data/configs that we gathered, create the keys that
		// will be used to create the individual stores, and track the initial values
		// for the store as a nested object by those keys. Also separate out the validFieldsData
		// using these same keys.
		const initialStoresWithValues = {};

		// Create the store if it hasn't been done already.
		// The initial values for the data store require some massaging:
		// Some are arrays when we need single values (this may change once
		// repeatable fields are implemented), others have special requirements.
		validFieldsData.forEach( ( currentField ) => {
			const {
				fieldConfig = {},
				pod,
				itemId,
				formCounter,
			} = currentField;

			const storeKey = createStoreKey(
				pod,
				itemId,
				formCounter,
				isEditPodScreen() ? STORE_KEY_EDIT_POD : STORE_KEY_DFV
			);

			API._fieldDataByStoreKeyPrefix[ storeKey ] = [
				...( API._fieldDataByStoreKeyPrefix[ storeKey ] || [] ),
				currentField,
			];

			// "Boolean Group" fields are actually comprised of other fields with their own
			// named values, so instead of just one key/value, they'll have multiple ones.
			// These are handled very differently, so process them and return early.
			if ( 'boolean_group' === fieldConfig.type ) {
				const booleanGroupValues = {};

				fieldConfig.boolean_group.forEach( ( groupItem ) => {
					if ( ! groupItem.name ) {
						return;
					}

					// Apply defaults if we're on the Edit Pod screen.
					if (
						isEditPodScreen() &&
						'undefined' === typeof currentField.fieldItemData?.[ groupItem.name ]
					) {
						booleanGroupValues[ groupItem.name ] = groupItem.default || '';
					} else {
						booleanGroupValues[ groupItem.name ] = currentField.fieldItemData?.[ groupItem.name ];
					}
				} );

				initialStoresWithValues[ storeKey ] = {
					...( initialStoresWithValues[ storeKey ] || {} ),
					...booleanGroupValues,
				};

				return;
			}

			// If we're on the Edit Pod screen, fall back to the `default` value
			// if a value isn't set. On other screens, this is handled on the back-end.
			let valueOrDefault;

			if ( isEditPodScreen() ) {
				valueOrDefault = ( 'undefined' !== typeof currentField.fieldValue && null !== currentField.fieldValue )
					? currentField.fieldValue
					: currentField.default;
			} else {
				valueOrDefault = ( 'undefined' !== typeof currentField.fieldValue && null !== currentField.fieldValue )
					? currentField.fieldValue
					: '';
			}

			initialStoresWithValues[ storeKey ] = {
				...( initialStoresWithValues[ storeKey ] || {} ),
				[ fieldConfig.name ]: valueOrDefault,
			};
		} );

		// eslint-disable-next-line no-console
		// console.log( 'Pods init with initial values:', initialStoresWithValues );

		// Create stores for each of the individual keys we found (the keys of
		// the initialStoresWithValues object).
		const initialStoreKeys = Object.keys( initialStoresWithValues );

		const storeKeys = initialStoreKeys.map( ( storeKey ) => {
			// The Edit Pod screen gets a different store set up than
			// other contexts.
			if ( isEditPodScreen() ) {
				return initEditPodStore(
					window.podsAdminConfig,
					storeKey
				);
			} else if ( window.podsDFVConfig ) {
				return initPodStore(
					window.podsDFVConfig,
					initialStoresWithValues[ storeKey ],
					storeKey,
				);
			}

			// Something is wrong if neither set of globals is set.
			throw new Error( 'Missing window.podsDFVConfig, cannot set up Pods DFV' );
		} );

		// Creates a container for the React app to "render",
		// although it doesn't actually render anything in the container,
		// but places the fields in the correct places with Portals.
		const dfvRootContainer = document.createElement( 'div' );
		dfvRootContainer.class = 'pods-dfv-container';

		document.body.appendChild( dfvRootContainer );

		// Set up the DFV app.
		ReactDOM.render(
			<>
				{ storeKeys.map( ( storeKey ) => (
					<App
						storeKey={ storeKey }
						key={ storeKey }
					/>
				) ) }
			</>,
			dfvRootContainer
		);

		/**
		 * Run an action after Pods DFV init has completed.
		 */
		this.hooks.doAction( 'pods_init_complete' );
	},

	/**
	 * Backward compatible API methods are below here.
	 */

	/**
	 * Attempt to detect the form information (based on pod, item ID, and form counter).
	 *
	 * If any argument is not provided, the first instance will be returned.
	 *
	 * @deprecated 3.0
	 *
	 * @param {string|null} pod         Pod slug/name. (Optional.)
	 * @param {int|null}    itemId      Object ID. (Optional.)
	 * @param {int|null}    formCounter Form index. (Optional.)
	 *
	 * @returns {object|undefined} Object of form information, or undefined if not found.
	 */
	detectForm: ( pod = null, itemId = null, formCounter = null ) =>
		API.detectForm( pod, itemId, formCounter ),

	/**
	 * Attempt to detect the field information (based on pod, item ID, field name, and form counter).
	 *
	 * If any argument is not provided, the first instance will be returned.
	 *
	 * @deprecated 3.0
	 *
	 * @param {string|null} pod         Pod slug/name. (Optional.)
	 * @param {int|null}    itemId      Object ID. (Optional.)
	 * @param {string}      fieldName   Field name.
	 * @param {int|null}    formCounter Form index. (Optional.)
	 *
	 * @returns {object|undefined} Object of field information, or undefined if not found.
	 */
	detectField: ( pod = null, itemId = null, fieldName, formCounter = null ) =>
		API.detectField( fieldName, pod, itemId, formCounter ),

	/**
	 * Get list of field configs (based on pod, item ID, and form counter).
	 *
	 * @deprecated 3.0
	 *
	 * @param {string|null} pod         Pod slug/name. (Optional.)
	 * @param {int|null}    itemId      Object ID. (Optional.)
	 * @param {int|null}    formCounter Form index. (Optional.)
	 *
	 * @returns {object|undefined} Object of field data keyed by field name, or undefined if not found.
	 */
	getFields: ( pod = null, itemId = null, formCounter = null ) =>
		API.getFields( pod, itemId, formCounter ),

	/**
	 * Get specific field config (based on pod, item ID, field name, and form counter).
	 *
	 * @deprecated 3.0
	 *
	 * @param {string|null} pod         Pod slug/name. (Optional.)
	 * @param {int|null}    itemId      Object ID. (Optional.)
	 * @param {string}      fieldName   Field name.
	 * @param {int|null}    formCounter Form index. (Optional.)
	 *
	 * @returns {object|undefined} Field data, or undefined if not found.
	 */
	getField: ( pod = null, itemId = null, fieldName, formCounter = null ) =>
		API.getField( fieldName, pod, itemId, formCounter ),

	/**
	 * Get current field values (based on pod, item ID, and form counter).
	 *
	 * @deprecated 3.0
	 *
	 * @param {string|null} pod         Pod slug/name. (Optional.)
	 * @param {int|null}    itemId      Object ID. (Optional.)
	 * @param {int|null}    formCounter Form index. (Optional.)
	 *
	 * @returns {array|undefined} Field values array or undefined.
	 */
	getFieldValues: ( pod = null, itemId = null, formCounter = null ) =>
		API.getFieldValues( pod, itemId, formCounter ),

	/**
	 * Get current field values with configs (based on pod, item ID, and form counter).
	 *
	 * @deprecated 3.0
	 *
	 * @param {string|null} pod         Pod slug/name. (Optional.)
	 * @param {int|null}    itemId      Object ID. (Optional.)
	 * @param {int|null}    formCounter Form index. (Optional.)
	 *
	 * @returns {array|undefined} Field values array or undefined.
	 */
	getFieldValuesWithConfigs: ( pod = null, itemId = null, formCounter = null ) =>
		API.getFieldValuesWithConfigs( pod, itemId, formCounter ),

	/**
	 * Get current field value (based on pod, item ID, field name, and form counter).
	 *
	 * @deprecated 3.0
	 *
	 * @param {string|null} pod         Pod slug/name. (Optional.)
	 * @param {int|null}    itemId      Object ID. (Optional.)
	 * @param {string}      fieldName   Field name.
	 * @param {int|null}    formCounter Form index. (Optional.)
	 *
	 * @returns {any} Field value or undefined.
	 */
	getFieldValue: ( pod = null, itemId = null, fieldName, formCounter = null ) =>
		API.getFieldValue( fieldName, pod, itemId, formCounter ),

	/**
	 * Set current field value (based on pod, item ID, field name, and form counter).
	 *
	 * @deprecated 3.0
	 *
	 * @param {string|null} pod         Pod slug/name. (Optional.)
	 * @param {int|null}    itemId      Object ID. (Optional.)
	 * @param {string}      fieldName   Field name.
	 * @param {any}         value       New value.
	 * @param {int|null}    formCounter Form index. (Optional.)
	 *
	 * @returns {true|undefined} True if set, or undefined if not found.
	 */
	setFieldValue: ( pod = null, itemId = null, fieldName, value, formCounter = null ) =>
		API.setFieldValue( fieldName, value, pod, itemId, formCounter ),

};

/**
 * Maybe setup the public API.
 */
if ( ! window?.podsDFVConfig?.disablePublicApi ) {
	window.PodsDFVAPI = API;
}

/**
 * Kick everything off on DOMContentLoaded
 */
document.addEventListener( 'DOMContentLoaded', () => {
	// For the Media context, init gets called later.
	if ( isMediaModal() ) {
		return;
	}

	window.PodsDFV.init();
} );

// Load the Gutenberg modal listener if we're inside a Pods modal with Gutenberg active
const LoadModalListeners = () => {
	useEffect( () => {
		if ( isModalWindow() && isGutenbergEditorLoaded() ) {
			initPodsGbModalListener();
		}
	}, [] );

	return null;
};

registerPlugin( 'pods-load-modal-listeners', {
	render: LoadModalListeners,
} );
