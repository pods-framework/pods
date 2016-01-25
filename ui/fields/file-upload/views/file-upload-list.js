/*global jQuery, _, Backbone, Mn, pods_ui */
(function ( $, app ) {
	'use strict';

	/**
	 *
	 */
	app.FileUploadItem = Mn.LayoutView.extend( {
		tagName: 'li',

		className: 'pods-file hidden',

		ui: {
			button_remove: '.pods-file-remove'
		},

		template: _.template( $( '#file-upload-item-template' ).html() ),

		triggers: {
			'click @ui.button_remove': 'remove:file'
		},

		// @todo: abstract this out.  All fields need access to the field meta and individual views shouldn't have to
		// worry about marshalling that data around.
		serializeData: function () {
			var data = this.model.toJSON();

			data.attr = this.options[ 'field_attributes' ];
			data.options = this.options[ 'field_options' ];

			return data;
		}

	} );

	/**
	 *
	 */
	app.FileUploadList = Mn.CollectionView.extend( {
		tagName: 'ul',

		className: 'pods-files pods-files-list',

		childView: app.FileUploadItem,

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

}( jQuery, pods_ui ) );