<?php

/**
 * Base Plugin register
 *
 * Register all plugins to Dependency Class
 *
 * @package Tribe
 * @since 4.9
 *
 */
abstract class Tribe__Abstract_Plugin_Register {

	/**
	 * The absolute path to the plugin file, the one that contains the plugin header.
	 *
	 * @var string
	 */
	protected $base_dir;

	/**
	 * @var string
	 */
	protected $main_class;

	/**
	 * @var string
	 */
	protected $version;

	/**
	 * @since 4.9.17
	 *
	 * @var array
	 */
	protected $classes_req = [];

	/**
	 * @var array
	 */
	protected $dependencies = [
		'parent-dependencies' => [],
		'co-dependencies'     => [],
		'addon-dependencies'  => [],
	];

	/**
	 * Registers a plugin with dependencies
	 */
	public function register_plugin() {
		tribe_register_plugin(
			$this->base_dir,
			$this->main_class,
			$this->version,
			$this->classes_req,
			$this->dependencies
		);
	}

	/**
	 * Returns whether or not the dependencies have been met
	 *
	 * This is basically an aliased function - register_plugins, upon
	 * second calling, returns whether or not a plugin should load.
	 *
	 * @deprecated since 4.9.17 It is unused by any Tribe plugins and returned void.
	 * @todo       remove in 4.11
	 */
	public function has_valid_dependencies() {
		_deprecated_function( __METHOD__, '4.9.17' );
	}
}