/*global jQuery, _, Backbone, Mn, wp */
import * as templateImport from '~/ui/fields-mv/_src/file-upload/views/file-upload-form.html';
const template = templateImport.default || templateImport; // Currently two differnt style string importers for build and test

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
