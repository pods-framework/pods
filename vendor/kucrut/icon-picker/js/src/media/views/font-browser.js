/**
 * wp.media.view.IconPickerFontBrowser
 */
var IconPickerFontBrowser = wp.media.View.extend( _.extend({
	className: function() {
		var className = 'attachments-browser iconpicker-fonts-browser';

		if ( ! this.options.sidebar ) {
			className += ' hide-sidebar';
		}

		return className;
	},

	initialize: function() {
		this.groups = this.options.groups;

		this.createToolbar();
		this.createLibrary();

		if ( this.options.sidebar ) {
			this.createSidebar();
		}
	},

	createLibrary: function() {
		this.items = new wp.media.view.IconPickerFontLibrary({
			controller: this.controller,
			collection: this.collection,
			selection:  this.options.selection,
			baseType:   this.options.baseType,
			type:       this.options.type
		});

		// Add keydown listener to the instance of the library view
		this.items.listenTo( this.controller, 'attachment:keydown:arrow',     this.items.arrowEvent );
		this.items.listenTo( this.controller, 'attachment:details:shift-tab', this.items.restoreFocus );

		this.views.add( this.items );
	},

	createToolbar: function() {
		this.toolbar = new wp.media.view.Toolbar({
			controller: this.controller
		});

		this.views.add( this.toolbar );

		// Dropdown filter
		this.toolbar.set( 'filters', new wp.media.view.IconPickerFontFilter({
			controller: this.controller,
			model:      this.collection.props,
			priority:   - 80
		}).render() );

		// Search field
		this.toolbar.set( 'search', new wp.media.view.Search({
			controller: this.controller,
			model:      this.collection.props,
			priority:   60
		}).render() );
	}
}, wp.media.view.IconPickerBrowser ) );

module.exports = IconPickerFontBrowser;
