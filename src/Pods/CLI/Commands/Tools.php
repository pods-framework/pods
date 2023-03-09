<?php

namespace Pods\CLI\Commands;

use Exception;
use Pods\Tools\Repair;
use Pods\Tools\Reset;
use Pods_Migrate_Packages;
use PodsInit;
use PodsMigrate;
use WP_CLI;
use WP_CLI_Command;
use function WP_CLI\Utils\make_progress_bar;

/**
 * Pods Tools commands.
 *
 * @since 2.9.10
 */
class Tools extends WP_CLI_Command {

	/**
	 * Delete all content for Pod.
	 *
	 * ## OPTIONS
	 *
	 * <pod>
	 * : The pod name.
	 *
	 * [--test]
	 * : Whether to run the tool in test mode and not add/change/remove any data in the database.
	 *
	 * ## EXAMPLES
	 *
	 * wp pods tools delete-all-content your_pod
	 * - Delete all content for the pod "your_pod".
	 *
	 * wp pods tools delete-all-content your_pod --test
	 * - Preview the deleting of all content for the pod "your_pod", without changing the database.
	 *
	 * @subcommand delete-all-content
	 *
	 * @since 2.9.10
	 *
	 * @param array $args       The list of positional arguments.
	 * @param array $assoc_args The list of associative arguments.
	 */
	public function delete_all_content( $args, $assoc_args ) {
		$api = pods_api();

		$pod_name  = $args[0];
		$test_mode = ! empty( $assoc_args['test'] );

		// Run the tool.
		if ( empty( $pod_name ) ) {
			WP_CLI::error( __( 'No Pod specified.', 'pods' ) );

			return;
		} else {
			try {
				$pod = $api->load_pod( [ 'name' => $pod_name ], false );

				if ( empty( $pod ) ) {
					WP_CLI::error( __( 'Pod not found.', 'pods' ) );

					return;
				} else {
					$tool = pods_container( Reset::class );

					$mode = 'full';

					if ( $test_mode ) {
						$mode = 'preview';
					}

					$tool->delete_all_content_for_pod( $pod, $mode );
				}
			} catch ( Exception $exception ) {
				WP_CLI::error( $exception->getMessage() );

				return;
			}
		}

		WP_CLI::debug( __( 'Command timing', 'pods' ) );
		WP_CLI::success( __( 'Content deleted', 'pods' ) );
	}

	/**
	 * Delete all relationship data for Pod.
	 *
	 * ## OPTIONS
	 *
	 * <pod>
	 * : The pod name.
	 *
	 * [--fields]
	 * : The field name(s) (leave empty to delete relationship data for all fields on pod).
	 *
	 * [--test]
	 * : Whether to run the tool in test mode and not add/change/remove any data in the database.
	 *
	 * ## EXAMPLES
	 *
	 * wp pods tools delete-all-relationship-data your_pod
	 * - Delete all relationship data for the pod "your_pod".
	 *
	 * wp pods tools delete-all-relationship-data your_pod --test
	 * - Preview the deleting of all relationship data for the pod "your_pod", without changing the database.
	 *
	 * @subcommand delete-all-relationship-data
	 *
	 * @since 2.9.10
	 *
	 * @param array $args       The list of positional arguments.
	 * @param array $assoc_args The list of associative arguments.
	 */
	public function delete_all_relationship_data_for_pod( $args, $assoc_args ) {
		$api = pods_api();

		$pod_name    = $args[0];
		$field_names = ! empty( $assoc_args['fields'] ) ? $assoc_args['fields'] : null;
		$test_mode   = ! empty( $assoc_args['test'] );

		// Run the tool.
		if ( empty( $pod_name ) ) {
			WP_CLI::error( __( 'No Pod specified.', 'pods' ) );

			return;
		} else {
			try {
				$pod = $api->load_pod( [ 'name' => $pod_name ], false );

				if ( empty( $pod ) ) {
					WP_CLI::error( __( 'Pod not found.', 'pods' ) );

					return;
				} else {
					$tool = pods_container( Reset::class );

					$mode = 'full';

					if ( $test_mode ) {
						$mode = 'preview';
					}

					$tool->delete_all_relationship_data_for_pod( $pod, $field_names, $mode );
				}
			} catch ( Exception $exception ) {
				WP_CLI::error( $exception->getMessage() );

				return;
			}
		}

		WP_CLI::debug( __( 'Command timing', 'pods' ) );
		WP_CLI::success( __( 'Relationship data deleted', 'pods' ) );
	}

