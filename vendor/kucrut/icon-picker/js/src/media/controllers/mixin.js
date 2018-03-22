/**
 * Methods for the state
 *
 * @mixin
 */
var iconPickerMixin = {

	/**
	 * @returns {object}
	 */
	ipGetSidebarOptions: function() {
		var frameOptions = this.frame.options,
		    options = {};

		if ( frameOptions.SidebarView && frameOptions.SidebarView.prototype instanceof wp.media.view.IconPickerSidebar ) {
			options.sidebar     = true;
			options.SidebarView = frameOptions.SidebarView;
		} else {
			options.sidebar = false;
		}

		return options;
	},

	/**
	 * Get image icon URL
	 *
	 * @returns {string}
	 */
	ipGetIconUrl: function() {
		return '';
	}
};

module.exports = iconPickerMixin;
