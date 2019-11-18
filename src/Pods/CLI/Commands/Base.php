<?php

namespace Pods\CLI\Commands;

use Pods\REST\V1\Endpoints\Base as Base_Endpoint;
use WP_Error;

/**
 * Class Base
 *
 * @since 2.8
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
	protected $endpoint;

	/**
	 * Handle setup of things needed by command.
	 *
	 * @since 2.8
	 */
	public function hook() {
		$this->add_commands();
	}

	/**
	 * Add commands based on endpoint object.
	 *
	 * @since 2.8
	 */
	public function add_commands() {
		if ( method_exists( $this->endpoint, 'get' ) ) {
			$command = sprintf( '%1$s %2$s %3$s', $this->namespace, $this->command, 'get' );

			WP_CLI::add_command( $command, [ $this, 'get', ], $this->build_command_args( 'get' ) );
		}

		if ( method_exists( $this->endpoint, 'create' ) ) {
			$command = sprintf( '%1$s %2$s %3$s', $this->namespace, $this->command, 'add' );

			WP_CLI::add_command( $command, [ $this, 'add', ], $this->build_command_args( 'add' ) );
		}

		if ( method_exists( $this->endpoint, 'update' ) ) {
			$command = sprintf( '%1$s %2$s %3$s', $this->namespace, $this->command, 'update' );

			WP_CLI::add_command( $command, [ $this, 'update', ], $this->build_command_args( 'update' ) );
		}

		if ( method_exists( $this->endpoint, 'delete' ) ) {
			$command = sprintf( '%1$s %2$s %3$s', $this->namespace, $this->command, 'delete' );

			WP_CLI::add_command( $command, [ $this, 'delete', ], $this->build_command_args( 'delete' ) );
		}
	}

	/**
	 * Map the get CLI command to the Endpoint::get() method.
	 *
	 * @since 2.8
	 *
	 * @param array $args       List of positional arguments.
	 * @param array $assoc_args List of associative arguments.
	 */
	public function get( array $args, array $assoc_args ) {
		return $this->run_endpoint_method( $assoc_args, 'get' );
	}

	/**
	 * Map the add CLI command to the Endpoint::create() method.
	 *
	 * @since 2.8
	 *
	 * @param array $args       List of positional arguments.
	 * @param array $assoc_args List of associative arguments.
	 */
	public function add( array $args, array $assoc_args ) {
		return $this->run_endpoint_method( $assoc_args, 'create' );
	}

	/**
	 * Map the update CLI command to the Endpoint::update() method.
	 *
	 * @since 2.8
	 *
	 * @param array $args       List of positional arguments.
	 * @param array $assoc_args List of associative arguments.
	 */
	public function update( array $args, array $assoc_args ) {
		return $this->run_endpoint_method( $assoc_args, 'update' );
	}

	/**
	 * Map the delete CLI command to the Endpoint::delete() method.
	 *
	 * @since 2.8
	 *
	 * @param array $args       List of positional arguments.
	 * @param array $assoc_args List of associative arguments.
	 */
	public function delete( array $args, array $assoc_args ) {
		return $this->run_endpoint_method( $assoc_args, 'delete' );
	}

	/**
	 * Run endpoint method using args provided.
	 *
	 * @since 2.8
	 *
	 * @param array  $assoc_args List of associative arguments.
	 * @param string $method     Method name.
	 */
	public function run_endpoint_method( array $assoc_args, $method ) {
		if ( ! method_exists( $this->endpoint, $method ) ) {
			return;
		}

		$assoc_args = $this->json_or_args( $assoc_args );

		$valid = $this->validate_args( $assoc_args );

		if ( is_wp_error( $valid ) ) {
			return $this->output_error_response( $valid );
		}

		$response = call_user_func( [ $this->endpoint, $method ], $assoc_args );

		if ( is_wp_error( $response ) ) {
			return $this->output_error_response( $response );
		}

		// @todo Output response data in a better way.

		WP_CLI::success( __( 'Command successful', 'pods' ) );
	}

	/**
	 * Get the list of arguments with JSON expanded if provided.
	 *
	 * @since 2.8
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
	 * @since 2.8
	 *
	 * @param array  $assoc_args List of associative arguments.
	 * @param string $command    Command name.
	 *
	 * @return true|WP_Error Whether the args validated or the WP_Error object with what failed.
	 */
	public function validate_args( array $assoc_args, $command ) {
		// @todo Get list of args and determine what's really required.

		return true;
	}

	/**
	 * Get list of properly formatted CLI command arguments.
	 *
	 * @since 2.8
	 *
	 * @param string $command Command name.
	 *
	 * @return array List of properly formatted CLI command arguments.
	 */
	public function build_command_args( $command ) {
		$command_mapping = [
			'get'    => 'READ_args',
			'add'    => 'CREATE_args',
			'update' => 'EDIT_args',
			'delete' => 'DELETE_args',
		];

		if ( ! isset( $command_mapping[ $command ] ) ) {
			return [];
		}

		$method = $command_mapping[ $command ];

		if ( ! method_exists( $this->endpoint, $method ) ) {
			return [];
		}

		$rest_args = call_user_func( [ $this->endpoint, $method ] );

		if ( empty( $rest_args ) ) {
			return [];
		}

		// @todo Map the REST args to CLI args formatting.
	}

	/**
	 * Output the CLI error response from the WP_Error object.
	 *
	 * @since 2.8
	 *
	 * @param WP_Error $error The error object.
	 */
	public function output_error_response( WP_Error $error ) {
		$error_message = sprintf( '%1$s [%2$s]', $error->get_error_message(), $error->get_error_code() );

		WP_CLI::error( $error_message );
	}
}
