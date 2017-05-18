/*global jQuery, _, Backbone, Marionette, wp */
import template from 'pods-dfv/_src/file-upload/views/file-upload-form.html';

import { PodsFieldView } from 'pods-dfv/_src/core/pods-field-views';

export const FileUploadForm = PodsFieldView.extend( {
	tagName: 'div',

	ui: {
		addButton: '.pods-dfv-list-add'
	},

	template: _.template( template ),

	triggers: {
		'click @ui.addButton': 'add:file:click'
	}
} );
