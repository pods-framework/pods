(function ( root, factory ) {
	if ( typeof define === 'function' && define.amd ) {
		define( [ 'backbone.marionette', 'backbone.radio', 'underscore' ], factory );
	}
	else if ( typeof exports !== 'undefined' ) {
		module.exports = factory( require( 'backbone.marionette' ), require( 'backbone.radio' ), require( 'underscore' ) );
	}
	else {
		factory( root.Backbone.Marionette, root.Backbone.Radio, root._ );
	}
}( this, function ( Marionette, Radio, _ ) {
	'use strict';

	Marionette.Application.prototype._initChannel = function () {
		this.channelName = _.result( this, 'channelName' ) || 'global';
		this.channel = _.result( this, 'channel' ) || Radio.channel( this.channelName );
	};
} ));