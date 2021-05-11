<?php
/**
 * Formatting function wrappers to allow use of
 * multibyte string functions where they are available
 * as using base functions on a multibyte string will porovide incorrect results
 *
 * @since 4.7.18
 */

if ( ! function_exists( 'tribe_detect_encoding' ) ) {
	/**
	 * Detects multibyte encoding if the function is available, returns false if not
	 * @since 4.7.18
	 *
	 * @param  string       $string the string to detect encoding of
	 * @return string|bool         the mb encoding of the string
	 *          false if the function is not available or the encoding cannot be determined
	 */
	function tribe_detect_encoding( $string ) {
		return function_exists( 'mb_detect_encoding' ) ? mb_detect_encoding( $string ) : false;
	}
}

if ( ! function_exists( 'tribe_maybe_urldecode' ) ) {
	/**
	 * Detects urlencoded strings if the function is available, and converts them.
	 * Returns false if not able to detect
	 * @since 4.7.18
	 *
	 * @param  string       $string the string to detect encoding of
	 * @return string|bool         the urldecoded string
	 *          the original string if the function is not available or the encoding cannot be determined
	 */
	function tribe_maybe_urldecode( $string ) {
		$encoding = function_exists( 'mb_detect_encoding' ) ? mb_detect_encoding( $string ) : $string;

		if ( 'ASCII' === $encoding ) {
			return urldecode( $string );
		}

		return $string;
	}
}

if ( ! function_exists( 'tribe_strlen' ) ) {
	/**
	 * Get the length of a string, uses mb_strlen when available
	 * @since 4.7.18
	 *
	 * @param  string $string the string to get the length of
	 * @return int         string length
	 */
	function tribe_strlen( $string ) {
		if ( function_exists( 'mb_strlen' ) ) {
			$encoding = tribe_detect_encoding( $string );
			$string = tribe_maybe_urldecode( $string );
			// we test for encoding and pass it if we get it
			if ( $encoding ) {
				return mb_strlen( $string, $encoding );
			}

			return mb_strlen( $string );
		}

		return strlen( $string );
	}
}

if ( ! function_exists( 'tribe_substr' ) ) {
	/**
	 * Get a substring, using multibyte functions if available
	 * @since 4.7.18
	 *
	 * @param  string  $string string to crop
	 * @param  int     $start  start position
	 * @param  int     $length (optional) substring length
	 * @return int          substring
	 */
	function tribe_substr( $string, $start = 0, $length = null ) {
		// Handle a passed $length
		if ( ! empty( $length ) ) {
			return function_exists( 'mb_substr' ) ? mb_substr( $string, $start, $length ) : substr( $string, $start, $length );
		}

		return function_exists( 'mb_substr' ) ? mb_substr( $string, $start ) : substr( $string, $start );
	}
}

if ( ! function_exists( 'tribe_strtoupper' ) ) {
	/**
	 * Convert string to uppercase, using multibyte functions if available
	 * @since 4.7.18
	 *
	 * @param  string $string string to convert
	 * @return string         converted string
	 */
	function tribe_strtoupper( $string ) {
		// If it's a number, don't try to convert it
		if ( is_numeric( $string ) ) {
			return $string;
		}

		if ( function_exists( 'mb_strtoupper' ) ) {
			$encoding = tribe_detect_encoding( $string );
			$string = tribe_maybe_urldecode( $string );
			// we test for encoding and pass it if we get it
			if ( $encoding ) {
				return mb_strtoupper( $string, $encoding );
			}

			return mb_strtoupper( $string );
		}

		return strtoupper( $string );
	}
}

if ( ! function_exists( 'tribe_strtolower' ) ) {
	/**
	 * Convert string to lowercase, using multibyte functions if available
	 * @since 4.7.18
	 *
	 * @param  string $string string to convert
	 * @return string         converted string
	 */
	function tribe_strtolower( $string ) {
		// If it's a number, don't try to convert it
		if ( is_numeric( $string ) ) {
			return $string;
		}

		if ( function_exists( 'mb_strtolower' ) ) {
			$encoding = tribe_detect_encoding( $string );
			$string = tribe_maybe_urldecode( $string );
			// we test for encoding and pass it if we get it
			if ( $encoding ) {
				return mb_strtolower( $string, $encoding );
			}

			return mb_strtolower( $string );
		}

		return strtolower( $string );
	}
}

if ( ! function_exists( 'tribe_uc_first_letter' ) ) {
	function tribe_uc_first_letter( $string ) {
		if ( is_numeric( $string ) ) {
			return $string;
		}

		$first_char = tribe_substr( $string, 0, 1 );
		$letter = tribe_strtoupper( $first_char );
		// fallback in case it gets garbled
		if ( '?' === $letter ) {
			$letter = $first_char;
		}

		return $letter;
	}
}

if ( ! function_exists( 'tribe_strpos' ) ) {
	/**
	 * Find the numeric position of the first occurrence of needle in the haystack string using multibyte function if available.
	 *
	 * @since 4.9.19
	 *
	 * @param string $haystack The string to search in.
	 * @param string $needle   The string to find in haystack.
	 * @param int    $offset   The search offset. If it is not specified, 0 is used. A negative offset counts from the end of the string.
	 *
	 * @return int|false The numeric position of the first occurrence of needle in the haystack string. If needle is not found, it returns false.
	 *
	 * @see strpos    The fallback function used if mb_strpos does not exist.
	 * @see mb_strpos The multibyte compatible version of strpos.
	 */
	function tribe_strpos( $haystack, $needle, $offset = 0 ) {
		if ( function_exists( 'mb_strpos' ) ) {
			$encoding = tribe_detect_encoding( $haystack );

			// Use encoding if it was detected.
			if ( $encoding ) {
				return mb_strpos( $haystack, $needle, $offset, $encoding );
			}

			return mb_strpos( $haystack, $needle, $offset );
		}

		return strpos( $haystack, $needle, $offset );
	}
}
