<?php

namespace Pods\REST\V1\Endpoints;

use Pods\REST\V1\Main;
use Tribe__Documentation__Swagger__Builder_Interface as Builder_Interface;
use Tribe__Documentation__Swagger__Provider_Interface as Provider_Interface;
use Tribe__REST__Endpoints__READ_Endpoint_Interface as READ_Endpoint_Interface;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class Swagger_Documentation
 *
 * @since 2.8.0
 */
class Swagger_Documentation implements Provider_Interface, READ_Endpoint_Interface, Builder_Interface {

	/**
	 * @var string
	 */
	protected $open_api_version = '3.0.0';

	/**
	 * @var string
	 */
	protected $current_rest_api_version;

	/**
	 * @var Provider_Interface[]
	 */
	protected $documentation_providers = [];

	/**
	 * @var Provider_Interface[]
	 */
	protected $definition_providers = [];

	public function hook() {
		/** @var Main $main */
		$main = pods_container( 'pods.rest-v1.main' );

		$this->set_current_rest_api_version( $main->get_semantic_version() );
	}

	/**
	 * Set the current REST API version.
	 *
	 * @since 2.8.0
	 *
	 * @param string $current_rest_api_version Current REST API version.
	 */
	public function set_current_rest_api_version( $current_rest_api_version ) {
		$this->current_rest_api_version = $current_rest_api_version;
	}

	/**
	 * Handles GET requests on the endpoint.
	 *
	 * @since 2.8.0
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response An array containing the data on success or a WP_Error instance on failure.
	 */
	public function get( WP_REST_Request $request ) {
		$data = $this->get_documentation();

		return new WP_REST_Response( $data );
	}

	/**
	 * Returns an array in the format used by Swagger 2.0.
	 *
	 * @since 2.8.0
	 *
	 * @return array An array description of a Swagger supported component.
	 *
	 * @link  http://swagger.io/
	 */
	public function get_documentation() {
		/** @var Main $main */
		$main = pods_container( 'pods.rest-v1.main' );

		$documentation = [
			'openapi'    => $this->open_api_version,
			'info'       => $this->get_api_info(),
			'servers'    => [
				[
					'url' => $main->get_url(),
				],
			],
			'paths'      => $this->get_paths(),
			'components' => [
				'schemas' => $this->get_definitions(),
			],
		];

		/**
		 * Filters the Swagger documentation generated for the REST API.
		 *
		 * @since 2.8.0
		 *
		 * @param array                 $documentation An associative PHP array in the format supported by Swagger.
		 * @param Swagger_Documentation $this          This documentation endpoint instance.
		 *
		 * @link  http://swagger.io/
		 */
		$documentation = apply_filters( 'pods_rest_swagger_documentation', $documentation, $this );

		return $documentation;
	}

	/**
	 * Get the REST API info.
	 *
	 * @since 2.8.0
	 *
	 * @return array The REST API info.
	 */
	protected function get_api_info() {
		return [
			'title'       => __( 'Pods REST API', 'pods' ),
			'description' => __( 'Pods REST API allows administration of Pods configurations easily and conveniently.', 'pods' ),
			'version'     => $this->current_rest_api_version,
		];
	}

	/**
	 * Get the REST API paths.
	 *
	 * @since 2.8.0
	 *
	 * @return array The REST API paths.
	 */
	protected function get_paths() {
		$paths = [];

		foreach ( $this->documentation_providers as $path => $endpoint ) {
			if ( $this === $endpoint ) {
				continue;
			}

			$paths[ $path ] = $endpoint->get_documentation();
		}

		return $paths;
	}

	/**
	 * Get the list of REST API definitions.
	 *
	 * @since 2.8.0
	 *
	 * @return array List of REST API definitions.
	 */
	protected function get_definitions() {
		$definitions = [];

		foreach ( $this->definition_providers as $type => $provider ) {
			$definitions[ $type ] = $provider->get_documentation();
		}

		return $definitions;
	}

	/**
	 * Registers a documentation provider for a path.
	 *
	 * @since 2.8.0
	 *
	 * @param string             $path     Documentation path.
	 * @param Provider_Interface $endpoint Docuemntation endpoint object.
	 */
	public function register_documentation_provider( $path, Provider_Interface $endpoint ) {
		$this->documentation_providers[ $path ] = $endpoint;
	}

	/**
	 * Get list of documentation providers.
	 *
	 * @since 2.8.0
	 *
	 * @return Provider_Interface[] List of documentation providers.
	 */
	public function get_registered_documentation_providers() {
		return $this->documentation_providers;
	}

	/**
	 * Registers a documentation provider for a definition.
	 *
	 * @since 2.8.0
	 *
	 * @param string             $type     The documentation provider type.
	 * @param Provider_Interface $provider The provider interface object.
	 */
	public function register_definition_provider( $type, Provider_Interface $provider ) {
		$this->definition_providers[ $type ] = $provider;
	}

	/**
	 * Get list of definition providers.
	 *
	 * @since 2.8.0
	 *
	 * @return Provider_Interface[] List of definition providers.
	 */
	public function get_registered_definition_providers() {
		return $this->definition_providers;
	}

	/**
	 * Returns the content of the `args` array that should be used to register the endpoint
	 * with the `register_rest_route` function.
	 *
	 * @since 2.8.0
	 *
	 * @return array Args to use for READ requests.
	 */
	public function READ_args() {
		return [];
	}

	/**
	 * Get REST API Documentation for the Swagger endpoint.
	 *
	 * @since 2.8.0
	 *
	 * @return array REST API Documentation for the Swagger endpoint.
	 */
	protected function get_own_documentation() {
		return [
			'get' => [
				'responses' => [
					'200' => [
						'description' => __( 'Returns the documentation for the Pods REST API in Swagger consumable format.', 'pods' ),
					],
				],
			],
		];
	}
}
