/*global jQuery, _, Backbone, Marionette, wp */
import template from 'pods-dfv/_src/pick/views/checkbox-item.html';

import {PodsFieldListView, PodsFieldView} from 'pods-dfv/_src/core/pods-field-views';

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

	templateContext: function () {
		return {
			ordinal: this.model.collection.indexOf( this.model )
		};
	},

	modelChanged: function () {
		this.render();
	}
} );

/**
 *
 */
export const CheckboxView = PodsFieldListView.extend( {
	tagName: 'ul',

	className: 'pods-checkbox-view',

	childView: CheckboxItem,

	onChildviewToggleSelected: function ( childView ) {
		const fieldConfig = this.fieldModel.get( 'fieldConfig' );
		const limit = fieldConfig.pick_limit;
		const numSelected = this.collection.filterBySelected().length;

		// Enforce selection limit, ignoring de-selection.  Note that 'selected' is the value before the change here
		if ( numSelected < limit || childView.model.get( 'selected' ) ) {
			childView.model.toggleSelected();
		}
	}

} );