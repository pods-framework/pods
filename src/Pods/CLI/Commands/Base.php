<?php

namespace Pods\CLI\Commands;

use Pods\REST\V1\Endpoints\Base as Base_Endpoint;
use WP_CLI;
use WP_Error;
use WP_REST_Request;

/**
 * Class Base
 *
 * @since 2.8.0
 */
abstract class Base {

	/**
	 * @var string
	 */
	protected $namespace = 'pods';

	/**
	 * @var string
	 */
	protected $command = '';

	/**
	 * @var Base_Endpoint
	 */
	protected $endpoint_single;

	/**
	 * @var Base_Endpoint
	 */
	protected $endpoint_single_slug;

	/**
	 * @var Base_Endpoint
	 */
	protected $endpoint_archive;

	/**
	 * Handle setup of things needed by command.
	 *
	 * @since 2.8.0
	 */
	public function hook() {
		// Permissions are relaxed for WP-CLI context.
		add_filter( 'pods_is_admin', '__return_true' );

		$this->add_commands();
	}

	/**
	 * Add commands based on endpoint object.
	 *
	 * @since 2.8.0
	 */
	public function add_commands() {
		if ( $this->endpoint_archive && method_exists( $this->endpoint_archive, 'get' ) ) {
			$command = sprintf( '%1$s %2$s %3$s', $this->namespace, $this->command, 'list' );

			WP_CLI::add_command( $command, [
				$this,
				'list_items',
			], $this->build_command_args( 'list', $this->endpoint_archive ) );
		}

		if ( $this->endpoint_archive && method_exists( $this->endpoint_archive, 'create' ) ) {
			$command = sprintf( '%1$s %2$s %3$s', $this->namespace, $this->command, 'add' );

			WP_CLI::add_command( $command, [
				$this,
				'add',
			], $this->build_command_args( 'add', $this->endpoint_archive ) );
		}

		if ( $this->endpoint_single && method_exists( $this->endpoint_single, 'get' ) ) {
			$command = sprintf( '%1$s %2$s %3$s', $this->namespace, $this->command, 'get' );

			WP_CLI::add_command( $command, [
				$this,
				'get',
			], $this->build_command_args( 'get', $this->endpoint_single ) );
		}

		if ( $this->endpoint_single_slug && method_exists( $this->endpoint_single_slug, 'get' ) ) {
			$command = sprintf( '%1$s %2$s %3$s', $this->namespace, $this->command, 'get-by-slug' );

			WP_CLI::add_command( $command, [
				$this,
				'get_by_slug',
			], $this->build_command_args( 'get', $this->endpoint_single_slug ) );
		}

		if ( $this->endpoint_single && method_exists( $this->endpoint_single, 'update' ) ) {
			$command = sprintf( '%1$s %2$s %3$s', $this->namespace, $this->command, 'update' );

			WP_CLI::add_command( $command, [
				$this,
				'update',
			], $this->build_command_args( 'update', $this->endpoint_single ) );
		}

		if ( $this->endpoint_single_slug && method_exists( $this->endpoint_single_slug, 'update' ) ) {
			$command = sprintf( '%1$s %2$s %3$s', $this->namespace, $this->command, 'update-by-slug' );

			WP_CLI::add_command( $command, [
				$this,
				'update_by_slug',
			], $this->build_command_args( 'update', $this->endpoint_single_slug ) );
		}

		if ( $this->endpoint_single && method_exists( $this->endpoint_single, 'delete' ) ) {
			$command = sprintf( '%1$s %2$s %3$s', $this->namespace, $this->command, 'delete' );

			WP_CLI::add_command( $command, [
				$this,
				'delete',
			], $this->build_command_args( 'delete', $this->endpoint_single ) );
		}

		if ( $this->endpoint_single_slug && method_exists( $this->endpoint_single_slug, 'delete' ) ) {
			$command = sprintf( '%1$s %2$s %3$s', $this->namespace, $this->command, 'delete-by-slug' );

			WP_CLI::add_command( $command, [
				$this,
				'delete_by_slug',
			], $this->build_command_args( 'delete', $this->endpoint_single_slug ) );
		}
	}

	/**
	 * List items.
	 *
	 * @since 2.8.0
	 *
	 * @param array $args       List of positional arguments.
	 * @param array $assoc_args List of associative arguments.
	 *
	 * @throws WP_CLI\ExitException
	 */
	public function list_items( array $args, array $assoc_args ) {
		return $this->run_endpoint_method( $args, $assoc_args, 'get', $this->endpoint_archive );
	}

