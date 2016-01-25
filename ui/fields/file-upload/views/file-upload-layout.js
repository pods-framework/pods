/*global jQuery, _, Backbone, Mn, wp, pods_ui */
(function ( $, app ) {
	'use strict';

	app.FileUploadLayout = Mn.LayoutView.extend( {

		template: _.template( $( '#file-upload-layout-template' ).html() ),

		regions: {
			list: '.pods-ui-list',
			form: '.pods-ui-form'
		},

		fieldMeta: {},

		initialize: function () {
			// @todo: abstract this out.  All fields need access to the field meta and individual views shouldn't have to
			// worry about marshalling that data around.
			this.fieldMeta = this.getOption( 'fieldMeta' );

			this.collection = new app.FileUploadCollection( this.getOption( 'modelData' ) );
			this.model = new app.FileUploadModel();
		},

		onShow: function () {
			// @todo: abstract this out.  All fields need access to the field meta and individual views shouldn't have to
			// worry about marshalling that data around.
			var listView = new app.FileUploadList( { collection: this.collection, fieldMeta: this.fieldMeta } );
			var formView = new app.FileUploadForm( { fieldMeta: this.fieldMeta } );

			this.showChildView( 'list', listView );
			this.showChildView( 'form', formView );
		},

		onChildviewRemoveFile: function ( childView ) {
			this.collection.remove( childView.model );
		},

		onChildviewAddFile: function () {
			var uploader = new app.MediaModal( this.fieldMeta[ 'field_options' ] );
			var collection = this.collection;

			// @todo: define a common interface for the method(s) to be called and event(s) to be fired
			$( uploader ).on( 'select', function ( e, data_object ) {
				collection.add( data_object[ 'new_files' ] );
			} );

			uploader.go();
		}

	} );
}( jQuery, pods_ui ) );