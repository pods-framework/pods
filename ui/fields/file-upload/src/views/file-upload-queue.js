/*global jQuery, _, Backbone, Mn */
const $ = jQuery;

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
