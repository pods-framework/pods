<?php

/**
 * Determines if upsells should be hidden.
 *
 * @since 4.14.16
 *
 * @param string $slug Which upsell is this conditional for, if nothing passed it will apply to all.
 *
 * @return bool
 */
function tec_should_hide_upsell( $slug = 'all' ) {
	$verify = static function( $needle, $haystack ) {
		// In all cases if true or false boolean we return that.
		if ( is_bool( $haystack ) ) {
			return $haystack;
		}

		if ( is_string( $haystack ) ) {
			// When all just return true to hide.
			if ( 'all' === $haystack ) {
				return true;
			}

			$truthy = tribe_is_truthy( $haystack );
			if ( $truthy ) {
				return $truthy;
			}
		}

		// Now allow multiple to be targeted as a string.
		$haystack = explode( '|', $haystack );

		// If the  `all` string is on the haystack
		if ( in_array( 'all', $haystack, true ) ) {
			return true;
		}

		return in_array( $needle, $haystack, true );
	};

	// If upsells have been manually hidden, respect that.
	if ( defined( 'TEC_HIDE_UPSELL' ) ) {
		return $verify( $slug, TEC_HIDE_UPSELL );
	}

	// If upsells have been manually hidden, respect that.
	if ( defined( 'TRIBE_HIDE_UPSELL' ) ) {
		return $verify( $slug, TRIBE_HIDE_UPSELL );
	}

	$env_var = getenv( 'TEC_HIDE_UPSELL' );
	if ( false !== $env_var ) {
		return $verify( $slug, $env_var );
	}

	/**
	 * Allows filtering of the Upsells for anything using Common.
	 *
	 * @since 4.14.16
	 *
	 * @param bool|string $hide Determines if Upsells are hidden.
	 * @param bool|string $slug Which slug we are testing against.
	 */
	$haystack = apply_filters( 'tec_should_hide_upsell', false, $slug );

	/**
	 * Allows filtering of the Upsells for anything using Common, for one specific slug.
	 *
	 * @since 4.14.16
	 *
	 * @param bool|string $hide Determines if Upsells are hidden.
	 * @param bool|string $slug Which slug we are testing against.
	 */
	$haystack = apply_filters( "tec_should_hide_upsell_{$slug}", $haystack, $slug );

	return $verify( $slug, $haystack );
}
