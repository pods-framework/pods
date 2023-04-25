<?php

namespace Pods\REST\V1;

use Exception;
use Pods\REST\V1\Endpoints\Field;
use Pods\REST\V1\Endpoints\Field_Slug;
use Pods\REST\V1\Endpoints\Fields;
use Pods\REST\V1\Endpoints\Group;
use Pods\REST\V1\Endpoints\Group_Slug;
use Pods\REST\V1\Endpoints\Groups;
use Pods\REST\V1\Endpoints\Pod;
use Pods\REST\V1\Endpoints\Pod_Slug;
use Pods\REST\V1\Endpoints\Pods;
use Pods\REST\V1\Endpoints\Swagger_Documentation;
use Pods\REST\V1\Validator\Base as Base_Validator;
use Tribe__Documentation__Swagger__Builder_Interface as Swagger_Builder_Interface;
use WP_REST_Server;
use tad_DI52_ServiceProvider;

/**
 * Class Service_Provider
 *
 * Add REST API endpoints and objects.
 *
 * @since 2.8.0
 */
class Service_Provider extends tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 */
	public $namespace;

	/**
	 * Registers the classes and functionality needed for the REST API.
	 *
	 * @since 2.8.0
	 */
	public function register() {
		$this->container->singleton( 'pods.rest-v1.main', Main::class );
		$this->container->singleton( 'pods.rest-v1.messages', Messages::class );
		$this->container->singleton( 'pods.rest-v1.validator', Base_Validator::class );
		$this->container->singleton( 'pods.rest-v1.repository', Post_Repository::class );

		$messages        = pods_container( 'pods.rest-v1.messages' );
		$post_repository = pods_container( 'pods.rest-v1.repository' );
		$validator       = pods_container( 'pods.rest-v1.validator' );

		$endpoints = $this->get_endpoints();

		foreach ( $endpoints as $key => $endpoint ) {
			if ( is_numeric( $key ) ) {
				$key = $endpoint;
			}

			$after_build_methods = [];

			if ( method_exists( $endpoint, 'hook' ) ) {
				$after_build_methods[] = 'hook';
			}

			$this->container->singleton( $key, new $endpoint( $messages, $post_repository, $validator ), $after_build_methods );
		}

		$this->hooks();
	}

	/**
	 * Get the list of endpoints.
	 *
	 * @since 2.8.11
	 *
	 * @return string[] The list of endpoints.
	 */
	public function get_endpoints() {
		$endpoints = [
			'pods.rest-v1.endpoints.pods'          => Pods::class,
			'pods.rest-v1.endpoints.pod'           => Pod::class,
			'pods.rest-v1.endpoints.pod-slug'      => Pod_Slug::class,
			'pods.rest-v1.endpoints.fields'        => Fields::class,
			'pods.rest-v1.endpoints.field'         => Field::class,
			'pods.rest-v1.endpoints.field-slug'    => Field_Slug::class,
			'pods.rest-v1.endpoints.groups'        => Groups::class,
			'pods.rest-v1.endpoints.group'         => Group::class,
			'pods.rest-v1.endpoints.group-slug'    => Group_Slug::class,
			'pods.rest-v1.endpoints.documentation' => Swagger_Documentation::class,
		];

		return (array) apply_filters( 'pods_rest_v1_endpoints', $endpoints );
	}

	/**
	 * Registers the REST API endpoints.
	 *
	 * @since 2.8.0
	 */
	public function register_endpoints() {
		/** @var Main $main */
		$main = pods_container( 'pods.rest-v1.main' );

		$this->namespace = $main->get_pods_route_namespace();

		$endpoints = $this->get_endpoints();

		foreach ( $endpoints as $key => $endpoint ) {
			if ( is_numeric( $key ) ) {
				$key = $endpoint;
			}

			try {
				$endpoint_obj = pods_container( $key );

				if ( method_exists( $endpoint_obj, 'register_routes' ) ) {
					$endpoint_obj->register_routes( $this->namespace, true );
				}
			} catch ( Exception $exception ) {
				pods_debug_log( $exception );
			}
		}

		$this->register_endpoint_documentation();
	}

	/**
	 * Builds, registers and returns the Swagger.io documentation provider endpoint.
	 *
	 * @since 2.8.0
	 *
	 * @return Swagger_Builder_Interface
	 */
	protected function register_endpoint_documentation() {
		/** @var Swagger_Builder_Interface $endpoint */
		$endpoint = pods_container( 'pods.rest-v1.endpoints.documentation' );

		register_rest_route( $this->namespace, '/doc', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $endpoint, 'get' ],
			'permission_callback' => '__return_true',
		] );

		//$endpoint->register_definition_provider( 'XYZ', new Tribe__REST__V1__Documentation__XYZ_Definition_Provider() );

		$endpoint->register_documentation_provider( '/doc', $endpoint );

		return $endpoint;
	}

	/**
	 * Hooks all the methods and actions the class needs.
	 *
	 * @since 2.8.0
	 */
	protected function hooks() {
		add_action( 'rest_api_init', [ $this, 'register_endpoints' ] );
	}
}
