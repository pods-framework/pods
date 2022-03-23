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
import { select, dispatch } from '@wordpress/data'
import { registerPlugin } from '@wordpress/plugins';

/**
 * Pods dependencies
 */
import {
	createStoreKey,
	initPodStore,
	initEditPodStore,
} from 'dfv/src/store/store';
import PodsDFVApp from 'dfv/src/core/pods-dfv-app';

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
	 * Store the field configs for reference.
	 */
	_fieldDataByStoreKeyPrefix: {},

	/**
	 * Private Pods instance of hooks. Can be used such as:
	 *
	 * ```
	 * window.PodsDFV.hooks.addAction( 'pods_init_complete', 'pods/dfv', () => {} );
	 * ```
	 */
	hooks: createHooks(),

	/**
	 * Get list of field configs (based on pod, item ID, and form counter).
	 *
	 * @param {string} pod         Pod slug/name.
	 * @param {int}    itemId      Object ID.
	 * @param {int}    formCounter Form index. (Optional.)
	 *
	 * @returns {array|undefined} Array of field data, or undefined if not found.
	 */
	getFields( pod, itemId, formCounter = 0 ) {
		if ( isEditPodScreen() ) {
			return undefined;
		}

		const storeKey = createStoreKey(
			pod,
			itemId,
			formCounter,
			isEditPodScreen() ? STORE_KEY_EDIT_POD : STORE_KEY_DFV
		);

		return this._fieldDataByStoreKeyPrefix[ storeKey ] || undefined;
	},

	/**
	 * Get specific field config (based on pod, item ID, form counter, and field name).
	 *
	 * @param {string} pod         Pod slug/name.
	 * @param {int}    itemId      Object ID.
	 * @param {string} fieldName   Field name.
	 * @param {int}    formCounter Form index. (Optional.)
	 *
	 * @returns {object|undefined} Field data, or undefined if not found.
	 */
	getField( pod, itemId, fieldName, formCounter = 0 ) {
		if ( isEditPodScreen() ) {
			return undefined;
		}

		const fields = this.getFields( pod, itemId, formCounter );

		if ( ! Array.isArray( fields ) || ! fields.length ) {
			return undefined;
		}

		return fields.find( ( field ) => field.fieldConfig?.name === fieldName );
	},

	/**
	 * Get current field value (based on pod, item ID, form counter, and field name).
	 *
	 * @param {string} pod         Pod slug/name.
	 * @param {int}    itemId      Object ID.
	 * @param {string} fieldName   Field name.
	 * @param {int}    formCounter Form index. (Optional.)
	 *
	 * @returns {any} Field value or undefined.
	 */
	getFieldValue( pod, itemId, fieldName, formCounter = 0 ) {
		if ( isEditPodScreen() ) {
			return undefined;
		}

		const storeKey = createStoreKey(
			pod,
			itemId,
			formCounter,
			isEditPodScreen() ? STORE_KEY_EDIT_POD : STORE_KEY_DFV
		);

		return select( storeKey )?.getPodOptions()?.[ fieldName ];
	},

	/**
	 * Set current field value (based on pod, item ID, form counter, and field name).
	 *
	 * @param {string} pod         Pod slug/name.
	 * @param {int}    itemId      Object ID.
	 * @param {string} fieldName   Field name.
	 * @param {any}    value       New value.
	 * @param {int}    formCounter Form index. (Optional.)
	 */
	setFieldValue( pod, itemId, fieldName, value, formCounter = 0 ) {
		if ( isEditPodScreen() ) {
			return;
		}

		const storeKey = createStoreKey(
			pod,
			itemId,
			formCounter,
			isEditPodScreen() ? STORE_KEY_EDIT_POD : STORE_KEY_DFV
		);

		dispatch( storeKey ).setOptionValue( fieldName, value );
	},

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
				fieldValue: data.fieldValue || null,
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

			this._fieldDataByStoreKeyPrefix[ storeKey ] = [
				...( this._fieldDataByStoreKeyPrefix[ storeKey ] || [] ),
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
		console.log( 'Pods init with initial values:', initialStoresWithValues );

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
					<PodsDFVApp
						fieldsData={ this._fieldDataByStoreKeyPrefix[ storeKey ] }
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
};

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
