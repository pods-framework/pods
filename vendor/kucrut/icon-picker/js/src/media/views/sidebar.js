/**
 * wp.media.view.IconPickerSidebar
 */
var IconPickerSidebar = wp.media.view.Sidebar.extend({
	initialize: function() {
		var selection = this.options.selection;

		wp.media.view.Sidebar.prototype.initialize.apply( this, arguments );

		selection.on( 'selection:single', this.createSingle, this );
		selection.on( 'selection:unsingle', this.disposeSingle, this );

		if ( selection.single() ) {
			this.createSingle();
		}
	},

	/**
	 * @abstract
	 */
	createSingle: function() {},

	/**
	 * @abstract
	 */
	disposeSingle: function() {}
});

module.exports = IconPickerSidebar;
