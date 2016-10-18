/*global jQuery, _, Backbone, Marionette */
import * as fields from '~/ui/fields-mv/_src/field-manifest';
import {PodsFieldModel} from '~/ui/fields-mv/_src/core/pods-field-model';

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
				fieldModel = new PodsFieldModel( {
					attributes: data.field_meta.field_attributes,
					options   : data.field_meta.field_options
				} );

				newField = new FieldClass( {
					el        : this,
					model     : fieldModel,
					collection: data.model_data
				} );

				newField.render();
				fields[ data.field_meta.field_attributes.id ] = newField;
				jQuery( this ).trigger( 'render' );
			}
		}
	} );
};