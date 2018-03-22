var $ = jQuery,
	Attachments = wp.media.view.Attachments,
	IconPickerFontLibrary;

/**
 * wp.media.view.IconPickerFontLibrary
 */
IconPickerFontLibrary = Attachments.extend({
	className: 'attachments iconpicker-items clearfix',

	initialize: function() {
		Attachments.prototype.initialize.apply( this, arguments );

		_.bindAll( this, 'scrollToSelected' );
		_.defer( this.scrollToSelected, this );
		this.controller.on( 'open', this.scrollToSelected, this );
		$( this.options.scrollElement ).off( 'scroll', this.scroll );
	},

	_addItem: function( model ) {
		this.views.add( this.createAttachmentView( model ), {
			at: this.collection.indexOf( model )
		});
	},

	_removeItem: function( model ) {
		var view = this._viewsByCid[ model.cid ];
		delete this._viewsByCid[ model.cid ];

		if ( view ) {
			view.remove();
		}
	},

	render: function() {
		_.each( this._viewsByCid, this._removeItem, this );
		this.collection.each( this._addItem, this );

		return this;
	},

	createAttachmentView: function( model ) {
		var view = new wp.media.view.IconPickerFontItem({
			controller: this.controller,
			model:      model,
			collection: this.collection,
			selection:  this.options.selection,
			baseType:   this.options.baseType,
			type:       this.options.type
		});

		return this._viewsByCid[ view.cid ] = view;
	},

	/**
	 * Scroll to selected item
	 */
	scrollToSelected: function() {
		var selected = this.options.selection.single(),
		    singleView, distance;

		if ( ! selected ) {
			return;
		}

		singleView = this.getView( selected );

		if ( singleView && ! this.isInView( singleView.$el ) ) {
			distance = (
				singleView.$el.offset().top -
				parseInt( singleView.$el.css( 'paddingTop' ), 10 ) -
				this.$el.offset().top +
				this.$el.scrollTop() -
				parseInt( this.$el.css( 'paddingTop' ), 10 )
			);

			this.$el.scrollTop( distance );
		}
	},

	getView: function( model ) {
		return _.findWhere( this._viewsByCid, { model: model });
	},

	isInView: function( $elem ) {
		var docViewTop    = this.$window.scrollTop(),
		    docViewBottom = docViewTop + this.$window.height(),
		    elemTop       = $elem.offset().top,
		    elemBottom    = elemTop + $elem.height();

		return ( ( elemBottom <= docViewBottom ) && ( elemTop >= docViewTop ) );
	},

	prepare: function() {},
	ready: function() {},
	initSortable: function() {},
	scroll: function() {}
});

module.exports = IconPickerFontLibrary;
