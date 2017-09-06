<?php
/**
 * Implements Pods command for WP-CLI
 */
class Pods_CLI_Command extends WP_CLI_Command {

	/**
	 *
	 *
	 * @synopsis --pod=<pod> --<field>=<value>
	 */
	function add( $args, $assoc_args ) {

		$pod  = $assoc_args['pod'];
		$item = pods_var_raw( 'item', $assoc_args );

		unset( $assoc_args['pod'] );

		if ( isset( $assoc_args['item'] ) ) {
			unset( $assoc_args['item'] );
		}

		if ( ! empty( $assoc_args ) ) {
			$id = pods( $pod, $item )->save( $assoc_args );

			if ( 0 < $id ) {
				WP_CLI::success( __( 'Pod item added', 'pods' ) );
				WP_CLI::line( "ID: {$id}" );
			} else {
				WP_CLI::error( __( 'Error saving pod item', 'pods' ) );
			}
		} else {
			WP_CLI::error( __( 'No data sent for saving', 'pods' ) );
		}

	}

	/**
	 *
	 *
	 * @synopsis --pod=<pod> [--item=<item>] --<field>=<value>
	 */
	function save( $args, $assoc_args ) {

		$pod  = $assoc_args['pod'];
		$item = pods_var_raw( 'item', $assoc_args );

		unset( $assoc_args['pod'] );

		if ( isset( $assoc_args['item'] ) ) {
			unset( $assoc_args['item'] );
		}

		if ( ! empty( $assoc_args ) ) {
			$id = pods( $pod, $item )->save( $assoc_args );

			if ( 0 < $id ) {
				WP_CLI::success( __( 'Pod item saved', 'pods' ) );
				WP_CLI::line( "ID: {$id}" );
			} else {
				WP_CLI::error( __( 'Error saving pod item', 'pods' ) );
			}
		} else {
			WP_CLI::error( __( 'No data sent for saving', 'pods' ) );
		}

	}

	/**
	 *
	 *
	 * @synopsis --pod=<pod> --item=<item>
	 */
	function duplicate( $args, $assoc_args ) {

		$id = pods( $assoc_args['pod'], $assoc_args['item'] )->duplicate();

		if ( 0 < $id ) {
			WP_CLI::success( __( 'Pod item duplicated', 'pods' ) );
			WP_CLI::line( "New ID: {$id}" );
		} else {
			WP_CLI::error( __( 'Error duplicating pod item', 'pods' ) );
		}

	}

	/**
	 *
	 *
	 * @synopsis --pod=<pod> --item=<item>
	 */
	function delete( $args, $assoc_args ) {

		$deleted = pods( $assoc_args['pod'], $assoc_args['item'] )->delete();

		if ( $deleted ) {
			WP_CLI::success( __( 'Pod item deleted', 'pods' ) );
		} else {
			WP_CLI::error( __( 'Error deleting pod item', 'pods' ) );
		}

	}

	/**
	 *
	 *
	 * @synopsis --pod=<pod> --file=<file> [--item=<item>] [--format=<format>] [--fields=<fields>] [--depth=<depth>]
	 */
	/*function export ( $args, $assoc_args ) {

		$params = array(
			'fields' => pods_var_raw( 'fields', $assoc_args ),
			'depth' => pods_var_raw( 'depth', $assoc_args )
		);

		$data = pods( $assoc_args[ 'pod' ], $assoc_args[ 'item' ] )->export( $params, null, pods_var_raw( 'format', $assoc_args ) );

		// @todo write to file

		// @todo success message

	}*/

	/**
	 *
	 *
	 * @synopsis --pod=<pod> --file=<file> [--format=<format>] [--numeric-mode=<numeric_mode>]
	 */
	/*function import ( $args, $assoc_args ) {

		$data = array(); // @todo get data from file

		$ids = pods_api( $assoc_args[ 'pod' ] )->import( $data, (boolean) pods_var_raw( 'numeric_mode', $assoc_args, false, null, true ), pods_var_raw( 'format', $assoc_args ) );

		// @todo success message

	}*/

}

WP_CLI::add_command( 'pods', 'Pods_CLI_Command' );
