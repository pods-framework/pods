<?php

/**
 * Implements PodsAPI command for WP-CLI
 */
class PodsAPI_CLI_Command extends WP_CLI_Command {

	/**
	 * Add a pod.
	 *
	 * ## OPTIONS
	 *
	 * --name=<name>
	 * : The pod name, the default type is post_type.
	 *
	 * [--<field>=<value>]
	 * : The field => value pair(s) to save.
	 *
	 * ## EXAMPLES
	 *
	 * wp pods-api add-pod --name=book
	 * wp pods-api add-pod --name=book --type=post_type
	 * wp pods-api add-pod --name=book --type=post_type --label=Books --singular_label=Book
	 * wp pods-api add-pod --name=genre --type=taxonomy --label=Genres --singular_label=Genre
	 *
	 * @subcommand add-pod
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	public function add_pod( $args, $assoc_args ) {

		// Don't allow id to be set.
		if ( isset( $assoc_args['id'] ) ) {
			unset( $assoc_args['id'] );
		}

		$api = pods_api();

		$id = 0;

		try {
			$id = $api->save_pod( $assoc_args );
		} catch ( Exception $e ) {
			WP_CLI::error( sprintf( __( 'Error saving pod: %s', 'pods' ), $e->getMessage() ) );
		}

		if ( 0 < $id ) {
			WP_CLI::success( __( 'Pod added.', 'pods' ) );
			WP_CLI::line( sprintf( __( 'New ID: %s', 'pods' ), $id ) );
		} else {
			WP_CLI::error( __( 'Pod not added.', 'pods' ) );
		}

	}

	/**
	 * Save a pod.
	 *
	 * ## OPTIONS
	 *
	 * --name=<name>
	 * : The pod name.
	 *
	 * [--<field>=<value>]
	 * : The field => value pair(s) to save.
	 *
	 * ## EXAMPLES
	 *
	 * wp pods-api save-pod --name=book --type=post_type
	 * wp pods-api save-pod --name=book --type=post_type --label=Books --singular_label=Book
	 * wp pods-api save-pod --name=genre --type=taxonomy --label=Genres --singular_label=Genre
	 *
	 * @subcommand save-pod
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	public function save_pod( $args, $assoc_args ) {

		// Don't allow id to be set.
		if ( isset( $assoc_args['id'] ) ) {
			unset( $assoc_args['id'] );
		}

		$api = pods_api();

		$id = 0;

		try {
			$pod = $api->load_pod( $assoc_args['name'] );

			if ( ! $pod ) {
				WP_CLI::error( sprintf( __( 'Pod "%s" does not exist.', 'pods' ), $assoc_args['name'] ) );
			}

			$id = $api->save_pod( $assoc_args );
		} catch ( Exception $e ) {
			WP_CLI::error( sprintf( __( 'Error saving pod: %s', 'pods' ), $e->getMessage() ) );
		}

		if ( 0 < $id ) {
			WP_CLI::success( __( 'Pod saved.', 'pods' ) );
			WP_CLI::line( sprintf( __( 'ID: %s', 'pods' ), $id ) );
		} else {
			WP_CLI::error( __( 'Pod not saved.', 'pods' ) );
		}

	}

	/**
	 * Duplicate a pod.
	 *
	 * ## OPTIONS
	 *
	 * --name=<name>
	 * : The pod name.
	 *
	 * [--new_name=<new_name>]
	 * : The new pod name (defaults to a unique non-conflicting name).
	 *
	 * [--<field>=<value>]
	 * : The field => value pair(s) to save.
	 *
	 * ## EXAMPLES
	 *
	 * wp pods-api duplicate-pod --name=book
	 * wp pods-api duplicate-pod --name=book --new_name=book2
	 * wp pods-api duplicate-pod --name=book --new_name=book2 --label="Books Two" --singular_label="Book Two"
	 *
	 * @subcommand duplicate-pod
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	public function duplicate_pod( $args, $assoc_args ) {

		// Don't allow id to be set.
		if ( isset( $assoc_args['id'] ) ) {
			unset( $assoc_args['id'] );
		}

		$api = pods_api();

		$id = 0;

		try {
			$pod = $api->load_pod( $assoc_args['name'] );

			if ( ! $pod ) {
				WP_CLI::error( sprintf( __( 'Pod "%s" does not exist.', 'pods' ), $assoc_args['name'] ) );
			}

			$id = $api->duplicate_pod( $assoc_args );
		} catch ( Exception $e ) {
			WP_CLI::error( sprintf( __( 'Error duplicating pod: %s', 'pods' ), $e->getMessage() ) );
		}

		if ( 0 < $id ) {
			WP_CLI::success( __( 'Pod duplicated.', 'pods' ) );
			WP_CLI::line( sprintf( __( 'New ID: %s', 'pods' ), $id ) );
		} else {
			WP_CLI::error( __( 'Pod not duplicated.', 'pods' ) );
		}

	}

	/**
	 * Reset a pod which will delete all pod items.
	 *
	 * ## OPTIONS
	 *
	 * --name=<name>
	 * : The pod name.
	 *
	 * ## EXAMPLES
	 *
	 * wp pods-api reset-pod --name=book
	 *
	 * @subcommand reset-pod
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	public function reset_pod( $args, $assoc_args ) {

		$api = pods_api();

		$reset = false;

		try {
			$pod = $api->load_pod( $assoc_args['name'] );

			if ( ! $pod ) {
				WP_CLI::error( sprintf( __( 'Pod "%s" does not exist.', 'pods' ), $assoc_args['name'] ) );
			}

			$reset = $api->reset_pod( $assoc_args );
		} catch ( Exception $e ) {
			WP_CLI::error( sprintf( __( 'Error resetting pod: %s', 'pods' ), $e->getMessage() ) );
		}

		if ( $reset ) {
			WP_CLI::success( __( 'Pod content reset.', 'pods' ) );
		} else {
			WP_CLI::error( __( 'Pod content not reset.', 'pods' ) );
		}

	}

	/**
	 * Delete a pod, which will NOT delete all pod items by default.
	 *
	 * ## OPTIONS
	 *
	 * --name=<name>
	 * : The pod name.
	 *
	 * [--delete-all]
	 * : Delete all pod content for the pod.
	 *
	 * ## EXAMPLES
	 *
	 * wp pods-api delete-pod --name=book
	 * wp pods-api delete-pod --name=book --delete_all
	 *
	 * @subcommand delete-pod
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	public function delete_pod( $args, $assoc_args ) {

		$api = pods_api();

		// Handle prettified arg name
		if ( ! empty( $assoc_args['delete-all'] ) ) {
			$assoc_args['delete_all'] = true;

			unset( $assoc_args['delete-all'] );
		}

		$deleted = false;

		try {
			$pod = $api->load_pod( $assoc_args['name'] );

			if ( ! $pod ) {
				WP_CLI::error( sprintf( __( 'Pod "%s" does not exist.', 'pods' ), $assoc_args['name'] ) );
			}

			$deleted = $api->delete_pod( $assoc_args );
		} catch ( Exception $e ) {
			WP_CLI::error( sprintf( __( 'Error deleting pod: %s', 'pods' ), $e->getMessage() ) );
		}

		if ( $deleted ) {
			WP_CLI::success( __( 'Pod deleted.', 'pods' ) );
		} else {
			WP_CLI::error( __( 'Pod not deleted.', 'pods' ) );
		}

	}

	/**
	 * Activate a component.
	 *
	 * ## OPTIONS
	 *
	 * --component=<component>
	 * : The component identifier.
	 *
	 * ## EXAMPLES
	 *
	 * wp pods-api activate-component --component=templates
	 *
	 * @subcommand activate-component
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	public function activate_component( $args, $assoc_args ) {

		if ( ! class_exists( 'PodsInit' ) ) {
			WP_CLI::error( __( 'PodsInit not available', 'pods' ) );

			return;
		}

		$component = $assoc_args['component'];

		$active = PodsInit::$components->is_component_active( $component );

		if ( $active ) {
			WP_CLI::error( sprintf( __( 'Component %s is already active.', 'pods' ), $component ) );
		} else {
			PodsInit::$components->activate_component( $component );

			WP_CLI::success( __( 'Component activated.', 'pods' ) );
		}

	}

	/**
	 * Deactivate a component.
	 *
	 * ## OPTIONS
	 *
	 * --component=<component>
	 * : The component identifier.
	 *
	 * ## EXAMPLES
	 *
	 * wp pods-api deactivate-component --component=templates
	 *
	 * @subcommand deactivate-component
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	public function deactivate_component( $args, $assoc_args ) {

		if ( ! class_exists( 'PodsInit' ) ) {
			WP_CLI::error( __( 'PodsInit not available', 'pods' ) );

			return;
		}

		$component = $assoc_args['component'];

		$active = PodsInit::$components->is_component_active( $component );

		if ( ! $active ) {
			WP_CLI::error( sprintf( __( 'Component %s is not active.', 'pods' ), $component ) );
		} else {
			PodsInit::$components->deactivate_component( $component );

			WP_CLI::success( __( 'Component deactivated.', 'pods' ) );
		}

	}

	/**
	 * Clear the Pods cache.
	 *
	 * ## EXAMPLES
	 *
	 * wp pods-api clear-cache
	 *
	 * @subcommand clear-cache
	 */
	public function cache_clear() {

		pods_api()->cache_flush_pods();

		WP_CLI::success( __( 'Pods cache cleared', 'pods' ) );

	}

