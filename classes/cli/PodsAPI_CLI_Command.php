<?php
/**
 * Implements PodsAPI command for WP-CLI
 */
class PodsAPI_CLI_Command extends WP_CLI_Command {

	/**
	 *
	 *
	 * @synopsis   --name=<name> --type=<type> --<field>=<value>
	 * @subcommand add-pod
	 */
	function add_pod( $args, $assoc_args ) {

		if ( isset( $assoc_args['id'] ) ) {
			unset( $assoc_args['id'] );
		}

		$id = pods_api()->save_pod( $assoc_args );

		if ( 0 < $id ) {
			WP_CLI::success( __( 'Pod added', 'pods' ) );
			WP_CLI::line( "ID: {$id}" );
		} else {
			WP_CLI::error( __( 'Error adding pod', 'pods' ) );
		}

	}

	/**
	 *
	 *
	 * @synopsis   --<field>=<value>
	 * @subcommand save-pod
	 */
	function save_pod( $args, $assoc_args ) {

		$id = pods_api()->save_pod( $assoc_args );

		if ( 0 < $id ) {
			WP_CLI::success( __( 'Pod saved', 'pods' ) );
			WP_CLI::line( "ID: {$id}" );
		} else {
			WP_CLI::error( __( 'Error saving pod', 'pods' ) );
		}

	}

	/**
	 *
	 *
	 * @synopsis   --<field>=<value>
	 * @subcommand duplicate-pod
	 */
	function duplicate_pod( $args, $assoc_args ) {

		$id = pods_api()->duplicate_pod( $assoc_args );

		if ( 0 < $id ) {
			WP_CLI::success( __( 'Pod duplicated', 'pods' ) );
			WP_CLI::line( "New ID: {$id}" );
		} else {
			WP_CLI::error( __( 'Error duplicating pod', 'pods' ) );
		}

	}

	/**
	 *
	 *
	 * @synopsis   --<field>=<value>
	 * @subcommand reset-pod
	 */
	function reset_pod( $args, $assoc_args ) {

		$reset = pods_api()->reset_pod( $assoc_args );

		if ( $reset ) {
			WP_CLI::success( __( 'Pod content reset', 'pods' ) );
		} else {
			WP_CLI::error( __( 'Error resetting pod', 'pods' ) );
		}

	}

	/**
	 *
	 *
	 * @synopsis   --<field>=<value>
	 * @subcommand delete-pod
	 */
	function delete_pod( $args, $assoc_args ) {

		$deleted = pods_api()->delete_pod( $assoc_args );

		if ( $deleted ) {
			WP_CLI::success( __( 'Pod deleted', 'pods' ) );
		} else {
			WP_CLI::error( __( 'Error deleting pod', 'pods' ) );
		}

	}

	/**
	 * Activate a component
	 *
	 * @synopsis   --component=<component>
	 * @subcommand activate-component
	 */
	function activate_component( $args, $assoc_args ) {

		if ( ! class_exists( 'PodsInit' ) ) {
			WP_CLI::error( __( 'PodsInit not available', 'pods' ) );

			return;
		}

		$component = $assoc_args['component'];

		$active = PodsInit::$components->is_component_active( $component );

		if ( $active ) {
			WP_CLI::error( sprintf( __( 'Component %s is already active', 'pods' ), $component ) );
		} else {
			PodsInit::$components->activate_component( $component );

			WP_CLI::success( __( 'Component activated', 'pods' ) );
		}

	}

	/**
	 * Deactivate a component
	 *
	 * @synopsis   --component=<component>
	 * @subcommand deactivate-component
	 */
	function deactivate_component( $args, $assoc_args ) {

		if ( ! class_exists( 'PodsInit' ) ) {
			WP_CLI::error( __( 'PodsInit not available', 'pods' ) );

			return;
		}

		$component = $assoc_args['component'];

		$active = PodsInit::$components->is_component_active( $component );

		if ( ! $active ) {
			WP_CLI::error( sprintf( __( 'Component %s is already not active', 'pods' ), $component ) );
		} else {
			PodsInit::$components->deactivate_component( $component );

			WP_CLI::success( __( 'Component deactivated', 'pods' ) );
		}

	}

	/**
	 * Clear pods cache
	 *
	 * @subcommand clear-cache
	 */
	function clear_cache() {

		pods_api()->cache_flush_pods();

		WP_CLI::success( __( 'Pod cache cleared', 'pods' ) );
	}

	/**
	 *
	 *
	 * @synopsis   --pod=<pod> --file=<file>
	 * @subcommand export-pod
	 */
	/*function export_pod ( $args, $assoc_args ) {
		$data = pods_api()->load_pod( array( 'name' => $assoc_args[ 'pod' ] ) );

		if ( !empty( $data ) ) {
			$data = json_encode( $data );

			// @todo write to file
		}

		// @todo success message
	}*/

	/**
	 *
	 *
	 * @synopsis   --file=<file>
	 * @subcommand import-pod
	 */
	/*function import_pod ( $args, $assoc_args ) {
		$data = ''; // @todo get data from file

		$package = array();

		if ( !empty( $data ) )
			$package = @json_decode( $data, true );

		if ( is_array( $package ) && !empty( $package ) ) {
			$api = pods_api();

			if ( isset( $package[ 'id' ] ) )
				unset( $package[ 'id' ] );

			$try = 1;
			$check_name = $package[ 'name' ];

			while ( $api->load_pod( array( 'name' => $check_name, 'table_info' => false ), false ) ) {
				$try++;
				$check_name = $package[ 'name' ] . $try;
			}

			$package[ 'name' ] = $check_name;

			$id = $api->save_pod( $package );

			if ( 0 < $id ) {
				WP_CLI::success( __( 'Pod imported', 'pods' ) );
				WP_CLI::line( "ID: {$id}" );
			}
			else
				WP_CLI::error( __( 'Error importing pod', 'pods' ) );
		}
		else
			WP_CLI::error( __( 'Invalid package, Pod not imported', 'pods' ) );
	}*/

}

WP_CLI::add_command( 'pods-api', 'PodsAPI_CLI_Command' );
