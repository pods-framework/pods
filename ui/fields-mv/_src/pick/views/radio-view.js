/*global jQuery, _, Backbone, Marionette, wp */
import template from '~/ui/fields-mv/_src/pick/views/radio-item.html';

import {PodsFieldListView, PodsFieldView} from '~/ui/fields-mv/_src/core/pods-field-views';

/**
 *
 */
export const RadioItem = PodsFieldView.extend( {
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
	tagName: 'ul',

	className: 'pods-radio',

	childView: RadioItem
} );