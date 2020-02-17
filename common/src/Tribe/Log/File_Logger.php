<?php
/**
 * Simple file based logging implementation.
 *
 * By default, this logger uses the system temporary directory for logging
 * purposes and performs daily log rotation.
 */
class Tribe__Log__File_Logger implements Tribe__Log__Logger {
	protected $module_id = 'tribe_tmp_file_logger';
	protected $log_dir   = '';
	protected $log_file  = '';
	protected $context   = 'a';
	protected $handle;

	public function __construct() {
		$this->set_log_dir();
		$this->set_log_file();
	}

	public function __destruct() {
		$this->close_handle();
	}

	protected function set_log_dir() {
		/**
		 * Controls the directory used for logging.
		 *
		 * @var string $log_dir
		 */
		$this->log_dir = apply_filters( 'tribe_file_logger_directory', sys_get_temp_dir() );
	}

	/**
	 * Sets the path for the log file we're currently interested in using.
	 *
	 * @param string $date = null
	 */
	protected function set_log_file( $date = null ) {
		$this->log_file = $this->get_log_file_name( $date );
		$this->obtain_handle();
	}

	/**
	 * Used to switch between contexts for reading ('r') and writing
	 * ('a' := append) modes.
	 *
	 * @see fopen() documentation
	 *
	 * @param string $context
	 */
	protected function set_context( $context ) {
		$this->context = $context;
		$this->close_handle();
		$this->obtain_handle();
	}

	/**
	 * Attempts to obtain a file handle for the current log file.
	 */
	protected function obtain_handle() {
		$this->close_handle();

		if ( ! file_exists( $this->log_file ) && $this->is_available() ) {
			touch( $this->log_file );
		}

		// Bail if we're attempting to write but don't have permission.
		if ( 'r' !== $this->context && ! is_writable( $this->log_file ) ) {
			return;
		}

		if ( is_readable( $this->log_file ) ) {
			$this->handle = fopen( $this->log_file, $this->context );
		}
	}

	/**
	 * Closes the current file handle, if one is open.
	 */
	protected function close_handle() {
		// is_resource() only returns true for open resources
		if ( is_resource( $this->handle ) ) {
			fclose( $this->handle );
		}
	}

	/**
	 * Returns the log name to be used for reading/writing events for a specified date
	 * (defaulting to today, if no date is specified).
	 *
	 * @param string $date = null
	 *
	 * @return string
	 */
	protected function get_log_file_name( $date = null ) {
		if ( null === $date ) {
			$date = date_i18n( 'Y-m-d' );
		}

		$filename = $this->log_dir . DIRECTORY_SEPARATOR . $this->get_log_file_basename() . $date . '.log';

		/**
		 * Dictates the filename of the log used to record events for the specified date.
		 *
		 * @var string $filename
		 * @var string $date
		 */
		return apply_filters( 'tribe_file_logger_filename', $filename, $date );
	}

	protected function get_log_file_basename() {
		/**
		 * Log files share a common prefix, which aids identifying archived/rotated logs.
		 * This filter allows a degree of control to be exercised over the prefix to avoid
		 * conflicts, etc.
		 *
		 * @var string $log_file_base_name
		 */
		return apply_filters( 'tribe_file_logger_file_prefix', $this->module_id . '_' );
	}

	/**
	 * Returns a 'human friendly' name for the logging implementation.
	 *
	 * @return string
	 */
	public function get_name() {
		return __( 'Default (uses temporary files)', 'tribe-common' );
	}

	/**
	 * Indicates if the logger will work in the current environment.
	 *
	 * @return bool
	 */
	public function is_available() {
		return is_writable( $this->log_dir ) && is_readable( $this->log_dir );
	}

	/**
	 * Responsible for commiting the entry to the log.
	 *
	 * @param string $entry
	 * @param string $type
	 * @param string $src
	 */
	public function log( $entry, $type = Tribe__Log::DEBUG, $src = '' ) {
		// Ensure we're in 'append' mode before we try to write
		if ( 'a' !== $this->context ) {
			$this->set_context( 'a' );
		}

		// Couldn't obtain the file handle? We'll bail out without causing further disruption
		if ( ! $this->handle ) {
			return;
		}

		fputcsv( $this->handle, array( date_i18n( 'Y-m-d H:i:s' ), $entry, $type, $src ) );
	}

