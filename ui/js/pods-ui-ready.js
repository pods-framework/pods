/*global jQuery, _, Backbone, Mn */
const $ = jQuery;
import { FileUpload } from '../fields/file-upload/src/file-upload';

const app = {
	fields: {}
};
export default app;

// @todo: just here for testing the file upload queue, long term solution needed to expose things
import * as Queue from '../fields/file-upload/src/views/file-upload-queue';
app.Queue = Queue;

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
		let data = {}, field_id, field;
		let defaults = {
			field_type: 'hidden'
		};

		// Combine data from all in-line data scripts in the container
		$( this ).find( 'script.data' ).each( function () {
				var this_data = $.parseJSON( $( this ).html() );
				$.extend( data, this_data );
				$( this ).remove();
			}
		);

		// Merge inline data with the defaults and startup the new control
		data = $.extend( defaults, data );
		field = field_factory( data.field_type );

		if ( field !== undefined ) {
			field_id = data.field_meta[ 'field_attributes' ].id;

			app.fields[ field_id ] = new field( {
				el        : this,
				field_meta: data[ 'field_meta' ],
				model_data: data[ 'model_data' ]
			} );
			app.fields[ field_id ].render();
		}
	} );
};

/**
 * @param {string} field_type
 */
const field_factory = function ( field_type ) {
	let field_control;

	switch ( field_type ) {
		case 'file-upload':
			field_control = FileUpload;
			break;

		case 'pick':
			// field_control = Pick
			break;

		default:
			//field_control = Hidden;
			break;
	}

	return field_control;
};