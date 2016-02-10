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
	model: FileUploadModel,

	field_meta: {},

	initialize: function ( models, field_meta ) {
		this.field_meta = field_meta || {};
		this.field_meta.field_options = this.field_meta.field_options || {};

		// add() will always be called once per model in the collection
		this.listenTo( this, 'add', this.onCollectionAdd );
	},

	onCollectionAdd: function ( model, collection, options ) {
		this.truncateToFileLimit( this.field_meta.field_options.file_limit );
	},

	truncateToFileLimit: function ( limit ) {
		var first_model;

		if ( limit != 0 && this.length > limit ) {

			// We've gone over the item limit, so destroy the top (oldest) item, LIFO-style
			// Note: calling destroy() directly on the model will send a REST DELETE request, this bypasses that behavior
			first_model = this.at( 0 );
			first_model.trigger( 'destroy', first_model );
		}
	}

} );
