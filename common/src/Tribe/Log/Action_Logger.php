<?php
/**
 * Hooks the `tribe_log` action based logger under the existing one for back-compatibility.
 *
 * @since   4.9.16
 *
 * @package Tribe\Log
 */

namespace Tribe\Log;

use Monolog\Logger;
use Tribe__Log;

/**
 * Class Action_Logger
 *
 * @since   4.9.16
 *
 * @package Tribe\Log
 */
class Action_Logger implements \Tribe__Log__Logger {

	/**
	 * {@inheritDoc}
	 *
	 * @since 4.9.16
	 */
	public function is_available() {
		return true;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 4.9.16
	 */
	public function get_name() {
		return __( 'Action-based Logger', 'tribe-common' );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 4.9.16
	 */
	public function log( $entry, $type = Tribe__Log::DEBUG, $src = '' ) {
		$message = empty( $src ) ? $entry : $src . ': ' . $entry;

		do_action( 'tribe_log', $this->translate_log_level( $type ), $message );
	}

	/**
	 * Translates the log types used by `Tribe__Log` to those used by Monolog.
	 *
	 * @since 4.9.16
	 *
	 * @param string $type The `Tribe__Log` log type.
	 *
	 * @return int The Monolog equivalent of the current level.
	 */
	protected function translate_log_level( $type ) {
		switch ( $type ) {
			case Tribe__Log::DEBUG:
				return Logger::DEBUG;
			case Tribe__Log::ERROR:
				return Logger::ERROR;
			case Tribe__Log::WARNING:
				return Logger::WARNING;
			case Tribe__Log::SUCCESS:
			default:
				return Logger::INFO;
		}
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 4.9.16
	 */
	public function retrieve( $limit = 0, array $args = array() ) {
		return [
			[
				'message' => __(
					'The Action Logger will dispatch any logging message using the "tribe_log" action writing, by ' .
					'default, to the PHP error log.',
					'tribe-common' )
			],
		];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 4.9.16
	 */
	public function list_available_logs() {
		return [];
	}

	/**
	 * Changes the Monolog logger channel to the specified one.
	 *
	 * @since 4.9.16
	 *
	 * @param string $log_identifier The channel to switch to.
	 * @param bool   $create         Unused by this class.
	 *
	 * @return bool The exit status of the channel change.
	 *
	 * @uses \Tribe\Log\Monolog_Logger::set_channel().
	 */
	public function use_log( $log_identifier, $create = false ) {
		return tribe( 'monolog' )->set_global_channel( $log_identifier );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 4.9.16
	 */
	public function cleanup() {
		return true;
	}
}
