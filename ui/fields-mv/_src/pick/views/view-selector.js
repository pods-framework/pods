/*global jQuery, _, Backbone, Mn, wp */
import * as templateImport from './view-selector-template.html';
const template = templateImport.default || templateImport; // Currently two differnt style string importers for build and test

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