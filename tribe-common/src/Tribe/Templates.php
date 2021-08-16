<?php
/**
 * Templating functionality for common tribe
 */

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( class_exists( 'Tribe__Templates' ) ) {
	return;
}

/**
 * Handle views and template files.
 */
class Tribe__Templates {
	/**
	 * Check to see if this is operating in the main loop
	 *
	 * @param WP_Query $query
	 *
	 * @return bool
	 */
	protected static function is_main_loop( $query ) {
		return $query->is_main_query();
	}

	/**
	 * Look for the stylesheets. Fall back to $fallback path if the stylesheets can't be located or the array is empty.
	 *
	 * @param array|string $stylesheets Path to the stylesheet
	 * @param bool|string  $fallback    Path to fallback stylesheet
	 *
	 * @return bool|string Path to stylesheet
	 */
	public static function locate_stylesheet( $stylesheets, $fallback = false ) {
		if ( ! is_array( $stylesheets ) ) {
			$stylesheets = [ $stylesheets ];
		}
		if ( empty( $stylesheets ) ) {
			return $fallback;
		}
		foreach ( $stylesheets as $filename ) {
			if ( file_exists( get_stylesheet_directory() . '/' . $filename ) ) {
				$located = trailingslashit( get_stylesheet_directory_uri() ) . $filename;
				break;
			} else {
				if ( file_exists( get_template_directory() . '/' . $filename ) ) {
					$located = trailingslashit( get_template_directory_uri() ) . $filename;
					break;
				}
			}
		}
		if ( empty( $located ) ) {
			return $fallback;
		}

		return $located;
	}

	/**
	 * Add our own method is_embed to check by WordPress Version and function is_embed
	 * to prevent fatal errors in WordPress 4.3 and earlier
	 *
	 * @version 4.2.1
	 */
	public static function is_embed() {
		global $wp_version;
		if ( version_compare( $wp_version, '4.4', '<' ) || ! function_exists( 'is_embed' ) ) {
			return false;
		}

		return is_embed();

	}

}//end class
