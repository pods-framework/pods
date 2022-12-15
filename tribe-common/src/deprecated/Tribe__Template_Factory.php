<?php
/**
 * Template Factory
 *
 * The parent class for managing the view methods in core and addons
 *
 */
_deprecated_file( __FILE__, '6.0.0', '' );

if ( class_exists( 'Tribe__Template_Factory' ) ) {
	return;
}

class Tribe__Template_Factory {
	/**
	 * @deprecated 6.0.0
	 * @return string[] An array of registered vendor script handles.
	 */
	public static function get_vendor_scripts() {
		_deprecated_function( __METHOD__, '6.0.0' );
		return [];
	}

	/**
	 * @deprecated 6.0.0
	 * @return string
	 */
	public static function getMinFile( $file, $_deprecated ) {
		_deprecated_function( __METHOD__, '6.0.0', 'Tribe__Assets::maybe_get_min_file' );
		return Tribe__Assets::maybe_get_min_file( $file );
	}
}