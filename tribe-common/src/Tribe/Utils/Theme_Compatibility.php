<?php
/**
 * Add theme compatibility classes.
 *
 * @since   4.14.0
 *
 * @package Tribe\Utils
 */
namespace Tribe\Utils;

use Compatibility_Classes;
use \WP_Theme;

class Theme_Compatibility {
	/**
	 * List of themes which have compatibility requirements.
	 *
	 * @since 4.14.0
	 *
	 * @var   array
	 */
	protected static $themes = [
		'avada',
		'divi',
		'enfold',
		'genesis',
		'twentyseventeen',
		'twentynineteen',
		'twentytwenty',
		'twentytwentyone',
	];

	/**
	 * Checks if the current theme needs a compatibility fix.
	 *
	 * @since  4.14.0
	 *
	 * @param null|string $theme Optionally, pass a specific theme name in to see if compatibility
	 *                      is required for that theme.
	 *
	 * @return boolean
	 */
	public static function is_compatibility_required( $theme = null ) {
		// Passing a theme name skips these checks.
		if ( empty( $theme ) ) {
			$current_theme = static::get_current_theme( true );

			if ( empty( $current_theme ) || empty( $current_theme->get_template() ) ) {
				return false;
			}

			$theme = $current_theme->get_template();
		}

		$required = in_array( $theme, static::get_registered_themes() );

		/**
		 * Allows hooking in to enforce compatibility by other plugins.
		 *
		 * @since 4.14.0
		 *
		 * @param boolean $required  If compatibility is required.
		 * @param null|string $theme The optional theme name string.
		 */
		$required = apply_filters( 'tribe_compatibility_required', $required, $theme );

		return tribe_is_truthy( $required );
	}

	/**
	 * Contains the logic for if this object's classes should be added to the queue.
	 *
	 * @since 4.14.0
	 *
	 * @param boolean $add   Whether to add the class to the queue or not.
	 * @param array   $class The array of compatibility class names to add.
	 * @param string  $queue The queue we want to get 'admin', 'display', 'all'.
	 *
	 * @return boolean Whether compatibility classes should be added or not.
	 */
	public static function should_add_compatibility_class_to_queue( $add, $class, $queue ) {
		if (
			'admin' === $queue
			|| ! static::is_compatibility_required()
		) {
			return $add;
		}

		if ( in_array( $class, static::get_compatibility_classes() ) ) {
			$add = true;
		}

		/**
		 * Filters whether we should add a specific class to the queue.
		 *
		 * @since 4.14.0
		 *
		 * @param boolean $add   Whether to add the class to the queue or not.
		 * @param array   $class The array of compatibility class names to add.
		 * @param string  $queue The queue we want to get 'admin', 'display', 'all'.
		 */
		return apply_filters( 'tribe_compatibility_add_class', $add, $class, $queue );
	}

	/**
	 * Add compatibility classes.
	 *
	 * @since 4.14.0
	 *
	 * @return void
	 */
	public static function add_compatibility_classes() {
		tribe( Compatibility_Classes::class )->add_classes( static::get_compatibility_classes() );
	}

	/**
	 * Fetches the correct class strings for theme and child theme if available + the container class.
	 *
	 * @since 4.14.0
	 *
	 * @return array $classes
	 */
	public static function get_container_classes() {
		$classes =  [ 'tribe-compatibility-container' ];

		if ( static::is_compatibility_required() ) {
			$classes = array_merge( $classes, static::get_compatibility_classes() );
		}

		/**
		 * Filters the HTML classes applied to a compatibility container.
		 *
		 * @since 4.14.0
		 *
		 * @param array  $html_classes Array of classes used for this container.
		 */
		return apply_filters( 'tribe_compatibility_container_classes', $classes );
	}

	/**
	 * Fetches the correct class strings for theme and child theme if available.
	 *
	 * @since 4.14.0
	 *
	 * @return array $classes
	 */
	public static function get_compatibility_classes() {
		$classes      = [];
		$current_theme = static::get_current_theme( true );

		if ( empty( $current_theme ) || empty( $current_theme->get_template() ) ) {
			return $classes;
		}

		// Detect if we're using a child theme.
		if ( $parent = $current_theme->parent ) {
			$classes[] = sanitize_html_class( 'tribe-theme-' . $parent->get_template() );
			$classes[] = sanitize_html_class( 'tribe-theme-child-' . $current_theme->get_template() );
		} else {
			$classes[] = sanitize_html_class( 'tribe-theme-' . $current_theme->get_template() );
		}

		/**
		 * Filters the list of classes we're adding.
		 *
		 * @since 4.14.0
		 *
		 * @param array $classes An array of classes in the shape `[ <slug> => boolean ]`.
		 */
		return apply_filters( 'tribe_compatibility_classes', $classes );
	}

	/**
	 * Returns a list of themes registered for compatibility with our Views.
	 *
	 * @since  4.14.0
	 *
	 * @return array An array of the themes registered.
	 */
	public static function get_registered_themes() {
		/**
		 * Filters the list of themes that are registered for compatibility.
		 *
		 * @since 4.14.0
		 *
		 * @param array $registered An array of views in the shape `[ <slug> ]`.
		 */
		return (array) apply_filters( 'tribe_theme_compatibility_registered', self::$themes );
	}

	/**
	 * Returns an array of active themes (parent and child).
	 *
	 * @since 4.14.0
	 *
	 * @return array $themes An array in the format [ 'parent' => 'theme name', 'child' => 'theme name' ].
	 *                       Empty array if none found.
	 */
	public static function get_active_themes() {
		$themes        = [];
		$current_theme = static::get_current_theme( true );

		if ( empty( $current_theme ) ) {
			return $themes;
		}

		$parent_theme  = $current_theme->parent();

		// No parent theme.
		if ( empty( $parent_theme ) ) {
			$themes['parent'] = strtolower( $current_theme->get_template() );
			return $themes;
		}

		$themes['parent'] = strtolower( $parent_theme->get_template() );
		$child_theme      = $current_theme->get( 'stylesheet' );

		// if the 2 options are the same, then there is no child theme.
		if ( $child_theme !== $parent_theme ) {
			$themes['child'] = strtolower( $child_theme );
		}

		return $themes;
	}

	/**
	 * Get the current theme.
	 *
	 * @since 4.14.0
	 *
	 * @param boolean $object Pass true if you want the theme object returned instead of the name.
	 *
	 * @return string|object|boolean Will return the theme name by default.
	 *                               Will return the theme object if passed boolean true as the parameter.
	 *                               Will return boolean false if the theme is not found.
	 */
	public static function get_current_theme( $object = false ) {
		$current_theme = wp_get_theme();

		// If we can't get it for some reason...
		if ( ! $current_theme instanceof WP_Theme || ! $current_theme->exists() ) {
			return false;
		}

		if ( $object ) {
			return $current_theme;
		}

		return $current_theme->get_template();
	}

	/**
	 * Checks if the provided theme is active.
	 *
	 * @since 4.14.0
	 *
	 * @param string $theme The theme name like 'avada' or 'twentytwenty',
	 *
	 * @return boolean True if the requested theme is active,
	 *                 false if the current theme could not be found or is not the requested theme.
	 */
	public static function is_active_theme( $check ) {
		$current_theme = wp_get_theme();

		// Current theme is not
		if ( ! $current_theme instanceof \WP_Theme ) {
			$theme = false;
		} elseif ( ! $current_theme->exists() ) {
			$theme = false;
		} else {
			$theme = $current_theme->get_template();
		}

		return ! empty( $theme ) && strtolower( $check ) === strtolower( $theme );
	}
}
