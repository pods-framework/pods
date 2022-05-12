<?php

/**
 * Implements Pods command for WP-CLI
 */
class Pods_CLI_Command extends WP_CLI_Command {

	/**
	 * Add a pod item.
	 *
	 * ## OPTIONS
	 *
	 * --pod=<pod>
	 * : The pod name.
	 *
	 * --<field>=<value>
	 * : The field => value pair(s) to save.
	 *
	 * ## EXAMPLES
	 *
	 * wp pods add --pod=my_pod --my_field_name1=Value --my_field_name2="Another Value"
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	public function add( $args, $assoc_args ) {

		$pod_name = $assoc_args['pod'];

		unset( $assoc_args['pod'] );

		$pod = pods( $pod_name, null, false );

		if ( ! $pod->valid() ) {
			WP_CLI::error( sprintf( __( 'Pod "%s" does not exist.', 'pods' ), $assoc_args['pod'] ) );
		}

		if ( ! empty( $assoc_args ) ) {
			$id = 0;

			try {
				$id = $pod->add( $assoc_args );
			} catch ( Exception $e ) {
				WP_CLI::error( sprintf( __( 'Error saving pod item: %s', 'pods' ), $e->getMessage() ) );
			}

			if ( 0 < $id ) {
				WP_CLI::success( __( 'Pod item added.', 'pods' ) );
				WP_CLI::line( sprintf( __( 'New ID: %s', 'pods' ), $id ) );
			} else {
				WP_CLI::error( __( 'Pod item not added.', 'pods' ) );
			}
		} else {
			WP_CLI::error( __( 'No data sent for saving.', 'pods' ) );
		}

	}

	/**
	 * Save a pod item.
	 *
	 * ## OPTIONS
	 *
	 * --pod=<pod>
	 * : The pod name.
	 *
	 * [--item=<item>]
	 * : The item to save for, it is not used for a settings pod.
	 *
	 * --<field>=<value>
	 * : The field => value pair(s) to save.
	 *
	 * ## EXAMPLES
	 *
	 * wp pods save --pod=my_pod --item=123 --my_field_name1=Value2 --my_field_name2="Another Value2"
	 * wp pods save --pod=my_settings_pod --my_option_field_name1=Value --my_option_field_name2="Another Value2"
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	public function save( $args, $assoc_args ) {

		$pod_name = $assoc_args['pod'];
		$item     = pods_v( 'item', $assoc_args );

		unset( $assoc_args['pod'] );

		if ( null !== $item ) {
			unset( $assoc_args['item'] );
		}

		$pod = pods( $pod_name, $item, false );

		if ( ! $pod->valid() ) {
			WP_CLI::error( sprintf( __( 'Pod "%s" does not exist.', 'pods' ), $assoc_args['pod'] ) );
		}

		if ( null !== $item && ! $pod->exists() ) {
			WP_CLI::error( sprintf( __( 'Pod "%1$s" item "%2$s" does not exist.', 'pods' ), $assoc_args['pod'], $assoc_args['item'] ) );
		}

		if ( ! empty( $assoc_args ) ) {
			$id = 0;

			try {
				$id = $pod->save( $assoc_args );
			} catch ( Exception $e ) {
				WP_CLI::error( sprintf( __( 'Error saving pod item: %s', 'pods' ), $e->getMessage() ) );
			}

			if ( 0 < $id ) {
				WP_CLI::success( __( 'Pod item saved.', 'pods' ) );
				WP_CLI::line( sprintf( __( 'ID: %s', 'pods' ), $id ) );
			} else {
				WP_CLI::error( __( 'Pod item not saved.', 'pods' ) );
			}
		} else {
			WP_CLI::error( __( 'No data sent for saving.', 'pods' ) );
		}

	}

	/**
	 * Duplicate a pod item.
	 *
	 * ## OPTIONS
	 *
	 * --pod=<pod>
	 * : The pod name.
	 *
	 * --item=<item>
	 * : The pod item to delete.
	 *
	 * ## EXAMPLES
	 *
	 * wp pods duplicate --pod=my_pod --item=123
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	public function duplicate( $args, $assoc_args ) {

		$pod = pods( $assoc_args['pod'], $assoc_args['item'], false );

		if ( ! $pod->valid() ) {
			WP_CLI::error( sprintf( __( 'Pod "%s" does not exist.', 'pods' ), $assoc_args['pod'] ) );
		}

		if ( ! $pod->exists() ) {
			WP_CLI::error( sprintf( __( 'Pod "%1$s" item "%2$s" does not exist.', 'pods' ), $assoc_args['pod'], $assoc_args['item'] ) );
		}

		$id = 0;

		try {
			$id = $pod->duplicate( $assoc_args );
		} catch ( Exception $e ) {
			WP_CLI::error( sprintf( __( 'Error saving pod item: %s', 'pods' ), $e->getMessage() ) );
		}

		if ( 0 < $id ) {
			WP_CLI::success( __( 'Pod item duplicated.', 'pods' ) );
			WP_CLI::line( sprintf( __( 'New ID: %s', 'pods' ), $id ) );
		} else {
			WP_CLI::error( __( 'Pod item not duplicated.', 'pods' ) );
		}

	}

	/**
	 * Delete a pod item.
	 *
	 * ## OPTIONS
	 *
	 * --pod=<pod>
	 * : The pod name.
	 *
	 * --item=<item>
	 * : The pod item to delete.
	 *
	 * ## EXAMPLES
	 *
	 * wp pods delete --pod=my_pod --item=123
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	public function delete( $args, $assoc_args ) {

		$pod = pods( $assoc_args['pod'], $assoc_args['item'], false );

		if ( ! $pod->valid() ) {
			WP_CLI::error( sprintf( __( 'Pod "%s" does not exist.', 'pods' ), $assoc_args['pod'] ) );
		}

		if ( ! $pod->exists() ) {
			WP_CLI::error( sprintf( __( 'Pod "%1$s" item "%2$s" does not exist.', 'pods' ), $assoc_args['pod'], $assoc_args['item'] ) );
		}

		$deleted = false;

		try {
			$deleted = $pod->delete();
		} catch ( Exception $e ) {
			WP_CLI::error( sprintf( __( 'Error saving pod item: %s', 'pods' ), $e->getMessage() ) );
		}

		if ( $deleted ) {
			WP_CLI::success( __( 'Pod item deleted.', 'pods' ) );
		} else {
			WP_CLI::error( __( 'Pod item not deleted.', 'pods' ) );
		}

	}

	/**
	 * Export a single pod item to a file.
	 *
	 * ## OPTIONS
	 *
	 * --pod=<pod>
	 * : The pod name.
	 *
	 * --file=<file>
	 * : The file to save to including path (defaults to current path).
	 *
	 * [--item=<item>]
	 * : The item to save for, it is not used for a settings pod.
	 *
	 * [--fields=<fields>]
	 * : The comma-separated list of fields to export (defaults to all fields).
	 *
	 * [--depth=<depth>]
	 * : The depth of related objects to recursively export (default is 1 level deep, only returns IDs for related objects).
	 *
	 * ## EXAMPLES
	 *
	 * wp pods export-item --pod=my_pod --item=123 --file="item-data.json"
	 * wp pods export-item --pod=my_pod --item=123 --file="/path/to/item-data.json"
	 * wp pods export-item --pod=my_pod --item=123 --file="item-data.json" --fields="ID,post_title,post_content,my_field_name1,my_field_name2"
	 * wp pods export-item --pod=my_pod --item=123 --file="item-data.json" --depth=2
	 *
	 * @subcommand export-item
	 */
	public function export_item( $args, $assoc_args ) {

		$pod_name = $assoc_args['pod'];
		$item     = pods_v( 'item', $assoc_args );

		unset( $assoc_args['pod'] );

		if ( null !== $item ) {
			unset( $assoc_args['item'] );
		}

		$pod = pods( $pod_name, $item, false );

		if ( ! $pod->valid() ) {
			WP_CLI::error( sprintf( __( 'Pod "%s" does not exist.', 'pods' ), $assoc_args['pod'] ) );
		}

		if ( null !== $item && ! $pod->exists() ) {
			WP_CLI::error( sprintf( __( 'Pod "%1$s" item "%2$s" does not exist.', 'pods' ), $assoc_args['pod'], $assoc_args['item'] ) );
		}

		$params = array(
			'fields' => pods_v( 'fields', $assoc_args, null, true ),
			'depth'  => (int) pods_v( 'depth', $assoc_args, 1, true ),
		);

		$data = false;

		try {
			$data = $pod->export( $params );
		} catch ( Exception $e ) {
			WP_CLI::error( sprintf( __( 'Error exporting pod item: %s', 'pods' ), $e->getMessage() ) );
		}

		if ( ! empty( $data ) ) {
			// Load PodsMigrate class file for use.
			pods_migrate();

			$file = $assoc_args['file'];

			$export_file = PodsMigrate::export_data_to_file( $file, $data, true );

			if ( $export_file ) {
				WP_CLI::success( sprintf( __( 'Pod item exported: %s', 'pods' ), $export_file ) );
			} else {
				WP_CLI::error( __( 'Pod item not exported.', 'pods' ) );
			}
		} else {
			WP_CLI::error( __( 'No export data found.', 'pods' ) );
		}

	}

