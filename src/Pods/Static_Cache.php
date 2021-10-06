<?php

namespace Pods;

/**
 * Static cache class used for storing on-page cached vars but not storing them into the object cache
 * with support for multisite. Each site has their own cache.
 *
 * @since 2.8
 */
class Static_Cache {

	/**
	 * The cache array.
	 *
	 * @since 2.8
	 *
	 * @var array
	 */
	protected static $cache = [];

	/**
	 * Get the cache value from the cache.
	 *
	 * @since 2.8
	 *
	 * @param string $key   The cache key.
	 * @param string $group The cache group.
	 *
	 * @return mixed|false The cache value from the cache.
	 */
	public function get( $key, $group = 'global' ) {
		$blog_id = get_current_blog_id();

		if ( ! isset( self::$cache[ $blog_id ][ $group ][ $key ] ) ) {
			return false;
		}

		return self::$cache[ $blog_id ][ $group ][ $key ];
	}

	/**
	 * Set the cache value in the cache.
	 *
	 * @since 2.8
	 *
	 * @param string $key   The cache key.
	 * @param mixed  $value The cache value.
	 * @param string $group The cache group.
	 */
	public function set( $key, $value, $group = 'global' ) {
		$blog_id = get_current_blog_id();

		if ( ! isset( self::$cache[ $blog_id ] ) ) {
			self::$cache[ $blog_id ] = [];
		}

		if ( ! isset( self::$cache[ $blog_id ][ $group ] ) ) {
			self::$cache[ $blog_id ][ $group ] = [];
		}

		self::$cache[ $blog_id ][ $group ][ $key ] = $value;
	}

	/**
	 * Delete the cache value from the cache.
	 *
	 * @since 2.8
	 *
	 * @param string $key   The cache key.
	 * @param string $group The cache group.
	 */
	public function delete( $key, $group = 'global' ) {
		$blog_id = get_current_blog_id();

		if ( ! isset( self::$cache[ $blog_id ][ $group ][ $key ] ) ) {
			return false;
		}

		unset( self::$cache[ $blog_id ][ $group ][ $key ] );

		return true;
	}

	/**
	 * Flush the cache.
	 *
	 * @since 2.8
	 *
	 * @param string $group The cache group.
	 */
	public function flush( $group = null ) {
		$blog_id = get_current_blog_id();

		if ( $group ) {
			if ( ! isset( self::$cache[ $blog_id ] ) ) {
				self::$cache[ $blog_id ] = [];
			}

			self::$cache[ $blog_id ][ $group ] = [];
		} else {
			self::$cache[ $blog_id ] = [];
		}
	}

}
