<?php

namespace Pods\REST\Interfaces\Swagger;

use Pods\REST\Interfaces\Endpoints\READ_Interface;

/**
 * Provider interface.
 *
 * @credit The Events Calendar team - https://github.com/the-events-calendar/tribe-common
 *
 * @since 3.0
 */
interface Builder_Interface {
	/**
	 * Registers a documentation provider for a path.
	 *
	 * @since 3.0
	 *
	 * @param string         $path
	 * @param READ_Interface $endpoint
	 */
	public function register_documentation_provider( $path, Provider_Interface $endpoint );

	/**
	 * @since 3.0
	 *
	 * @return Provider_Interface[]
	 */
	public function get_registered_documentation_providers();

	/**
	 * Registers a documentation provider for a definition.
	 *
	 * @since 3.0
	 *
	 * @param string             $type
	 * @param Provider_Interface $provider
	 */
	public function register_definition_provider( $type, Provider_Interface $provider );

	/**
	 * @since 3.0
	 *
	 * @return Provider_Interface[]
	 */
	public function get_registered_definition_providers();
}
