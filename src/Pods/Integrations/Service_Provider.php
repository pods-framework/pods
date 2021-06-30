<?php

namespace Pods\Integrations;

use tad_DI52_ServiceProvider;

/**
 * Class Service_Provider
 *
 * Add third party integrations where needed.
 *
 * @since 2.8
 */
class Service_Provider extends tad_DI52_ServiceProvider {

	/**
	 * Registers the classes and functionality needed for third party integrations.
	 *
	 * @since 2.8
	 */
	public function register() {
		$this->container->singleton( 'pods.integration.genesis', Genesis::class );
		$this->container->singleton( 'pods.integration.yarpp', YARPP::class );
		$this->container->singleton( 'pods.integration.jetpack', Jetpack::class );

		$this->hooks();
	}

	/**
	 * Hooks all the methods and actions the class needs.
	 *
	 * @since 2.8
	 */
	protected function hooks() {
		add_filter( 'pods_admin_config_pod_fields_post_type_supported_features', $this->container->callback( 'pods.integration.genesis', 'add_post_type_supports' ) );
		add_filter( 'pods_admin_config_pod_fields_post_type_supported_features', $this->container->callback( 'pods.integration.yarpp', 'add_post_type_supports' ) );
		add_filter( 'pods_admin_config_pod_fields_post_type_supported_features', $this->container->callback( 'pods.integration.jetpack', 'add_post_type_supports' ) );
	}
}
