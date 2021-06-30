<?php

namespace Pods;

use tad_DI52_ServiceProvider;

/**
 * Class Service_Provider
 *
 * @since 2.8
 */
class Service_Provider extends tad_DI52_ServiceProvider {

	/**
	 * Registers the classes and functionality needed.
	 *
	 * @since 2.8
	 */
	public function register() {
		$this->container->singleton( Permissions::class, Permissions::class );

		$this->hooks();
	}

	/**
	 * Hooks all the methods and actions the class needs.
	 *
	 * @since 2.8
	 */
	protected function hooks() {
		// Nothing here for now.
	}
}
