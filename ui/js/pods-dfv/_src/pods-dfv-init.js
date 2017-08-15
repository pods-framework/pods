/*global jQuery, _, Backbone, Marionette */
import {PodsDFVFieldModel} from 'pods-dfv/_src/core/pods-field-model';

import * as fields from 'pods-dfv/_src/field-manifest';

// jQuery selector for inline scripts with field definitions
const SCRIPT_TARGET = 'script.pods-dfv-field-data';

// key: FieldClass
const FieldClasses = {
	'file': fields.FileUpload,
	'avatar': fields.FileUpload,
	'pick': fields.Pick
};

/**
 * Custom jQuery plugin to handle Pods MV Fields
 *
 * @param {Object} fields Object to which new fields will be added, in { fieldId: fieldInstance } format
 */
export const PodsDFVInit = function ( fields ) {

	return this.each( function () {
		let data, FieldClass, newField, fieldModel;

		data = { fieldType: '' };

		// Combine data from all in-line data scripts in the container
		jQuery( this ).find( SCRIPT_TARGET ).each( function () {
				const thisData = jQuery.parseJSON( jQuery( this ).html() );
				jQuery.extend( data, thisData );
				jQuery( this ).remove();
			}
		);

		// Ignore anything that doesn't have the field type set
		if ( data.fieldType !== undefined ) {

			// Lookup the class to instantiate by key
			FieldClass = FieldClasses[ data.fieldType ];

			if ( FieldClass !== undefined ) {
				// Assemble the model
				fieldModel = new PodsDFVFieldModel( {
					htmlAttr   : data.htmlAttr,
					fieldConfig: data.fieldConfig
				} );

				newField = new FieldClass( {
					el           : this,
					model        : fieldModel,
					fieldItemData: data.fieldItemData
				} );

				// Render the field, stash a reference, trigger an event for the outside world
				newField.render();
				fields[ data.htmlAttr.id ] = newField;
				jQuery( this ).trigger( 'render' );
			}
		}
	} );
};