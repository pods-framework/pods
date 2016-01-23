/*global jQuery, _, Backbone, Mn, pods_ui */
(function ( $, app ) {
	'use strict';

	app.FileUploadItem = Mn.LayoutView.extend( {

		tagName: 'li',

		className: 'pods-file hidden',

		ui: {
			remove: '.pods-file-remove'
		},

		template: _.template( $( '#file-upload-item-template' ).html() ),

		triggers: {
			'click @ui.remove': 'remove:file'
		},

		modelEvents: {
			change: 'render'
		},

		serializeData: function () {
			var data = this.model.toJSON();
			data.attributes = this.options.field_attributes;
			data.options = this.options.field_options;

			return data;
		},

		initialize: function ( options ) {
			this.fieldAttributes = options.field_attributes;
			this.fieldOptions = options.field_options;
		}

	} );

	app.FileUploadList = Mn.CollectionView.extend( {
		tagName  : 'ul',
		className: 'pods-files pods-files-list',
		childView: app.FileUploadItem,

		initialize: function ( options ) {
			this.childViewOptions = options.fieldMeta;
		}
	} );

}( jQuery, pods_ui ) );