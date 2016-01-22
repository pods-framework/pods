/*global jQuery, _, Backbone, Mn, pods_ui */
(function ( $, app ) {
	'use strict';

	app.FileUploadItem = Mn.LayoutView.extend( {
		tagName : 'li',
		className: 'pods-file hidden',
		template: _.template( $( '#file-upload-item-template' ).html() )
	} );

	app.FileUploadList = Mn.CollectionView.extend( {
		tagName  : 'ul',
		className: 'pods-files pods-files-list',
		childView: app.FileUploadItem
	} );

}( jQuery, pods_ui ) );