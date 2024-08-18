<?php

namespace Pods\Integrations\Query_Monitor\Collectors;

use QM_DataCollector;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Debug
 *
 * @since TBD
 */
class Debug extends QM_DataCollector {

	/**
	 * {@inheritDoc}
	 */
	public $id = 'pods-debug';

	/**
	 * The data to be tracked.
	 *
	 * @since TBD
	 *
	 * @var array
	 */
	protected static $custom_data = [];

	/**
	 * {@inheritDoc}
	 */
	public function process() {
		$this->data['debug_data'] = self::get_debug_data();
	}

	/**
	 * Track debug data.
	 *
	 * @since TBD
	 *
	 * @param mixed  $debug    The debug data to track.
	 * @param string $context  The context where the debug came from.
	 * @param string $function The function/method name where the debug was called.
	 * @param int    $line     The line number where the debug was called.
	 */
	public static function track_debug_data( $debug, string $context, string $function, int $line ): void {
		self::$custom_data[] = compact( 'debug', 'context', 'function', 'line' );
	}

	/**
	 * Get all of the debug data tracked.
	 *
	 * @since TBD
	 *
	 * @return array All of the debug data tracked.
	 */
	public static function get_debug_data(): array {
		return self::$custom_data;
	}

	/**
	 * Reset all of the debug data tracked.
	 *
	 * @since TBD
	 */
	public static function reset_debug_data(): void {
		self::$custom_data = [];
	}
}
