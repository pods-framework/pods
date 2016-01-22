/*global jQuery, _, Backbone, Mn, pods_ui */
(function ( $, app ) {
	'use strict';

	app.FileUploadForm = Mn.LayoutView.extend( {

		tagName : 'div',

		ui: {
			button: '.pods-file-add'
		},

		template: _.template( $( '#file-upload-form-template' ).html() ),

		fieldMeta: {},

		triggers: {
			'click @ui.button': 'add:file'
		},

		modelEvents: {
			change: 'render'
		}

	} );

}( jQuery, pods_ui ) );