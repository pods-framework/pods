/*global jQuery, _, Backbone, PodsMn */
/**
 *
 */
export const FileUploadModel = Backbone.Model.extend( {
	defaults: {
		'id': 0,
		'icon': '',
		'name': '',
		'edit_link': '',
		'link': '',
		'download': ''
	}
} );

/**
 *
 */
export const FileUploadCollection = Backbone.Collection.extend( {
	model: FileUploadModel
} );
