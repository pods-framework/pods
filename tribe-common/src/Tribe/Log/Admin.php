<?php
class Tribe__Log__Admin {
	public function __construct() {
		add_action( 'wp_ajax_tribe_logging_controls', [ $this, 'listen' ] );
		add_action( 'init', [ $this, 'serve_log_downloads' ] );
		add_action( 'plugins_loaded', [ $this, 'register_script' ] );
	}

	/**
	 * Returns the HTML comprising the event log section for use in the
	 * Events > Settings > Help screen.
	 *
	 * @return string
	 */
	public function display_log() {
		$log_choices  = $this->get_available_logs();
		$log_engines  = $this->get_log_engines();
		$log_levels   = $this->get_logging_levels();
		$log_entries  = $this->get_log_entries();
		$download_url = $this->get_log_url();

		ob_start();
		include trailingslashit( Tribe__Main::instance()->plugin_path ) . 'src/admin-views/event-log.php';
		return ob_get_clean();
	}

	/**
	 * Listens for changes to the event log settings updating and returning
	 * an appropriate response.
	 */
	public function listen() {
		$fields = wp_parse_args( $_POST, [
			'check'      => '',
			'log-level'  => '',
			'log-engine' => '',
		] );

		foreach ( $fields as &$single_field ) {
			$single_field = sanitize_text_field( $single_field );
		}

		if ( ! wp_verify_nonce( $fields['check'], 'logging-controls' ) ) {
			return;
		}

		/**
		 * Fires before log settings are committed.
		 *
		 * This will not happen unless a nonce check has already passed.
		 */
		do_action( 'tribe_common_update_log_settings' );

		$this->update_logging_level( $fields['log-level'] );
		$this->update_logging_engine( $fields['log-engine'] );

		/**
		 * Fires immediately after log settings have been committed.
		 */
		do_action( 'tribe_common_updated_log_settings' );

		$data = [
			'logs' => $this->get_available_logs(),
		];

		if ( ! empty( $fields['log-view'] ) ) {
			$data['entries'] = $this->get_log_entries( $fields['log-view'] );
		}

		wp_send_json_success( $data );
	}

	/**
	 * Sets the current logging level to the provided level (if it is a valid
	 * level, else will set the level to 'default').
	 *
	 * @param string $level
	 */
	protected function update_logging_level( $level ) {
		$this->log_manager()->set_level( $level );
	}

	/**
	 * Sets the current logging engine to the provided class (if it is a valid
	 * and currently available logging class, else will set this to null - ie
	 * no logging).
	 *
	 * @param string $engine
	 */
	protected function update_logging_engine( $engine ) {
		try {
			$this->log_manager()->set_current_logger( $engine );
		}
		catch ( Exception $e ) {
			// The class name did not relate to a valid logging engine
		}
	}

	/**
	 * Register our script early.
	 */
	public function register_script() {
		tribe_asset(
			Tribe__Main::instance(),
			'tribe-common-logging-controls',
			'admin-log-controls.js',
			[ 'jquery' ],
			'admin_enqueue_scripts',
			[
				'conditionals' => [ Tribe__Admin__Help_Page::instance(), 'is_current_page' ],
				'localize'     => (object) [
					'name' => 'tribe_logger_data',
					'data' => [
						'check' => wp_create_nonce( 'logging-controls' ),
					],
				],
			]
		);
	}

	/**
	 * Returns a list of logs that are available for perusal.
	 *
	 * @return array
	 */
	protected function get_available_logs() {
		$current_logger = $this->current_logger();

		if ( $current_logger ) {
			$available_logs = $this->current_logger()->list_available_logs();
		}

		if ( empty( $available_logs ) ) {
			return [ '' => _x( 'None currently available', 'log selector', 'tribe-common' ) ];
		}

		return $available_logs;
	}

	/**
	 * Returns a list of logging engines that are available for use.
	 *
	 * @return array
	 */
	protected function get_log_engines() {
		$available_engines = $this->log_manager()->get_logging_engines();

		if ( empty( $available_engines ) ) {
			return [ '' => _x( 'None currently available', 'log engines', 'tribe-common' ) ];
		}

		$engine_list = [];

		foreach ( $available_engines as $class_name => $engine ) {
			/**
			 * @var Tribe__Log__Logger $engine
			 */
			$engine_list[ $class_name ] = $engine->get_name();
		}

		return $engine_list;
	}

	/**
	 * Returns all log entries for the current or specified log.
	 *
	 * @return array
	 */
	protected function get_log_entries( $log = null ) {
		if ( $logger = $this->current_logger() ) {
			$logger->use_log( $log );
			return (array) $logger->retrieve();
		}

		return [];
	}

	/**
	 * Returns an array of logging levels arranged as key:value pairs, with
	 * each key being the level code and the value being the human-friendly
	 * description.
	 *
	 * @return array
	 */
	protected function get_logging_levels() {
		$levels           = [];
		$available_levels = $this->log_manager()->get_logging_levels();

		foreach ( $available_levels as $logging_level ) {
			$levels[ $logging_level[0] ] = $logging_level[1];
		}

		return $levels;
	}

	/**
	 * Provides a URL that can be used to download the current or specified
	 * log.
	 *
	 * @param $log
	 *
	 * @return string
	 */
	protected function get_log_url( $log = null ) {
		$query = [
			'tribe-common-log' => 'download',
			'check'            => wp_create_nonce( 'download_log' ),
		];

		$log_download_url = add_query_arg( $query, get_admin_url( null, 'edit.php' ) );

		return esc_url( $log_download_url );
	}

	/**
	 * Facilitate downloading of logs.
	 */
	public function serve_log_downloads() {
		if ( empty( $_GET['tribe-common-log'] ) || 'download' !== $_GET['tribe-common-log'] ) {
			return;
		}

		if ( ! wp_verify_nonce( @$_GET['check'], 'download_log' ) ) {
			return;
		}

		if ( empty( $_GET['log'] ) || ! in_array( $_GET['log'], $this->get_available_logs() ) ) {
			return;
		}

		$log_name = sanitize_file_name( $_GET['log'] );
		$this->current_logger()->use_log( $log_name );

		/**
		 * Provides an opportunity to modify the recommended filename for a downloaded
		 * log file.
		 *
		 * @param string $log_name
		 */
		$log_name = apply_filters( 'tribe_common_log_download_filename', $log_name );

		header( 'Content-Disposition: attachment; filename="tribe-log-' . $log_name . '"' );
		$output = fopen( 'php://output', 'w' );

		foreach ( $this->current_logger()->retrieve() as $log_entry ) {
			fputcsv( $output, $log_entry );
		}

		fclose( $output );
		exit();
	}

	/**
	 * Returns a reference to the main log management object.
	 *
	 * @return Tribe__Log
	 */
	protected function log_manager() {
		return tribe( 'logger' );
	}

	/**
	 * Returns the currently enabled logging object or null if it is not
	 * available.
	 *
	 * @return Tribe__Log__Logger|null
	 */
	protected function current_logger() {
		return tribe( 'logger' )->get_current_logger();
	}
}