	/**
	 * Export a Pods Package to a file.
	 *
	 * ## OPTIONS
	 *
	 * --file=<file>
	 * : The file to save to including path (defaults to current path).
	 *
	 * [--pods=<pods>]
	 * : A comma-separated list of Pods IDs to export (default is all Pods).
	 *
	 * [--templates=<templates>]
	 * : A comma-separated list of Pod Template IDs to export (default is all Templates).
	 *
	 * [--pages=<pages>]
	 * : A comma-separated list of Pod Page IDs to export (default is all Pod Pages).
	 *
	 * ## EXAMPLES
	 *
	 * wp pods-api export-pod --file="pods-package.json"
	 * wp pods-api export-pod --file="pods-package.json" --pods="book,genre"
	 * wp pods-api export-pod --file="/path/to/pods-package.json" --pods="book,genre"
	 * wp pods-api export-pod --templates="book-single,book-list" --file="pods-package.json"
	 * wp pods-api export-pod --pod-pages="books,books/*" --file="pods-package.json"
	 * wp pods-api export-pod --pods="book,genre" --templates="book-single,book-list" --pod-pages="books,books/*" --file="pods-package.json"
	 *
	 * @subcommand export-pod
	 */
	public function export_pod( $args, $assoc_args ) {

		if ( ! PodsInit::$components->is_component_active( 'migrate-packages' ) ) {
			WP_CLI::error( sprintf( __( 'Migrate Package is not activated. Try activating it: %s', 'pods' ), 'wp pods-api activate-component --component=migrate-packages' ) );
		}

		$params = array(
			'pods' => true,
		);

		if ( PodsInit::$components->is_component_active( 'templates' ) ) {
			$params['templates'] = true;
		}

		if ( PodsInit::$components->is_component_active( 'pages' ) ) {
			$params['pages'] = true;
		}

		$file = $assoc_args['file'];

		unset( $assoc_args['file'] );

		$params = array_merge( $params, $assoc_args );

		$data = false;

		try {
			$data = Pods_Migrate_Packages::export( $params );
		} catch ( Exception $e ) {
			WP_CLI::error( sprintf( __( 'Error exporting Pods Package: %s', 'pods' ), $e->getMessage() ) );
		}

		if ( ! empty( $data ) ) {
			// Load PodsMigrate class file for use.
			pods_migrate();

			// Only JSON format is supported for export.
			if ( false === strpos( $file, '.json' ) ) {
				$file .= '.json';
			}

			$export_file = PodsMigrate::export_data_to_file( $file, $data, true );

			if ( $export_file ) {
				WP_CLI::success( sprintf( __( 'Pods Package exported: %s', 'pods' ), $export_file ) );
			} else {
				WP_CLI::error( __( 'Pods Package not exported.', 'pods' ) );
			}
		} else {
			WP_CLI::error( __( 'No Pods Package data found.', 'pods' ) );
		}

	}

