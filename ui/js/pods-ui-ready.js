/*global jQuery, _, Backbone, Mn */
import { FileUpload } from '../fields/file-upload/src/file-upload';

const app = {
	fields: {}
};
export default app;

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
				field_control.render();
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
		} );

	};

	// Go
	$( PODS_UI_FIELDS ).pods_ui_field_init();

} );