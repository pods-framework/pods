/*global jQuery, _, Backbone, Mn */
import * as fieldClasses from './pods-ui-field-manifest';
import { PodsFieldModel } from './core/pods-field-model';
import { FileUploadCollection } from './file-upload/models/file-upload-model';
import { RelationshipCollection } from './pick/models/relationship-model';

/**
 * Custom jQuery plugin to handle Pods Fields
 *
 * @param {Object} fields Object to which new fields will be added, in { field_id: fieldInstance } format
 */
export const podsFieldsInit = function ( fields ) {

	/**
	 * @param {string} field_type
	 */
	const field_factory = function ( field_type ) {
		let field = {};

		switch ( field_type ) {
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

	return this.each( function () {
		let data = {}, field_model, field_id, field_control, field;

		// Combine data from all in-line data scripts in the container
		jQuery( this ).find( 'script.data' ).each( function () {
				var this_data = jQuery.parseJSON( jQuery( this ).html() );
				jQuery.extend( data, this_data );
				jQuery( this ).remove();
			}
		);

		// Ignore anything that doesn't have the field type set
		if ( data[ 'field_type' ] !== undefined ) {

			field_control = field_factory( data[ 'field_type' ] );
			if ( field_control.control !== undefined ) {
				field_model = new PodsFieldModel( {
					type      : data[ 'field_type' ],
					attributes: data.field_meta[ 'field_attributes' ],
					options   : data.field_meta[ 'field_options' ]
				} );

				field_id = data.field_meta[ 'field_attributes' ].id;

				field = new field_control.control( {
					el        : this,
					model     : field_model,
					collection: new field_control.collection( data[ 'model_data' ] )
				} );
				field.render();
				fields[ field_id ] = field;
			}
		}
	} );
};