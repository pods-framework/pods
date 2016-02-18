/*global jQuery, _, Backbone, Mn */
const $ = jQuery;

// @todo: just here for testing the file upload queue
import * as Queue from '../fields/file-upload/src/views/file-upload-queue';
import * as fieldClasses from './pods-ui-field-manifest';

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
jQuery( function ( $ ) {
	$( '.pods-form-ui-field' ).pods_ui_field_init();
} );

/**
 * Custom jQuery plugin to handle Pods Fields
 */
jQuery.fn.pods_ui_field_init = function () {

	return this.each( function () {
		let data = {}, field_id, field_control, field;

		// Combine data from all in-line data scripts in the container
		$( this ).find( 'script.data' ).each( function () {
				var this_data = $.parseJSON( $( this ).html() );
				$.extend( data, this_data );
				$( this ).remove();
			}
		);

		if ( data[ 'field_type' ] !== undefined ) {

			field_control = field_factory( data[ 'field_type' ] );

			if ( field_control.control !== undefined ) {
				field_id = data.field_meta[ 'field_attributes' ].id;
				field = new field_control.control( {
					el        : this,
					collection: new field_control.collection( data[ 'model_data' ] ),
					field_meta: data[ 'field_meta' ]
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
	let field;

	switch ( field_type ) {
		case 'file-upload':
			field = {
				control   : fieldClasses.FileUpload,
				collection: FileUploadCollection
			};
			break;

		case 'pick':
			field = {
				control   : fieldClasses.Pick,
				collection: RelationshipCollection
			};
			break;

		default:
			//field_control = Hidden;
			break;
	}

	return field;
};