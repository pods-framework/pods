/*global jQuery, _, Backbone, PodsMn, wp */
import template from 'pods-dfv/src/fields/pick/views/checkbox-item.html';

import {
	PodsFieldListView,
	PodsFieldView,
} from 'pods-dfv/src/core/pods-field-views';

/**
 *
 */
export const CheckboxItem = PodsFieldView.extend( {
	childViewEventPrefix: false, // Disable implicit event listeners in favor of explicit childViewTriggers and childViewEvents

	tagName: 'li',

	template: _.template( template ),

	className: 'pods-pick',

	ui: {
		checkbox: 'input.pods-form-ui-field-type-pick',
	},

	triggers: {
		'click @ui.checkbox': 'toggle:selected',
	},

	modelEvents: {
		change: 'modelChanged',
	},

	templateContext() {
		return {
			ordinal: this.model.collection.indexOf( this.model ),
		};
	},

	modelChanged() {
		this.render();
	},
} );

/**
 *
 */
export const CheckboxView = PodsFieldListView.extend( {
	childViewEventPrefix: false, // Disable implicit event listeners in favor of explicit childViewTriggers and childViewEvents

	tagName: 'ul',

	className: 'pods-checkbox-view',

	childView: CheckboxItem,

	childViewEvents: {
		'toggle:selected': 'onChildviewToggleSelected',
	},

	/**
	 *
	 */
	onAttach() {
		// Check initial selection limit status and enforce it if needed
		if ( ! this.validateSelectionLimit() ) {
			this.selectionLimitOver();
		}
	},

	/**
	 *
	 * @param childView
	 */
	onChildviewToggleSelected( childView ) {
		childView.model.toggleSelected();

		// Dynamically enforce selection limit
		if ( this.validateSelectionLimit() ) {
			this.selectionLimitUnder();
		} else {
			this.selectionLimitOver();
		}
	},

	/**
	 * @return {boolean} true if unlimited selections are allowed or we're below the selection limit
	 */
	validateSelectionLimit() {
		const fieldConfig = this.fieldModel.get( 'fieldConfig' );
		let limit, numSelected;

		limit = +fieldConfig.pick_limit; // Unary plus will implicitly cast to number
		numSelected = this.collection.filterBySelected().length;

		return 0 === limit || numSelected < limit;
	},

	/**
	 *
	 */
	selectionLimitOver() {
		this.$el
			.find( 'input:checkbox:not(:checked)' )
			.prop( 'disabled', true );
		this.trigger( 'selection:limit:over', this );
	},

	/**
	 *
	 */
	selectionLimitUnder() {
		this.$el.find( 'input:checkbox' ).prop( 'disabled', false );
		this.trigger( 'selection:limit:under', this );
	},
} );