	/**
	 * Add an item.
	 *
	 * @since 2.8.0
	 *
	 * @param array $args       List of positional arguments.
	 * @param array $assoc_args List of associative arguments.
	 *
	 * @throws WP_CLI\ExitException
	 */
	public function add( array $args, array $assoc_args ) {
		return $this->run_endpoint_method( $args, $assoc_args, 'create', $this->endpoint_archive );
	}

	/**
	 * Get an item by ID.
	 *
	 * @since 2.8.0
	 *
	 * @param array $args       List of positional arguments.
	 * @param array $assoc_args List of associative arguments.
	 *
	 * @throws WP_CLI\ExitException
	 */
	public function get( array $args, array $assoc_args ) {
		return $this->run_endpoint_method( $args, $assoc_args, 'get', $this->endpoint_single );
	}

	/**
	 * Get an item by slug.
	 *
	 * @since 2.8.0
	 *
	 * @param array $args       List of positional arguments.
	 * @param array $assoc_args List of associative arguments.
	 *
	 * @throws WP_CLI\ExitException
	 */
	public function get_by_slug( array $args, array $assoc_args ) {
		return $this->run_endpoint_method( $args, $assoc_args, 'get', $this->endpoint_single_slug );
	}

	/**
	 * Update an item by ID.
	 *
	 * @since 2.8.0
	 *
	 * @param array $args       List of positional arguments.
	 * @param array $assoc_args List of associative arguments.
	 *
	 * @throws WP_CLI\ExitException
	 */
	public function update( array $args, array $assoc_args ) {
		return $this->run_endpoint_method( $args, $assoc_args, 'update', $this->endpoint_single );
	}

	/**
	 * Update an item by slug.
	 *
	 * @since 2.8.0
	 *
	 * @param array $args       List of positional arguments.
	 * @param array $assoc_args List of associative arguments.
	 *
	 * @throws WP_CLI\ExitException
	 */
	public function update_by_slug( array $args, array $assoc_args ) {
		return $this->run_endpoint_method( $args, $assoc_args, 'update', $this->endpoint_single_slug );
	}

	/**
	 * Delete an item by ID.
	 *
	 * @since 2.8.0
	 *
	 * @param array $args       List of positional arguments.
	 * @param array $assoc_args List of associative arguments.
	 *
	 * @throws WP_CLI\ExitException
	 */
	public function delete( array $args, array $assoc_args ) {
		return $this->run_endpoint_method( $args, $assoc_args, 'delete', $this->endpoint_single );
	}

	/**
	 * Delete an item by slug.
	 *
	 * @since 2.8.0
	 *
	 * @param array $args       List of positional arguments.
	 * @param array $assoc_args List of associative arguments.
	 *
	 * @throws WP_CLI\ExitException
	 */
	public function delete_by_slug( array $args, array $assoc_args ) {
		return $this->run_endpoint_method( $args, $assoc_args, 'delete', $this->endpoint_single_slug );
	}

	/**
	 * Run endpoint method using args provided.
	 *
	 * @since 2.8.0
	 *
	 * @param array         $args       List of positional arguments.
	 * @param array         $assoc_args List of associative arguments.
	 * @param string        $method     Method name.
	 * @param Base_Endpoint $endpoint   Endpoint object.
	 *
	 * @throws WP_CLI\ExitException
	 */
	public function run_endpoint_method( array $args, array $assoc_args, $method, Base_Endpoint $endpoint ) {
		if ( ! method_exists( $endpoint, $method ) ) {
			return;
		}

		$assoc_args = $this->json_or_args( $assoc_args );
		$assoc_args = $this->validate_args( $args, $assoc_args, $method, $endpoint );

		if ( is_wp_error( $assoc_args ) ) {
			return $this->output_error_response( $assoc_args );
		}

		$attributes = [
			'args' => $assoc_args,
		];

		$method_mapping = [
			'list'   => 'GET',
			'add'    => 'POST',
			'get'    => 'GET',
			'update' => 'POST',
			'delete' => 'DELETE',
		];

		$rest_method = 'GET';

		if ( isset( $method_mapping[ $method ] ) ) {
			$rest_method = $method_mapping[ $method ];
		}

		$permissions_mapping = [
			'list'   => 'can_read',
			'add'    => 'can_create',
			'get'    => 'can_read',
			'update' => 'can_edit',
			'delete' => 'can_delete',
		];

		if ( isset( $permissions_mapping[ $method ] ) ) {
			$permissions_method = $permissions_mapping[ $method ];

			if ( method_exists( $endpoint, $permissions_method ) && ! $endpoint->$permissions_method() ) {
				\WP_CLI::error( __( 'The current user does not have access to this endpoint.', 'pods' ) );
			}
		}

		$route = $endpoint->get_route();

		// Add numeric args.
		if ( ! empty( $args ) ) {
			$route = sprintf( $route, ...$args );
		}

		$request = new WP_REST_Request( $rest_method, '/' . rest_get_url_prefix() . $route, $attributes );

		if ( 'POST' === $rest_method ) {
			$request->set_body_params( $assoc_args );
		} else {
			$request->set_query_params( $assoc_args );
		}

		$response = $endpoint->$method( $request );

		if ( is_wp_error( $response ) ) {
			return $this->output_error_response( $response );
		}

		if ( null !== $response ) {
			if ( is_object( $response ) || is_array( $response ) ) {
				$response = wp_json_encode( $response, JSON_PRETTY_PRINT );
			}

			WP_CLI::line( $response );
		}

		WP_CLI::success( __( 'Command successful', 'pods' ) );
	}

