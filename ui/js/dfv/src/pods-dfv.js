/**
 * External dependencies
 */
import React from 'react';
import ReactDOM from 'react-dom';
import { omit } from 'lodash';

/**
 * WordPress dependencies
 */
import {
	select,
	dispatch,
} from '@wordpress/data';

/**
 * Pods dependencies
 */
import { initPodStore } from 'dfv/src/admin/edit-pod/store/store';
import PodsDFVApp from 'dfv/src/core/pods-dfv-app';
import { PodsGbModalListener } from 'dfv/src/core/gb-modal-listener';
import * as models from 'dfv/src/config/model-manifest';

import { STORE_KEY_DFV } from 'dfv/src/admin/edit-pod/store/constants';
import FIELD_MAP from 'dfv/src/fields/field-map';

// Loads data from an object in this script tag.
const SCRIPT_TARGET = 'script.pods-dfv-field-data';

window.PodsDFV = {
	fields: FIELD_MAP,
	models,

	/**
	 * Initialize Pod data.
	 */
	init() {
		// Find all in-line data scripts
		const dataTags = [ ...document.querySelectorAll( SCRIPT_TARGET ) ];

		const fieldsData = dataTags.map( ( tag ) => {
			const data = JSON.parse( tag.innerHTML );

			// eslint-disable-next-line no-console
			console.log( 'parsed JSON field data:', data );

			// Ignore anything that doesn't have the field type set
			if ( ! data.fieldType ) {
				return undefined;
			}

			// @todo does this need to be included?
			// const globalConfig: window.podsAdminConfig || window.podsDFVConfig;

			// Clean up the field config.
			// Includes a kludge to disable the "Add New" button if we're inside a media modal.  This should
			// eventually be ironed out so we can use Add New from this context (see #4864)
			const cleanedFieldConfig = omit(
				( data.fieldConfig || {} ),
				[ '_field_object', 'output_options', 'item_id' ]
			);

			if ( tag.closest( '.media-modal-content' ) ) {
				cleanedFieldConfig.fieldConfig.pick_allow_add_new = '0';
			}

			cleanedFieldConfig.htmlAttr = data.htmlAttr || {};
			cleanedFieldConfig.fieldEmbed = data.fieldEmbed || false;

			return {
				fieldComponent: FIELD_MAP[ data.fieldType ]?.fieldComponent || null,
				parentNode: tag.parentNode,
				fieldConfig: cleanedFieldConfig,
				fieldItemData: data.fieldItemData,
			};
		} );

		// Create the store if it hasn't been done already.
		// The initial values for the data store require some massaging:
		// Some are arrays when we need single values (this may change once
		// repeatable fields are implemented), others have special requirements.
		const initialValues = fieldsData.reduce(
			( accumulator, currentValue ) => {
				const fieldConfig = currentValue.fieldConfig || {};

				// Fields have values provided as arrays, even if the field
				// type should just have a singular value.
				const value = [ 'avatar', 'file', 'pick' ].includes( fieldConfig.type )
					? ( currentValue.fieldItemData || fieldConfig.default || [] )
					: ( currentValue.fieldItemData?.[ 0 ] || fieldConfig.default || '' );

				// Some field types need the value to be adjusted because it's in a different
				// shape than expected.
				// @todo there are probably others?
				let formattedValue = value;

				switch ( fieldConfig.type ) {
					case 'pick':
						if ( 'multi' === fieldConfig.format_type ) {
							formattedValue = value
								.map( ( option ) => option.selected ? option.id : null )
								.filter( ( option ) => null !== option );
						} else {
							formattedValue = value.find( ( option ) => true === option.selected )?.id;
						}
						break;
					default:
						break;
				}

				return {
					...accumulator,
					[ fieldConfig.name ]: formattedValue,
				};
			},
			{}
		);

		if ( ! select( STORE_KEY_DFV ) ) {
			initPodStore(
				window.podsAdminConfig || window.podsDFVConfig || {},
				initialValues,
			);
		}

		// Creates a container for the React app to "render",
		// although it doesn't actually render anything in the container,
		// but places the fields in the correct places with Portals.
		const dfvRootContainer = document.createElement( 'div' );
		dfvRootContainer.id = 'pods-dfv-container';
		document.body.appendChild( dfvRootContainer );

		// Set up the DFV app.
		ReactDOM.render(
			<PodsDFVApp fieldsData={ fieldsData } />,
			dfvRootContainer
		);
	},

	isModalWindow() {
		return ( -1 !== location.search.indexOf( 'pods_modal=' ) );
	},

	isGutenbergEditorLoaded() {
		return ! select( 'core/editor' );
	},
};

/**
 * Kick everything off on DOMContentLoaded
 */
document.addEventListener( 'DOMContentLoaded', () => {
	window.PodsDFV.init();

	// Load the Gutenberg modal listener if we're inside a Pods modal with Gutenberg active
	if ( window.PodsDFV.isModalWindow() && window.PodsDFV.isGutenbergEditorLoaded() ) {
		PodsGbModalListener.init();
	}
} );
