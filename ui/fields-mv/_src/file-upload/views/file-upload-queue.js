/*global jQuery, _, Backbone, Marionette, wp */
import template from '~/ui/fields-mv/_src/file-upload/views/file-upload-queue.html';

export const FileUploadQueueModel = Backbone.Model.extend( {
	defaults: {
		id       : 0,
		filename : '',
		progress : 0,
		errorMsg: ''
	}
} );

/**
 *
 */
export const FileUploadQueueItem = Marionette.LayoutView.extend( {
	model: FileUploadQueueModel,

	tagName: 'li',

	template: _.template( template ),

	attributes: function () {
		return {
			class: 'pods-flex-item',
			id   : this.model.get( 'id' )
		};
	},

	modelEvents: {
		'change': 'onModelChanged'
	},

	onModelChanged: function () {
		this.render();
	}

} );

/**
 *
 */
export const FileUploadQueue = Marionette.CollectionView.extend( {
	tagName: 'ul',

	className: 'pods-flex-list pods-flex-queue',

	childView: FileUploadQueueItem
} );
