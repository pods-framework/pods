/*global jQuery, _, Backbone, Marionette */
import * as fields from '~/ui/fields-mv/_src/field-manifest';

// jQuery selector for inline scripts with field definitions
const SCRIPT_TARGET = 'script.pods-mv-field-data';

// key: FieldClass
const FieldClasses = {
	'file-upload': fields.FileUpload,
	'pick'       : fields.Pick
};

/**
 * Custom jQuery plugin to handle Pods Fields
 *
 * @param {Object} fields Object to which new fields will be added, in { fieldId: fieldInstance } format
 */
export const podsMVFieldsInit = function ( fields ) {

	return this.each( function () {
		let data, fieldModel, FieldClass, newField;

		data = {
			field_type: '',
			field_meta: {
				field_attributes: {},
				field_options   : {}
			},
			model_data: {}
		};

		// Combine data from all in-line data scripts in the container
		jQuery( this ).find( SCRIPT_TARGET ).each( function () {
				const thisData = jQuery.parseJSON( jQuery( this ).html() );
				jQuery.extend( data, thisData );
				jQuery( this ).remove();
			}
		);

		// Ignore anything that doesn't have the field type set
		if ( data.field_type !== undefined ) {

			FieldClass = FieldClasses[ data.field_type ];
			if ( FieldClass !== undefined ) {
				newField = new FieldClass( {
					el         : this,
					htmlAttr   : data.field_meta.field_attributes,
					fieldConfig: data.field_meta.field_options,
					fieldData  : data.model_data
				} );

				// Render the field, stash a reference, trigger an event for the outside world
				newField.render();
				fields[ data.field_meta.field_attributes.id ] = newField;
				jQuery( this ).trigger( 'render' );
			}
		}
	} );
};