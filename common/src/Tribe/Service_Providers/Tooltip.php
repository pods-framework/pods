<?php

/**
 * Class Tribe__Service_Providers__Tooltip
 *
 * @since 4.9.8
 *
 * Handles the registration and creation of our async process handlers.
 */
class Tribe__Service_Providers__Tooltip extends tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 4.9.8
	 */
	public function register() {
		tribe_singleton( 'tooltip.view', 'Tribe__Tooltip__View' );

		$this->hook();
	}

	/**
	 * Setup hooks for classes.
	 *
	 * @since 4.9.8
	 */
	private function hook() {
		add_action( 'tribe_common_loaded', [ $this, 'add_tooltip_assets' ] );
	}

	/**
	 * Register assets associated with tooltip
	 *
	 * @since 4.9.8
	 */
	public function add_tooltip_assets() {
		tribe_asset(
			Tribe__Main::instance(),
			'tribe-tooltip-css',
			'tooltip.css',
			[ 'tribe-common-skeleton-style' ],
			[ 'wp_enqueue_scripts', 'admin_enqueue_scripts' ]
		);

		tribe_asset(
			Tribe__Main::instance(),
			'tribe-tooltip-js',
			'tooltip.js',
			[ 'jquery', 'tribe-common' ],
			[ 'wp_enqueue_scripts', 'admin_enqueue_scripts' ]
		);
	}
}
