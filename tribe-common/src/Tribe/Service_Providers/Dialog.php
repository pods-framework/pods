<?php

namespace Tribe\Service_Providers;

/**
 * Class Dialog
 *
 * @since 4.10.0
 *
 * Handles the registration and creation of our async process handlers.
 */
class Dialog extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 4.10.0
	 */
	public function register() {
		tribe_singleton( 'dialog.view', '\Tribe\Dialog\View' );

		/**
		 * Allows plugins to hook into the register action to register views, etc
		 *
		 * @since 4.10.0
		 *
		 * @param Tribe\Service_Providers\Dialog $dialog
		 */
		do_action( 'tribe_dialog_register', $this );

		$this->hooks();
	}

	/**
	 * Set up hooks for classes.
	 *
	 * @since 4.10.0
	 */
	private function hooks() {
		add_action( 'tribe_common_loaded', [ $this, 'register_dialog_assets' ] );
		add_filter( 'tribe_template_public_namespace', [ $this, 'template_public_namespace' ], 10, 2 );

		/**
		 * Allows plugins to hook into the hooks action to register their own hooks
		 *
		 * @since 4.10.0
		 *
		 * @param Tribe\Service_Providers\Dialog $dialog
		 */
		do_action( 'tribe_dialog_hooks', $this );
	}

	/**
	  * {@inheritdoc}
	 *
	 * @since  4.10.0
	 */
	public function template_public_namespace( $namespace, $obj ) {
		if ( ! empty( $obj->template_namespace ) && 'dialog' === $obj->template_namespace ) {
			array_push( $namespace, 'dialog' );
		}

		return $namespace;
	}

	/**
	 * Register assets associated with dialog
	 *
	 * @since 4.10.0
	 */
	public function register_dialog_assets() {
		$main = \Tribe__Main::instance();

		tribe_asset(
			$main,
			'tribe-dialog',
			'dialog.css',
			[],
			[],
			[ 'groups' => 'tribe-dialog' ]
		);

		tribe_asset(
			$main,
			'mt-a11y-dialog',
			'node_modules/mt-a11y-dialog/a11y-dialog.js',
			[ 'underscore', 'tribe-common' ],
			[],
			[ 'groups' => 'tribe-dialog' ]
		);

		tribe_asset(
			$main,
			'tribe-dialog-js',
			'dialog.js',
			[ 'mt-a11y-dialog' ],
			[],
			[ 'groups' => 'tribe-dialog' ]
		);

		/**
		 * Allows plugins to hook into the assets action to register their own assets
		 *
		 * @since 4.10.0
		 *
		 * @param Tribe\Service_Providers\Dialog $dialog
		 */
		do_action( 'tribe_dialog_assets_registered', $this );
	}
}
