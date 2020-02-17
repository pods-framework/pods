<?php
if ( class_exists( 'Tribe__Log' ) ) {
	return;
}

/**
 * Provides access to and management of core logging facilities.
 */
class Tribe__Log {
	const DISABLE = 'disable';
	const DEBUG   = 'debug';
	const WARNING = 'warning';
	const ERROR   = 'error';
	const CLEANUP = 'tribe_common_log_cleanup';
	const SUCCESS = 'success';
	const COLORIZE = 'colorize';

	/**
	 * @var Tribe__Log__Admin
	 */
	protected $admin;

	/**
	 * @var Tribe__Log__Logger
	 */
	protected $current_logger;

	/**
	 * @var string
	 */
	protected $current_level;

	/**
	 * All logging levels in priority order. Each level is represented by
	 * an array in the form [ code => description ].
	 *
	 * @var array
	 */
	protected $levels = array();

	/**
	 * Alternative representation of the $levels property allowing quick look
	 * up of levels by priority.
	 *
	 * @var array
	 */
	protected $prioritized_levels = array();

	/**
	 * Instantiated loggers, stored for re-use.
	 *
	 * @var array
	 */
	protected $loggers = array();


	public function __construct() {
		if ( is_admin() ) {
			$this->admin = new Tribe__Log__Admin();
		}

		$this->current_level = $this->get_level();
		$this->log_cleanup();
	}

	/**
	 * @return Tribe__Log__Admin
	 */
	public function admin() {
		return $this->admin;
	}

	/**
	 * Facilitates daily cleanup and log rotation.
	 */
	protected function log_cleanup() {
		$this->register_cleanup_task();
		do_action( self::CLEANUP, array( $this, 'do_cleanup' ) );
	}

	/**
	 * Schedules a daily cleanup task if one is not already in place.
	 */
	protected function register_cleanup_task() {
		if ( ! wp_next_scheduled( self::CLEANUP ) ) {
			wp_schedule_event( strtotime( '+1 day' ), 'daily', self::CLEANUP );
		}
	}

	/**
	 * Call the cleanup() method for each available logging engine.
	 *
	 * We don't just call it on the current engine since, if there was a recent change,
	 * we'll generally still want the now unused engine's output to be cleaned up.
	 */
	public function do_cleanup() {
		foreach ( $this->get_logging_engines() as $engine ) {
			/**
			 * @var Tribe__Log__Logger $engine
			 */
			$engine->cleanup();
		}
	}

	/**
	 * Logs a debug-level entry.
	 *
	 * @param string $entry
	 * @param string $src
	 */
	public function log_debug( $entry, $src ) {
		$this->log( $entry, self::DEBUG, $src );
	}

	/**
	 * Logs a warning.
	 *
	 * @param string $entry
	 * @param string $src
	 */
	public function log_warning( $entry, $src ) {
		$this->log( $entry, self::WARNING, $src );
	}

	/**
	 * Logs an error.
	 *
	 * @param string $entry
	 * @param string $src
	 */
	public function log_error( $entry, $src ) {
		$this->log( $entry, self::ERROR, $src );
	}

	/**
	 * Logs a successful operation.
	 *
	 * @param string $entry
	 * @param string $src
	 */
	public function log_success( $entry, $src ) {
		$this->log( $entry, self::SUCCESS, $src );
	}

	/**
	 * Logs an entry colorizing it.
	 *
	 * This will only apply to WP-CLI based logging.
	 *
	 * @param string $entry
	 * @param string $src
	 */
	public function log_colorized( $entry, $src ) {
		$this->log( $entry, self::COLORIZE, $src );
	}

