/*global jQuery, _, Backbone, Marionette */
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
