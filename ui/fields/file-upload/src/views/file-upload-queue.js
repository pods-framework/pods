/*global jQuery, _, Backbone, Mn, wp */
import * as queue_item_template from '../templates/file-upload-queue.html';


export const FileUploadQueueModel = Backbone.Model.extend( {
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
export const FileUploadQueueItem = Mn.LayoutView.extend( {
	model: FileUploadQueueModel,

	tagName: 'li',

	template: _.template( queue_item_template.default ),

	attributes: function () {
		return {
			class: 'pods-file',
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

	className: 'pods-files pods-files-queue',

	childView: FileUploadQueueItem
} );
