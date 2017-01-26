/*global jQuery, _, Backbone, Marionette, wp */
import template from '~/ui/js/pods-dfv/_src/file-upload/views/file-upload-item.html';

import { PodsFieldListView, PodsFieldView } from '~/ui/js/pods-dfv/_src/core/pods-field-views';

/**
 * Individual list items, representing a single file
 */
export const FileUploadItem = PodsFieldView.extend( {
	tagName: 'li',

	className: 'pods-flex-item',

	ui: {
		dragHandle  : '.pods-flex-handle',
		editLink    : '.pods-flex-edit-link',
		viewLink    : '.pods-flex-link',
		downloadLink: '.pods-flex-download',
		removeButton: '.pods-flex-remove',
		itemName    : '.pods-flex-name'
	},

	template: _.template( template ),

	triggers: {
		'click @ui.removeButton': 'remove:file:click'
	}
} );

/**
 * The file list container
 */
export const FileUploadList = PodsFieldListView.extend( {
	tagName: 'ul',

	className: 'pods-flex-list',

	childView: FileUploadItem,

	onAttach: function () {
		const fieldConfig = this.options.fieldModel.get( 'fieldConfig' );

		// @todo
		// http://stackoverflow.com/questions/1735372/jquery-sortable-list-scroll-bar-jumps-up-when-sorting/4187833#4187833

		if ( 1 != fieldConfig[ 'file_limit' ] ) {
			var sort_axis = 'y';

			if ( 'tiles' == fieldConfig[ 'file_field_template' ] ) {
				sort_axis = '';
			}

			// init sortable
			this.$el.sortable( {
				containment      : 'parent',
				axis             : sort_axis,
				scrollSensitivity: 40,
				tolerance        : 'pointer',
				opacity          : 0.6
			} );
		}
	}
} );

