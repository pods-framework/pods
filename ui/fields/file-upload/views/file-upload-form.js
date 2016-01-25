/*global jQuery, _, Backbone, Mn, pods_ui */
(function ( $, app ) {
	'use strict';

	app.FileUploadForm = Mn.LayoutView.extend( {

		tagName : 'div',

		ui: {
			add_button: '.pods-file-add'
		},

		template: _.template( $( '#file-upload-form-template' ).html() ),

		triggers: {
			'click @ui.add_button': 'add:file'
		},

		// @todo: abstract this out.  All fields need access to the field meta and individual views shouldn't have to
		// worry about marshalling that data around.
		serializeData: function () {
			var data = {};

			data.attributes = this.options.field_meta[ 'field_attributes' ];
			data.options = this.options.field_meta['field_options'];

			return data;
		}

	} );

}( jQuery, pods_ui ) );