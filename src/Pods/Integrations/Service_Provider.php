<?php

namespace Pods\Integrations;

use tad_DI52_ServiceProvider;

/**
 * Class Service_Provider
 *
 * Add third party integrations where needed.
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

		if ( Polylang::is_active() ) {
			$this->container->singleton( 'pods.integration.i18n', Polylang::class );
		} elseif ( WPML::is_active() ) {
			$this->container->singleton( 'pods.integration.i18n', WPML::class );
		}

		$i18n_hooks = $this->container->callback( 'pods.integration.i18n', 'hook' );
		if ( is_callable( $i18n_hooks ) ) {
			$i18n_hooks();
		}
	}
}
