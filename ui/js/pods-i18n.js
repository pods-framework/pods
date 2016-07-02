/*global pods_localized_strings */
'use strict';
var PodsI18n = (function () {

	/**
	 * Only visible to the closure, not exposed externally
	 */
	var translateString = function ( str ) {

		if ( typeof pods_localized_strings != 'undefined' ) {

			/**
			 * Converts string into reference object variable
			 * Uses the same logic as PHP to create the same references
			 */
			var ref = '__' + str;

			if ( typeof pods_localized_strings[ ref ] != 'undefined' ) {
				return pods_localized_strings[ ref ];
			}
			else if ( pods_localized_strings.debug == true ) {
				console.log( 'Pods__: String not found "' + str + '" (reference used: "' + ref + '")' );
			}
		}

		return str;
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