	/**
	 * Import a Pods Package from a file.
	 *
	 * ## OPTIONS
	 *
	 * --file=<file>
	 * : The file to save to including path (defaults to current path).
	 *
	 * [--replace]
	 * : Overwrite imported items if they already exist (defaults to false).
	 *
	 * ## EXAMPLES
	 *
	 * wp pods-api import-pod --file="pods-package.json"
	 * wp pods-api import-pod --file="/path/to/pods-package.json"
	 * wp pods-api import-pod --file="pods-package.json" --replace
	 *
	 * @subcommand import-pod
	 */
	public function import_pod( $args, $assoc_args ) {

		if ( ! PodsInit::$components->is_component_active( 'migrate-packages' ) ) {
			WP_CLI::error( sprintf( __( 'Migrate Package is not activated. Try activating it: %s', 'pods' ), 'wp pods-api activate-component --component=migrate-packages' ) );
		}

		$replace = false;

		if ( ! empty( $assoc_args['replace'] ) ) {
			$replace = true;
		}

		$file = $assoc_args['file'];

		$imported = false;

		try {
			// Load PodsMigrate class file for use.
			pods_migrate();

			// Only JSON format is supported for import.
			if ( false === strpos( $file, '.json' ) ) {
				WP_CLI::error( sprintf( __( 'Invalid file format, the file must use the .json extension: %s', 'pods' ), $file ) );
			}

			$data = PodsMigrate::get_data_from_file( $file, true );

			if ( empty( $data ) ) {
				WP_CLI::error( __( 'No Pods Package data found.', 'pods' ) );
			}

			$imported = Pods_Migrate_Packages::import( $data, $replace );
		} catch ( Exception $e ) {
			WP_CLI::error( sprintf( __( 'Error exporting Pods Package: %s', 'pods' ), $e->getMessage() ) );
		}

		if ( ! empty( $imported ) ) {
			WP_CLI::success( __( 'Pods Package imported.', 'pods' ) );
		} else {
			WP_CLI::error( __( 'Pods Package not imported.', 'pods' ) );
		}

	}

}

WP_CLI::add_command( 'pods-legacy-api', 'PodsAPI_CLI_Command' );
