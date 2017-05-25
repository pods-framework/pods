/*global jQuery, _, Backbone, Marionette, wp */
import template from 'pods-dfv/_src/pick/views/checkbox-item.html';

import {PodsFieldListView, PodsFieldView} from 'pods-dfv/_src/core/pods-field-views';

/**
 *
 */
export const CheckboxItem = PodsFieldView.extend( {
	childViewEventPrefix: false, // Disable implicit event listeners in favor of explicit childViewTriggers and childViewEvents

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
	childViewEventPrefix: false, // Disable implicit event listeners in favor of explicit childViewTriggers and childViewEvents

	tagName: 'ul',

	className: 'pods-checkbox-view',

	childView: CheckboxItem,

	childViewEvents: {
		'toggle:selected': 'onChildviewToggleSelected'
	},

	/**
	 *
	 */
	onAttach: function () {

		// Check initial selection limit status and enforce it if needed
		if ( ! this.validateSelectionLimit() ) {
			this.selectionLimitOver();
		}
	},

	/**
	 *
	 * @param childView
	 */
	onChildviewToggleSelected: function ( childView ) {

		childView.model.toggleSelected();

		// Dynamically enforce selection limit
		if ( this.validateSelectionLimit() ) {
			this.selectionLimitUnder();
		} else {
			this.selectionLimitOver();
		}
	},

	/**
	 * @returns {boolean} true if unlimited selections are allowed or we're below the selection limit
	 */
	validateSelectionLimit: function ( ) {
		const fieldConfig = this.fieldModel.get( 'fieldConfig' );
		let limit, numSelected;

		limit = +fieldConfig.pick_limit;  // Unary plus will implicitly cast to number
		numSelected = this.collection.filterBySelected().length;

		if ( 0 === limit || numSelected < limit ) {
			return true;
		} else {
			return false;
		}
	},

	/**
	 *
	 */
	selectionLimitOver: function ( ) {
		this.$el.find( 'input:checkbox:not(:checked)' ).prop( 'disabled', true );
		this.trigger( 'selection:limit:over', this );
	},

	/**
	 *
	 */
	selectionLimitUnder: function ( ) {
		this.$el.find( 'input:checkbox' ).prop( 'disabled', false );
		this.trigger( 'selection:limit:under', this );
	}

} );