	/**
	 * Get the list of arguments with JSON expanded if provided.
	 *
	 * @since 2.8.0
	 *
	 * @param array $assoc_args List of associative arguments.
	 *
	 * @return array List of arguments with JSON expanded if provided.
	 */
	public function json_or_args( array $assoc_args ) {
		if ( isset( $assoc_args['json'] ) ) {
			$assoc_args = array_merge( $assoc_args, json_decode( $assoc_args['json'], true ) );

			unset( $assoc_args['json'] );
		}

		return $assoc_args;
	}

	/**
	 * Determine whether the args validated.
	 *
	 * @since 2.8.0
	 *
	 * @param array         $args       List of positional arguments.
	 * @param array         $assoc_args List of associative arguments.
	 * @param string        $method     Method name.
	 * @param Base_Endpoint $endpoint   Endpoint object.
	 *
	 * @return array|WP_Error The associative args that validated or the WP_Error object with what failed.
	 */
	public function validate_args( array $args, array $assoc_args, $method, Base_Endpoint $endpoint ) {
		$rest_args = $this->get_rest_args( $method, $endpoint );

		if ( empty( $rest_args ) ) {
			return $assoc_args;
		}

		foreach ( $rest_args as $param => $arg ) {
			// Handle path args.
			if ( isset( $arg['in'] ) && 'path' === $arg['in'] ) {
				if ( empty( $args ) ) {
					if ( empty( $arg['required'] ) ) {
						continue;
					}

					return new WP_Error( 'cli-missing-positional-argument', sprintf( __( 'Missing positional argument: %s', 'pods' ), $param ) );
				}

				$value = array_shift( $args );

				$value = $this->validate_arg( $value, $arg, $param );

				if ( is_wp_error( $value ) ) {
					return $value;
				}

				$assoc_args[ $param ] = $value;

				continue;
			}

			// Handle normal args.
			$value = null;

			if ( isset( $assoc_args[ $param ] ) ) {
				$value = $assoc_args[ $param ];
			}

			$value = $this->validate_arg( $value, $arg, $param );

			if ( is_wp_error( $value ) ) {
				return $value;
			}

			if ( null !== $value ) {
				$assoc_args[ $param ] = $value;
			}
		}

		return $assoc_args;
	}

