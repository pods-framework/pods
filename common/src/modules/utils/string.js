/**
 * External dependencies
 */
import { escapeRegExp, isUndefined, isString, identity } from 'lodash';

/**
 * Test if a string is equivalent to a true value
 *
 * @param {string} value The value to be tested
 * @returns {boolean} true if the value is a valid "true" value.
 */
export const isTruthy = ( value ) => {
	const validValues = [
		'true',
		'yes',
		'1',
	];
	return validValues.indexOf( value ) !== -1;
};

/**
 * Test if a string is equivalent to a false value
 *
 * @param {string} value The value to be tested
 * @returns {boolean} true if the value is a valid "false" value.
 */
export const isFalsy = ( value ) => {
	const validValues = [
		'false',
		'no',
		'0',
		'',
	];
	return validValues.indexOf( value ) !== -1;
};

export const replaceWithObject = ( str = '', pairs = {} ) => {
	const substrs = Object.keys( pairs ).map( escapeRegExp );
	return str.split( RegExp( `(${ substrs.join( '|' ) })` ) )
		.map( part => isUndefined( pairs[ part ] ) ? part : pairs[ part ] )
		.join( '' );
};

/**
 * Extract the words from a string into an array of words removing all the empty spaces.
 *
 * @param {string} text The initial text
 * @returns {array} Return an array with the words
 */
export const getWords = ( text = '' ) => {
	if ( ! isString( text ) ) {
		return [];
	}
	return text.split( /\s/ ).filter( identity );
};

/**
 * Apply separators specifically for check box style list where if there are more than 2 words the first
 * separators except the last is different from the rest, and if there are only 2 words it only uses
 * the last separator instead
 *
 * @param {array} words The list of words to join
 * @param {string} startSeparator the separator applied if there are more than 2 words between all the words except the last one
 * @param {string} endSeparator separator applied between the last words
 * @returns {string} return a string with custom separators between words
 */
export const wordsAsList = ( words, startSeparator = ', ', endSeparator = ' & ' ) => {
	if ( words.length <= 1 ) {
		return words.join( '' );
	} else {
		const start = words.slice( 0, words.length - 1 ).join( startSeparator );
		const last = words[ words.length - 1 ];
		return `${ start }${ endSeparator }${ last }`;
	}
};

/**
 * Creates a string that only contains a-z characters, useful specially for keys
 *
 * @param {string} text Then ame to be normalized
 * @returns {string} A formatted string with no spacing and only a-z chars
 */
export const normalize = ( text = '' ) => {
	if ( ! isString( text ) ) {
		return '';
	}
	return text.toLowerCase()
		// Remove any non word or space
		.replace( /[^a-z\s]/g, '' )
		.trim()
		.replace( /\s+/g, '-' );
};

/**
 * Remove invalid characters from a string that aren't consider as valid for a block name.
 *
 * @since 4.8
 *
 * @param {string} text The text to be formatted as block name
 * @returns {string} The formatted text
 */
export const toBlockName = ( text = '' ) => {
	if ( ! isString( text ) ) {
		return '';
	}

	// Remove any non numeric, a-z or - value
	return text.replace(/[^a-zA-Z0-9-]/g, '' );
}
