/*global jQuery, _, Backbone, Marionette */
/**
 *
 * @param {Object} options
 *
 * @param {Object} options.browseButton   Existing and attached DOM node
 * @param {Object} options.uiRegion       Marionette.Region object
 * @param {Object} options.fieldConfig
 *
 * @param {string} options.fieldConfig.file_modal_title
 * @param {string} options.fieldConfig.file_modal_add_button
 * @param {string} options.fieldConfig.file_limit
 * @param {string} options.fieldConfig.limit_extensions
 * @param {string} options.fieldConfig.limit_types
 * @param {string} options.fieldConfig.file_attachment_tab
 *
 * @param {Object} options.fieldConfig.plupload_init
 * @param {Object} options.fieldConfig.plupload_init.browse_button
 *
 * @class
 */
export const PodsFileUploader = Marionette.Object.extend( {

	constructor: function ( options ) {
		// Magically set the object properties we need, they'll just "be there" for the concrete instance
		this.browseButton = options.browseButton;
		this.uiRegion = options.uiRegion;
		this.fieldConfig = options.fieldConfig;

		Marionette.Object.call( this, options );
	}

} );