	/**
	 * Adds an entry to the log (if it is at the appropriate level, etc) and outputs information using WP-CLI if available.
	 *
	 * This is simply a shorthand for calling log() on the current logger.
	 */
	public function log( $entry, $type = self::DEBUG, $src = '' ) {
		$original_type = $type;

		// some levels are really just debug information
		$debug_types = array( self::SUCCESS, self::COLORIZE );

		if ( in_array( $type, $debug_types ) ) {
			$type = self::DEBUG;
		}

		if ( $this->should_log( $type ) ) {
			$this->get_current_logger()->log( $entry, $type, $src );
		}

		/**
		 * Whether to log the message to wp-cli, if available, or not.
		 *
		 * @since 4.9.6
		 *
		 * @param bool $log_to_wpcli Whether to log to wp-cli, if available, or not.
		 * @param string $entry The message entry.
		 * @param string $type  The message type.
		 * @param string $src   The message source.
		 */
		$log_to_wpcli = apply_filters( 'tribe_common_log_to_wpcli', true, $entry, $type, $src );

		// Only go further if we have WP_CLI or if we want to log to WP-CLI.
		if ( ! class_exists( 'WP_CLI' ) || false === $log_to_wpcli ) {
			return false;
		}

		// We are always logging to WP-CLI if available
		switch ( $original_type ) {
			case self::ERROR:
				WP_CLI::error( $entry );
				break;
			case self::WARNING:
				WP_CLI::warning( $entry );
				break;
			case self::SUCCESS:
				WP_CLI::success( $entry );
				break;
			case self::DEBUG:
				WP_CLI::debug( $entry, $src );
				break;

			case self::COLORIZE:
			default:
				WP_CLI::log( WP_CLI::colorize( $entry ) );
				break;
		}
	}

	/**
	 * Returns a list of available logging engines as an array where each
	 * key is the classname and the value is the logger itself.
	 *
	 * @return array
	 */
	public function get_logging_engines() {
		$available_engines = array();
		$bundled_engines   = array(
			'Tribe__Log__File_Logger',
		);

		foreach ( $bundled_engines as $engine_class ) {
			$engine = $this->get_engine( $engine_class );

			// Check that we have a valid engine that is available for use in the current environment
			if ( $engine && $engine->is_available() ) {
				$available_engines[ $engine_class ] = $engine;
			}
		}

		/**
		 * Offers a chance to modify the array of currently available logging engines.
		 *
		 * The array is organized with each key as the class name of the logging
		 * implementation and the matching value is the actual logger object.
		 *
		 * @var array $available_engines
		 */
		return apply_filters( 'tribe_common_logging_engines', $available_engines );
	}

	/**
	 * Returns the currently active logger.
	 *
	 * If no loggers are available, this will be the null logger which is a no-op
	 * implementation (making it safe to call Tribe__Log__Logger methods on the
	 * return value of this method at all times).
	 *
	 * @since 4.6.2 altered the return signature to only return instances of Tribe__Log__Logger
	 *
	 * @return Tribe__Log__Logger
	 */
	public function get_current_logger() {
		if ( ! $this->current_logger ) {
			$engine = tribe_get_option( 'logging_class', null );
			$available = $this->get_logging_engines();

			if ( empty( $engine ) || ! isset( $available[ $engine ] ) ) {
				return $this->current_logger = new Tribe__Log__Null_Logger();
			} else {
				$this->current_logger = $this->get_engine( $engine );
			}
		}

		return $this->current_logger;
	}

	/**
	 * Sets the current logging engine to the provided class (if it is a valid
	 * and currently available logging class, else will set this to null - ie
	 * no logging).
	 *
	 * @param string $engine
	 *
	 * @throws Exception if the specified logging engine is invalid
	 */
	public function set_current_logger( $engine ) {
		$available_engines = $this->get_logging_engines();

		// Make sure to de-duplicate the slashes on class names.
		$engine = str_replace( '\\\\', '\\', $engine );

		if ( ! isset( $available_engines[ $engine ] ) ) {
			throw new Exception( sprintf( __( 'Cannot set %s as the current logging engine', 'tribe-common' ), $engine ) );
		}

		tribe_update_option( 'logging_class', $engine );
		$this->current_logger = $available_engines[ $engine ];
	}

	/**
	 * Attempts to return the requested logging object or null if that
	 * is not possible.
	 *
	 * @param $class_name
	 *
	 * @return Tribe__Log__Logger|null
	 */
	public function get_engine( $class_name ) {
		if ( ! isset( $this->loggers[ $class_name ] ) ) {
			$object = new $class_name;

			if ( $object instanceof Tribe__Log__Logger ) {
				$this->loggers[ $class_name ] = new $class_name();
			}
		}

		if ( isset( $this->loggers[ $class_name ] ) ) {
			return $this->loggers[ $class_name ];
		}

		return null;
	}

