/*global jQuery, _, Backbone, Mn, wp */
import * as template from '~/ui/fields-mv/_src/file-upload/views/file-upload-form.html';

import { PodsFieldListView, PodsFieldView } from '~/ui/fields-mv/_src/core/pods-field-views';

export const FileUploadForm = PodsFieldView.extend( {
	tagName: 'div',

	ui: {
		addButton: '.pods-flex-add'
	},

	template: _.template( template ),

	triggers: {
		'click @ui.addButton': 'add:file:click'
	}
} );
