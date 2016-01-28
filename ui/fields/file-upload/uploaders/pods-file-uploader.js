/*global jQuery, _, Backbone, Mn, pods_ui */
(function ( $, app ) {
	'use strict';

	/**
	 *
	 * @param {Object} options
	 *
	 * @param {Object} options.main_layout
	 * @param {Object} options.field_options
	 *
	 * @param {string} options.field_options.file_modal_title
	 * @param {string} options.field_options.file_modal_add_button
	 * @param {string} options.field_options.file_limit
	 * @param {string} options.field_options.limit_extensions
	 * @param {string} options.field_options.limit_types
	 * @param {string} options.field_options.file_attachment_tab
	 *
	 * @param {Object} options.field_options.plupload_init
	 * @param {Object} options.field_options.plupload_init.browse_button
	 *
	 * @class
	 */
	app.PodsFileUploader = Mn.Object.extend( {

		constructor: function ( options ) {
			// Magically set the object properties we need, they'll just "be there" for the concrete instance
			this.main_layout = options.main_layout;
			this.field_options = options.field_options;

			Mn.Object.call( this, options );
		}

	} );

}( jQuery, pods_ui ) );