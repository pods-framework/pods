/*global jQuery, _, Backbone, Mn */
/**
 * Individual list items, representing a single file
 */
const $ = jQuery;
export const FileUploadItem = Mn.LayoutView.extend( {
	tagName: 'li',

	className: 'pods-file',

	ui: {
		drag_handle  : '.pods-file-handle',
		download_link: '.pods-file-download',
		remove_button: '.pods-file-remove'
	},

	template: _.template( $( '#file-upload-item-template' ).html() ),

	triggers: {
		'click @ui.remove_button': 'remove:file:click'
	},

	/**
	 * @todo: abstract this out.  All fields need access to the field meta and individual views shouldn't have to
	 * worry about marshalling that data around.
	 *
	 * The return value here is what will be made available to the template
	 */
	serializeData: function () {
		var data = this.model.toJSON();

		data.attr = this.options[ 'field_attributes' ];
		data.options = this.options[ 'field_options' ];

		return data;
	}

} );

/**
 * The file list container
 */
export const FileUploadList = Mn.CollectionView.extend( {
	tagName: 'ul',

	className: 'pods-files pods-files-list',

	childView: FileUploadItem,

	// @todo: abstract this out.  All fields need access to the field meta and individual views shouldn't have to
	// worry about marshalling that data around.
	initialize: function ( options ) {
		this.childViewOptions = options.field_meta;
	},

	onAttach: function () {

		// @todo
		// http://stackoverflow.com/questions/1735372/jquery-sortable-list-scroll-bar-jumps-up-when-sorting/4187833#4187833

		// @todo: turn this into a list view behavior

		if ( 1 != this.options[ 'field_meta' ][ 'field_options' ][ 'file_limit' ] ) {
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

