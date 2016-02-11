/*global jQuery, _, Backbone, Mn */
/**
 *
 * @param {Object} options
 *
 * @param {Object} options.browse_button   Existing and attached DOM node
 * @param {Object} options.ui_region       Marionette.Region object
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
export const PodsFileUploader = Mn.Object.extend( {

	constructor: function ( options ) {
		// Magically set the object properties we need, they'll just "be there" for the concrete instance
		this.browse_button = options.browse_button;
		this.ui_region = options.ui_region;
		this.field_options = options.field_options;

		Mn.Object.call( this, options );
	}

} );

