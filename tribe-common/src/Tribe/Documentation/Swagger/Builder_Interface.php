<?php

interface Tribe__Documentation__Swagger__Builder_Interface {
	/**
	 * Registers a documentation provider for a path.
	 *
	 * @param                                            $path
	 * @param Tribe__REST__Endpoints__READ_Endpoint_Interface $endpoint
	 */
	public function register_documentation_provider( $path, Tribe__Documentation__Swagger__Provider_Interface $endpoint );

	/**
	 * @return Tribe__Documentation__Swagger__Provider_Interface[]
	 */
	public function get_registered_documentation_providers();

	/**
	 * Registers a documentation provider for a definition.
	 *
	 * @param                                                  string $type
	 * @param Tribe__Documentation__Swagger__Provider_Interface       $provider
	 */
	public function register_definition_provider( $type, Tribe__Documentation__Swagger__Provider_Interface $provider );

	/**
	 * @return Tribe__Documentation__Swagger__Provider_Interface[]
	 */
	public function get_registered_definition_providers();
}
