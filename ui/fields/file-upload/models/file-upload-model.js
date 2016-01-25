/*global jQuery, _, Backbone, Mn, pods_ui */
(function ( $, app ) {
	'use strict';

	var UNLIMITED_FILES = 0;

	/**
	 *
	 */
	app.FileUploadModel = Backbone.Model.extend( {
		defaults: {
			'id'      : 0,
			'icon'    : '',
			'name'    : 'undefined',
			'filename': 'undefined',
			'link'    : false
		}
	} );

	/**
	 *
	 */
	app.FileUploadCollection = Backbone.Collection.extend( {
		model: app.FileUploadModel,

		initialize: function () {
			this.listenTo( this, 'add', this.onCollectionAdd );
		},

		onCollectionAdd: function ( model, collection, options ) {
			this.truncateToFileLimit( 1 );
		},

		truncateToFileLimit: function( limit ) {

			if ( limit != UNLIMITED_FILES && this.length > limit ) {

				// Over the item limit, so destroy the top (oldest) item, LIFO-style
				this.remove( this.at( 0 ) );
			}
		}

	} );

}( jQuery, pods_ui ) );