	/**
	 * Export all pod items to a file.
	 *
	 * ## OPTIONS
	 *
	 * --pod=<pod>
	 * : The pod name.
	 *
	 * --file=<file>
	 * : The file to save to including path (defaults to current path).
	 *
	 * [--fields=<fields>]
	 * : The comma-separated list of fields to export (defaults to all fields).
	 *
	 * [--depth=<depth>]
	 * : The depth of related objects to recursively export (default is 1 level deep, only returns IDs for related objects).
	 *
	 * [--params=<params>]
	 * : The params to pass into the Pods::find() call, provided in arg1=A&arg2=B or JSON format (default is limit=-1).
	 *
	 * ## EXAMPLES
	 *
	 * wp pods export --pod=my_pod --file="items.json"
	 * wp pods export --pod=my_pod --file="/path/to/items.json"
	 * wp pods export --pod=my_pod --file="items.json" --fields="ID,post_title,post_content,my_field_name1,my_field_name2"
	 * wp pods export --pod=my_pod --file="items.json" --depth=2
	 * wp pods export --pod=my_pod --file="items.json" --params="{\"limit\":10,\"orderby\":\"t.ID DESC\"}"
	 * wp pods export --pod=my_pod --file="items.json" --params="limit=10&orderby=t.ID DESC"
	 */
	public function export( $args, $assoc_args ) {

		$pod_name = $assoc_args['pod'];

		unset( $assoc_args['pod'] );

		$pod = pods( $pod_name, array( 'limit' => -1 ), false );

		if ( ! $pod->valid() ) {
			WP_CLI::error( sprintf( __( 'Pod "%s" does not exist.', 'pods' ), $assoc_args['pod'] ) );
		}

		$params = array(
			'fields' => pods_v( 'fields', $assoc_args, null, true ),
			'depth'  => (int) pods_v( 'depth', $assoc_args, 1, true ),
		);

		// Handle custom find() params.
		$find_params = pods_v( 'params', $assoc_args, null, true );

		if ( is_string( $find_params ) ) {
			$params['params'] = array();

			if ( false !== strpos( $params['params'], '{' ) ) {
				// Pull the find params from JSON format.
				$params['params'] = json_decode( $params['params'], true );
			} else {
				// Pull the find params from string argument format.
				wp_parse_str( $find_params, $params['params'] );
			}
		}

		$data = false;

		try {
			$data = $pod->export_data( $params );
		} catch ( Exception $e ) {
			WP_CLI::error( sprintf( __( 'Error exporting pod items: %s', 'pods' ), $e->getMessage() ) );
		}

		if ( ! empty( $data ) ) {
			// Load PodsMigrate class file for use.
			pods_migrate();

			$file = $assoc_args['file'];

			$export_file = PodsMigrate::export_data_to_file( $file, $data );

			if ( $export_file ) {
				WP_CLI::success( sprintf( __( 'Pod items exported: %s', 'pods' ), $export_file ) );
			} else {
				WP_CLI::error( __( 'Pod items not exported.', 'pods' ) );
			}
		} else {
			WP_CLI::error( __( 'No pod item export data found.', 'pods' ) );
		}

	}

}

WP_CLI::add_command( 'pods-legacy', 'Pods_CLI_Command' );
