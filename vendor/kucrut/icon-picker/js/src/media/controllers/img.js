var Library = wp.media.controller.Library,
	l10n = wp.media.view.l10n,
	models = wp.media.model,
	views = wp.media.view,
	IconPickerImg;

/**
 * wp.media.controller.IconPickerImg
 *
 * @augments wp.media.controller.Library
 * @augments wp.media.controller.State
 * @augments Backbone.Model
 * @mixes    media.selectionSync
 * @mixes    wp.media.controller.iconPickerMixin
 */
IconPickerImg = Library.extend( _.extend({}, wp.media.controller.iconPickerMixin, {
	defaults: _.defaults({
		id:            'image',
		baseType:      'image',
		syncSelection: false
	}, Library.prototype.defaults ),

	initialize: function( options ) {
		var selection = this.get( 'selection' );

		this.options = options;

		this.set( 'library', wp.media.query({ type: options.data.mimeTypes }) );

		this.routers = {
			upload: {
				text:     l10n.uploadFilesTitle,
				priority: 20
			},
			browse: {
				text:     l10n.mediaLibraryTitle,
				priority: 40
			}
		};

		if ( ! ( selection instanceof models.Selection ) ) {
			this.set( 'selection', new models.Selection( selection, {
				multiple: false
			}) );
		}

		Library.prototype.initialize.apply( this, arguments );
	},

	activate: function() {
		Library.prototype.activate.apply( this, arguments );
		this.get( 'library' ).observe( wp.Uploader.queue );
		this.frame.on( 'open', this.updateSelection, this );
		this.updateSelection();
	},

	deactivate: function() {
		Library.prototype.deactivate.apply( this, arguments );
		this.get( 'library' ).unobserve( wp.Uploader.queue );
		this.frame.off( 'open', this.updateSelection, this );
	},

	getContentView: function( mode ) {
		var content = ( mode === 'upload' ) ? this.uploadContent() : this.browseContent();

		this.frame.$el.removeClass( 'hide-toolbar' );

		return content;
	},

	/**
	 * Media library content
	 *
	 * @returns {wp.media.view.IconPickerImgBrowser} "Browse" content view.
	 */
	browseContent: function() {
		var options = _.extend({
			model:            this,
			controller:       this.frame,
			collection:       this.get( 'library' ),
			selection:        this.get( 'selection' ),
			sortable:         this.get( 'sortable' ),
			search:           this.get( 'searchable' ),
			filters:          this.get( 'filterable' ),
			dragInfo:         this.get( 'dragInfo' ),
			idealColumnWidth: this.get( 'idealColumnWidth' ),
			suggestedWidth:   this.get( 'suggestedWidth' ),
			suggestedHeight:  this.get( 'suggestedHeight' )
		}, this.ipGetSidebarOptions() );

		if ( this.id === 'svg' ) {
			options.AttachmentView = views.IconPickerSvgItem;
		}

		return new views.IconPickerImgBrowser( options );
	},

	/**
	 * Render callback for the content region in the `upload` mode.
	 *
	 * @returns {wp.media.view.UploaderInline} "Upload" content view.
	 */
	uploadContent: function() {
		return new wp.media.view.UploaderInline({
			controller: this.frame
		});
	},

	updateSelection: function() {
		var selection = this.get( 'selection' ),
		    target    = this.frame.target,
		    icon      = target.get( 'icon' ),
		    type      = target.get( 'type' ),
		    selected;

		if ( this.id === type ) {
			selected = models.Attachment.get( icon );
			this.dfd = selected.fetch();
		}

		selection.reset( selected ? selected : null );
	},

	/**
	 * Get image icon URL
	 *
	 * @param  {object} model - Selected icon model.
	 * @param  {string} size  - Image size.
	 *
	 * @returns {string} Icon URL.
	 */
	ipGetIconUrl: function( model, size ) {
		var url   = model.get( 'url' ),
		    sizes = model.get( 'sizes' );

		if ( undefined === size ) {
			size = 'thumbnail';
		}

		if ( sizes && sizes[ size ]) {
			url = sizes[ size ].url;
		}

		return url;
	}
}) );

module.exports = IconPickerImg;
