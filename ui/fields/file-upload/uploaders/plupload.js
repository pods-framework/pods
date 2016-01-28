/*global jQuery, _, Backbone, Mn, wp, plupload, pods_ui */
(function ( $, app ) {
	'use strict';

	app.Plupload = app.PodsFileUploader.extend( {
		plupload: {},

		/**
		 * plupload needs references to a couple of elements already in the DOM
		 */
		initialize: function () {
			this.listenTo( this.main_layout, 'attached:view', this.onAttachedView );
		},

		onAttachedView: function ( layoutView ) {
			var button = layoutView.$el.find( '.pods-file-add' );

			if ( button.length > 0 ) {
				this.field_options.plupload_init.browse_button = button[ 0 ];
				this.plupload = new plupload.Uploader( this.field_options.plupload_init );
				this.plupload.init();
			}
		},

		// @todo:
		// This should never be called for plupload as it intercepts the button click event itself
		invoke: function () {

			this.plupload.bind( 'FilesAdded', function ( up, files ) {
				$.each( files, function ( index, file ) {
					// @todo
				} );

				up.refresh();
				up.start();
			} );
		}

	} );

}( jQuery, pods_ui ) );