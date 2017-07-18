/*global jQuery, _, Backbone, Marionette, wp */
import template from 'pods-dfv/_src/file-upload/views/file-upload-queue.html';

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
export const FileUploadQueueItem = Marionette.View.extend( {
	model: FileUploadQueueModel,

	tagName: 'li',

	template: _.template( template ),

	attributes: function () {
		return {
			class: 'pods-dfv-list-item',
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

	className: 'pods-dfv-list pods-dfv-list-queue',

	childView: FileUploadQueueItem
} );
