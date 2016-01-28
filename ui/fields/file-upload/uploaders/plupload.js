/*global jQuery, _, Backbone, Mn, wp, plupload, pods_ui */
(function ( $, app ) {
	'use strict';

	app.Plupload = app.PodsFileUploader.extend( {
		plupload: {},

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

		go: function () {

			console.log( 'go' );
			this.plupload.bind( 'FilesAdded', function ( up, files ) {
				$.each( files, function ( index, file ) {
					console.log( index );
					console.log( file );
					//$( '#pods-form-ui-pods-meta-single-file-1 ul.pods-files-queue' ).show();
				} );

				up.refresh();
				up.start();
			} );
		}

	} );

}( jQuery, pods_ui ) );