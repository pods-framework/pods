import jQuery from 'jquery';
import { mnRenderer } from 'pods-dfv/_src/core/renderers/mn-renderer';
import { reactRenderer } from 'pods-dfv/_src/core/renderers/react-renderer';
import { PodsGbModalListener } from 'pods-dfv/_src/core/gb-modal-listener';
import * as fields from 'pods-dfv/_src/field-manifest';
import * as models from 'pods-dfv/_src/model-manifest';

const INIT_TARGETS = '.pods-form-ui-field';           // Where to look for scripts
const SCRIPT_TARGET = 'script.pods-dfv-field-data';   // What scripts to look for

const fieldClasses =  {
	'file': {
		FieldClass: fields.FileUpload,
		renderer: mnRenderer
	},
	'avatar': {
		FieldClass: fields.FileUpload,
		renderer: mnRenderer
	},
	'pick': {
		FieldClass: fields.Pick,
		renderer: mnRenderer
	},
	'text': {
		FieldClass: fields.PodsDFVText,
		renderer: reactRenderer
	}
};

const PodsDFV = {
	fields: fieldClasses,
	models: models,
	fieldInstances: {},

	/**
	 *
	 */
	init: function () {
		// Loop through any targets that may contain scripts
		jQuery( INIT_TARGETS ).each( function () {
			let data = { fieldType: undefined };

			// Combine data from all in-line data scripts in the container
			// and remove the scripts from the page
			jQuery( this ).find( SCRIPT_TARGET ).each( function () {
				const newData = jQuery.parseJSON( jQuery( this ).html() );

				// Kludge to disable the "Add New" button if we're inside a media modal.  This should
				// eventually be ironed out so we can use Add New from this context (see #4864
				if ( jQuery( this ).parents( '.media-modal-content' ).length ) {
					// eslint-disable-next-line
					newData.fieldConfig.pick_allow_add_new = 0;
				}

				jQuery.extend( data, newData );
				jQuery( this ).remove();
			} );

			// Ignore anything that doesn't have the field type set
			if ( data.fieldType !== undefined ) {
				let field = fieldClasses[ data.fieldType ];

				if ( field !== undefined ) {
					//self.fieldInstances[ data.htmlAttr.id ] = field.renderer( field.fieldClass, data );
					field.renderer( field.FieldClass, this, data );
				}
			}
		} );
	},

	isModalWindow: function () {
		return ( -1 !== location.search.indexOf( 'pods_modal=' ) );
	},

	isGutenbergEditorLoaded: function () {
		return ( wp.data !== undefined && wp.data.select( 'core/editor' ) !== undefined );
	}
};
export default PodsDFV;

/**
 * Kick everything off on DOMContentLoaded
 */
document.addEventListener( 'DOMContentLoaded', () => {
	PodsDFV.init();

	/**
	 * This is temporary duct tape for WordPress 5.0 only to work around a
	 * Gutenberg compatibility bug
	 *
	 * See:
	 *   https://github.com/pods-framework/pods/issues/5197
	 *   https://github.com/WordPress/gutenberg/issues/7176
	 *
	 * @todo Delete this when WP 5.0.1 comes out
	 */
	if ( PodsDFV.isGutenbergEditorLoaded() && window.tinymce ) {
		wp.data.subscribe( function() {
			if ( wp.data.select( 'core/editor' ).isSavingPost() && window.tinymce.editors) {
				for ( let i = 0; i < tinymce.editors.length; i++ ) {
					tinymce.editors[ i ].save();
				}
			}

		} );
	}

	// Load the Gutenberg modal listener if we're inside a Pods modal with Gutenberg active
	if ( PodsDFV.isModalWindow() && PodsDFV.isGutenbergEditorLoaded()) {
		PodsGbModalListener.init();
	}
} );
