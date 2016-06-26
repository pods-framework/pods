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

	childView: FlexItem,

	/*
	events: {
		'click .button.pods-flex-relationship-add': 'add_new_click'
	}
	*/

	filter: function( child, index, collection ) {
		return child.attributes.selected;
	}

	/*
	add_new_click: function ( e ) {
		e.preventDefault();
		var item_model = app.get_spoof_data.create_spoof_item();
		this.collection.add( item_model ); // add item to collection; view is updated via event 'add'
	}
	*/
} );