/**
 * wp.media.controller.IconPickerFont
 *
 * @class
 * @augments wp.media.controller.State
 * @augments Backbone.Model
 * @mixes    wp.media.controller.iconPickerMixin
 */
var IconPickerFont = wp.media.controller.State.extend( _.extend({}, wp.media.controller.iconPickerMixin, {
	defaults: {
		multiple: false,
		menu:     'default',
		toolbar:  'select',
		baseType: 'font'
	},

	initialize: function() {
		var data = this.get( 'data' );

		this.set( 'groups', new Backbone.Collection( data.groups ) );
		this.set( 'library', new wp.media.model.IconPickerFonts( data.items ) );
		this.set( 'selection', new wp.media.model.Selection( null, {
			multiple: this.get( 'multiple' )
		}) );
	},

	activate: function() {
		this.frame.on( 'open', this.updateSelection, this );
		this.resetFilter();
		this.updateSelection();
	},

	deactivate: function() {
		this.frame.off( 'open', this.updateSelection, this );
	},

	resetFilter: function() {
		this.get( 'library' ).props.set( 'group', 'all' );
	},

	updateSelection: function() {
		var selection = this.get( 'selection' ),
		    library   = this.get( 'library' ),
		    target    = this.frame.target,
		    icon      = target.get( 'icon' ),
		    type      = target.get( 'type' ),
		    selected;

		if ( this.id === type ) {
			selected = library.findWhere({ id: icon });
		}

		selection.reset( selected ? selected : null );
	},

	getContentView: function() {
		return new wp.media.view.IconPickerFontBrowser( _.extend({
			controller: this.frame,
			model:      this,
			groups:     this.get( 'groups' ),
			collection: this.get( 'library' ),
			selection:  this.get( 'selection' ),
			baseType:   this.get( 'baseType' ),
			type:       this.get( 'id' )
		}, this.ipGetSidebarOptions() ) );
	}
}) );

module.exports = IconPickerFont;
