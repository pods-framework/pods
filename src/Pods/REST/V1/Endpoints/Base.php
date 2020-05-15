<?php

namespace Pods\REST\V1\Endpoints;

use Exception;
use Pods\Whatsit;
use Tribe__REST__Messages_Interface as Messages_Interface;
use Tribe__REST__Post_Repository as Post_Repository;
use Tribe__Utils__Array as Utils_Array;
use Tribe__Validator__Interface as Validator_Interface;
use WP_Error;
use WP_REST_Request;

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
	 * @var string
	 */
	public $object;

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

		$no_description = __( 'No description provided.', 'pods' );

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

	/**
	 * Handle getting the object archive.
	 *
	 * @since 2.8
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array|WP_Error The response or an error.
	 * @throws Exception
	 */
	public function archive_by_args( WP_REST_Request $request ) {
		$params = [
			'return_type' => $request['return_type'],
		];

		if ( in_array( $this->object, [ 'group', 'field' ], true ) && ! empty( $request['pod'] ) ) {
			$params['pod'] = $request['pod'];
		}

		if ( 'field' === $this->object && ! empty( $request['group'] ) ) {
			$params['group'] = $request['group'];
		}

		if ( ! empty( $request['types'] ) ) {
			$params['type'] = Utils_Array::list_to_array( $request['types'] );
		}

		if ( ! empty( $request['ids'] ) ) {
			$params['id'] = Utils_Array::list_to_array( $request['ids'] );
		}

		if ( ! empty( $request['args'] ) ) {
			$params['args'] = $request['args'];

			// Attempt to convert from JSON to array if needed.
			if ( is_string( $params['args'] ) ) {
				$json = @json_decode( $params['args'], true );

				if ( is_array( $json ) ) {
					$params['args'] = $json;
				}
			}
		}

		if ( ! empty( $request['return_type'] ) ) {
			$params['return_type'] = $request['return_type'];
		}

		$object_plural = $this->object . 's';
		$method        = 'load_' . $object_plural;

		$api = pods_api();

		$api->display_errors = 'wp_error';

		$objects = $api->$method( $params );

		if ( is_wp_error( $objects ) ) {
			return $objects;
		}

		// Handle parent details.
		if ( in_array( $this->object, [ 'group', 'field' ], true ) && 1 === (int) $request['include_parent'] ) {
			foreach ( $objects as $k => $object ) {
				// Set temporary data so parent data gets exported.
				$object->set_arg( 'parent_data', $object->get_parent() );
			}
		}

		return [
			$object_plural => $objects,
		];
	}

	/**
	 * Handle creating the object using specific REST / Pods API arguments.
	 *
	 * @since 2.8
	 *
	 * @param WP_REST_Request $request   REST API Request object.
	 * @param bool            $return_id Whether to return the object ID (off returns full response).
	 *
	 * @return array|WP_Error
	 *
	 * @throws Exception
	 */
	public function create_by_args( WP_REST_REQUEST $request, $return_id = false ) {
		$params = $this->setup_params( $request );

		$api = pods_api();

		$api->display_errors = 'wp_error';

		if ( empty( $params['name'] ) && empty( $params['label'] ) ) {
			return new WP_Error( 'rest-object-not-added-fields-required', sprintf( __( '%s not added, name or label is required.', 'pods' ), ucwords( $this->object ) ) );
		}

		if ( empty( $params['name'] ) ) {
			$params['name'] = pods_clean_name( $params['label'], true );
		}

		$load_method = 'load_' . $this->object;

		$load_params = [
			'name' => $params['name'],
		];

		if ( ! empty( $params['pod'] ) ) {
			$load_params['pod'] = $params['pod'];
		}

		if ( ! empty( $params['pod_id'] ) ) {
			$load_params['pod_id'] = $params['pod_id'];
		}

		$loaded_object = $api->$load_method( $load_params );

		if ( $loaded_object && ! is_wp_error( $loaded_object ) ) {
			return new WP_Error( 'rest-object-not-added-already-exists', sprintf( __( '%s not added, it already exists.', 'pods' ), ucwords( $this->object ) ) );
		}

		$method = 'save_' . $this->object;

		$id = $api->$method( $params );

		if ( is_wp_error( $id ) ) {
			return $id;
		}

		if ( empty( $id ) ) {
			return new WP_Error( 'rest-object-not-added', sprintf( __( '%s not added.', 'pods' ), ucwords( $this->object ) ) );
		}

		return $this->get_by_args( [
			'id' => $id,
		], 'id', $request, $return_id );
	}

	/**
	 * Setup the parameters for saving.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array Parameters for saving.
	 */
	protected function setup_params( WP_REST_Request $request ) {
		$defaults = [
			'id'    => null,
			'name'  => null,
			'label' => null,
			'args'  => null,
		];

		$params = wp_parse_args( $request->get_params(), $defaults );
		$params = array_filter( $params, [ $this->validator, 'is_not_null' ] );

		if ( isset( $params['args'] ) ) {
			$args = $params['args'];

			unset( $params['args'] );

			// Attempt to convert from JSON to array if needed.
			if ( is_string( $args ) ) {
				$json = @json_decode( $args, true );

				if ( is_array( $json ) ) {
					$args = $json;
				}
			}

			if ( is_array( $args ) ) {
				$params = array_merge( $params, $args );
			}
		}

		return $params;
	}

	/**
	 * Handle getting the object using specific REST / Pods API arguments.
	 *
	 * @since 2.8
	 *
	 * @param string|array    $rest_param REST API parameter name to look for OR arguments to pass to loader.
	 * @param string          $api_arg    Pods API argument name to use for lookups.
	 * @param WP_REST_Request $request    The request object.
	 * @param bool            $return_id  Whether to return the object ID (off returns full response).
	 *
	 * @return array|WP_Error The response or an error.
	 * @throws Exception
	 */
	public function get_by_args( $rest_param, $api_arg, WP_REST_Request $request, $return_id = false ) {
		if ( is_array( $rest_param ) ) {
			$args = $rest_param;
		} else {
			$identifier = $request[ $rest_param ];

			$args = [
				$api_arg => $identifier,
			];

			if ( $rest_param !== $api_arg ) {
				unset( $request[ $rest_param ] );
			}
		}

		$args = array_merge( $request->get_params(), $args );

		$api = pods_api();

		$api->display_errors = 'wp_error';

		$method = 'load_' . $this->object;

		$object = $api->$method( $args );

		if ( is_wp_error( $object ) ) {
			return $object;
		}

		if ( empty( $object ) ) {
			return new WP_Error( 'rest-object-not-found', sprintf( __( '%s not found.', 'pods' ), ucwords( $this->object ) ) );
		}

		$data = [
			$this->object => $object->get_args(),
		];

		// Setup groups.
		if ( 'pod' === $this->object && 1 === (int) $request['include_groups'] ) {
			$data['groups'] = $object->get_groups();
		}

		// Setup fields.
		if ( in_array( $this->object, [ 'pod', 'group' ], true ) && 1 === (int) $request['include_fields'] ) {
			$data['fields'] = $object->get_fields();
		}

		return $data;
	}

	/**
	 * Handle updating the object using specific REST / Pods API arguments.
	 *
	 * @since 2.8
	 *
	 * @param string          $rest_param REST API parameter name to look for.
	 * @param string          $api_arg    Pods API argument name to use for lookups.
	 * @param WP_REST_Request $request    REST API Request object.
	 *
	 * @return array|WP_Error
	 *
	 * @throws Exception
	 */
	public function update_by_args( $rest_param, $api_arg, WP_REST_Request $request ) {
		$api = pods_api();

		$api->display_errors = 'wp_error';

		$identifier = $request[ $rest_param ];

		// Send proper identifier argument.
		if ( $rest_param !== $api_arg ) {
			$request[ $api_arg ] = $identifier;

			unset( $request[ $rest_param ] );
		}

		$method = 'load_' . $this->object;

		$object = $api->$method( [
			$api_arg => $identifier,
		] );

		if ( is_wp_error( $object ) ) {
			return $object;
		}

		if ( ! $object instanceof Whatsit ) {
			return new WP_Error( 'rest-object-not-found', sprintf( __( '%s not found.', 'pods' ), ucwords( $this->object ) ) );
		}

		$params = $this->setup_params( $request );

		$params['id'] = $object['id'];

		// Pass the object for reuse.
		$params[ $this->object ] = $object;

		if ( in_array( $this->object, [ 'group', 'field' ], true ) ) {
			$params['pod'] = $object->get_parent_object();
		}

		$save_method = 'save_' . $this->object;

		// Handle save.
		$saved = $api->$save_method( $params );

		if ( is_wp_error( $saved ) ) {
			return $saved;
		}

		// Return the refreshed object data.
		return $this->get_by_args( [
			$api_arg       => $identifier,
			'bypass_cache' => true,
		], $api_arg, $request );
	}

	/**
	 * Handle deleting the object using specific REST / Pods API arguments.
	 *
	 * @since 2.8
	 *
	 * @param string|array    $rest_param REST API parameter name to look for OR arguments to pass to loader.
	 * @param string          $api_arg    Pods API argument name to use for lookups.
	 * @param WP_REST_Request $request    REST API Request object.
	 *
	 * @return array|WP_Error
	 *
	 * @throws Exception
	 */
	public function delete_by_args( $rest_param, $api_arg, WP_REST_Request $request ) {
		if ( is_array( $rest_param ) ) {
			$args = $rest_param;
		} else {
			$identifier = $request[ $rest_param ];

			$args = [
				$api_arg => $identifier,
			];

			if ( $rest_param !== $api_arg ) {
				unset( $request[ $rest_param ] );
			}
		}

		$args = array_merge( $request->get_params(), $args );

		$api = pods_api();

		$api->display_errors = 'wp_error';

		$method = 'delete_' . $this->object;

		$deleted = $api->$method( $args );

		if ( is_wp_error( $deleted ) ) {
			return $deleted;
		}

		if ( ! $deleted ) {
			return new WP_Error( 'rest-object-not-deleted', sprintf( __( '%s not deleted.', 'pods' ), ucwords( $this->object ) ) );
		}

		return [
			'status' => 'deleted',
		];
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
}
