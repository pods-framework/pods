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
				fieldConfig: directRender ? undefined : cleanedFieldConfig,
				fieldItemData: data.fieldItemData || null,
				fieldValue: data.fieldValue || null,
			};
		} );

		// Filter out any that we skipped.
		const validFieldsData = fieldsData.filter( ( fieldData ) => !! fieldData );

		// Create the store if it hasn't been done already.
		// The initial values for the data store require some massaging:
		// Some are arrays when we need single values (this may change once
		// repeatable fields are implemented), others have special requirements.
		const initialValues = validFieldsData.reduce(
			( accumulator, currentField ) => {
				const fieldConfig = currentField.fieldConfig || {};

				// "Boolean Group" fields are actually comprised of other fields with their own
				// named values, so instead of just one key/value, they'll have multiple ones.
				// These are handled very differently, so process them and return early.
				if ( 'boolean_group' === fieldConfig.type ) {
					const values = {};

					fieldConfig.boolean_group.forEach( ( groupItem ) => {
						if ( ! groupItem.name ) {
							return;
						}

						// Apply defaults if we're on the Edit Pod screen.
						if ( isEditPodScreen && undefined === currentField.fieldItemData?.[ groupItem.name ] ) {
							values[ groupItem.name ] = groupItem.default || '';
						} else {
							values[ groupItem.name ] = currentField.fieldItemData?.[ groupItem.name ];
						}
					} );

					return {
						...accumulator,
						...values,
					};
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

				return {
					...accumulator,
					[ fieldConfig.name ]: valueOrDefault,
				};
			},
			{}
		);

		// eslint-disable-next-line no-console
		console.log( 'Pods init with initial values:', initialValues );

		// The Edit Pod screen gets a different store set up than
		// other contexts.
		let storeKey = null;

		if ( isEditPodScreen ) {
			storeKey = initEditPodStore( window.podsAdminConfig );
		} else if ( window.podsDFVConfig ) {
			storeKey = initPodStore( window.podsDFVConfig, initialValues );
		} else {
			// Something is wrong if neither set of globals is set.
			return;
		}

		// Creates a container for the React app to "render",
		// although it doesn't actually render anything in the container,
		// but places the fields in the correct places with Portals.
		const dfvRootContainer = document.createElement( 'div' );
		dfvRootContainer.class = 'pods-dfv-container';

		document.body.appendChild( dfvRootContainer );

		// Set up the DFV app.
		ReactDOM.render(
			<PodsDFVApp fieldsData={ validFieldsData } storeKey={ storeKey } />,
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
