<?php

namespace Pods\Integrations\Query_Monitor\Collectors;

use QM_Backtrace;
use QM_DataCollector;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Debug
 *
 * @since 3.2.7
 */
class Debug extends QM_DataCollector {

	public $id = 'pods-debug';

	/**
	 * The data to be tracked.
	 *
	 * @since 3.2.7
	 *
	 * @var array
	 */
	protected static $custom_data = [];

	public function process() {
		$this->data['debug_data'] = self::get_debug_data();
	}

	/**
	 * Track debug data.
	 *
	 * @since 3.2.7
	 *
	 * @param mixed  $debug    The debug data to track.
	 * @param string $context  The context where the debug came from.
	 * @param string $function The function/method name where the debug was called.
	 * @param int    $line     The line number where the debug was called.
	 */
	public static function track_debug_data( $debug, string $context, string $function, int $line ): void {
		$trace = new QM_Backtrace( [
			'ignore_hook' => [
				current_filter() => true,
			],
			'ignore_func' => [
				'pods_debug_log_data' => true,
			],
		] );

		self::$custom_data[] = compact( 'debug', 'context', 'function', 'line', 'trace' );
	}

	/**
	 * Get all of the debug data tracked.
	 *
	 * @since 3.2.7
	 *
	 * @return array All of the debug data tracked.
	 */
	public static function get_debug_data(): array {
		return self::$custom_data;
	}

	/**
	 * Reset all of the debug data tracked.
	 *
	 * @since 3.2.7
	 */
	public static function reset_debug_data(): void {
		self::$custom_data = [];
	}
}
