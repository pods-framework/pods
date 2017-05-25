/*global jQuery, _, Backbone, Marionette, wp */

// Globally disable implicit event listeners in favor of explicit childViewTriggers and childViewEvents
Marionette.setEnabled( 'childViewEventPrefix', false );

import template from 'pods-dfv/_src/file-upload/views/file-upload-form.html';

import {PodsFieldView} from 'pods-dfv/_src/core/pods-field-views';

export const FileUploadForm = PodsFieldView.extend( {
	tagName: 'div',

	template: _.template( template ),

	ui: {
		addButton: '.pods-dfv-list-add'
	},

	triggers: {
		'click @ui.addButton': 'childview:add:file:click'
	}
} );
