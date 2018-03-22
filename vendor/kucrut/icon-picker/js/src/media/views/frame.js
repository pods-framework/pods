/**
 * wp.media.view.MediaFrame.IconPicker
 *
 * A frame for selecting an icon.
 *
 * @class
 * @augments wp.media.view.MediaFrame.Select
 * @augments wp.media.view.MediaFrame
 * @augments wp.media.view.Frame
 * @augments wp.media.View
 * @augments wp.Backbone.View
 * @augments Backbone.View
 * @mixes wp.media.controller.StateMachine
 */

var l10n = wp.media.view.l10n,
	Select = wp.media.view.MediaFrame.Select,
	IconPicker;

IconPicker = Select.extend({
	initialize: function() {
		_.defaults( this.options, {
			title:       l10n.iconPicker.frameTitle,
			multiple:    false,
			ipTypes:     iconPicker.types,
			target:      null,
			SidebarView: null
		});

		if ( this.options.target instanceof wp.media.model.IconPickerTarget ) {
			this.target = this.options.target;
		} else {
			this.target = new wp.media.model.IconPickerTarget();
		}

		Select.prototype.initialize.apply( this, arguments );
	},

	createStates: function() {
		var Controller;

		_.each( this.options.ipTypes, function( props ) {
			if ( ! wp.media.controller.hasOwnProperty( 'IconPicker' + props.controller ) ) {
				return;
			}

			Controller = wp.media.controller[ 'IconPicker' + props.controller ];

			this.states.add( new Controller({
				id:      props.id,
				content: props.id,
				title:   props.name,
				data:    props.data
			}) );
		}, this );
	},

	/**
	 * Bind region mode event callbacks.
	 */
	bindHandlers: function() {
		this.on( 'router:create:browse', this.createRouter, this );
		this.on( 'router:render:browse', this.browseRouter, this );
		this.on( 'content:render', this.ipRenderContent, this );
		this.on( 'toolbar:create:select', this.createSelectToolbar, this );
		this.on( 'open', this._ipSetState, this );
		this.on( 'select', this._ipUpdateTarget, this );
	},

	/**
	 * Set state based on the target's icon type
	 */
	_ipSetState: function() {
		var stateId = this.target.get( 'type' );

		if ( ! stateId || ! this.states.findWhere({ id: stateId }) ) {
			stateId = this.states.at( 0 ).id;
		}

		this.setState( stateId );
	},

	/**
	 * Update target's attributes after selecting an icon
	 */
	_ipUpdateTarget: function() {
		var state    = this.state(),
			selected = state.get( 'selection' ).single(),
			props;

		props = {
			type:  state.id,
			icon:  selected.get( 'id' ),
			sizes: selected.get( 'sizes' ),
			url:   state.ipGetIconUrl( selected )
		};

		this.target.set( props );
	},

	browseRouter: function( routerView ) {
		var routers = this.state().routers;

		if ( routers ) {
			routerView.set( routers );
		}
	},

	ipRenderContent: function() {
		var state   = this.state(),
		    mode    = this.content.mode(),
		    content = state.getContentView( mode );

		this.content.set( content );
	}
});

module.exports = IconPicker;
