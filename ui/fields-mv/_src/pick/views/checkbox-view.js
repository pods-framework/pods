/*global jQuery, _, Backbone, Marionette, wp */
import template from '~/ui/fields-mv/_src/pick/views/checkbox-item.html';

import {PodsFieldListView, PodsFieldView} from '~/ui/fields-mv/_src/core/pods-field-views';

/**
 *
 */
export const CheckboxItem = PodsFieldView.extend( {
	tagName: 'li',

	template: _.template( template ),

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
		};
	},

	modelChanged: function () {
		this.render();
	},

	onToggleSelected: function () {
		this.model.toggleSelected();
	}
} );

/**
 *
 */
export const CheckboxView = PodsFieldListView.extend( {
	tagName: 'ul',

	className: 'pods-checkbox-view',

	childView: CheckboxItem
} );