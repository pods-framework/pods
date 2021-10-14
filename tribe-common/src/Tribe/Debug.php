<?php

class Tribe__Debug {
	/**
	 * constructor
	 */
	public function __construct() {
		add_action( 'tribe_debug', [ __CLASS__, 'render' ], 10, 2 );
	}

	/**
	 * Tribe debug function. usage: self::debug( 'Message', $data, 'log' );
	 *
	 * @param string      $title  Message to display in log
	 * @param string|bool $data   Optional data to display
	 * @param string      $format Optional format (log|warning|error|notice)
	 *
	 * @return void
	 */
	public static function debug( $title, $data = false, $format = 'log' ) {
		do_action( 'tribe_debug', $title, $data, $format );
	}

	/**
	 * Render the debug logging to the php error log. This can be over-ridden by removing the filter.
	 *
	 * @param string      $title  - message to display in log
	 * @param string|bool $data   - optional data to display
	 * @param string      $format - optional format (log|warning|error|notice)
	 *
	 * @return void
	 */
	public static function render( $title, $data = false, $format = 'log' ) {
		$format = ucfirst( $format );
		if ( Tribe__Settings_Manager::instance()->get_option( 'debugEvents' ) ) {
			$plugin = basename( dirname( Tribe__Main::instance()->plugin_path ) );
			error_log( "$plugin/common -  $format: $title" );
			if ( $data && $data != '' ) {
				error_log( "$plugin/common - $format: " . print_r( $data, true ) );
			}
		}
	}

	/**
	 * Static Singleton Factory Method
	 *
	 * @return Tribe__Debug
	 */
	public static function instance() {
		static $instance;

		if ( ! $instance ) {
			$class_name = __CLASS__;
			$instance = new $class_name;
		}

		return $instance;
	}
}
