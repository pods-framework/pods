<?php

namespace Pods\REST\V1\Endpoints;

use Tribe__REST__Messages_Interface as Messages_Interface;
use Tribe__REST__Post_Repository as Post_Repository;
use Tribe__Validator__Interface as Validator_Interface;
use Tribe__Utils__Array as Utils_Array;

/**
 * Class Base
 *
 * @since 2.8
 */
abstract class Base {

	/**
	 * @since 2.8
	 * @var string
	 */
	public $route;

	/**
	 * @since 2.8
	 * @var Messages_Interface
	 */
	protected $messages;

	/**
	 * @since 2.8
	 * @var Post_Repository
	 */
	protected $post_repository;

	/**
	 * @since 2.8
	 * @var Validator_Interface
	 */
	protected $validator;

	/**
	 * @since 2.8
	 * @var array
	 */
	protected $supported_query_vars = [];

	/**
	 * Base constructor.
	 *
	 * @since 2.8
	 *
	 * @param Messages_Interface  $messages
	 * @param Post_Repository     $post_repository
	 * @param Validator_Interface $validator
	 */
	public function __construct(
		Messages_Interface $messages, Post_Repository $post_repository = null, Validator_Interface $validator = null
	) {
		$this->messages        = $messages;
		$this->post_repository = $post_repository;
		$this->validator       = $validator;
	}

	/**
	 * Converts an array of arguments suitable for the WP REST API to the Swagger format.
	 *
	 * @since 2.8
	 *
	 * @param array $args     List of arguments to convert to Swagger format.
	 * @param array $defaults List of defaults to merge into the arguments.
	 *
	 * @return array The converted arguments.
	 */
	public function swaggerize_args( array $args = [], array $defaults = [] ) {
		if ( empty( $args ) ) {
			return $args;
		}

		$no_description = __( 'No description provided', 'pods' );

		$defaults = array_merge( [
			'in'          => 'body',
			'schema'      => [
				'type'    => 'string',
				'default' => '',
			],
			'description' => $no_description,
			'required'    => false,
			'items'       => [
				'type' => 'integer',
			],
		], $defaults );

		$swaggerized = [];

		foreach ( $args as $name => $info ) {
			$type = false;

			if ( isset( $info['swagger_type'] ) ) {
				$type = $info['swagger_type'];
			} elseif ( isset( $info['type'] ) ) {
				$type = $info['type'];
			}

			if ( is_array( $type ) ) {
				$type = $this->convert_type( $type );
			}

			$schema = null;

			if ( is_array( $type ) ) {
				$schema = $type;

				unset( $info['swagger_type'] );
			} else {
				$schema = [
					'type'    => $type,
					'default' => isset( $info['default'] ) ? $info['default'] : false,
				];
			}

			$read = [
				'name'             => $name,
				'description'      => isset( $info['description'] ) ? $info['description'] : false,
				'in'               => isset( $info['in'] ) ? $info['in'] : false,
				'collectionFormat' => isset( $info['collectionFormat'] ) ? $info['collectionFormat'] : false,
				'schema'           => $schema,
				'items'            => isset( $info['items'] ) ? $info['items'] : false,
				'required'         => isset( $info['required'] ) ? $info['required'] : false,
			];

			if ( isset( $info['swagger_type'] ) ) {
				$read['schema']['type'] = $info['swagger_type'];
			}

			if ( isset( $read['schema']['type'] ) && $read['schema']['type'] !== 'array' ) {
				unset( $defaults['items'] );
			}

			$merged = array_merge( $defaults, array_filter( $read ) );

			unset( $merged['type'], $merged['default'] );

			$swaggerized[] = $merged;
		}

		return $swaggerized;
	}

	/**
	 * Parses the arguments populated parsing the request filling out with the defaults.
	 *
	 * @since 2.8
	 *
	 * @param array $args     List of arguments to fill out with defaults.
	 * @param array $defaults List of defaults to merge with arguments.
	 *
	 * @return array List of arguments populated from the request.
	 */
	protected function parse_args( array $args, array $defaults ) {
		foreach ( $this->supported_query_vars as $request_key => $query_var ) {
			if ( isset( $defaults[ $request_key ] ) ) {
				$defaults[ $query_var ] = $defaults[ $request_key ];
			}
		}

		$args = wp_parse_args( array_filter( $args, [ $this, 'is_not_null' ] ), $defaults );

		return $args;
	}

	/**
	 * Check whether a value is null or not.
	 *
	 * @since 2.8
	 *
	 * @param mixed $value The value to check.
	 *
	 * @return bool Whether a value is null or not.
	 */
	public function is_not_null( $value ) {
		return null !== $value;
	}

	/**
	 * Converts REST format type argument to the corresponding Swagger.io definition.
	 *
	 * @since 2.8
	 *
	 * @param string $type A type string or an array of types to define a `oneOf` type.
	 *
	 * @return string A converted type or the original types array.
	 */
	protected function convert_type( $type ) {
		$rest_to_swagger_type_map = [
			'int'  => 'integer',
			'bool' => 'boolean',
		];

		return Utils_Array::get( $rest_to_swagger_type_map, $type, $type );
	}

	/**
	 * Get the route path for this endpoint.
	 *
	 * @since 2.8
	 *
	 * @return string The route path.
	 */
	public function get_route() {
		/** @var Main $main */
		$main = tribe( 'pods.rest-v1.main' );

		$namespace = $main->get_pods_route_namespace();

		return $namespace . $this->route;
	}
}
