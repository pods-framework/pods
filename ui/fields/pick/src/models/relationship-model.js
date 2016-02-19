/*global jQuery, _, Backbone, Mn */

/**
 * Single item model representing a row of relationship data
 */
export const RelationshipModel = Backbone.Model.extend( {
	defaults: {
		'id'      : 0,
		'name'    : '',
		'icon'    : '',
		'selected': false
	},

	toggleSelected: function () {
		this.set( 'selected', !this.get( 'selected' ) );
	}
} );

/**
 * Relationship item collection
 */
export const RelationshipCollection = Backbone.Collection.extend( {
	model: RelationshipModel,

	// Return a new collection containing just the selected items in this one
	filterSelected: function () {

		// Get an array with only the selected items
		const filtered = this.filter( function ( item_model ) {
			return ( item_model.get( 'selected' ) );
		} );

		// this.filter is going to return an array, so create a collection out of it
		return new RelationshipCollection( filtered );
	}
} );