	/**
	 * Sets the current logging level to the provided level (if it is a valid
	 * level, else will set the level to 'default').
	 *
	 * @param string $level
	 */
	public function set_level( $level ) {
		$available_levels = wp_list_pluck( $this->get_logging_levels(), 0 );

		if ( ! in_array( $level, $available_levels ) ) {
			$level = self::DISABLE;
		}

		tribe_update_option( 'logging_level', $level );
		$this->current_level = $level;
	}

	/**
	 * Returns the current logging level as a string.
	 *
	 * @return string
	 */
	public function get_level() {
		$current_level = tribe_get_option( 'logging_level', null );
		$available_levels = wp_list_pluck( $this->get_logging_levels(), 0 );

		if ( ! in_array( $current_level, $available_levels ) ) {
			$current_level = self::DISABLE;
		}

		return $current_level;
	}

	/**
	 * Returns a list of logging levels.
	 *
	 * The format is an array of arrays, each inner array being comprised of the
	 * level code (index 0) and a human readable description (index 1).
	 *
	 * The ordering of the inner arrays is critical as it dictates what will be logged
	 * when a given logging level is in effect. Example: if the current logging level
	 * is "error" mode (only record error-level problems) then debug-level notices will
	 * *not* be recorded and nor will warnings.
	 *
	 * On the other hand, if the current logging level is "debug" then debug level
	 * notices *and* all higher levels (including warnings and errors) will be recorded.
	 *
	 * @return array
	 */
	public function get_logging_levels() {
		if ( empty( $this->levels ) ) {
			/**
			 * Provides an opportunity to add or remove logging levels. This is expected
			 * to be organized as an array of arrays: the ordering of each inner array
			 * is critical, see Tribe__Log::get_logging_levels() docs.
			 *
			 * General form:
			 *
			 *     [
			 *         [ 'disable' => 'description' ],  // * Do not log anything
			 *         [ 'error'   => 'description' ],  // ^ Log only the most critical problems
			 *         [ 'warning' => 'description' ],  // | ...
			 *         [ 'debug'   => 'description' ]   // v Log as much data as possible, including less important trivia
			 *     ]
			 *
			 * @param array $logging_levels
			 */
			$this->levels = (array) apply_filters( 'tribe_common_logging_levels', array(
				array( self::DISABLE, __( 'Disabled', 'tribe-common' ) ),
				array( self::ERROR,   __( 'Only errors', 'tribe-common' ) ),
				array( self::WARNING, __( 'Warnings and errors', 'tribe-common' ) ),
				array( self::DEBUG,   __( 'Full debug (all events)', 'tribe-common' ) ),
			) );
		}

		return $this->levels;
	}

	/**
	 * Indicates if errors relating to the specified logging level should indeed
	 * be logged.
	 *
	 * Examples if the current logging level is "warning" (log all warnings and errors):
	 *
	 *     * Returns true for "error"
	 *     * Returns true for "warning"
	 *     * Returns false for "debug"
	 *
	 * The above assumes we are using the default logging levels.
	 *
	 * @param string $level_code
	 *
	 * @return bool
	 */
	protected function should_log( $level_code ) {
		if ( empty( $this->prioritized_levels ) ) {
			$this->build_prioritized_levels();
		}

		// Protect against the possibility non-existent level codes might be passed in
		if ( ! isset( $this->prioritized_levels[ $level_code ] ) ) {
			return false;
		}

		return $this->prioritized_levels[ $level_code ] <= $this->prioritized_levels[ $this->current_level ];
	}

	/**
	 * Creates a second list of logging levels allowing easy lookup of
	 * their relative priorities (ie, a means of quickly checking if
	 * an "error" level entry should be recorded when we're in debug
	 * mode).
	 */
	protected function build_prioritized_levels() {
		foreach ( $this->get_logging_levels() as $index => $level_data ) {
			$this->prioritized_levels[ $level_data[ 0 ] ] = $index;
		}
	}
}
