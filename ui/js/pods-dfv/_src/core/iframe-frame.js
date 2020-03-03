/*global jQuery, _, Backbone, PodsMn, wp, PodsI18n */

import { PodsDFVModal } from 'pods-dfv/_src/core/dfv-modal';

/**
 * A frame for displaying a modal popup with iframe content
 *
 * @augments wp.media.view.Frame
 */
export const IframeFrame = wp.media.view.Frame.extend( {
	className: 'pods-modal-frame',

	template: _.template( '<div class="media-frame-title" /><div class="media-frame-iframe" />' ),

	regions: [ 'title', 'iframe' ],

	initialize: function () {
		wp.media.view.Frame.prototype.initialize.apply( this, arguments );

		// Ensure core UI is enabled.
		this.$el.addClass( 'wp-core-ui' );

		this.initState();
		this.initModal();

		this.on( 'iframe:create:default', this.iframeContent, this );
		this.iframe.mode( 'default' );

		this.on( 'title:create:default', this.createTitle, this );
		this.title.mode( 'default' );
	},

	initState: function () {
		const title = this.options.title || PodsI18n.__( 'Add New Record' );
		const src = this.options.src || '/';

		this.states.add( [
			new wp.media.controller.State( {
				id: 'default',
				title: title,
				src: src
			} )
		] );

		this.options.state = 'default';
	},

	initModal: function () {
		this.modal = new PodsDFVModal( {
			controller: this
		} );

		this.modal.content( this );
	},

	render: function () {
		// Activate the default state if no active state exists.
		if ( !this.state() && this.options.state ) {
			this.setState( this.options.state );
		}

		/**
		 * call 'render' directly on the parent class
		 */
		return wp.media.view.Frame.prototype.render.apply( this, arguments );
	},

	/**
	 * @param {Object} content
	 * @this wp.media.controller.Region
	 */
	iframeContent: function ( content ) {
		content.view = new wp.media.view.Iframe( {
			controller: this
		} );
	},

	createTitle: function ( title ) {
		title.view = new wp.media.View( {
			controller: this,
			tagName: 'h1'
		} );
	}
} );
