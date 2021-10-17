/**
 * Methods for the browser views
 */
var IconPickerBrowser = {
	createSidebar: function() {
		this.sidebar = new this.options.SidebarView({
			controller: this.controller,
			selection:  this.options.selection
		});

		this.views.add( this.sidebar );
	}
};

module.exports = IconPickerBrowser;
