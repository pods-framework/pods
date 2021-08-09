/*global jQuery, _, Backbone, PodsMn, wp */
import template from 'pods-dfv/_src/file-upload/views/file-upload-form.html';

import { PodsFieldView } from 'pods-dfv/_src/core/pods-field-views';

export const FileUploadForm = PodsFieldView.extend( {
	childViewEventPrefix: false, // Disable implicit event listeners in favor of explicit childViewTriggers and childViewEvents

	tagName: 'div',

	template: _.template( template ),

	ui: {
		addButton: '.pods-dfv-list-add'
	},

	triggers: {
		'click @ui.addButton': 'childview:add:file:click'
	}
} );
