/*global jQuery, _, Backbone, Marionette, wp */
import template from 'pods-dfv/_src/pick/views/add-new.html';

import {PodsFieldView} from 'pods-dfv/_src/core/pods-field-views';

export const AddNew = PodsFieldView.extend( {
	// Disable implicit event listeners in favor of explicit childViewTriggers and childViewEvents
	childViewEventPrefix: false,

	tagName: 'div',

	className: 'podsform-dfv-list-relationship-container',

	ui: {
		addButton: 'a.pods-related-add-new'
	},

	template: _.template( template ),

	triggers: {
		'click @ui.addButton': 'add:new:click'
	},

	onAddNewClick: function () {
		this.trigger( 'childview:add:new', this );
	}
} );
