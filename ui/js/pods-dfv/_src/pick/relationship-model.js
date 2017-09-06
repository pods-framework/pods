/*global jQuery, _, Backbone, Marionette */

/**
 *
 */
export const RelationshipModel = Backbone.Model.extend( {
	defaults: {
		'id'           : 0,
		'name'         : '',
		'icon'         : '',
		'use_dashicon' : false,
		'link'         : '',
		'edit_link'    : '',
		'selected'     : false
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
	filterBySelected: function () {

		// Get an array with only the selected items
		const filtered = this.filter( function ( itemModel ) {
			return ( itemModel.get( 'selected' ) );
		} );

		// this.filter is going to return an array, so create a collection out of it
		return new RelationshipCollection( filtered );
	},

	/**
	 * Return a new collection containing just the unselected items in this one
	 *
	 * @returns {*}
	 */
	filterByUnselected: function () {

		// Get an array with only the unselected items
		const filtered = this.filter( function ( itemModel ) {
			return !( itemModel.get( 'selected' ) );
		} );

		// this.filter is going to return an array, so create a collection out of it
		return new RelationshipCollection( filtered );
	}

} );

