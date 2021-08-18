<?php

namespace Pods;

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

		$this->hooks();
	}

	/**
	 * Hooks all the methods and actions the class needs.
	 *
	 * @since 2.8.0
	 */
	protected function hooks() {
		// Nothing here for now.
	}
}
