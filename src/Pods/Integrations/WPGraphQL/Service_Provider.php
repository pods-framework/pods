<?php

namespace Pods\Integrations\WPGraphQL;

use tad_DI52_ServiceProvider;

/**
 * Class Service_Provider
 *
 * @since 2.9.0
 */
class Service_Provider extends tad_DI52_ServiceProvider {

	/**
	 * Registers the classes and functionality needed.
	 *
	 * @since 2.9.0
	 */
	public function register() {
		$this->container->singleton( Integration::class, Integration::class );
		$this->container->singleton( Settings::class, Settings::class );

		$this->hooks();
	}

	/**
	 * Hooks all the methods and actions the class needs.
	 *
	 * @since 2.9.0
	 */
	protected function hooks() {
		add_action( 'init', [ $this, 'hook_init' ] );
	}

	public function hook_init() {
		$integration = pods_container( Integration::class );

		$requirements = $integration->get_requirements();

		$pods_admin = pods_admin();

		// Only hook into the integration if the requirements are met.
		if ( ! $pods_admin->check_requirements( $requirements ) ) {
			return;
		}

		$integration->hook();

		// Get the Settings instance and register the settings.
		$settings = pods_container( Settings::class );

		$settings->hook();
	}
}
