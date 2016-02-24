/*global jQuery, _, Backbone, Mn, wp */
import * as itemTemplate from './file-upload-item.html';
import { PodsFieldListView, PodsFieldView } from '../../../_src/core/pods-field-views';

/**
 * Individual list items, representing a single file
 */
export const FileUploadItem = PodsFieldView.extend( {
	tagName: 'li',

	className: 'pods-file',

	ui: {
		dragHandle  : '.pods-file-handle',
		downloadLink: '.pods-file-download',
		removeButton: '.pods-file-remove'
	},

	template: _.template( itemTemplate.default ),

	triggers: {
		'click @ui.removeButton': 'remove:file:click'
	}
} );

/**
 * The file list container
 */
export const FileUploadList = PodsFieldListView.extend( {
	tagName: 'ul',

	className: 'pods-files pods-files-list',

	childView: FileUploadItem,

	onAttach: function () {
		const fieldOptions = this.options.fieldModel.get( 'options' );

		// @todo
		// http://stackoverflow.com/questions/1735372/jquery-sortable-list-scroll-bar-jumps-up-when-sorting/4187833#4187833

		if ( 1 != fieldOptions[ 'file_limit' ] ) {
			// init sortable
			this.$el.sortable( {
				containment      : 'parent',
				axis             : 'y',
				scrollSensitivity: 40,
				tolerance        : 'pointer',
				opacity          : 0.6
			} );
		}
	}
} );

