import { PodsGbModalListener } from 'dfv/src/core/gb-modal-listener';
import * as models from 'dfv/src/config/model-manifest';
import FIELD_MAP from 'dfv/src/fields/field-map';

import { select } from '@wordpress/data';

// Loads data from an object in this script tag.
const SCRIPT_TARGET = 'script.pods-dfv-field-data';

window.PodsDFV = {
	fields: FIELD_MAP,
	models,
	fieldInstances: {},

	/**
	 * Initialize Pod data.
	 */
	init() {
		// Find all in-line data scripts
		const dataTags = [ ...document.querySelectorAll( SCRIPT_TARGET ) ];

		dataTags.forEach( ( tag ) => {
			const data = JSON.parse( tag.innerHTML );

			// Kludge to disable the "Add New" button if we're inside a media modal.  This should
			// eventually be ironed out so we can use Add New from this context (see #4864)
			if ( tag.closest( '.media-modal-content' ) ) {
				data.fieldConfig.pick_allow_add_new = 0;
			}

			// Ignore anything that doesn't have the field type set
			if ( data.fieldType === undefined ) {
				return;
			}

			const field = FIELD_MAP[ data.fieldType ];

			// @todo remove this later
			// We need to only depend on the `config` and `fieldType`
			// properties, so discard the others for now, until they're
			// removed from the API.
			const actualData = {
				config: window.podsAdminConfig || window.podsDFVConfig,
				fieldType: data.fieldType,
				fieldEmbed: data.fieldEmbed || false,
				data,
			};

			// eslint-disable-next-line no-console
			console.log( 'config data:', actualData );

			if ( field !== undefined ) {
				field.renderer( field.fieldComponent, tag.parentNode, actualData );
			}
		} );
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