	/**
	 * Retrieve up to $limit most recent log entries in reverse chronological
	 * order. If $limit is a negative or zero value, there is no limit.
	 *
	 * Supports passing a 'log' argument to recover
	 *
	 * @see Tribe__Log__Logger::list_available_logs()
	 *
	 * @param int   $limit
	 * @param array $args
	 *
	 * @return array
	 */
	public function retrieve( $limit = 0, array $args = array() ) {
		// Ensure we're in 'read' mode before we try to retrieve
		if ( 'r' !== $this->context ) {
			$this->set_context( 'r' );
		}

		// Couldn't obtain the file handle? We'll bail out without causing further disruption
		if ( ! $this->handle ) {
			return array();
		}

		$rows = array();

		while ( $current_row = fgetcsv( $this->handle ) ) {
			if ( $limit && $limit === count( $rows ) ) {
				array_shift( $rows );
			}

			$rows[] = $current_row;
		}

		return array_reverse( $rows );
	}

	/**
	 * Returns a list of currently accessible logs (current first, oldest last).
	 * Each is refered to by date.
	 *
	 * Example:
	 *
	 *     [ '2016-12-31',
	 *       '2016-12-30',
	 *       '2016-12-30',
	 *       '2016-12-30',
	 *       '2016-12-30', ... ]
	 *
	 * @since 4.6.2 added extra safety checks before attempting to access log directory
	 *
	 * @return array
	 */
	public function list_available_logs() {
		$logs = array();

		// This could be called when the log dir is not accessible.
		if ( ! $this->is_available() ) {
			return $logs;
		}

		$basename = $this->get_log_file_basename();

		/**
		 * Though the is_available() method tests to see if the log directory is
		 * readable and writeable there are situations where that isn't a
		 * sufficient check by itself, hence the try/catch block.
		 *
		 * @see https://central.tri.be/issues/90436
		 */
		try {
			$log_files_dir = new DirectoryIterator( $this->log_dir );

			// Look through the log storage directory
			foreach ( $log_files_dir as $node ) {
				if ( ! $node->isReadable() ) {
					continue;
				}

				$name = $node->getFilename();

				// DirectoryIterator::getExtension() is only available on 5.3.6
				if ( version_compare( phpversion(), '5.3.6', '>=' ) ) {
					$ext = $node->getExtension();
				} else {
					$ext = pathinfo( $name, PATHINFO_EXTENSION );
				}

				// Skip unless it is a .log file with the expected prefix
				if ( 'log' !== $ext || 0 !== strpos( $name, $basename ) ) {
					continue;
				}

				if ( preg_match( '/([0-9]{4}\-[0-9]{2}\-[0-9]{2})/', $name, $matches ) ) {
					$logs[] = $matches[1];
				}
			}

			rsort( $logs );
		} catch ( Exception $e ) {
			return $logs;
		}

		return $logs;
	}

	/**
	 * Switches to the specified log. The $log_identifier should take the
	 * form of a "yyyy-mm-dd" format date string.
	 *
	 * If optional param $create is true then it will try to create a log
	 * using the provided identifier. If the log does not exist, cannot be
	 * created or an invalid identifier has been passed in then boolean false
	 * will be returned, otherwise it will attempt to switch to the new log.
	 *
	 * @param mixed $log_identifier
	 * @param bool $create
	 *
	 * @return bool
	 */
	public function use_log( $log_identifier, $create = false ) {
		$log_file = $this->get_log_file_name( $log_identifier );
		$exists   = file_exists( $log_file );

		if ( ! $exists && ! $create ) {
			return false;
		}

		if ( ! $exists && $create && preg_match( '/^([0-9]{4}\-[0-9]{2}\-[0-9]{2})$/', $log_file ) ) {
			if ( false === file_put_contents( $log_file, '' ) ) {
				return false;
			}
		}

		$this->set_log_file( $log_identifier );
		return true;
	}

	/**
	 * Performs routine maintenance and cleanup work (such as log rotation)
	 * whenever it is called.
	 */
	public function cleanup() {
		// Default to retaining 7 days worth of logs
		$cutoff = date_i18n( 'Y-m-d', current_time( 'timestamp' ) - WEEK_IN_SECONDS );

		/**
		 * Logs falling on or earlier than this date will be removed.
		 *
		 * @param string $cutoff 'Y-m-d' format date string
		 */
		$cutoff = apply_filters( 'tribe_file_logger_cutoff', $cutoff );

		foreach ( $this->list_available_logs() as $available_log ) {
			if ( $available_log <= $cutoff ) {
				unlink( $this->get_log_file_name( $available_log ) );
			}
		}
	}
}
