<?php

namespace Pods\CLI\Commands;

use Pods\REST\V1\Endpoints\Base as Base_Endpoint;
use WP_CLI;
use WP_Error;
use WP_REST_Request;

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
	protected $endpoint_single;

	/**
	 * @var Base_Endpoint
	 */
	protected $endpoint_archive;

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
		if ( method_exists( $this->endpoint_archive, 'get' ) ) {
			$command = sprintf( '%1$s %2$s %3$s', $this->namespace, $this->command, 'list' );

			WP_CLI::add_command( $command, [
				$this,
				'list_items',
			], $this->build_command_args( 'list', $this->endpoint_archive ) );
		}

		if ( method_exists( $this->endpoint_archive, 'create' ) ) {
			$command = sprintf( '%1$s %2$s %3$s', $this->namespace, $this->command, 'add' );

			WP_CLI::add_command( $command, [
				$this,
				'add',
			], $this->build_command_args( 'add', $this->endpoint_archive ) );
		}

		if ( method_exists( $this->endpoint_single, 'get' ) ) {
			$command = sprintf( '%1$s %2$s %3$s', $this->namespace, $this->command, 'get' );

			WP_CLI::add_command( $command, [
				$this,
				'get',
			], $this->build_command_args( 'get', $this->endpoint_single ) );
		}

		if ( method_exists( $this->endpoint_single, 'update' ) ) {
			$command = sprintf( '%1$s %2$s %3$s', $this->namespace, $this->command, 'update' );

			WP_CLI::add_command( $command, [
				$this,
				'update',
			], $this->build_command_args( 'update', $this->endpoint_single ) );
		}

		if ( method_exists( $this->endpoint_single, 'delete' ) ) {
			$command = sprintf( '%1$s %2$s %3$s', $this->namespace, $this->command, 'delete' );

			WP_CLI::add_command( $command, [
				$this,
				'delete',
			], $this->build_command_args( 'delete', $this->endpoint_single ) );
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
	public function list_items( array $args, array $assoc_args ) {
		return $this->run_endpoint_method( $assoc_args, 'get', $this->endpoint_archive );
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
		return $this->run_endpoint_method( $assoc_args, 'create', $this->endpoint_archive );
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
		return $this->run_endpoint_method( $assoc_args, 'get', $this->endpoint_single );
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
		return $this->run_endpoint_method( $assoc_args, 'update', $this->endpoint_single );
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
		return $this->run_endpoint_method( $assoc_args, 'delete', $this->endpoint_single );
	}

	/**
	 * Run endpoint method using args provided.
	 *
	 * @since 2.8
	 *
	 * @param array         $assoc_args List of associative arguments.
	 * @param string        $method     Method name.
	 * @param Base_Endpoint $endpoint   Endpoint object.
	 */
	public function run_endpoint_method( array $assoc_args, $method, Base_Endpoint $endpoint ) {
		if ( ! method_exists( $endpoint, $method ) ) {
			return;
		}

		$assoc_args = $this->json_or_args( $assoc_args );

		$valid = $this->validate_args( $assoc_args );

		if ( is_wp_error( $valid ) ) {
			return $this->output_error_response( $valid );
		}

		$attributes = [
			'args' => $assoc_args,
		];

		$request = new WP_REST_Request( '', '', $attributes );

		$response = $endpoint->$method( $request );

		if ( is_wp_error( $response ) ) {
			return $this->output_error_response( $response );
		}

		// @todo Output response data in a better way.

		var_dump( $response );

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
	public function validate_args( array $assoc_args, $command = null ) {
		// @todo Get list of args and determine what's really required.

		$runner    = WP_CLI::get_runner();
		$arguments = $runner->arguments;

		if ( null === $command && ! empty( $arguments ) ) {
			$command = end( $arguments );
		}

		return true;
	}

	/**
	 * Get list of properly formatted CLI command arguments.
	 *
	 * @since 2.8
	 *
	 * @param string        $command  Command name.
	 * @param Base_Endpoint $endpoint Endpoint object.
	 *
	 * @return array List of properly formatted CLI command arguments.
	 */
	public function build_command_args( $command, Base_Endpoint $endpoint ) {
		$command_mapping = [
			'list'   => 'READ_args',
			'add'    => 'CREATE_args',
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

		$cli_args = [];

		// @todo Map the REST args to CLI args formatting.

		return $cli_args;
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
