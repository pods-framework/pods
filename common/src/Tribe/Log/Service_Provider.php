<?php
/**
 * ${CARET}
 *
 * @since   4.9.16
 *
 * @package Tribe\Log
 */


namespace Tribe\Log;


use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;

class Service_Provider extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 4.9.16
	 */
	public function register() {
		$this->container->singleton( Logger::class, [ $this, 'build_logger' ] );
		$this->container->singleton( 'monolog',
			function () {
				return $this->container->make( Logger::class );
			}
		);

		add_action( 'tribe_log', [ $this, 'dispatch_log' ], 10, 3 );

		/**
		 * Filters whether to make the Action Logger available as logger or not.
		 *
		 * @since 4.9.16
		 *
		 * @param bool $use_action_logger Whether to allow logging messages from the \Tribe\Log\Logger class using the
		 *                                `tribe_log` action or not.
		 */
		$use_action_logger = apply_filters( 'tribe_log_use_action_logger', false );

		if ( $use_action_logger ) {
			add_filter( 'tribe_common_logging_engines', [ $this, 'add_logging_engine' ] );
		}
	}

	/**
	 * Builds and returns the Monolog Logger instance that will listen to the `tribe_log` action.
	 *
	 * To avoid the over-head introduced by filtering the filters are applied here, only once, when the instance is
	 * first built. Any later call will use the singleton instance stored in the container.
	 *
	 * @since 4.9.16
	 *
	 * @return Logger
	 */
	public function build_logger() {
		/**
		 * Filters the level of the messages that will be logged.
		 *
		 * The threshold is inclusive of the level; it default to log any warning and above.
		 *
		 * @since 4.9.16
		 *
		 * @param int  The threshold level; if the level of a message is this level or above, then it will be logged.
		 *
		 * @see   \Monolog\Logger for possible levels.
		 */
		$level_threshold = apply_filters( 'tribe_log_level', Logger::WARNING );

		$error_log_handler = new ErrorLogHandler( null, $level_threshold );

		/**
		 * Filters whether to use canonical format for the logs or not.
		 *
		 * @since 4.9.16
		 *
		 * @param bool $use_canonical_format Whether to use canonical format for the logs or not; defaults to `true`.
		 */
		$use_canonical_format = apply_filters( 'tribe_log_canonical', true );

		if ( $use_canonical_format ) {
			$error_log_handler->setFormatter( new Canonical_Formatter() );
		}

		$handlers = [
			'default' => $error_log_handler
		];

		/**
		 * Filters the list of handlers that will handle dispatched log messages.
		 *
		 * All handlers should implement the `\Monolog\Handler\HandlerInterface`.
		 *
		 * @since 4.9.16
		 *
		 * @param array $handlers An array of default log handlers.
		 */
		$handlers = apply_filters( 'tribe_log_handlers', $handlers );

		// Monolog will log to stderr when no handlers are set.
		$logger = new Monolog_Logger( Monolog_Logger::DEFAULT_CHANNEL );

		$logger->setHandlers( $handlers );

		return $logger;
	}

	/**
	 * Dispatch a message of a specific level.
	 *
	 * Available levels are: `debug`, `info`, `notice`, `warning`, `error`, `critical`, `alert`, `emergency`.
	 *
	 * @since 4.9.16
	 *
	 * @param string|int $level   Either the log level or the log pretty name, see long description.
	 * @param string     $message The message to log.
	 * @param array      $context An array of values to define the context.
	 *
	 * @see   \Monolog\Logger for the log level constants and names.
	 */
	public function dispatch_log( $level = 'debug', $message = '', array $context = [] ) {
		// Goes from something like `debug` to `100`.
		$level = is_numeric( $level ) ? $level : Logger::toMonologLevel( $level );

		/** @var Logger $logger */
		$logger = $this->container->make( Logger::class );

		$logger->log( $level, $message, $context );
	}

	/**
	 * Makes the action-based logging engine available in the backend.
	 *
	 * @since 4.9.16
	 *
	 * @param array $logging_engines An array of available logging engines.
	 *
	 * @return array The updated array of logging engines.
	 */
	public function add_logging_engine( array $logging_engines = [] ) {
		$logging_engines[ Action_Logger::class ] = new Action_Logger();

		return $logging_engines;
	}
}
