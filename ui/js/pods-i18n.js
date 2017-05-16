/*global podsLocalizedStrings */
'use strict';
var PodsI18n = (function () {

	/**
	 * Only visible to the closure, not exposed externally
	 */
	var translateString = function ( str ) {
		var translated = str, ref;

		if ( typeof podsLocalizedStrings !== 'undefined' ) {

			/**
			 * Converts string into reference object variable
			 * Uses the same logic as PHP to create the same references
			 */
			ref = '__' + str;

			if ( typeof podsLocalizedStrings[ ref ] !== 'undefined' ) {
				translated = podsLocalizedStrings[ ref ];
			}
			else if ( podsLocalizedStrings.debug ) {
				console.log( 'PodsI18n: String not found "' + str + '" (reference used: "' + ref + '")' );
			}
		}

		return translated;
	};

	/**
	 * The returned object, this is what we'll expose to the outside world
	 */
	return {
		/**
		 * @param {string} str
		 * @returns {string}
		 */
		__: function ( str ) {
			return translateString( str );
		},
		/**
		 * @param {string} str
		 * @param {array} args
		 * @returns {string}
		 */
		_s: function( str, args ) {
			str = translateString( str );
			if ( ! args.length ) {
				return str;
			}
			var i, c;
			if ( 1 < args.length ) {
				for ( i=0, c=1; i < args.length; i++, c++ ) {
					str = str.replace( "%" + c + "$s", args[ i ] );
				}
			} else {
				if ( -1 === str.indexOf( '%1$s' ) ) {
					str = str.replace( "%s", args[0] );
				} else {
					str = str.replace( "%1$s", args[0] );
				}
			}
			return str;
		}
	};

}());