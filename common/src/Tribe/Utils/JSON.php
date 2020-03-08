<?php


/**
 * Class Tribe__Utils__JSON
 *
 * Provides JSON related utility functions.
 */
class Tribe__Utils__JSON {

	/**
	 * Recursively escapes quotes and JSON relevant chars in a string to avoid json operation errors.
	 *
	 * The method will recursively escape any string found.
	 *
	 * @param array|string $value Either a string to escape or an array of strings to escape.
	 *
	 * @return array|string Either an array of escaped strings or the escaped string.
	 */
	public static function escape_string( $value ) {
		if ( ! ( is_string( $value ) || is_array( $value ) ) ) {
			return $value;
		}
		if ( is_array( $value ) ) {
			$escaped = array();
			foreach ( $value as $key => $subvalue ) {
				$escaped[ $key ] = self::escape_string( $subvalue );
			}

			return $escaped;
		}

		$escapers     = array( "\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c" );
		$replacements = array( "\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b" );

		return str_replace( $escapers, $replacements, $value );
	}
}
