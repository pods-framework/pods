/*global jQuery, _, Backbone, Mn */
/**
 *
 */
export const FileUploadModel = Backbone.Model.extend( {
	defaults: {
		'id'       : 0,
		'icon'     : null,
		'name'     : '',
		'edit_link': '',
		'link'     : '',
		'download' : ''
	}
} );

/**
 *
 */
export const FileUploadCollection = Backbone.Collection.extend( {
	model: FileUploadModel
} );
