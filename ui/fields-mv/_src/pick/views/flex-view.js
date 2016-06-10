/*global jQuery, _, Backbone, Mn, wp */
import * as flexTemplate from './flex-item.html';
import {PodsFieldListView, PodsFieldView} from '../../../_src/core/pods-field-views';

/**
 *
 */
export const FlexItem = PodsFieldView.extend( {
	tagName  : 'li',

	className: 'pods-flex-item pods-relationship',

	template : _.template( flexTemplate.default ),

	events: {
		'click .pods-flex-remove a': 'delete_item_click'
	},

	/*
	render: function () {
		var $el = this.$el;

		$el.empty();
		$el.attr( 'id', this.model.get( 'id' ) );
		$el.html( this.template( this.model.attributes ) );

		return this; // for chainable calls, like .render().el
	},
	*/

	delete_item_click: function ( e ) {
		e.preventDefault();

		// "delete" really just toggles selected to false for relationships
		this.model.toggle_selected();
	}
} );

/**
 *  Represents the markup of the container as a whole
 */
export const FlexView = PodsFieldListView.extend( {	// Cache the template function for the overall container
	tagName: 'ul',

	className: 'pods-flex-list pods-relationship',

	childView: FlexItem

	//template: _.template( $( '#flex-collection-template' ).html() ),

	/*
	events: {
		'click .button.pods-flex-relationship-add': 'add_new_click'
	}
	*/


	/*
	render: function () {
		var $el, filtered_collection;

		// Rebuild the entire view from scratch, starting with the container template
		$el = this.$el;
		$el.empty();
		$el.html( this.template() );

		// We only want to display selected items, our specialized collection has a helper method to give us a
		// new collection with just that
		filtered_collection = this.collection.filter_selected();

		// Add an item view to the HTML list for each one in our filtered collection
		filtered_collection.each( function ( item_model ) {
			var view = new app.flex_item_view( { model: item_model } );
			$el.find( 'ul.pods-collection-container' ).append( view.render().el );
		} );

		// Chaining
		return this;
	},
	*/

	/*
	add_new_click: function ( e ) {
		e.preventDefault();
		var item_model = app.get_spoof_data.create_spoof_item();
		this.collection.add( item_model ); // add item to collection; view is updated via event 'add'
	}
	*/
} );