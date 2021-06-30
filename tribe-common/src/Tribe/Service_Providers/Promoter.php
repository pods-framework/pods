<?php

/**
 * Class Tribe__Service_Providers__Promoter_Connector
 *
 * @since 4.9
 *
 * Handles the registration and creation of our async process handlers.
 */
class Tribe__Service_Providers__Promoter extends tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 */
	public function register() {
		tribe_singleton( 'promoter.auth', 'Tribe__Promoter__Auth' );
		tribe_singleton( 'promoter.pue', 'Tribe__Promoter__PUE', [ 'load' ] );
		tribe_singleton( 'promoter.view', 'Tribe__Promoter__View' );

		$this->hook();
	}

	/**
	 * Setup hooks for classes.
	 */
	private function hook() {
		add_action( 'template_redirect', tribe_callback( 'promoter.view', 'display_auth_check_view' ), 10, 0 );
		add_action( 'init', tribe_callback( 'promoter.view', 'add_rewrites' ) );

		/** @var Tribe__Promoter__PUE $pue */
		$pue = tribe( 'promoter.pue' );

		// Only add the setting if a promoter key is present.
		if ( $pue->has_license_key() ) {
			add_action(
				'init',
				tribe_callback( 'promoter.auth', 'register_setting' )
			);
		}

		// The usage of a high priority so we can push the icon to the end
		add_action( 'admin_bar_menu', [ $this, 'add_promoter_logo_on_admin_bar' ], 1000 );
		add_action( 'tribe_common_loaded', [ $this, 'add_promoter_assets' ] );
	}

	/**
	 * Add Admin Bar link to the promoter website
	 *
	 * @since 4.9.2
	 * @param $wp_admin_bar
	 */
	public function add_promoter_logo_on_admin_bar( $wp_admin_bar ) {
		/** @var Tribe__Promoter__PUE $pue */
		$pue = tribe( 'promoter.pue' );
		if ( ! $pue->has_license_key() ) {
			return;
		}

		/**
		 * It uses and inline SVG as will provider more flexibility for styling so we can change
		 * the fill of the path property of the SVG so we can match the WP installations.
		 */
		$args = [
			'id'    => 'promoter-admin-bar',
			'title' => sprintf(
				"<span class='promoter-admin-bar__icon'>%s</span><span class='promoter-admin-bar__text'>%s</span>",
				'<svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 21.07 20"><path d="M17.36 9.37l2.39-5.15h-7.44l-.59-2.59A1.72 1.72 0 0 0 10 0H0l.23.88L5.22 20h1.45l-2.5-9.56h3.51L8.24 13A1.73 1.73 0 0 0 10 14.66h11.07zm.19-3.74l-1.8 3.88 2.62 3.74h-7.13l2-3.84a1.58 1.58 0 0 0 .15-.69l-.71-3.09zM1.82 1.41H10a.31.31 0 0 1 .31.31l1.62 7.06a.32.32 0 0 1-.31.25H3.81z" fill="#82878c"/><path d="M4.5 2.74H7a2.24 2.24 0 0 1 2.17 1.65A1.17 1.17 0 0 1 7.92 6H6.73l.48 1.7H6zm2.62 1.08h-1l.32 1.11h1a.4.4 0 0 0 .44-.55.79.79 0 0 0-.76-.56z" fill="#82878c"/></svg>',
				'Promoter'
			),
			'href'  => 'https://promoter.theeventscalendar.com/',
			'meta'  => [
				'target' => '_blank',
				'class'  => 'promoter-admin-bar-link',
			],
		];
		$wp_admin_bar->add_node( $args );
	}

	/**
	 * Register assets associated with promoter
	 *
	 * @since 4.9.2
	 */
	public function add_promoter_assets() {
		tribe_asset(
			Tribe__Main::instance(),
			'promoter',
			'promoter.css',
			[],
			[ 'wp_enqueue_scripts', 'admin_enqueue_scripts' ],
			[
				'conditionals' => [ $this, 'should_load_promoter_styles' ],
			]
		);
	}

	/**
	 * Only load the styles related to promoter if user is logged in and there's a valid license
	 * for promoter
	 *
	 * @since 4.9.2
	 *
	 * @return bool
	 */
	public function should_load_promoter_styles() {
		return is_user_logged_in() && tribe( 'promoter.pue' )->has_license_key();
	}
}
