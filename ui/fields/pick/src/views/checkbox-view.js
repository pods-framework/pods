/*global jQuery, _, Backbone, Mn, wp */
import * as checkbox_item from '../templates/checkbox-item.html';
import { PodsFieldListView, PodsFieldView } from '../../../core/pods-field-views';

/**
 * Represents the markup of a single row of relationship data
 */
export const CheckboxItem = PodsFieldView.extend( {
	tagName: 'li',

	template: _.template( checkbox_item.default ),

	className: 'pods-pick',

	ui: {
		checkbox: 'input.pods-form-ui-field-type-pick'
	},

	triggers: {
		'click @ui.checkbox': 'toggle:selected'
	},

	modelEvents: {
		'change': 'modelChanged'
	},

	templateHelpers: function () {
		return {
			ordinal: this.model.collection.indexOf( this.model )
		}
	},

	modelChanged: function () {
		this.render();
	}

} );

/**
 *  Represents the markup of the container as a whole
 */
export const CheckboxView = PodsFieldListView.extend( {
	tagName: 'ul',

	childView: CheckboxItem
} );