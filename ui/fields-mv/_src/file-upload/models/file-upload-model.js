/*global jQuery, _, Backbone, Mn */
/**
 *
 */
export const FileUploadModel = Backbone.Model.extend( {
	defaults: {
		'id'  : 0,
		'icon': '',
		'name': '',
		'link': ''
	}
} );

/**
 *
 */
export const FileUploadCollection = Backbone.Collection.extend( {
	model: FileUploadModel
} );
