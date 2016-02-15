/*global jQuery, _, Backbone, Mn */
const app = {
	fields: {}
};
export default app;

import { FileUpload } from '../fields/file-upload/src/file-upload';

// @todo: just here for testing the file upload queue, long term solution needed to expose things
import * as Queue from '../fields/file-upload/src/views/file-upload-queue';
app.Queue = Queue;

jQuery( function ( $ ) {
	'use strict';

	const PODS_UI_FIELDS = '.pods-ui-field';
	const SCRIPT_TARGETS = 'script.data';

	/**
	 * @param container
	 * @param data
	 */
	const field_factory = function ( container, data ) {
		var field_control;

		switch ( data.field_type ) {

			case 'file-upload':
				field_control = new FileUpload( {
					el        : container,
					field_meta: data[ 'field_meta' ],
					model_data: data[ 'model_data' ]
				} );
				break;
		}

		return field_control;
	};

	/**
	 *
	 */
	$.fn.pods_ui_field_init = function () {

		return this.each( function () {
			var data = {};
			var field_id;
			var defaults = {
				field_type: 'hidden'
			};

			// Combine data from all in-line data scripts in the container
			$( this ).find( SCRIPT_TARGETS ).each( function () {
					var this_data = $.parseJSON( $( this ).html() );
					$.extend( data, this_data );
					$( this ).remove();
				}
			);

			// Merge inline data with the defaults and startup the new control
			data = $.extend( defaults, data );
			field_id = data[ 'field_meta' ][ 'field_attributes' ][ 'id' ];
			app.fields[ field_id ] = field_factory( this, data );
			app.fields[ field_id ].render();
		} );

	};

	// Go
	$( PODS_UI_FIELDS ).pods_ui_field_init();

} );