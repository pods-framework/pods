/**
 * External dependencies
 */
import React from 'react';
import ReactDOM from 'react-dom';
import { omit } from 'lodash';

/**
 * WordPress dependencies
 */
import { select } from '@wordpress/data';

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
				cleanedFieldConfig.fieldConfig.pick_allow_add_new = '0';
			}

			cleanedFieldConfig.htmlAttr = data.htmlAttr || {};
			cleanedFieldConfig.fieldEmbed = data.fieldEmbed || false;

			return {
				directRender,
				fieldComponent: FIELD_MAP[ data.fieldType ]?.fieldComponent || null,
				parentNode: tag.parentNode,
				fieldConfig: directRender ? undefined : cleanedFieldConfig,
				fieldItemData: data.fieldItemData || null,
			};
		} );

		// Filter out any that we skipped.
		const validFieldsData = fieldsData.filter( ( fieldData ) => !! fieldData );

		// Create the store if it hasn't been done already.
		// The initial values for the data store require some massaging:
		// Some are arrays when we need single values (this may change once
		// repeatable fields are implemented), others have special requirements.
		const initialValues = validFieldsData.reduce(
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

		// The Edit Pod screen gets a different store set up than
		// other contexts.
		if ( window.podsAdminConfig ) {
			initEditPodStore( window.podsAdminConfig );
		} else if ( window.podsDFVConfig ) {
			initPodStore( window.podsDFVConfig, initialValues );
		} else {
			// Something is wrong if neither set of globals is set.
			return;
		}

		// Creates a container for the React app to "render",
		// although it doesn't actually render anything in the container,
		// but places the fields in the correct places with Portals.
		const dfvRootContainer = document.createElement( 'div' );
		dfvRootContainer.id = 'pods-dfv-container';
		document.body.appendChild( dfvRootContainer );

		// Set up the DFV app.
		ReactDOM.render(
			<PodsDFVApp fieldsData={ validFieldsData } />,
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
