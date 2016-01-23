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
		}

	} );

}( jQuery, pods_ui ) );