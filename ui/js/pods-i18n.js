/*global podsLocalizedStrings */
'use strict';
var PodsI18n = (function () {

	/**
	 * Only visible to the closure, not exposed externally
	 */
	var translateString = function ( str ) {
		var translated = str;

		if ( podsLocalizedStrings !== undefined ) {

			/**
			 * Converts string into reference object variable
			 * Uses the same logic as PHP to create the same references
			 */
			var ref = '__' + str;

			if ( podsLocalizedStrings[ ref ] !== undefined ) {
				translated = podsLocalizedStrings[ ref ];
			}
			else if ( podsLocalizedStrings.debug == true ) {
				console.log( 'Pods__: String not found "' + str + '" (reference used: "' + ref + '")' );
			}
		}

		return translated;
	};

	/**
	 * The returned object, this is what we'll expose to the outside world
	 */
	return {
		__: function ( str ) {
			return translateString( str );
		}
	};

}());