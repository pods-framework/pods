/*global jQuery, _, Backbone, Mn, wp, pods_ui */
(function ( $, app ) {
	'use strict';

	/**
	 *
	 */
	app.FileUploadQueueItem = Mn.LayoutView.extend( {
		tagName: 'li',

		attributes: function () {
			// Return model data
			return {
				class: 'pods-file',
				id   : this.model.get( 'item_id' )
			};
		},

		template: _.template( $( '#file-upload-queue-template' ).html() )
	} );

	/**
	 *
	 */
	app.FileUploadQueue = Mn.CollectionView.extend( {
		tagName: 'ul',

		className: 'pods-files pods-files-queue',

		childView: app.FileUploadQueueItem
	} );

}( jQuery, pods_ui ) );