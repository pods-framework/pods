<?php

namespace Pods\CLI\Commands;

use Exception;
use WP_CLI;
use WP_CLI_Command;
use function WP_CLI\Utils\make_progress_bar;

/**
 * Pods Playbook commands.
 *
 * @since TBD
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
	 * @since 1.0.0
	 *
	 * @param array $args       The list of positional arguments.
	 * @param array $assoc_args The list of associative arguments.
	 */
	public function run( $args, $assoc_args ) {
		$playbook_file = $args[0];

		$test_mode = ! empty( $assoc_args['test'] );

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

		try {
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
						$action['params']
					];
				} elseif ( isset( $action['args'] ) ) {
					$action_args = $action['args'];
				}

				WP_CLI::debug( sprintf( '%1$s: %2$s > PodsAPI::%3$s( ...%4$s )', __( 'Running playbook action', 'pods' ), $action_comment, $action_name, wp_json_encode( $action_args ) ) );

				// Run the action if not in test mode.
				if ( ! $test_mode ) {
					if ( method_exists( $api, $action_name ) ) {
						$api->$action_name( ...$action_args );
					} else {
						// translators: %s: The action name.
						WP_CLI::warning( sprintf( __( 'Action not supported: %s', 'pods' ), $action_name ) );
					}
				}

				$progress_bar->tick();
			}

			$progress_bar->finish();
		} catch ( Exception $e ) {
			// translators: %s: The exception error message.
			WP_CLI::error( sprintf( __( 'Playbook error: %s', 'pods' ), $e->getMessage() ) );
		}

		WP_CLI::success( __( 'Playbook run completed.', 'pods' ) );
	}

}
