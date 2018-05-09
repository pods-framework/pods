/*global jQuery, _, Backbone, Marionette */
import { PodsDFVFieldModel } from 'pods-dfv/_src/core/pods-field-model';
import * as fields from 'pods-dfv/_src/field-manifest';
import * as models from 'pods-dfv/_src/model-manifest';

const INIT_TARGETS = '.pods-form-ui-field';           // Where to look for scripts
const SCRIPT_TARGET = 'script.pods-dfv-field-data';   // What scripts to look for

// key: FieldClass
const fieldClasses = {
	'file': fields.FileUpload,
	'avatar': fields.FileUpload,
	'pick': fields.Pick
};

const PodsDFV = {
	fields: fields,
	models: models,
	fieldInstances: {},

	/**
	 *
	 */
	init: function () {
		let self = this;

		// Loop through any targets that may contain scripts
		jQuery( INIT_TARGETS ).each( function () {
			let FieldClass, newField, fieldModel;
			let data = { fieldType: undefined };

			// Combine data from all in-line data scripts in the container
			// and remove the scripts from the page
			jQuery( this ).find( SCRIPT_TARGET ).each( function () {
				const newData = jQuery.parseJSON( jQuery( this ).html() );

				// Kludge to disable the "Add New" button if we're inside a media modal.  This should
				// eventually be ironed out so we can use Add New from this context (see #4864
				if ( jQuery( this ).parents( '.media-modal-content' ).length ) {
					newData.fieldConfig.pick_allow_add_new = 0;
				}

				jQuery.extend( data, newData );
				jQuery( this ).remove();
			} );

			// Ignore anything that doesn't have the field type set
			if ( data.fieldType !== undefined ) {

				// See if we can locate a class to be instantiated by field type
				FieldClass = fieldClasses[ data.fieldType ];
				if ( FieldClass !== undefined ) {

					// Assemble the model and create the field
					fieldModel = new PodsDFVFieldModel( {
						htmlAttr: data.htmlAttr,
						fieldConfig: data.fieldConfig
					} );

					newField = new FieldClass( {
						el: this,
						model: fieldModel,
						fieldItemData: data.fieldItemData
					} );

					// Render the field, trigger an event for the outside world, and stash a reference
					newField.render();
					jQuery( this ).trigger( 'render' );
					self.fieldInstances[ data.htmlAttr.id ] = newField;
				}
			}
		} );
	}
};
export default PodsDFV;

/**
 * Kick everything off on document ready
 */
jQuery( function () {
	PodsDFV.init();
} );
