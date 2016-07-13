/*global jQuery, _, Backbone, Mn, wp */
import * as templateImport from '~/ui/fields-mv/_src/pick/views/add-new.html';
const template = templateImport.default || templateImport; // Currently two differnt style string importers for build and test

import { PodsFieldListView, PodsFieldView } from '~/ui/fields-mv/_src/core/pods-field-views';

export const AddNew = PodsFieldView.extend( {
	tagName: 'div',

	className: 'podsform-flex-relationship-container',

	ui: {
		addButton: '.pods-related-add-new'
	},

	template: _.template( template ),

	triggers: {
		'click @ui.addButton': 'add:new:click'
	}
} );
