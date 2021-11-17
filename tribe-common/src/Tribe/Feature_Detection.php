<?php
/**
 * An abstraction layer to handle feature detection queries the plugin components
 * might need.
 *
 * @since 4.7.23
 */

use Tribe__Utils__Array as Arr;

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
	 * A set of example byte sizes of result sets.
	 *
	 * @since 4.10.2
	 *
	 * @var array
	 */
	public static $example_size = [
		'post_result' => 6000,
	];

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

	/**
	 * Returns the value of the `max_allowed_packet`  MYSQL variable, if set, or a default value.
	 *
	 * @since 4.10.2
	 *
	 * @return int The byte size of the `max_allowed_packet`  MYSQL variable.
	 */
	public function get_mysql_max_packet_size() {
		/**
		 * Filters the value of the `max_allowed_packet` variable before it's read from the database.
		 *
		 * If the value returned from this filter is not `null`, then it will be assumed to be the value.
		 *
		 * @since 4.10.2
		 *
		 * @param int $mysql_max_packet_size The value of the `max_allowed_packet` variable, initially `null`.
		 */
		$mysql_max_packet_size = apply_filters( 'tribe_max_allowed_packet_size', null );

		if ( null !== $mysql_max_packet_size ) {
			return absint( $mysql_max_packet_size );
		}

		/** @var Tribe__Cache $cache */
		$cache = tribe( 'cache' );

		$cached = $cache->get( 'max_allowed_packet' );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;
		$mysql_max_packet_size = $wpdb->get_var( "SHOW VARIABLES LIKE 'max_allowed_packet'", 1 );
		// At min set it to 2 MBs.
		$mysql_max_packet_size = absint( max( absint( $mysql_max_packet_size ), 2097152 ) );

		$cache->set( 'max_allowed_packet', $mysql_max_packet_size, WEEK_IN_SECONDS );

		return $mysql_max_packet_size;
	}

	/**
	 * Returns the suggested SQL LIMIT value, based on the `max_allowed_packet` size and example string length.
	 *
	 * This is useful to size "reasonable" LIMITs when dealing with either very long queries or potentially long
	 * result sets.
	 *
	 * @since 4.10.2
	 *
	 * @param string $example_string The example string.
	 *
	 * @return int The suggested LIMIT value.
	 */
	public function mysql_limit_for_string( $example_string ) {
		$byte_size = function_exists( 'mb_strlen' )
			? mb_strlen( $example_string )
			: strlen( $example_string );

		return $this->mysql_limit_for_size( $byte_size );
	}

	/**
	 * Returns the SQL LIMIT for a byte size, in relation to the `max_allowed_packet` value.
	 *
	 * @since 4.10.2
	 *
	 * @param int $byte_size The byte size to check.
	 *
	 * @return int The SQL LIMIT value.
	 */
	public function mysql_limit_for_size( $byte_size ) {
		return absint( floor( $this->get_mysql_max_packet_size() / $byte_size ) * 0.8 );
	}

	/**
	 * Provides the SQL LIMIT value, in relation to the `max_allowed_packet` value, for a pre-existing example.
	 *
	 * Defaults to the complete post result example string if the example is not found.
	 *
	 * @since 4.10.2
	 *
	 * @param string $example The name of the example to return. See the `Tribe__Feature_Detection::$example_sizes`
	 *                        prop for the available examples. Defaults to the `post_result` one.
	 *
	 * @return int The SQL LIMIT value for the example.
	 */
	public function mysql_limit_for_example( $example ) {
		$example_size = Arr::get( static::$example_size, $example, static::$example_size['post_result'] );

		return $this->mysql_limit_for_size( $example_size );
	}
}
