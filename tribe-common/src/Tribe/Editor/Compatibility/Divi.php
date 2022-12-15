<?php

namespace Tribe\Editor\Compatibility;

use WP_Post;
use Tribe__Cache;
use Tribe__Cache_Listener;

/**
 * Editor Compatibility with Divi theme's builder option.
 *
 * @since 4.14.13
 */
class Divi {
	/**
	 * The key for the Divi classic editor.
	 *
	 * @since 4.14.13
	 *
	 * @var string
	 */
	public static $classic_key = 'et_enable_classic_editor';

	/**
	 * The value for enabling the Divi classic editor.
	 *
	 * @since 4.14.13
	 *
	 * @var string
	 */
	public static $classic_on = 'on';

	/**
	 * The value for disabling the Divi classic editor.
	 *
	 * @since 4.14.13
	 *
	 * @var string
	 */
	public static $classic_off = 'off';

	/**
	 * Registers the hooks and filters required based on if the Classic Editor plugin is active.
	 *
	 * @since 4.14.13
	 */
	public function init() {
		if ( ! static::is_divi_active() ) {
			return;
		}

		$this->hooks();
	}

	/**
	 * Hooks for loading logic outside this class.
	 *
	 * @since 4.14.13
	 */
	public function hooks() {
		// Trying to filter out instances where we shouldn't add this to save cycles is futile.
		add_filter( 'tribe_editor_should_load_blocks', [ $this, 'filter_tribe_editor_should_load_blocks' ], 20 );
	}

	public static function is_divi_active() {
		/** @var Tribe__Cache $cache */
		$cache = tribe( 'cache' );

		$divi = $cache->get( 'is_divi' );

		if ( false !== $divi ) {
			// Stored as an int - convert to a boolean.
			return (bool) $divi;
		}

		// OK, do it the hard way.
		$theme = wp_get_theme();
		// Handle theme children and variations.
		$divi = 'Divi' == $theme->name || 'Divi' == $theme->template || 'Divi' == $theme->parent_theme;

		// Cache to save us this work next time.
		$cache->set( 'is_divi', (int) $divi, Tribe__Cache::NON_PERSISTENT, Tribe__Cache_Listener::TRIGGER_UPDATED_OPTION );

		// Stored as an int - convert to a boolean.
		return (bool) $divi;
	}

	/**
	 * Filters tribe_editor_should_load_blocks based on internal logic.
	 *
	 * @since 4.14.13
	 *
	 * @param boolean $should_load_blocks Whether we should force blocks over classic.
	 *
	 * @return boolean Whether we should force blocks or classic.
	 */
	public function filter_tribe_editor_should_load_blocks( $should_load_blocks ) {
		// et_enable_classic_editor
		$divi_option = get_option( 'et_divi', [] );

		if ( empty( $divi_option[ static::$classic_key ] ) ) {
			return $should_load_blocks;
		} else if ( static::$classic_on === $divi_option[ static::$classic_key ] ) {
			return false;
		}

		return $should_load_blocks;
	}
}
