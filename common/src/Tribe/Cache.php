<?php

/**
 * Manage setting and expiring cached data
 *
 * Select actions can be used to force cached
 * data to expire. Implemented so far:
 *  - save_post
 *
 * When used in its ArrayAccess API the cache will provide non persistent storage.
 */
class Tribe__Cache implements ArrayAccess {
	const NO_EXPIRATION  = 0;
	const NON_PERSISTENT = - 1;

	/**
	 * @var array
	 */
	protected $non_persistent_keys = array();

	public static function setup() {
		wp_cache_add_non_persistent_groups( array( 'tribe-events-non-persistent' ) );
	}

	/**
	 * @param string $id
	 * @param mixed  $value
	 * @param int    $expiration
	 * @param string $expiration_trigger
	 *
	 * @return bool
	 */
	public function set( $id, $value, $expiration = 0, $expiration_trigger = '' ) {
		$key = $this->get_id( $id, $expiration_trigger );

		/**
		 * Filters the expiration for cache objects to provide the ability
		 * to make non-persistent objects be treated as persistent.
		 *
		 * @param int    $expiration         Cache expiration time.
		 * @param string $id                 Cache ID.
		 * @param mixed  $value              Cache value.
		 * @param string $expiration_trigger Action that triggers automatic expiration.
		 * @param string $key                Unique cache key based on Cache ID and expiration trigger last run time.
		 *
		 * @since 4.8
		 */
		$expiration = apply_filters( 'tribe_cache_expiration', $expiration, $id, $value, $expiration_trigger, $key );

		if ( self::NON_PERSISTENT === $expiration ) {
			$group      = 'tribe-events-non-persistent';
			$expiration = 1;

			// Add so we know what group to use in the future.
			$this->non_persistent_keys[] = $key;
		} else {
			$group = 'tribe-events';
		}

		return wp_cache_set( $key, $value, $group, $expiration );
	}

	/**
	 * @param        $id
	 * @param        $value
	 * @param int    $expiration
	 * @param string $expiration_trigger
	 *
	 * @return bool
	 */
	public function set_transient( $id, $value, $expiration = 0, $expiration_trigger = '' ) {
		return set_transient( $this->get_id( $id, $expiration_trigger ), $value, $expiration );
	}

	/**
	 * Get cached data. Optionally set data if not previously set.
	 *
	 * Note: When a default value or callback is specified, this value gets set in the cache.
	 *
	 * @param string $id                 The key for the cached value.
	 * @param string $expiration_trigger Optional. Hook to trigger cache invalidation.
	 * @param mixed  $default            Optional. A default value or callback that returns a default value.
	 * @param int    $expiration         Optional. When the default value expires, if it gets set.
	 * @param mixed  $args               Optional. Args passed to callback.
	 *
	 * @return mixed
	 */
	public function get( $id, $expiration_trigger = '', $default = false, $expiration = 0, $args = array() ) {
		$group = in_array( $id, $this->non_persistent_keys ) ? 'tribe-events-non-persistent' : 'tribe-events';
		$value = wp_cache_get( $this->get_id( $id, $expiration_trigger ), $group );

		// Value found.
		if ( false !== $value ) {
			return $value;
		}

		if ( is_callable( $default ) ) {
			// A callback has been specified.
			$value = call_user_func_array( $default, $args );
		} else {
			// Default is a value.
			$value = $default;
		}

		// No need to set a cache value to false since non-existent values return false.
		if ( false !== $value ) {
			$this->set( $id, $value, $expiration, $expiration_trigger );
		}

		return $value;
	}

	/**
	 * @param string $id
	 * @param string $expiration_trigger
	 *
	 * @return mixed
	 */
	public function get_transient( $id, $expiration_trigger = '' ) {
		return get_transient( $this->get_id( $id, $expiration_trigger ) );
	}

	/**
	 * @param string $id
	 * @param string $expiration_trigger
	 *
	 * @return bool
	 */
	public function delete( $id, $expiration_trigger = '' ) {
		return wp_cache_delete( $this->get_id( $id, $expiration_trigger ), 'tribe-events' );
	}

