global.document = require( 'jsdom' ).jsdom( '<html></html>' );
global.window = document.defaultView;
global.jQuery = require( 'jquery' );
global._ = require( 'underscore' );
global.Backbone = require( 'backbone' );
global.Mn = require( 'backbone.marionette' );
global.assert = require( 'assert' );
