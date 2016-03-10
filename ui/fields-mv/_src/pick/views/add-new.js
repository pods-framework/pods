/*global jQuery, _, Backbone, Mn, wp */
import * as template from './add-new.html';
import { PodsFieldListView, PodsFieldView } from '../../../_src/core/pods-field-views';

export const AddNew = PodsFieldView.extend( {
	tagName: 'div',

	className: 'podsform-flex-relationship-container',

	ui: {
		addButton: '.pods-related-add-new'
	},

	template: _.template( template.default ),

	triggers: {
		'click @ui.addButton': 'add:new:click'
	}
} );
