/*global jQuery, _, Backbone, Mn, wp */
import * as templateImport from '~/ui/fields-mv/_src/file-upload/views/file-upload-queue.html';
const template = templateImport.default || templateImport; // Currently two differnt style string importers for build and test

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
export const FileUploadQueueItem = Mn.LayoutView.extend( {
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
export const FileUploadQueue = Mn.CollectionView.extend( {
	tagName: 'ul',

	className: 'pods-flex-list pods-flex-queue',

	childView: FileUploadQueueItem
} );
