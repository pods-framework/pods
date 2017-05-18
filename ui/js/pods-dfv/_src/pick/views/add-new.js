/*global jQuery, _, Backbone, Marionette, wp */
import template from 'pods-dfv/_src/pick/views/add-new.html';

import { PodsFieldView } from 'pods-dfv/_src/core/pods-field-views';

export const AddNew = PodsFieldView.extend( {
	tagName: 'div',

	className: 'podsform-dfv-list-relationship-container',

	ui: {
		addButton: '.pods-related-add-new'
	},

	template: _.template( template ),

	triggers: {
		'click @ui.addButton': 'add:new:click'
	}
} );
