/*global jQuery, _, Backbone, Mn */
import * as fieldClasses from './pods-mv-fields-manifest';
import { PodsFieldModel } from './core/pods-field-model';
import { FileUploadCollection } from './file-upload/models/file-upload-model';
import { RelationshipCollection } from './pick/models/relationship-model';

/**
 * @param {string} fieldType
 */
const fieldFactory = function ( fieldType ) {
	let field = {};

	switch ( fieldType ) {
		case 'file-upload':
			field.control = fieldClasses.FileUpload;
			field.collection = FileUploadCollection;
			break;

		case 'pick':
			field.control = fieldClasses.Pick;
			field.collection = RelationshipCollection;
			break;
	}

	return field;
};

/**
 * Custom jQuery plugin to handle Pods Fields
 *
 * @param {Object} fields Object to which new fields will be added, in { fieldId: fieldInstance } format
 */
export const podsFieldsInit = function ( fields ) {

	return this.each( function () {
		let data = {}, fieldModel, fieldId, fieldControl, field;

		// Combine data from all in-line data scripts in the container
		jQuery( this ).find( 'script.pods-mv-field-data' ).each( function () {
				const thisData = jQuery.parseJSON( jQuery( this ).html() );
				jQuery.extend( data, thisData );
				jQuery( this ).remove();
			}
		);

		// Ignore anything that doesn't have the field type set
		if ( data[ 'field_type' ] !== undefined ) {

			fieldControl = fieldFactory( data[ 'field_type' ] );
			if ( fieldControl.control !== undefined ) {
				fieldModel = new PodsFieldModel( {
					type      : data[ 'field_type' ],
					attributes: data[ 'field_meta' ][ 'field_attributes' ],
					options   : data[ 'field_meta' ][ 'field_options' ]
				} );

				fieldId = data[ 'field_meta' ][ 'field_attributes' ].id;

				field = new fieldControl.control( {
					el        : this,
					model     : fieldModel,
					collection: new fieldControl.collection( data[ 'model_data' ] )
				} );
				field.render();
				fields[ fieldId ] = field;
			}
		}
	} );
};