/*global jQuery, _, Backbone, Mn, wp, pods_ui */
(function ( $, app ) {
	'use strict';

	// @todo: last vestiges of knowledge about any specific uploaders?
	var PLUPLOAD_UPLOADER = 'plupload';

	app.FileUploadLayout = Mn.LayoutView.extend( {
		template: _.template( $( '#file-upload-layout-template' ).html() ),

		regions: {
			list     : '.pods-ui-file-list',
			ui_region: '.pods-ui-region', // "Utility" container for uploaders to use
			form     : '.pods-ui-form'
		},

		field_meta: {}, // @todo: things to be yanked when we abstract our field data needs

		uploader: {},

		initialize: function () {
			// @todo: abstract this out.  All fields need access to the field meta and individual views shouldn't have to
			// worry about marshalling that data around.
			this.field_meta = this.getOption( 'field_meta' );

			this.collection = new app.FileUploadCollection( this.getOption( 'model_data' ), this.field_meta );
			this.model = new app.FileUploadModel();
		},

		onRender: function () {
			// @todo: abstract this out.  All fields need access to the field meta and individual views shouldn't have to
			// worry about marshalling that data around.
			var listView = new app.FileUploadList( { collection: this.collection, field_meta: this.field_meta } );
			var formView = new app.FileUploadForm( { field_meta: this.field_meta } );

			this.showChildView( 'list', listView );
			this.showChildView( 'form', formView );

			// Setup the uploader and listen for a response event
			this.uploader = this.createUploader();
			this.listenTo( this.uploader, 'added:files', this.onAddedFiles );
		},

		/**
		 * Fired by a remove:file:click trigger in any child view
		 *
		 * @param childView View that was the source of the event
		 */
		onChildviewRemoveFileClick: function ( childView ) {
			this.collection.remove( childView.model );
		},

		/**
		 * Fired by a add:file:click trigger in any child view
		 *
		 * plupload fields should never generate this event as it places a shim over our button and handles the event
		 * internally
		 */
		onChildviewAddFileClick: function () {
			// Invoke the uploader
			this.uploader.invoke();
		},

		/**
		 * Concrete uploader implementations simply need to: this.trigger( 'added:files', new_files )
		 *
		 * @param {Object[]} data An array of model objects to be added
		 */
		onAddedFiles: function ( data ) {
			this.collection.add( data );
		},

		createUploader: function () {
			var options = this.field_meta[ 'field_options' ];
			var Uploader;

			// Determine which uploader object to use
			// @todo: last vestiges of knowledge about any specific uploaders?
			if ( PLUPLOAD_UPLOADER == options[ 'file_uploader' ] ) {
				Uploader = app.Plupload;
			}
			else {
				Uploader = app.MediaModal;
			}

			this.uploader = new Uploader( {
				// We provide regular DOM element for the button
				browse_button: this.getRegion( 'form' ).getEl( '.pods-file-add' ).get(),
				ui_region    : this.getRegion( 'ui_region' ),
				field_options: options
			} );
			return this.uploader;
		}

	} );

}( jQuery, pods_ui ) );