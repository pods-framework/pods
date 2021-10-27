/**
 * External dependencies
 */
import React, { useEffect } from 'react';
import ReactDOM from 'react-dom';
import { omit } from 'lodash';

/**
 * WordPress dependencies
 */
import { select } from '@wordpress/data';
import { registerPlugin } from '@wordpress/plugins';

/**
 * Pods dependencies
 */
import {
	initPodStore,
	initEditPodStore,
} from 'dfv/src/store/store';
import PodsDFVApp from 'dfv/src/core/pods-dfv-app';
import { PodsGbModalListener } from 'dfv/src/core/gb-modal-listener';
import * as models from 'dfv/src/config/model-manifest';

import FIELD_MAP from 'dfv/src/fields/field-map';

// Loads data from an object in this script tag.
const SCRIPT_TARGET = 'script.pods-dfv-field-data';

window.PodsDFV = {
	models,
	dfvRootContainer: null,

	/**
	 * Initialize Pod data.
	 *
	 * @param {string} selector Selector to target script tags. If empty, selects all DFV script tags from the document.
	 */
	init( selector = '' ) {
		const isEditPodScreen = 'undefined' !== typeof window.podsAdminConfig;

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
				// The itemId is used when there are multiple instances of a
				// pod as a form on the page.
				itemId: tag.dataset.itemId || null,
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
		const fieldDataByStoreKeyPrefix = {};

		// Create the store if it hasn't been done already.
		// The initial values for the data store require some massaging:
		// Some are arrays when we need single values (this may change once
		// repeatable fields are implemented), others have special requirements.
		validFieldsData.forEach( ( currentField ) => {
			const {
				fieldConfig = {},
				pod,
				group,
				itemId,
			} = currentField;

			// @todo should group be used here?
			const storeKeyPrefix = `${ pod }-${ group }-${ itemId }`;

			fieldDataByStoreKeyPrefix[ storeKeyPrefix ] = [
				...( fieldDataByStoreKeyPrefix[ storeKeyPrefix ] || [] ),
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
					if ( isEditPodScreen && 'undefined' === typeof currentField.fieldItemData?.[ groupItem.name ] ) {
						booleanGroupValues[ groupItem.name ] = groupItem.default || '';
					} else {
						booleanGroupValues[ groupItem.name ] = currentField.fieldItemData?.[ groupItem.name ];
					}
				} );

				initialStoresWithValues[ storeKeyPrefix ] = {
					...( initialStoresWithValues[ storeKeyPrefix ] || {} ),
					...booleanGroupValues,
				};

				return;
			}

			// If we're on the Edit Pod screen, fall back to the `default` value
			// if a value isn't set. On other screens, this is handled on the back-end.
			let valueOrDefault;

			if ( isEditPodScreen ) {
				valueOrDefault = ( 'undefined' !== typeof currentField.fieldValue && null !== currentField.fieldValue )
					? currentField.fieldValue
					: currentField.default;
			} else {
				valueOrDefault = ( 'undefined' !== typeof currentField.fieldValue && null !== currentField.fieldValue )
					? currentField.fieldValue
					: '';
			}

			initialStoresWithValues[ storeKeyPrefix ] = {
				...( initialStoresWithValues[ storeKeyPrefix ] || {} ),
				[ fieldConfig.name ]: valueOrDefault,
			};
		} );

		// eslint-disable-next-line no-console
		console.log( 'Pods init with initial values:', initialStoresWithValues );

		// Create stores for each of the individual keys we found (the keys of
		// the initialStoresWithValues object).
		const storeKeyPrefixes = Object.keys( initialStoresWithValues );

		const storeKeys = storeKeyPrefixes.map( ( storeKeyPrefix ) => {
			// The Edit Pod screen gets a different store set up than
			// other contexts.
			if ( isEditPodScreen ) {
				return initEditPodStore(
					window.podsAdminConfig,
					storeKeyPrefix
				);
			} else if ( window.podsDFVConfig ) {
				return initPodStore(
					window.podsDFVConfig,
					initialStoresWithValues[ storeKeyPrefix ],
					storeKeyPrefix,
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
				{ storeKeyPrefixes.map( ( storeKeyPrefix, index ) => (
					<PodsDFVApp
						fieldsData={ fieldDataByStoreKeyPrefix[ storeKeyPrefix ] }
						storeKey={ storeKeys[ index ] }
						key={ storeKeys[ index ] }
					/>
				) ) }
			</>,
			dfvRootContainer
		);
	},

	isMediaModal() {
		return window.location.pathname === '/wp-admin/upload.php';
	},

	isModalWindow() {
		return ( -1 !== location.search.indexOf( 'pods_modal=' ) );
	},

	isGutenbergEditorLoaded() {
		return ( select( 'core/editor' ) !== undefined );
	},
};

/**
 * Kick everything off on DOMContentLoaded
 */
document.addEventListener( 'DOMContentLoaded', () => {
	// For the Media context, init gets called later.
	if ( window.PodsDFV.isMediaModal() ) {
		return;
	}

	window.PodsDFV.init();
} );

// Load the Gutenberg modal listener if we're inside a Pods modal with Gutenberg active
const LoadModalListeners = () => {
	useEffect( () => {
		if ( window.PodsDFV.isModalWindow() && window.PodsDFV.isGutenbergEditorLoaded() ) {
			PodsGbModalListener.init();
		}
	}, [] );

	return null;
};

registerPlugin( 'pods-load-modal-listeners', {
	render: LoadModalListeners,
} );
