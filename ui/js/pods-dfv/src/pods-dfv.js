import jQuery from 'jquery';
import { mnRenderer } from 'pods-dfv/src/core/renderers/mn-renderer';
import { reactRenderer } from 'pods-dfv/src/core/renderers/react-renderer';
import { reactDirectRenderer } from 'pods-dfv/src/core/renderers/react-direct-renderer';
import { PodsGbModalListener } from 'pods-dfv/src/core/gb-modal-listener';
import * as fields from 'pods-dfv/src/field-manifest';
import * as models from 'pods-dfv/src/model-manifest';

const SCRIPT_TARGET = 'script.pods-dfv-field-data';   // What scripts to look for

const fieldClasses =  {
	'file': {
		FieldClass: fields.File,
		renderer: mnRenderer
	},
	'avatar': {
		FieldClass: fields.File,
		renderer: mnRenderer
	},
	'pick': {
		FieldClass: fields.Pick,
		renderer: mnRenderer
	},
	'text': {
		FieldClass: fields.PodsDFVText,
		renderer: reactRenderer
	},
	'password': {
		FieldClass: fields.PodsDFVPassword,
		renderer: reactRenderer
	},
	'number': {
		FieldClass: fields.PodsDFVNumber,
		renderer: reactRenderer
	},
	'email': {
		FieldClass: fields.PodsDFVEmail,
		renderer: reactRenderer
	},
	'paragraph': {
		FieldClass: fields.PodsDFVParagraph,
		renderer: reactRenderer
	},
	'edit-pod': {
		FieldClass: fields.PodsDFVEditPod,
		renderer: reactDirectRenderer
	},
};

window.PodsDFV = {
	fields: fieldClasses,
	models: models,
	fieldInstances: {},

	/**
	 *
	 */
	init: function () {
		// Find all in-line data scripts
		jQuery( SCRIPT_TARGET ).each( function () {
			const parent = jQuery( this ).parent().get( 0 );
			const data = jQuery.parseJSON( jQuery( this ).html() );

			// Kludge to disable the "Add New" button if we're inside a media modal.  This should
			// eventually be ironed out so we can use Add New from this context (see #4864)
			if ( jQuery( this ).parents( '.media-modal-content' ).length ) {
				// eslint-disable-next-line
				data.fieldConfig.pick_allow_add_new = 0;
			}

			// Ignore anything that doesn't have the field type set
			if ( data.fieldType !== undefined ) {
				let field = fieldClasses[ data.fieldType ];

				if ( field !== undefined ) {
					//self.fieldInstances[ data.htmlAttr.id ] = field.renderer( field.fieldClass, data );
					field.renderer( field.FieldClass, parent, data );
				}
			}

			jQuery( this ).remove();
		} );
	},

	isModalWindow: function () {
		return ( -1 !== location.search.indexOf( 'pods_modal=' ) );
	},

	isGutenbergEditorLoaded: function () {
		return ( wp.data !== undefined && wp.data.select( 'core/editor' ) !== undefined );
	}
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
