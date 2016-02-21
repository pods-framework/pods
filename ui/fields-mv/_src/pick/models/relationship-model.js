/*global jQuery, _, Backbone, Mn */

/**
 *
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
 *
 */
export const RelationshipCollection = Backbone.Collection.extend( {
	model: RelationshipModel,

	/**
	 *
	 * @param { ?string[] } ids
	 */
	setSelected: function ( ids ) {
		this.map( function ( thisModel ) {
			const selected = _.contains( ids, thisModel.get( 'id' ) + '' );
			thisModel.set( 'selected', selected );
		} );
	},

	/**
	 * Return a new collection containing just the selected items in this one
	 *
	 * @returns {*}
	 */
	filterSelected: function () {

		// Get an array with only the selected items
		const filtered = this.filter( function ( item_model ) {
			return ( item_model.get( 'selected' ) );
		} );

		// this.filter is going to return an array, so create a collection out of it
		return new RelationshipCollection( filtered );
	}
} );

