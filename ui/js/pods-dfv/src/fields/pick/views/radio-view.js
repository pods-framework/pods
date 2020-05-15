/*global jQuery, _, Backbone, PodsMn, wp */
import template from 'pods-dfv/src/fields/pick/views/radio-item.html';

import { PodsFieldListView, PodsFieldView } from 'pods-dfv/src/core/pods-field-views';

/**
 *
 */
export const RadioItem = PodsFieldView.extend( {
	childViewEventPrefix: false, // Disable implicit event listeners in favor of explicit childViewTriggers and childViewEvents

	tagName: 'li',

	template: _.template( template ),

	templateContext: function () {
		return {
			ordinal: this.model.collection.indexOf( this.model ) + 1 // One based indexing unlike checkboxes
		};
	},

	modelChanged: function () {
		this.render();
	}

} );

/**
 *
 */
export const RadioView = PodsFieldListView.extend( {
	childViewEventPrefix: false, // Disable implicit event listeners in favor of explicit childViewTriggers and childViewEvents

	tagName: 'ul',

	className: 'pods-radio',

	childView: RadioItem
} );
