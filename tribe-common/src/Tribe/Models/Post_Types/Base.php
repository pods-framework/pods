<?php
/**
 * The base, abstract, class modeling a post.
 *
 * @since   4.9.18
 *
 * @package Tribe\Models\Post_Types
 */


namespace Tribe\Models\Post_Types;

use Tribe__Cache as Cache;
use Tribe__Cache_Listener as Cache_Listener;

/**
 * Class Base
 *
 * @since   4.9.18
 *
 * @package Tribe\Models\Post_Types
 */
abstract class Base {
	/**
	 * The post object base for this post type instance.
	 *
	 * @since 4.9.18
	 *
	 * @var \WP_Post
	 */
	protected $post;

	/**
	 * Builds, and returns, a post type model from a given post.
	 *
	 * @since 4.9.18
	 *
	 * @param \WP_Post|int $post The post ID or post object.
	 *
	 * @return Base|Nothing Either the built Post Type model, or a Nothing model if the post does not exist.
	 */
	public static function from_post( $post ) {
		$post = get_post( $post );

		if ( ! $post instanceof \WP_Post ) {
			return new Nothing();
		}

		$instance       = new static;
		$instance->post = $post;

		return $instance;
	}

	/**
	 * Returns the slug that will be prefixed to the cache key for the model.
	 *
	 * @since 4.9.18
	 *
	 * @return string The slug that will be prefixed to the cache key for the model.
	 */
	abstract protected function get_cache_slug();

	/**
	 * Returns the cached model properties for the specified filter, if any.
	 *
	 * @since 4.9.18
	 *
	 * @param string $filter Type of filter to apply, used here as the stored post values might change.
	 *
	 * @return array|false An array of model properties, or `false` if not found.
	 */
	protected function get_cached_properties( $filter ) {
		$cache_slug = $this->get_cache_slug();

		if ( empty( $cache_slug ) ) {
			return false;
		}

		// Cache by post ID and filter.
		$cache_key  = $cache_slug . '_' . $this->post->ID . '_' . $filter;

		return ( new Cache() )->get( $cache_key, Cache_Listener::TRIGGER_SAVE_POST );
	}

	/**
	 * Builds and returns the properties for the model.
	 *
	 * In this method child classes should also implement any caching trigger mechanism, if any.
	 *
	 * @since 4.9.18
	 *
	 * @param string $filter The type of filter to build the properties for.
	 *
	 * @return array An array of built properties.
	 */
	abstract protected function build_properties( $filter );

	/**
	 * Returns an array of the model properties.
	 *
	 * @since 4.9.18
	 *
	 * @param string $filter The type of filter to get the properties for.
	 *
	 * @return array The model properties. This value might be cached.
	 */
	protected function get_properties( $filter ) {
		$cached = $this->get_cached_properties( $filter);

		if ( false !== $cached ) {
			return $cached;
		}

		$props = $this->build_properties( $filter );

		$cache_slug = $this->get_cache_slug();

		/**
		 * Filters the array of properties that will be used to decorate the post object handled by the class.
		 *
		 * @since 4.9.18
		 *
		 * @param array    $props An associative array of all the properties that will be set on the "decorated" post
		 *                        object.
		 * @param \WP_Post $post  The post object handled by the class.
		 */
		$props = apply_filters( "tribe_post_type_{$cache_slug}_properties", $props, $this->post );

		return $props;
	}

	/**
	 * Returns the WP_Post version of this model.
	 *
	 * @since 4.9.18
	 *
	 * @param string $output The required return type. One of OBJECT, ARRAY_A, or ARRAY_N, which correspond to a WP_Post
	 *                       object,an associative array, or a numeric array, respectively.
	 * @param string $filter Type of filter to apply. Accepts 'raw', 'edit', 'db', or 'display' and other formats
	 *                       supported by the specific type implementation.
	 *
	 * @return \WP_Post|array|null The post object version of this post type model or `null` if the post is not valid.
	 */
	public function to_post( $output = OBJECT, $filter = 'raw' ) {
		$properties = $this->get_properties( $filter );

		// Clone the post to avoid side effects.
		$post = clone $this->post;

		// And decorate the clone with the properties.
		foreach ( $properties as $key => $value ) {
			$post->{$key} = $value;
		}

		switch ( $output ) {
			case ARRAY_A:
				return (array) $post;
			case ARRAY_N:
				return array_values( (array) $post );
			case OBJECT:
			default;
				return $post;
		}
	}

	/**
	 * Returns the closure that should be used to cache the post type model when, and if, caching it is required.
	 *
	 * @since 4.9.18
	 *
	 * @param string $filter The kind of filter applied to the model.
	 *
	 * @return callable The closure, or callable, that should be used to cache this model when, and if, required.
	 */
	protected function get_caching_callback( $filter ) {
		$cache_slug = $this->get_cache_slug();

		if ( empty( $cache_slug ) ) {
			return '__return_true';
		}

		$callback = null;

		if ( wp_using_ext_object_cache() ) {
			/*
			 * If any real caching is in place , then define a function to cache this event when, and if, one of the
			 * lazy properties is loaded.
			 * Cache by post ID and filter.
			 */
			$cache_key = $cache_slug . '_' . $this->post->ID . '_' . $filter;
			$cache     = new Cache();
			$callback  = function () use ( $cache, $cache_key, $filter ) {
				$properties = $this->get_properties( $filter );

				/*
				 * Cache without expiration, but only until a post of the types managed by The Events Calendar is
				 * updated or created.
				 */
				$cache->set( $cache_key, $properties, 0, Cache_Listener::TRIGGER_SAVE_POST );
			};
		}

		return $callback;
	}
}
