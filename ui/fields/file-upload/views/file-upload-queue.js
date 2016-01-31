/*global jQuery, _, Backbone, Mn, wp, pods_ui */
(function ( $, app ) {
	'use strict';

	app.FileUploadQueueModel = Backbone.Model.extend( {
		defaults: {
			id       : 0,
			filename : '',
			progress : 0,
			error_msg: ''
		}
	} );

	/**
	 *
	 */
	app.FileUploadQueueItem = Mn.LayoutView.extend( {
		model: app.FileUploadQueueModel,

		tagName: 'li',

		template: _.template( $( '#file-upload-queue-template' ).html() ),

		attributes: function () {
			return {
				class: 'pods-file',
				id   : this.model.get( 'id' )
			};
		},

		modelEvents: {
			'change': 'onModelChanged'
		},

		onModelChanged: function() {
			this.render();
		}

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