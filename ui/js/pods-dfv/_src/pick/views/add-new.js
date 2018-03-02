/*global jQuery, _, Backbone, Marionette, wp */
import template from 'pods-dfv/_src/pick/views/add-new.html';

import {PodsFieldView} from 'pods-dfv/_src/core/pods-field-views';

const DISABLED_CLASS = 'button-disabled';

export const AddNew = PodsFieldView.extend( {
	childViewEventPrefix: false, // Disable implicit event listeners in favor of explicit childViewTriggers and childViewEvents

	tagName: 'div',

	className: 'podsform-dfv-list-relationship-container',

	ui: {
		addButton: 'a.pods-related-add-new'
	},

	template: _.template( template ),

	triggers: {
		'click @ui.addButton': 'add:new:click'
	},

	/**
	 *
	 */
	disable: function () {
		const addButton = this.getUI( 'addButton' );
		addButton.addClass( DISABLED_CLASS ); // Note: this just styles the link (button), click event enforces
	},

	/**
	 *
	 */
	enable: function () {
		const addButton = this.getUI( 'addButton' );
		addButton.removeClass( DISABLED_CLASS ); // Note: this just styles the link (button), click event enforces
	},

	/**
	 *
	 */
	onAddNewClick: function () {
		const addButton = this.getUI( 'addButton' );

		// Only pass the event up the view chain if we're enabled
		if ( ! addButton.hasClass( DISABLED_CLASS ) ) {
			this.trigger( 'childview:add:new', this );
		}
	}
} );