	/**
	 * Determine whether the arg validates.
	 *
	 * @since 2.8.0
	 *
	 * @param mixed  $value CLI value provided.
	 * @param array  $arg   REST API argument options.
	 * @param string $param Parameter name.
	 *
	 * @return mixed|WP_Error The argument value or the WP_Error object with what failed to validate.
	 */
	public function validate_arg( $value, array $arg, $param ) {
		$is_required = ! empty( $arg['required'] );
		$is_null     = null === $value;

		if ( $is_null ) {
			if ( ! $is_required ) {
				return $value;
			}

			return new WP_Error( 'cli-argument-required', sprintf( __( 'Argument is required: %s', 'pods' ), $param ) );
		}

		if ( 'integer' === $arg['type'] ) {
			$value = (int) $value;
		}

		if ( ! empty( $arg['validate_callback'] ) && is_callable( $arg['validate_callback'] ) ) {
			$valid = call_user_func( $arg['validate_callback'], $value );

			if ( ! $valid ) {
				$callable_name = null;

				if ( is_array( $arg['validate_callback'] ) ) {
					$callable_name = '';

					if ( is_object( $arg['validate_callback'][0] ) ) {
						$callable_name = get_class( $arg['validate_callback'][0] ) . '::';
					} elseif ( is_string( $arg['validate_callback'][0] ) ) {
						$callable_name = $arg['validate_callback'][0] . '::';
					}

					$callable_name .= $arg['validate_callback'][1];
				} elseif ( is_string( $arg['validate_callback'] ) ) {
					$callable_name = $arg['validate_callback'];
				}

				if ( empty( $callable_name ) ) {
					return new WP_Error( 'cli-argument-not-valid', sprintf( __( 'Argument not provided as expected: %s', 'pods' ), $param ) );
				}

				return new WP_Error( 'cli-argument-not-valid-with-callback', sprintf( __( 'Argument not provided as expected (%1$s): %2$s', 'pods' ), $callable_name, $param ) );
			}

			if ( is_wp_error( $valid ) ) {
				return $valid;
			}
		}

		$valid = rest_validate_value_from_schema( $value, $arg, $param );

		if ( ! $valid ) {
			return '';
		}

		if ( is_wp_error( $valid ) ) {
			return $valid;
		}

		return $value;
	}

	/**
	 * Get list of REST API arguments from endpoint.
	 *
	 * @since 2.8.0
	 *
	 * @param string        $command  Command name.
	 * @param Base_Endpoint $endpoint Endpoint object.
	 *
	 * @return array List of REST API arguments.
	 */
	public function get_rest_args( $command, Base_Endpoint $endpoint ) {
		$command_mapping = [
			'list'   => 'READ_args',
			'add'    => 'CREATE_args',
			'create' => 'CREATE_args',
			'get'    => 'READ_args',
			'update' => 'EDIT_args',
			'delete' => 'DELETE_args',
		];

		if ( ! isset( $command_mapping[ $command ] ) ) {
			return [];
		}

		$method = $command_mapping[ $command ];

		if ( ! method_exists( $endpoint, $method ) ) {
			return [];
		}

		$rest_args = $endpoint->$method();

		if ( empty( $rest_args ) ) {
			return [];
		}

		return $rest_args;
	}

	/**
	 * Get list of properly formatted CLI command arguments.
	 *
	 * @since 2.8.0
	 *
	 * @param string        $command  Command name.
	 * @param Base_Endpoint $endpoint Endpoint object.
	 *
	 * @return array List of properly formatted CLI command arguments.
	 */
	public function build_command_args( $command, Base_Endpoint $endpoint ) {
		$rest_args = $this->get_rest_args( $command, $endpoint );

		if ( empty( $rest_args ) ) {
			return [];
		}

		$cli_args = [
			'synopsis' => [],
		];

		foreach ( $rest_args as $param => $arg ) {
			$cli_arg = [
				'type' => 'assoc',
				'name' => $param,
				'optional' => empty( $arg['required'] ),
			];

			if ( ! empty( $arg['description'] ) ) {
				$cli_arg['description'] = $arg['description'];
			}

			if ( ! empty( $arg['default'] ) ) {
				$cli_arg['default'] = $arg['default'];
			}

			if ( isset( $arg['in'] ) && 'path' === $arg['in'] ) {
				// Handle path args.
				$cli_arg['type'] = 'positional';
			} elseif ( isset( $arg['cli_boolean'] ) && $arg['cli_boolean'] ) {
				// Handle flag args.
				$cli_arg['type'] = 'flag';
			} elseif ( ! empty( $arg['enum'] ) ) {
				// Handle enum options.
				$cli_arg['options'] = $arg['enum'];
			}

			$cli_args['synopsis'][] = $cli_arg;
		}

		return $cli_args;
	}

	/**
	 * Output the CLI error response from the WP_Error object.
	 *
	 * @since 2.8.0
	 *
	 * @param WP_Error $error The error object.
	 *
	 * @throws WP_CLI\ExitException
	 */
	public function output_error_response( WP_Error $error ) {
		$error_message = sprintf( '%1$s [%2$s]', $error->get_error_message(), $error->get_error_code() );

		WP_CLI::error( $error_message );
	}
}
