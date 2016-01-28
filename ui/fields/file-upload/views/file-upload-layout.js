/*global jQuery, _, Backbone, Mn, wp, pods_ui */
(function ( $, app ) {
	'use strict';

	var PLUPLOAD_UPLOADER = 'plupload';

	app.FileUploadLayout = Mn.LayoutView.extend( {

		template: _.template( $( '#file-upload-layout-template' ).html() ),

		regions: {
			list: '.pods-ui-list',
			form: '.pods-ui-form'
		},

		field_meta: {},

		uploader: {},

		childEvents: {
			// This callback will be called whenever a child is attached or emits a `attach` event
			attach: function ( layoutView ) {
				this.trigger( 'attached:view', layoutView );
			}
		},

		setUploader: function () {
			var options = this.field_meta[ 'field_options' ];
			var Uploader;

			// Determine which uploader object to use
			if ( PLUPLOAD_UPLOADER == options[ 'file_uploader' ] ) {
				Uploader = app.Plupload;
			}
			else {
				Uploader = app.MediaModal;
			}

			this.uploader = new Uploader( this, options );
			return this.uploader;
		},

		initialize: function () {
			// @todo: abstract this out.  All fields need access to the field meta and individual views shouldn't have to
			// worry about marshalling that data around.
			this.field_meta = this.getOption( 'field_meta' );

			this.collection = new app.FileUploadCollection( this.getOption( 'model_data' ), this.field_meta );
			this.model = new app.FileUploadModel();

			// Setup the uploader and listen for add:files events
			this.uploader = this.setUploader();
			this.listenTo( this.uploader, 'added:files', this.onAddFiles );
		},

		onShow: function () {
			// @todo: abstract this out.  All fields need access to the field meta and individual views shouldn't have to
			// worry about marshalling that data around.
			var listView = new app.FileUploadList( { collection: this.collection, field_meta: this.field_meta } );
			var formView = new app.FileUploadForm( { field_meta: this.field_meta } );

			this.showChildView( 'list', listView );
			this.showChildView( 'form', formView );
		},

		onChildviewRemoveFileClick: function ( childView ) {
			this.collection.remove( childView.model );
		},

		onChildviewAddFileClick: function () {
			// Invoke the uploader
			this.uploader.go();
		},

		onAddedFiles: function ( data ) {
			this.collection.add( data );
		}

	} );

}( jQuery, pods_ui ) );