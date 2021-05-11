/**
 * Configures this Object in the Global Tribe variable
 *
 * @since  4.7
 *
 * @type   {Object}
 */
tribe.utils = tribe.utils || {};

/**
 * A Module that allows one to convert a dash/dot/underscore/space separated
 * string to camelCase: foo-bar -> fooBar
 *
 * This module was heavily based on Sindresorhus work.
 * Only converted to play nicely on WordPress Plugin Env
 *
 * @see https://github.com/sindresorhus/camelcase
 *
 * @since  4.7
 *
 */
( function( obj, _ ) {
	'use strict';

	function preserveCamelCase( str ) {
		var isLastCharLower = false;
		var isLastCharUpper = false;
		var isLastLastCharUpper = false;

		for ( var i = 0; i < str.length; i++ ) {
			var char = str[ i ];

			if ( isLastCharLower && /[a-zA-Z]/.test( char ) && char.toUpperCase() === char ) {
				str = str.substr( 0, i ) + '-' + str.substr( i );

				isLastCharLower = false;
				isLastLastCharUpper = isLastCharUpper;
				isLastCharUpper = true;
				i++;
			} else if ( isLastCharUpper && isLastLastCharUpper && /[a-zA-Z]/.test( char ) && c.toLowerCase() === char ) {
				str = str.substr( 0, i - 1 ) + '-' + str.substr( i - 1 );

				isLastLastCharUpper = isLastCharUpper;
				isLastCharUpper = false;
				isLastCharLower = true;
			} else {
				isLastCharLower = char.toLowerCase() === char;
				isLastLastCharUpper = isLastCharUpper;
				isLastCharUpper = char.toUpperCase() === char;
			}
		}

		return str;
	}

	/**
	 * Converts a String into camelCase
	 *
	 * @since  4.7
	 *
	 * @param  {string} str String to be converted
	 *
	 * @return {string}
	 */
	obj.camelCase = function( str ) {
		// Makes sure we deal with strings only
		if ( arguments.length > 1 ) {
			// Remove any Empty Spaces
			str = _.map( arguments, function( val ) {
				return val.trim();
			} );

			// Remove any empty Entries and Join by `-`
			str = str.filter( str, function( val ){
				return 0 !== val.length;
			} ).join( '-' );
		} else {
			str = str.trim();
		}

		// by here we know what it is
		if ( 0 === str.length ) {
			return '';
		}

		if ( 1 === str.length ) {
			return str.toLowerCase();
		}

		if ( /^[a-z0-9]+$/.test( str ) ) {
			return str;
		}

		var hasUpperCase = str !== str.toLowerCase();

		if ( hasUpperCase ) {
			str = preserveCamelCase( str );
		}

		return str
			.replace( /^[_.\- ]+/, '' )
			.toLowerCase()
			.replace( /[_.\- ]+(\w|$)/g, function ( m, p1 ){
				return p1.toUpperCase();
			} );
	};
}( tribe.utils, window.underscore || _ ) );
