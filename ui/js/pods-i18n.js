/*global podsLocalizedStrings, sprintf */
'use strict';
var PodsI18n = (function () {

	/**
	 * Only visible to the closure, not exposed externally.
	 * @param {string} str
	 * @returns {string}
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
		 * @param {string} single
		 * @param {string} plural
		 * @param {number} number
		 *
		 * @returns {string}
		 */
		_n: function ( single, plural, number ) {

			// Unary + will implicitly cast to numeric
			if ( +number === 1 ) {
				return translateString( single );
			}
			else {
				return translateString( plural );
			}
		},
	};

}());