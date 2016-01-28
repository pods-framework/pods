/*global jQuery, _, Backbone, Mn, pods_ui */
(function ( $, app ) {
	'use strict';

	/**
	 *
	 *
	 * @param {Object} main_layout
	 *
	 * @param {Object} field_options
	 *
	 * @param {string} field_options.file_modal_title
	 * @param {string} field_options.file_modal_add_button
	 * @param {string} field_options.file_limit
	 * @param {string} field_options.limit_extensions
	 * @param {string} field_options.limit_types
	 * @param {string} field_options.file_attachment_tab
	 *
	 * @param {Object} field_options.plupload_init
	 * @param {Object} field_options.plupload_init.browse_button
	 *
	 * @class
	 */
	app.PodsFileUploader = function ( main_layout, field_options ) {
		// Magically set a couple object properties
		this.main_layout = main_layout;
		this.field_options = field_options;

		this.initialize.apply( this, null );
	};

	// Use Marionette's extend so other objects can extend this one
	app.PodsFileUploader.extend = Mn.extend;

	// Ensure we can trigger/listenTo events with Backbone.Events
	_.extend( app.PodsFileUploader.prototype, Backbone.Events, {

		// no-op methods intended to be overridden by classes that extend from this base
		initialize: function () { return; },
		go: function () { return; },

		destroy: function ( options ) {
			this.stopListening();
			return this;
		}
	} );

}( jQuery, pods_ui ) );