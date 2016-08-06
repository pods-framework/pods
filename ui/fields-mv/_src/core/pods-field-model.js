/*global jQuery, _, Backbone, Marionette */
export const PodsFieldModel = Backbone.Model.extend( {
	defaults: {
		type      : 'hidden',
		attributes: {},
		options   : {}
	}
} );