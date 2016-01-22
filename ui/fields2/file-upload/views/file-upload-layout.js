/*global jQuery, _, Backbone, Mn, pods_ui */
(function ( $, app ) {
	'use strict';

	app.FileUploadLayout = Mn.LayoutView.extend( {

		template: _.template( $( '#file-upload-layout-template' ).html() ),

		regions: {
			list: '.pods-ui-list',
			form: '.pods-ui-form'
		},

		collectionEvents: {
			add: 'itemAdded'
		},

		modelData: {},
		fieldMeta: {},

		initialize: function () {
			this.modelData = this.getOption( 'modelData' );
			this.fieldMeta = this.getOption( 'fieldMeta' );

			this.collection = new Backbone.Collection( this.modelData );
			this.model = new app.FileUploadModel();
		},

		onShow: function () {
			var listView = new app.FileUploadList( { collection: this.collection } );
			var formView = new app.FileUploadForm( { model: this.model, fieldMeta: this.fieldMeta } );

			this.showChildView( 'list', listView );
			this.showChildView( 'form', formView );
		},

		onChildviewAddFile: function ( childView ) {
		},

		itemAdded: function () {
		}

	} );

}( jQuery, pods_ui ) );