	/**
	 * Delete all groups and fields for Pod.
	 *
	 * ## OPTIONS
	 *
	 * <pod>
	 * : The pod name.
	 *
	 * [--test]
	 * : Whether to run the tool in test mode and not add/change/remove any data in the database.
	 *
	 * ## EXAMPLES
	 *
	 * wp pods tools delete-all-groups-and-fields your_pod
	 * - Delete all groups and fields for the pod "your_pod".
	 *
	 * wp pods tools delete-all-groups-and-fields your_pod --test
	 * - Preview the deleting of all groups and fields for the pod "your_pod", without changing the database.
	 *
	 * @subcommand delete-all-groups-and-fields
	 *
	 * @since 2.9.10
	 *
	 * @param array $args       The list of positional arguments.
	 * @param array $assoc_args The list of associative arguments.
	 */
	public function delete_all_groups_and_fields( $args, $assoc_args ) {
		$api = pods_api();

		$pod_name  = $args[0];
		$test_mode = ! empty( $assoc_args['test'] );

		// Run the tool.
		if ( empty( $pod_name ) ) {
			WP_CLI::error( __( 'No Pod specified.', 'pods' ) );

			return;
		} else {
			try {
				$pod = $api->load_pod( [ 'name' => $pod_name ], false );

				if ( empty( $pod ) ) {
					WP_CLI::error( __( 'Pod not found.', 'pods' ) );

					return;
				} else {
					$tool = pods_container( Reset::class );

					$mode = 'full';

					if ( $test_mode ) {
						$mode = 'preview';
					}

					$tool->delete_all_content_for_pod( $pod, $mode );
				}
			} catch ( Exception $exception ) {
				WP_CLI::error( $exception->getMessage() );

				return;
			}
		}

		WP_CLI::debug( __( 'Command timing', 'pods' ) );
		WP_CLI::success( __( 'Groups and Fields for Pod deleted', 'pods' ) );
	}

	/**
	 * Repair all groups and fields for Pod.
	 *
	 * ## OPTIONS
	 *
	 * <pod>
	 * : The pod name.
	 *
	 * [--test]
	 * : Whether to run the tool in test mode and not add/change/remove any data in the database.
	 *
	 * ## EXAMPLES
	 *
	 * wp pods tools repair-groups-and-fields your_pod
	 * - Repair groups and fields for the pod "your_pod".
	 *
	 * wp pods tools repair-groups-and-fields your_pod --test
	 * - Preview the repair of all groups and fields for the pod "your_pod", without changing the database.
	 *
	 * @subcommand repair-groups-and-fields
	 *
	 * @since 2.9.10
	 *
	 * @param array $args       The list of positional arguments.
	 * @param array $assoc_args The list of associative arguments.
	 */
	public function repair_groups_and_fields( $args, $assoc_args ) {
		$api = pods_api();

		$pod_name  = $args[0];
		$test_mode = ! empty( $assoc_args['test'] );

		// Run the tool.
		if ( empty( $pod_name ) ) {
			WP_CLI::error( __( 'No Pod specified.', 'pods' ) );

			return;
		} else {
			try {
				$pod = $api->load_pod( [ 'name' => $pod_name ], false );

				if ( empty( $pod ) ) {
					WP_CLI::error( __( 'Pod not found.', 'pods' ) );

					return;
				} else {
					$tool = pods_container( Repair::class );

					$mode = 'full';

					if ( $test_mode ) {
						$mode = 'preview';
					}

					$tool->repair_groups_and_fields_for_pod( $pod, $mode );
				}
			} catch ( Exception $exception ) {
				WP_CLI::error( $exception->getMessage() );

				return;
			}
		}

		WP_CLI::debug( __( 'Command timing', 'pods' ) );
		WP_CLI::success( __( 'Groups and Fields for Pod repaired', 'pods' ) );
	}

	/**
	 * Flush the Pods cache.
	 *
	 * ## OPTIONS
	 *
	 * [--pod=<pod>]
	 * : The pod name.
	 *
	 * ## EXAMPLES
	 *
	 * wp pods tools flush-cache
	 * - Flush the Pods cache.
	 *
	 * wp pods tools flush-cache --pod="your_pod"
	 * - Flush the Pods cache for the pod "your_pod".
	 *
	 * @subcommand flush-cache
	 *
	 * @since 2.9.10
	 *
	 * @param array $args       The list of positional arguments.
	 * @param array $assoc_args The list of associative arguments.
	 */
	public function flush_cache( $args, $assoc_args ) {
		$api = pods_api();

		$pod_name = pods_v( 'pod', $assoc_args );

		$pod = null;

		if ( ! empty( $pod_name ) ) {
			try {
				$pod = $api->load_pod( [ 'name' => $pod_name ] );
			} catch ( Exception $exception ) {
				WP_CLI::error( $exception->getMessage() );
			}
		}

		$api->cache_flush_pods( $pod );

		WP_CLI::debug( __( 'Command timing', 'pods' ) );
		WP_CLI::success( __( 'Pods cache flushed', 'pods' ) );
	}

}
