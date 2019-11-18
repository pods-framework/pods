<?php
/**
 * An abstraction layer to handle feature detection queries the plugin components
 * might need.
 *
 * @since 4.7.23
 */

/**
 * Class Tribe__Feature_Detection
 *
 * @since 4.7.23
 */
class Tribe__Feature_Detection {

	/**
	 * The name of the transient storing the support check results.
	 *
	 * @var string
	 */
	public static $transient = 'tribe_feature_detection';

	/**
	 * The name of the option that will be used to indicate a feature detection is running.
	 *
	 * @var string
	 */
	protected $lock_option_name;

	/**
	 * Checks whether async, AJAX-based, background processing is supported or not.
	 *
	 * To avoid making this costly check on each load the result of this check is cached
	 * in the `tribe_feature_detection` transient, under the `supports_async_process` key.
	 *
	 * @since 4.7.23
	 *
	 * @param bool $force Whether to use the cache value, if available, or force the check
	 *                    to be made again.
	 *
	 * @return bool Whether async, AJAX-based, background processing is supported or not.
	 */
	public function supports_async_process( $force = false ) {
		/**
		 * Filters whether async, AJAX-based, processing is supported or not.
		 *
		 * Returning a non `null` value here will make this method bail and
		 * return the filtered value immediately.
		 *
		 * @since 4.7.23
		 *
		 * @param bool $supports_async_process Whether async, AJAX-based, processing is supported or not.
		 * @param bool $force                  Whether the check is forcing the cached value to be refreshed
		 *                                     or not.
		 */
		$supports_async_process = apply_filters( 'tribe_supports_async_process', null, $force );
		if ( null !== $supports_async_process ) {
			return (bool) $supports_async_process;
		}

		$cached = get_transient( self::$transient );

		$this->lock_option_name = 'tribe_feature_support_check_lock';
		if (
			$force
			|| false === $cached
			|| ( is_array( $cached ) && ! isset( $cached['supports_async_process'] ) )
		) {
			if ( $this->is_locked() ) {
				// We're already running this check, bail and return the safe option for the time being.
				return false;
			}

			// Let's avoid race conditions by running two or more checks at the same time.
			$this->lock();

			// Log that we're checking for AJAX-based async process support using the tester.
			tribe( 'logger' )->log( 'Checking for AJAX-based async processing support triggering a test request.', Tribe__Log::DEBUG );

			/*
			 * Build and dispatch the tester: if it works a transient should be set.
			 */
			$tester = new Tribe__Process__Tester();
			tribe( 'logger' )->log( 'Dispatching AJAX-based async processing support test request.', Tribe__Log::DEBUG );
			$tester->dispatch();

			$wait_up_to             = 10;
			$start                  = time();
			$supports_async_process = false;
			$transient_name         = Tribe__Process__Tester::TRANSIENT_NAME;

			while ( time() <= $start + $wait_up_to ) {
				// We want to force a refetch from the database on each check.
				wp_cache_delete( $transient_name, 'transient' );
				$supports_async_process = (bool) get_transient( $transient_name );

				if ( $supports_async_process ) {
					break;
				}
				sleep( $wait_up_to / 5 );
			}

			// Remove it not to spoof future checks.
			delete_transient( $transient_name );

			$this->unlock();

			$cached['supports_async_process'] = $supports_async_process;

			if ( $supports_async_process ) {
				tribe( 'logger' )->log( 'AJAX-based async processing is supported.', Tribe__Log::DEBUG );
			} else {
				tribe( 'logger' )->log( 'AJAX-based async processing is not supported; background processing will rely on WP Cron.', Tribe__Log::DEBUG );
			}

			set_transient( self::$transient, $cached, WEEK_IN_SECONDS );
		}

		return $cached['supports_async_process'];
	}

	/**
	 * Sets the lock option to `1` to indicate a feature detection is running.
	 *
	 * @since 4.8.1
	 */
	protected function lock() {
		update_option( $this->lock_option_name, '1' );
	}

	/**
	 * Deletes the lock option to indicate the current feature detection process is done.
	 *
	 * @since 4.8.1
	 */
	protected function unlock() {
		delete_option( $this->lock_option_name );
	}

	/**
	 * Checks whether a feature detection lock is currently in place or not.
	 *
	 * @since 4.8.1
	 *
	 * @return bool Whether a feature detection lock is currently in place or not.
	 */
	protected function is_locked() {
		$lock_option = get_option( $this->lock_option_name );

		return ! empty( $lock_option );
	}
}
