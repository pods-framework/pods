<?php

namespace Pods;

use Pods\Data\Map_Field_Values;
use Pods\Theme\WP_Query_Integration;

use tad_DI52_ServiceProvider;

/**
 * Class Service_Provider
 *
 * @since 2.8.0
 */
class Service_Provider extends tad_DI52_ServiceProvider {

	/**
	 * Registers the classes and functionality needed.
	 *
	 * @since 2.8.0
	 */
	public function register() {
		$this->container->singleton( Permissions::class, Permissions::class );
		$this->container->singleton( Map_Field_Values::class, Map_Field_Values::class );
		$this->container->singleton( WP_Query_Integration::class, WP_Query_Integration::class );
		$this->container->singleton( Static_Cache::class, Static_Cache::class );

		$this->hooks();
	}

	/**
	 * Hooks all the methods and actions the class needs.
	 *
	 * @since 2.8.0
	 */
	protected function hooks() {
		add_action( 'init', $this->container->callback( WP_Query_Integration::class, 'hook' ), 20 );
	}
}
