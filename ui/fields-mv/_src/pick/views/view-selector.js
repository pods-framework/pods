/*global jQuery, _, Backbone, Mn, wp */
import * as template from './view-selector-template.html';

export const PickViewSelector = Mn.LayoutView.extend( {
	template: _.template( template ),

	ui: {
		checkbox: '.checkbox',
		select  : '.select',
		flex    : '.flex'
	},

	triggers: {
		'click @ui.checkbox': 'checkbox:view:click',
		'click @ui.select'  : 'select:view:click',
		'click @ui.flex'    : 'flex:view:click'
	}
} );