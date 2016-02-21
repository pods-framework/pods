/*global jQuery, _, Backbone, Mn, wp */
import * as formTemplate from '../templates/file-upload-form.html';
import { PodsFieldListView, PodsFieldView } from '../../../_src/core/pods-field-views';

export const FileUploadForm = PodsFieldView.extend( {
	tagName: 'div',

	ui: {
		add_button: '.pods-file-add'
	},

	template: _.template( formTemplate.default ),

	triggers: {
		'click @ui.add_button': 'add:file:click'
	}
} );
