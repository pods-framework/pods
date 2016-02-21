/*global jQuery, _, Backbone, Mn, wp */
import * as Template from '../templates/view-selector-template.html';

export const PickViewSelector = Mn.LayoutView.extend( {
	template: _.template( Template.default ),

	ui: {
		checkbox: '.checkbox',
		select  : '.select'
	},

	triggers: {
		'click @ui.checkbox': 'checkbox:view:click',
		'click @ui.select': 'select:view:click'
	}
} );