	/**
	 * @param string $id
	 * @param string $expiration_trigger
	 *
	 * @return bool
	 */
	public function delete_transient( $id, $expiration_trigger = '' ) {
		return delete_transient( $this->get_id( $id, $expiration_trigger ) );
	}

	/**
	 * @param string $key
	 * @param string $expiration_trigger
	 *
	 * @return string
	 */
	public function get_id( $key, $expiration_trigger = '' ) {
		$last = empty( $expiration_trigger ) ? '' : $this->get_last_occurrence( $expiration_trigger );
		$id   = $key . $last;
		if ( strlen( $id ) > 40 ) {
			$id = md5( $id );
		}

		return $id;
	}

	/**
	 * Returns the time of an action last occurrence.
	 *
	 * @param string $action The action to return the time for.
	 *
	 * @since 4.9.14 Changed the return value type from `int` to `float`.
	 *
	 * @return float The time (microtime) an action last occurred, or the current microtime if it never occurred.
	 */
	public function get_last_occurrence( $action ) {
		return (float) get_option( 'tribe_last_' . $action, microtime( true ) );
	}

	/**
	 * Sets the time (microtime) for an action last occurrence.
	 *
	 * @since 4.9.14 Changed the type of the time stored from an `int` to a `float`.
	 *
	 * @param string $action The action to record the last occurrence of.
	 * @param int    $timestamp The timestamp to assign to the action last occurrence or the current time (microtime).
	 */
	public function set_last_occurrence( $action, $timestamp = 0 ) {
		if ( empty( $timestamp ) ) {
			$timestamp = microtime( true );
		}
		update_option( 'tribe_last_' . $action, (float) $timestamp );
	}

	/**
	 * Builds a key from an array of components and an optional prefix.
	 *
	 * @param mixed  $components Either a single component of the key or an array of key components.
	 * @param string $prefix
	 * @param bool   $sort Whether component arrays should be sorted or not to generate the key; defaults to `true`.
	 *
	 * @return string The resulting key.
	 */
	public function make_key( $components, $prefix = '', $sort = true ) {
		$key = '';
		$components = is_array( $components ) ? $components : array( $components );
		foreach ( $components as $component ) {
			if ( $sort && is_array( $component ) ) {
				$is_associative = count( array_filter( array_keys( $component ), 'is_numeric' ) ) < count( array_keys( $component ) );
				if ( $is_associative ) {
					ksort( $component );
				} else {
					sort( $component );
				}
			}
			$key .= maybe_serialize( $component );
		}

		return $this->get_id( $prefix . md5( $key ) );
	}

	/**
	 * Whether a offset exists
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
	 * @param mixed $offset <p>
	 *                      An offset to check for.
	 *                      </p>
	 * @return boolean true on success or false on failure.
	 *                      </p>
	 *                      <p>
	 *                      The return value will be casted to boolean if non-boolean was returned.
	 * @since 5.0.0
	 */
	public function offsetExists( $offset ) {
		return in_array( $offset, $this->non_persistent_keys );
	}

	/**
	 * Offset to retrieve
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetget.php
	 * @param mixed $offset <p>
	 *                      The offset to retrieve.
	 *                      </p>
	 * @return mixed Can return all value types.
	 * @since 5.0.0
	 */
	public function offsetGet( $offset ) {
		return $this->get( $offset );
	}

	/**
	 * Offset to set
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetset.php
	 * @param mixed $offset <p>
	 *                      The offset to assign the value to.
	 *                      </p>
	 * @param mixed $value  <p>
	 *                      The value to set.
	 *                      </p>
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetSet( $offset, $value ) {
		$this->set( $offset, $value, self::NON_PERSISTENT );
	}

	/**
	 * Offset to unset
	 *
	 * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
	 * @param mixed $offset <p>
	 *                      The offset to unset.
	 *                      </p>
	 * @return void
	 * @since 5.0.0
	 */
	public function offsetUnset( $offset ) {
		$this->delete( $offset );
	}
}

