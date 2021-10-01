<?php

namespace Pods\Integrations;

use tad_DI52_ServiceProvider;

/**
 * Class Service_Provider
 *
 * Add third party integrations where needed.
 * @todo Make all integrations inherit the Integration abstract class and use loops.
 *
 * @since 2.8.0
 */
class Service_Provider extends tad_DI52_ServiceProvider {

	/**
	 * Registers the classes and functionality needed for third party integrations.
	 *
	 * @since 2.8.0
	 */
	public function register() {
		$this->container->singleton( 'pods.integration.genesis', Genesis::class );
		$this->container->singleton( 'pods.integration.yarpp', YARPP::class );
		$this->container->singleton( 'pods.integration.jetpack', Jetpack::class );

		$this->container->singleton( 'pods.integration.polylang', Polylang::class );
		$this->container->singleton( 'pods.integration.wpml', WPML::class );

		$this->hooks();
	}

	/**
	 * Hooks all the methods and actions the class needs.
	 *
	 * @since 2.8.0
	 */
	protected function hooks() {

		add_filter( 'pods_admin_config_pod_fields_post_type_supported_features', $this->container->callback( 'pods.integration.genesis', 'add_post_type_supports' ) );
		add_filter( 'pods_admin_config_pod_fields_post_type_supported_features', $this->container->callback( 'pods.integration.yarpp', 'add_post_type_supports' ) );
		add_filter( 'pods_admin_config_pod_fields_post_type_supported_features', $this->container->callback( 'pods.integration.jetpack', 'add_post_type_supports' ) );

		if ( ! did_action( 'plugins_loaded' ) ) {
			add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ] );
		} else {
			$this->plugins_loaded();
		}
	}

	/**
	 * All plugins are loaded.
	 *
	 * @since 2.8.0
	 */
	public function plugins_loaded() {

		$active = $this->container->callback( 'pods.integration.polylang', 'is_active' );
		if ( is_callable( $active ) && $active() ) {
			$hooks = $this->container->callback( 'pods.integration.polylang', 'hook' );
			if ( is_callable( $hooks ) ) {
				$hooks();
			}
		}
		$active = $this->container->callback( 'pods.integration.wpml', 'is_active' );
		if ( is_callable( $active ) && $active() ) {
			$hooks = $this->container->callback( 'pods.integration.wpml', 'hook' );
			if ( is_callable( $hooks ) ) {
				$hooks();
			}
		}
	}
}
