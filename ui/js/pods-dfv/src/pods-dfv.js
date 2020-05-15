import mnRenderer from 'pods-dfv/src/core/renderers/mn-renderer';
import reactRenderer from 'pods-dfv/src/core/renderers/react-renderer';
import reactDirectRenderer from 'pods-dfv/src/core/renderers/react-direct-renderer';
import { PodsGbModalListener } from 'pods-dfv/src/core/gb-modal-listener';

import * as fields from 'pods-dfv/src/field-manifest';
import * as models from 'pods-dfv/src/model-manifest';

// Loads data from an object in this script tag.
const SCRIPT_TARGET = 'script.pods-dfv-field-data';

const fieldClasses = {
	file: {
		FieldClass: fields.File,
		renderer: mnRenderer,
	},
	avatar: {
		FieldClass: fields.File,
		renderer: mnRenderer,
	},
	pick: {
		FieldClass: fields.Pick,
		renderer: mnRenderer,
	},
	text: {
		FieldClass: fields.PodsDFVText,
		renderer: reactRenderer,
	},
	password: {
		FieldClass: fields.PodsDFVPassword,
		renderer: reactRenderer,
	},
	number: {
		FieldClass: fields.PodsDFVNumber,
		renderer: reactRenderer,
	},
	email: {
		FieldClass: fields.PodsDFVEmail,
		renderer: reactRenderer,
	},
	paragraph: {
		FieldClass: fields.PodsDFVParagraph,
		renderer: reactRenderer,
	},
	'edit-pod': {
		FieldClass: fields.PodsDFVEditPod,
		renderer: reactDirectRenderer,
	},
};

window.PodsDFV = {
	fields: fieldClasses,
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

			console.log( data );

			// Kludge to disable the "Add New" button if we're inside a media modal.  This should
			// eventually be ironed out so we can use Add New from this context (see #4864)
			if ( tag.closest( '.media-modal-content' ) ) {
				data.fieldConfig.pick_allow_add_new = 0;
			}

			// Ignore anything that doesn't have the field type set
			if ( data.fieldType === undefined ) {
				return;
			}

			const field = fieldClasses[ data.fieldType ];

			// @todo remove this later
			// We need to only depend on the `config` and `fieldType`
			// properties, so discard the others for now, until they're
			// removed from the API.
			const actualData = {
				config: {
					...data.config,
				},
				fieldType: data.fieldType,
			};

			if ( field !== undefined ) {
				field.renderer( field.FieldClass, tag.parentNode, data );
			}
		} );
	},

	isModalWindow() {
		return ( -1 !== location.search.indexOf( 'pods_modal=' ) );
	},

	isGutenbergEditorLoaded() {
		return ( wp.data !== undefined && wp.data.select( 'core/editor' ) !== undefined );
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
