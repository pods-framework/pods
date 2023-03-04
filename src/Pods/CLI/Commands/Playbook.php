<?php

namespace Pods\CLI\Commands;

use Exception;
use Pods_Migrate_Packages;
use PodsInit;
use PodsMigrate;
use WP_CLI;
use WP_CLI_Command;
use function WP_CLI\Utils\make_progress_bar;

/**
 * Pods Playbook commands.
 *
 * @since 2.8.11
 */
class Playbook extends WP_CLI_Command {

	/**
	 * Run the playbook.
	 *
	 * ## OPTIONS
	 *
	 * <playbook>
	 * : The playbook .json file path.
	 *
	 * [--test]
	 * : Whether to run the playbook in test mode and not add/change/remove any data in the database.
	 *
	 * [--continue-on-error]
	 * : Whether to continue on errors when the playbook is run.
	 *
	 * ## EXAMPLES
	 *
	 * wp pods playbook run migration.json
	 * - Run the playbook of the migration.json file.
	 *
	 * wp pods playbook run upgrade.json
	 * - Run the playbook of the upgrade.json file.
	 *
	 * wp pods playbook run upgrade.json --test
	 * - Preview the playbook run of the upgrade.json file but without changing the database.
	 *
	 * @since 2.8.11
	 *
	 * @param array $args       The list of positional arguments.
	 * @param array $assoc_args The list of associative arguments.
	 */
	public function run( $args, $assoc_args ) {
		$playbook_file = $args[0];

		$test_mode         = ! empty( $assoc_args['test'] );
		$continue_on_error = ! empty( $assoc_args['continue-on-error'] );

		if ( ! file_exists( $playbook_file ) ) {
			WP_CLI::error( __( 'Playbook file does not exist.', 'pods' ) );
		}

		$json = file_get_contents( $playbook_file );

		if ( empty( $json ) ) {
			WP_CLI::error( __( 'Playbook file is empty.', 'pods' ) );
		}

		$playbook_actions = json_decode( $json, true );

		if ( ! is_array( $playbook_actions ) ) {
			WP_CLI::error( __( 'Playbook file contains invalid JSON.', 'pods' ) );
		}

		if ( empty( $playbook_actions ) ) {
			WP_CLI::error( __( 'Playbook file is empty.', 'pods' ) );
		}

		if ( $test_mode ) {
			WP_CLI::line( __( 'Running playbook in test mode.', 'pods' ) );
		} else {
			WP_CLI::line( __( 'Running playbook in live mode.', 'pods' ) );
		}

		$api = pods_api();

		// Enforce exceptions for errors.
		add_filter( 'pods_error_mode', static function() { return 'exception'; } );
		add_filter( 'pods_error_mode_force', '__return_true' );

		$total_actions = count( $playbook_actions );

		$progress_bar = make_progress_bar(
			sprintf(
				// translators: %1$d is the total number of actions to run; %2$s is the singular/plural name for action.
				__( 'Running playbook of actions | %1$d %2$s', 'pods' ),
				$total_actions,
				_n( 'action', 'actions', $total_actions, 'pods' )
			),
			$total_actions
		);

		foreach ( $playbook_actions as $action ) {
			if ( ! isset( $action['action'] ) ) {
				WP_CLI::warning( __( 'Action is invalid.', 'pods' ) );

				$progress_bar->tick();

				continue;
			}

			$action_name = $action['action'];

			$action_comment = isset( $action['#'] ) ? $action['#'] : $action_name;

			$action_args = [];

			// Check which kind of arguments we will use.
			if ( isset( $action['params'] ) ) {
				$action_args = [
					$action['params'],
				];
			} elseif ( isset( $action['args'] ) ) {
				$action_args = $action['args'];
			}

			WP_CLI::debug( sprintf( '%1$s: %2$s > PodsAPI::%3$s( ...%4$s )', __( 'Running playbook action', 'pods' ), $action_comment, $action_name, wp_json_encode( $action_args ) ) );

			// Run the action if not in test mode.
			if ( ! $test_mode ) {
				$this->run_action( $action_name, $action_args, $api, $continue_on_error );
			}

			$progress_bar->tick();
		}

		$progress_bar->finish();

		WP_CLI::success( __( 'Playbook run completed.', 'pods' ) );
	}

	/**
	 * Handle running the action with a try/catch for error handling.
	 *
	 * @param string  $action_name
	 * @param array   $action_args
	 * @param PodsAPI $api
	 * @param bool    $continue_on_error
	 *
	 * @throws WP_CLI\ExitException
	 */
	protected function run_action( $action_name, $action_args, $api, $continue_on_error ) {
		try {
			if ( 'run' !== $action_name && method_exists( $this, $action_name ) ) {
				$this->$action_name( ...$action_args );
			} elseif ( method_exists( $api, $action_name ) ) {
				$api->$action_name( ...$action_args );
			} else {
				// translators: %s: The action name.
				WP_CLI::warning( sprintf( __( 'Action not supported: %s', 'pods' ), $action_name ) );
			}
		} catch ( Exception $exception ) {
			// translators: %s: The exception error message.
			$playbook_error_message = sprintf( __( 'Playbook error: %s', 'pods' ), $exception->getMessage() );

			if ( $continue_on_error ) {
				WP_CLI::warning( $playbook_error_message );
			} else {
				WP_CLI::error( $playbook_error_message );
			}
		}
	}

	/**
	 * Import the package file for the playbook.
	 *
	 * @since 2.8.11
	 *
	 * @param string|array $data    The JSON file location, the JSON encoded package string, or an associative array containing the package data.
	 * @param bool         $replace Whether to replace existing items when found.
	 *
	 * @return array|bool
	 *
	 * @throws Exception
	 */
	protected function import_package( $data, $replace = false ) {
		if ( ! PodsInit::$components->is_component_active( 'migrate-packages' ) ) {
			// Attempt to include the Package component code manually.
			include_once PODS_DIR . 'components/Migrate-Packages/Migrate-Packages.php';

			if ( ! class_exists( 'Pods_Migrate_Packages' ) ) {
				throw new Exception( sprintf( __( 'Migrate Package is not activated. Try activating it: %s', 'pods' ), 'wp pods-api activate-component --component=migrate-packages' ), 'pods-package-import-error' );
			}
		}

		// Check that we have the file or package we expect.
		if ( empty( $data ) ) {
			throw new Exception( __( 'Playbook import package was not set.', 'pods' ), 'pods-package-import-error' );
		}

		$is_file = is_string( $data ) && '.json' === substr( $data, strrpos( $data, '.json' ) );

		if ( $is_file ) {
			// Get package file data.
			if ( ! file_exists( $data ) ) {
				throw new Exception( __( 'Playbook import package "file" does not exist.', 'pods' ), 'pods-package-import-error' );
			}

			// Load PodsMigrate class file for use.
			pods_migrate();

			$data = PodsMigrate::get_data_from_file( $data, true );
		}

		if ( empty( $data ) ) {
			throw new Exception( __( 'No Pods Package data found.', 'pods' ), 'pods-package-import-error' );
		}

		return Pods_Migrate_Packages::import( $data, $replace );
	}

}
