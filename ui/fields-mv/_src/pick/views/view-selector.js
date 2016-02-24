/*global jQuery, _, Backbone, Mn, wp */
import * as template from './view-selector-template.html';

export const PickViewSelector = Mn.LayoutView.extend( {
	template: _.template( template.default ),

	ui: {
		checkbox: '.checkbox',
		select  : '.select'
	},

	triggers: {
		'click @ui.checkbox': 'checkbox:view:click',
		'click @ui.select': 'select:view:click'
	}
} );