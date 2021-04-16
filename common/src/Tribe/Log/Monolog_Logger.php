<?php
/**
 * An extension of the base Monolog logger to add our need to replace the instance, and global, loggers.
 *
 * @since   4.9.16
 *
 * @package Tribe\Log
 */

namespace Tribe\Log;

use Monolog\Logger;

/**
 * Class Monolog_Logger
 *
 * @since   4.9.16
 *
 * @package Tribe\Log
 */
class Monolog_Logger extends Logger {
	/**
	 * @since 4.9.16
	 */
	const DEFAULT_CHANNEL = 'default';

	/**
	 * Resets the global channel to the default one.
	 *
	 * @since 4.9.16
	 *
	 * @return bool Whether the channel reset
	 */
	public function reset_global_channel() {
		return $this->set_global_channel( static::DEFAULT_CHANNEL );
	}

	/**
	 * Clones this logger and replaces it in the `tribe` container.
	 *
	 * @since 4.9.16
	 *
	 * @param string $channel The new logger name, also referred to as "channel" (hence the method name).
	 *
	 * @return bool Whether the channel change was successful or not.
	 */
	public function set_global_channel( $channel ) {
		$new = $this->withName( $channel );
		tribe_register( Logger::class, $new );
		tribe_register( 'monolog', $new );

		return $channel === tribe( 'monolog' )->getName();
	}
}