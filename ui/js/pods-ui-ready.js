/*global jQuery, _, Backbone, Mn */

// @todo: just here for testing the file upload queue
import * as Queue from '../fields/file-upload/src/views/file-upload-queue';
import * as fieldClasses from './pods-ui-field-manifest';

import { PodsFieldModel } from '../fields/core/pods-field-model';
import { FileUploadCollection } from '../fields/file-upload/src/models/file-upload-model';
import { RelationshipCollection } from '../fields/pick/src/models/relationship-model';

const app = {
	fieldClasses: fieldClasses,
	fields      : {},
	Queue       : Queue
};
export default app;

/**
 * This is the workhorse that currently kicks everything off
 */
jQuery( function () {
	jQuery( '.pods-form-ui-field' ).pods_ui_field_init();
} );

/**
 * Custom jQuery plugin to handle Pods Fields
 */
jQuery.fn.pods_ui_field_init = function () {

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
				app.fields[ field_id ] = field;
			}
		}
	} );
};

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