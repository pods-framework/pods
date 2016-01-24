/*global jQuery, _, Backbone, Mn, pods_ui */
(function ( $, app ) {
	'use strict';

	app.FileUploadForm = Mn.LayoutView.extend( {

		tagName : 'div',

		ui: {
			button_add: '.pods-file-add'
		},

		template: _.template( $( '#file-upload-form-template' ).html() ),

		triggers: {
			'click @ui.button_add': 'add:file'
		},

		// @todo: abstract this out.  All fields need access to the field meta and individual views shouldn't have to
		// worry about marshalling that data around.
		serializeData: function () {
			var data = {};

			data.attributes = this.options.fieldMeta[ 'field_attributes' ];
			data.options = this.options.fieldMeta['field_options'];

			return data;
		}

	} );

}( jQuery, pods_ui ) );