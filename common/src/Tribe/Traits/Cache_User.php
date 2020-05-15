<?php
/**
 * Provides methods to easily read and write to the Tribe Cache.
 *
 * Objects using this trait should define array request cache properties and, at the very least, dump the cache on
 * `__destruct`.
 *
 * @since   4.9.11
 *
 * @example
 * ```php
 * <?php
 * class Some_Class {
 *         use \Tribe\Cache_User;
 *
 *          protected $computation_cache = [];
 *
 *          public function __construct(){
 *              add_action( 'shutdown', [ $this, 'dump_cache' ] );
 *          }
 *
 *          public function calculate_something( $key ){
 *              $this->warmup_cache( 'computation', WEEK_IN_SECONDS, 'save_post' );
 *              if( isset( $this->computation_cache[$key] ) ){
 *                  return $this->computation_cache[$key];
 *              }
 *
 *              $computated = 23;
 *
 *              $this->computation_cache[$key] = $computated;
 *
 *              return $computated;
 *          }
 * }
 * ```
 *
 * @package Tribe
 */

namespace Tribe\Traits;

/**
 * Class Cache_User
 *
 * @since   4.9.11
 *
 * @package Tribe
 */
trait Cache_User {


	/**
	 * An array of caches and data for each key.
	 *
	 * @var array
	 */
	protected $caches = [];

	/**
	 * Dumps the temporary cache to the persistent one.
	 */
	public function dump_cache() {
		foreach ( $this->caches as $key => $cache ) {

			list( $cache, $prefix, $expiration, $expiration_trigger ) = array_values( $this->caches[ $key ] );

			if ( isset( $this->{$key . '_cache'} ) ) {
				/** @var \Tribe__Cache $cache */
				$cache->set( $prefix . $key,
					$this->{$key . '_cache'},
					$expiration,
					$expiration_trigger
				);
			}
		}
	}

	/**
	 * Warms up one of the caches used by the class, if not warmed up already.
	 *
	 * @since 4.9.11
	 *
	 * @param string $key                The key of the cache to warm up.
	 * @param int    $expiration         The expiration, in seconds, to set on the cache.
	 * @param string $expiration_trigger The expiration trigger to set on the cache; this should be one of those
	 *                                   supported by the `Tribe__Cache_Listener` class.
	 *
	 * @see \Tribe__Cache_Listener::add_hooks()
	 */
	protected function warmup_cache( $key, $expiration = 0, $expiration_trigger = '' ) {
		if ( ! isset( $this->caches[ $key ] ) ) {
			$this_class = get_class( $this );

			if ( ! property_exists( $this, $key . '_cache' ) ) {
				throw new \BadMethodCallException(
					sprintf(
						'The %s class should explicitly define a "%s" property to use the %s trait.',
						$this_class,
						$key . '_cache',
						__TRAIT__
					)
				);
			}

			$this->caches[ $key ] = [
				'cache_object'           => tribe( 'cache' ),
				'prefix'             => $this_class,
				'expiration'         => $expiration,
				'expiration_trigger' => $expiration_trigger,
			];
		}

		list( $cache, $prefix, $expiration, $expiration_trigger ) = array_values( $this->caches[ $key ] );

		if ( null === $this->{$key . '_cache'} ) {
			/** @var \Tribe__Cache $cache */
			$this->{$key . '_cache'} = $cache->get(
				$prefix . $key,
				$expiration_trigger,
				[],
				$expiration
			);
			if ( false === $this->{$key . '_cache'} ) {
				$this->{$key . '_cache'} = [];
			}
		}
	}
}
