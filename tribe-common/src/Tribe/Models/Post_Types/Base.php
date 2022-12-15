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
	 * The key used to store pre-serializes properties data in the cache.
	 *
	 * @since 5.0.3
	 */
	public const PRE_SERIALIZED_PROPERTY = '_tec_pre_serialized';

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
		$cache_key = $this->get_properties_cache_key( $filter );

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
	 * @param bool $force Whether to force a rebuild of the properties or not.
	 *
	 * @return array The model properties. This value might be cached.
	 */
	protected function get_properties( $filter, bool $force = false ) {
		$cached = ! $force ? $this->get_cached_properties( $filter ) : false;

		if ( false !== $cached ) {
			// Un-serialize the pre-serialized properties now, when classes will be most likely defined.
			$pre_serialized_properties = $cached[ self::PRE_SERIALIZED_PROPERTY ] ?? [];

			foreach ( $pre_serialized_properties as $key => $value ) {
				try {
					$cached[ $key ] = unserialize( $value, [ 'allowed_classes' => true ] );
				} catch ( \Throwable $t ) {
					/*
					 * Deal with the case where plugin A, B, C were active when the cache was built,
					 * but B and C are now inactive. In this case the un-serialization will fail for
					 * any pre-serialized value using classes from B and C: here we gracefully ignore
					 * each one of those.
					 */
				}
			}

			try {
				// Allow models to apply further unserialization operations.
				$cached = $this->scalar_unserialize_properties( $cached );

				/**
				 * Allows filtering the properties of the post type model after they have been unserialized from the
				 * cache..
				 *
				 * @since 5.0.3
				 *
				 * @param array<string,mixed> $cached The key-value map of the properties of the post type model.
				 * @param \WP_Post            $post   The post object of the post type model.
				 */
				$cached = apply_filters( "tec_model_{$this->get_cache_slug()}_read_cache_properties", $cached, $this->post );

				// Remove the pre-serialized properties from the cached properties.
				unset( $cached[ self::PRE_SERIALIZED_PROPERTY ] );

				return $cached;
			} catch ( \Throwable $t ) {
				// Rebuid the properties from cache failed, move on.
			}
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
	 * @since 5.0.3 Added the `$force` parameter.
	 *
	 * @param string $output The required return type. One of OBJECT, ARRAY_A, or ARRAY_N, which correspond to a WP_Post
	 *                       object,an associative array, or a numeric array, respectively.
	 * @param string $filter Type of filter to apply. Accepts 'raw', 'edit', 'db', or 'display' and other formats
	 *                       supported by the specific type implementation.
	 * @param bool   $force  Whether to force the post to be reloaded from the database or not.
	 *
	 * @return \WP_Post|array|null The post object version of this post type model or `null` if the post is not valid.
	 */
	public function to_post( $output = OBJECT, $filter = 'raw', bool $force = false ) {
		$properties = $this->get_properties( $filter, $force );

		switch ( $output ) {
			case ARRAY_A:
				return array_merge( (array) $this->post, $properties );
			case ARRAY_N:
				return array_values( array_merge( (array) $this->post, $properties ) );
			case OBJECT:
			default:
				// Clone the post to avoid side effects.
				$clone = clone $this->post;
				// And decorate the clone with the properties.
				foreach ( $properties as $key => $value ) {
					$clone->{$key} = $value;
				}

				return $clone;
		}
	}

	/**
	 * Returns the closure that should be used to cache the post type model when, and if, caching it is required.
	 *
	 * @since 4.9.18
	 *
	 * @param string $filter The kind of filter applied to the model.
	 * @return callable The closure, or callable, that should be used to cache this model when, and if, required.
	 */
	protected function get_caching_callback( $filter ) {
		$cache_slug = $this->get_cache_slug();

		if ( empty( $cache_slug ) ) {
			return '__return_true';
		}

		$callback = null;

		if ( wp_using_ext_object_cache() ) {
			$callback = $this->get_object_cache_callback( $filter );
		}

		return $callback;
	}

	/**
	 * Further scalarizes the properties of the post type model.
	 *
	 * Extending classes should implement this method to handle
	 * specific scalarization of the model properties.
	 *
	 * @since 5.0.3
	 *
	 * @param array<string,mixed> $properties A key-value map of the
	 *                                        properties of the post type model.
	 *
	 * @return array<string,mixed> The scalarized properties key-value map.
	 */
	protected function scalar_serialize_properties( array $properties ): array {
		return $properties;
	}

	/**
	 * Further un-scalarizes the properties of the post type model.
	 *
	 * Extending classes should implement this method to handle
	 * specific un-scalarization of the model properties.
	 *
	 * @since 5.0.3
	 *
	 * @param array<string,mixed> $properties A key-value map of the
	 *                                        properties of the post type model.
	 *
	 * @return array<string,mixed> The un-scalarized properties key-value map.
	 */
	protected function scalar_unserialize_properties( array $properties ): array {
		return $properties;
	}

	/**
	 * Returns the callback function that should be used to cache the model using object caching.
	 *
	 * If any real caching is in place , then define a function to cache this event when, and if, one of the
	 * lazy properties is loaded.
	 * Cache by post ID and filter.
	 * Cache could be pre-fetched: in that case only built-in PHP classes will be supported: for this reason
	 * object properties will be "scalarized".
	 *
	 * @since 5.0.3
	 *
	 * @param string $cache_slug The cache slug of the post type model.
	 * @param string $filter     The filter to cache the model for.
	 *
	 * @return \Closure The callback function that should be used to cache the model using object caching.
	 */
	protected function get_object_cache_callback( string $filter ): \Closure {
		$cache_key = $this->get_properties_cache_key( $filter );
		$cache = new Cache();

		return function () use ( $cache, $cache_key, $filter ) {
			$properties = $this->get_properties( $filter );
			$pre_serialized_properties = [];

			try {
				// Pre-serialize each object property and store it in a separate cache entry.
				foreach ( $properties as $key => &$value ) {
					try {
						if ( is_object( $value ) && ! $value instanceof \stdClass ) {
							// We might end up pre-serializing other built-in objects here, but let's play it safe.
							$pre_serialized_properties[ $key ] = serialize( $value );
						}
					} catch ( \Throwable $t ) {
						// Null the property: an object that cannot be serialized correctly is not cacheable.
						$value = null;
					}
				}
				unset( $value );

				// Remove the pre-serialized properties from the main cache entry.
				$properties = array_diff_key( $properties, $pre_serialized_properties );

				// Allow models to customize the pre-serialization further.
				$properties = $this->scalar_serialize_properties( $properties );

				// Add the pre-serialized properties to the main cache entry.
				if ( count( $pre_serialized_properties ) ) {
					$properties[ self::PRE_SERIALIZED_PROPERTY ] = $pre_serialized_properties;
				}

				/**
				 * Allows filtering the properties of the post type model before they are cached.
				 *
				 * @since 5.0.3
				 *
				 * @param array<string,mixed> $properties The key-value map of the properties of the post type model.
				 * @param \WP_Post            $post       The post object of the post type model.
				 */
				$properties = apply_filters( "tec_model_{$this->get_cache_slug()}_put_cache_properties", $properties, $this->post );
			} catch ( \Throwable $t ) {
				// If we can't serialize the properties, bail.
				return;
			}

			/*
			 * Cache without expiration, but only until a post of the types managed by The Events Calendar is
			 * updated or created.
			 */
			$cache->set( $cache_key, $properties, 0, Cache_Listener::TRIGGER_SAVE_POST );
		};
	}

	/**
	 * Returns the cache key to be used to cache the model properties.
	 *
	 * @since 5.0.3
	 *
	 * @param string $filter The filter to cache the model for.
	 *
	 * @return string The cache key to be used to cache the model properties.
	 */
	public function get_properties_cache_key( string $filter ): string {
		return $this->get_cache_slug() . '_' . $this->post->ID . '_' . $filter;
	}

	/**
	 * Commits the model properties to cache immediately.
	 *
	 * @since 5.0.3
	 *
	 * @param string $filter The filter to cache the model properties for.
	 *
	 * @return void The model properties are cached immediately.
	 */
	public function commit_to_cache( string $filter = 'raw' ): void {
		$caching_callback = $this->get_object_cache_callback( $filter );
		$caching_callback();
	}
}
