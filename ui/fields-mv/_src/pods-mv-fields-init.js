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
 * Custom jQuery plugin to handle Pods MV Fields
 *
 * @param {Object} fields Object to which new fields will be added, in { fieldId: fieldInstance } format
 */
export const podsMVFieldsInit = function ( fields ) {

	return this.each( function () {
		let data, FieldClass, newField;

		data = {
			fieldType  : '',
			htmlAttr   : {},
			fieldConfig: {},
			fieldData  : {}
		};

		// Combine data from all in-line data scripts in the container
		jQuery( this ).find( SCRIPT_TARGET ).each( function () {
				const thisData = jQuery.parseJSON( jQuery( this ).html() );
				jQuery.extend( data, thisData );
				jQuery( this ).remove();
			}
		);

		// Ignore anything that doesn't have the field type set
		if ( data.fieldType !== undefined ) {

			FieldClass = FieldClasses[ data.fieldType ];
			if ( FieldClass !== undefined ) {
				newField = new FieldClass( {
					el         : this,
					htmlAttr   : data.htmlAttr,
					fieldConfig: data.fieldConfig,
					fieldData  : data.fieldData
				} );

				// Render the field, stash a reference, trigger an event for the outside world
				newField.render();
				fields[ data.htmlAttr.id ] = newField;
				jQuery( this ).trigger( 'render' );
			}
		}
	} );
};