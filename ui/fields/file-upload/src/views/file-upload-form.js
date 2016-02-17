/*global jQuery, _, Backbone, Mn */
const $ = jQuery;

import * as form_template from '../templates/file-upload-form.html';

export const FileUploadForm = Mn.LayoutView.extend( {

	tagName: 'div',

	ui: {
		add_button: '.pods-file-add'
	},

	template: _.template( form_template.default ),

	triggers: {
		'click @ui.add_button': 'add:file:click'
	},

	/**
	 * @todo: abstract this out.  All fields need access to the field meta and individual views shouldn't have to
	 * worry about marshalling that data around.
	 *
	 * The return value here is what will be made available to the template
	 */
	serializeData: function () {
		var data = {};

		data.attributes = this.options.field_meta[ 'field_attributes' ];
		data.options = this.options.field_meta[ 'field_options' ];

		return data;
	}

} );
