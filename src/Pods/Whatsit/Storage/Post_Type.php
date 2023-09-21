<?php

namespace Pods\Whatsit\Storage;

use Pods\Whatsit;
use Pods\Whatsit\Store;
use WP_Post;
use WP_Query;

/**
 * Post_Type class.
 *
 * @since 2.8.0
 */
class Post_Type extends Collection {

	/**
	 * {@inheritdoc}
	 */
	protected static $type = 'post_type';

	/**
	 * @var array
	 */
	protected $primary_args = [
		'object_type'         => 'object_type',
		'object_storage_type' => 'object_storage_type',
		'ID'                  => 'id',
		'post_name'           => 'name',
		'post_title'          => 'label',
		'post_content'        => 'description',
		'post_parent'         => 'parent',
		'menu_order'          => 'weight',
	];

	/**
	 * @var array
	 */
	protected $secondary_args = [
		'type',
		'object',
	];

	/**
	 * {@inheritdoc}
	 */
	public function get_label() {
		return __( 'DB', 'pods' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get( array $args = [] ) {
		// Object type is required.
		if ( empty( $args['object_type'] ) ) {
			return null;
		}

		if ( ! empty( $args['id'] ) ) {
			return $this->to_object( $args['id'] );
		}

		if ( ! empty( $args['post'] ) ) {
			return $this->to_object( $args['post'] );
		}

		if ( ! empty( $args['name'] ) ) {
			$find_args = [
				'object_type' => $args['object_type'],
				'name'        => $args['name'],
				'limit'       => 1,
			];

			$objects = $this->find( $find_args );

			if ( $objects ) {
				return reset( $objects );
			}
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_by_identifier( $identifier, $parent = null ) {
		if ( $identifier instanceof Whatsit ) {
			return $identifier;
		}

		if ( is_int( $identifier ) ) {
			return $this->to_object( $identifier );
		}

		return parent::get_by_identifier( $identifier, $parent );
	}

	/**
	 * Maybe get the post object by ID if the $args allow for it.
	 *
	 * @param array $post_args Arguments to use.
	 *
	 * @return WP_Post|false|null
	 */
	private function maybe_get_by_id( array $post_args ) {
		// Only get by ID if we limit to one.
		if ( ! isset( $post_args['posts_per_page'] ) || 1 !== $post_args['posts_per_page'] ) {
			return null;
		}

		// Only get by ID if we have one post__in.
		if ( ! isset( $post_args['post__in'] ) || 1 !== count( $post_args['post__in'] ) ) {
			return null;
		}

		// Only get by ID if we are not filtering by meta / terms.
		if ( ! empty( $post_args['meta_query'] ) || ! empty( $post_args['tax_query'] ) ) {
			return null;
		}

		$first_id = (int) reset( $post_args['post__in'] );

		// Check if we have a valid ID (1+).
		if ( ! $first_id ) {
			return false;
		}

		$post = get_post( $first_id );

		if ( ! $post instanceof WP_Post ) {
			return false;
		}

		// Manually check other criteria.

		// Maybe filter by post_type.
		if (
			! isset( $post_args['post_type'] )
			|| (
				'any' !== $post_args['post_type']
				&& ! in_array( $post->post_type, (array) $post_args['post_type'], true )
			)
		) {
			return false;
		}

		// Maybe filter by post_status.
		if (
			! isset( $post_args['post_status'] )
			|| (
				'any' !== $post_args['post_status']
				&& ! in_array( $post->post_status, (array) $post_args['post_status'], true )
			)
		) {
			return false;
		}

		return $post;
	}

	/**
	 * {@inheritdoc}
	 */
	public function find( array $args = [] ) {
		// Object type AND parent/ID is required.
		if (
			empty( $args['object_type'] )
			&& empty( $args['id'] )
			&& empty( $args['parent'] )
		) {
			return [];
		}

		if ( ! isset( $args['bypass_cache'] ) ) {
			$api_cache = pods_api_cache();

			if ( ! $api_cache ) {
				$args['bypass_cache'] = true;
			}
		}

		if ( ! isset( $args['bypass_post_type_find'] ) ) {
			$args['bypass_post_type_find'] = false;
		}

		$fallback_mode = $this->fallback_mode;

		if ( isset( $args['fallback_mode'] ) ) {
			$fallback_mode = (boolean) $args['fallback_mode'];
		}

		$meta_query = [];

		if ( ! empty( $args['meta_query'] ) ) {
			$meta_query = (array) $args['meta_query'];
		}

		/**
		 * Filter the maximum number of posts to get for post type storage.
		 *
		 * @since 2.8.0
		 *
		 * @param int $limit
		 *
		 */
		$limit = apply_filters( 'pods_whatsit_storage_post_type_find_limit', 300 );

		$post_args = [
			'order'            => 'ASC',
			'orderby'          => 'title',
			'posts_per_page'   => $limit,
			'meta_query'       => $meta_query,
			'post_type'        => 'any',
			'post_status'      => [
				'publish',
				'draft',
			],
			'suppress_filters' => false,
		];

		if ( ! empty( $args['object_type'] ) ) {
			$post_args['post_type'] = [];

			$object_types = (array) $args['object_type'];

			foreach ( $object_types as $object_type ) {
				$post_args['post_type'][] = '_pods_' . $object_type;
			}

			// There is some sort of bug when you pass a single value array for post_type that causes no results.
			if ( 1 === count( $post_args['post_type'] ) ) {
				$post_args['post_type'] = current( $post_args['post_type'] );
			}
		}

		if ( ! isset( $args['args'] ) ) {
			$args['args'] = [];
		}

		$args['args'] = (array) $args['args'];

		$secondary_object_args = [
			'parent',
			'group',
		];

		foreach ( $secondary_object_args as $arg ) {
			$args      = $this->setup_arg( $args, $arg );
			$arg_value = $this->get_arg_value( $args, $arg );

			if ( '_null' === $arg_value ) {
				continue;
			}

			$args['args'][ $arg ] = $arg_value;
		}

		foreach ( $this->secondary_args as $arg ) {
			if ( ! array_key_exists( $arg, $args ) ) {
				continue;
			}

			$args['args'][ $arg ] = $args[ $arg ];
		}

		foreach ( $args['args'] as $arg => $value ) {
			if ( 'parent' === $arg ) {
				continue;
			}

			if ( null === $value ) {
				$post_args['meta_query'][] = [
					'key'     => $arg,
					'compare' => 'NOT EXISTS',
				];

				continue;
			}

			if ( ! is_array( $value ) ) {
				$value = trim( $value );

				$post_args['meta_query'][] = [
					'key'   => $arg,
					'value' => $value,
				];

				continue;
			}

			$value = (array) $value;
			$value = array_map( 'trim', $value );
			$value = array_unique( $value );
			$value = array_filter( $value, static function( $v ) {
				return null !== $v;
			} );

			if ( $value ) {
				sort( $value );

				$post_args['meta_query'][] = [
					'key'     => $arg,
					'value'   => $value,
					'compare' => 'IN',
				];
			}
		}//end foreach

		if ( ! empty( $args['id'] ) ) {
			$args['id'] = (array) $args['id'];
			$args['id'] = array_map( 'absint', $args['id'] );
			$args['id'] = array_unique( $args['id'] );
			$args['id'] = array_filter( $args['id'] );

			if ( $args['id'] ) {
				$post_args['post__in'] = $args['id'];
			} else {
				// Bypass WP_Query if we know there are things that won't match.
				$args['bypass_post_type_find'] = true;
			}
		}

		if ( ! empty( $args['name'] ) ) {
			$args['name'] = (array) $args['name'];
			$args['name'] = array_map( 'trim', $args['name'] );
			$args['name'] = array_unique( $args['name'] );
			$args['name'] = array_filter( $args['name'] );

			if (  $args['name'] ) {
				$post_args['post_name__in'] = $args['name'];
			} else {
				// Bypass WP_Query if we know there are things that won't match.
				$args['bypass_post_type_find'] = true;
			}
		}

		if ( ! empty( $args['args']['parent'] ) ) {
			$post_args['post_parent__in'] = (array) $args['args']['parent'];
			$post_args['post_parent__in'] = array_map( 'absint', $post_args['post_parent__in'] );
			$post_args['post_parent__in'] = array_unique( $post_args['post_parent__in'] );
			$post_args['post_parent__in'] = array_filter( $post_args['post_parent__in'] );

			if ( ! $post_args['post_parent__in'] ) {
				unset( $post_args['post_parent__in'] );

				// Bypass WP_Query if we know there are things that won't match.
				$args['bypass_post_type_find'] = true;
			}
		}

		if ( ! empty( $args['status'] ) ) {
			$args['status'] = (array) $args['status'];
			$args['status'] = array_map( 'trim', $args['status'] );
			$args['status'] = array_unique( $args['status'] );
			$args['status'] = array_filter( $args['status'] );

			if ( $args['status'] ) {
				sort( $args['status'] );

				if ( 1 === count( $args['status'] ) ) {
					$args['status'] = current( $args['status'] );
				}

				$post_args['post_status'] = $args['status'];
			} else {
				// Bypass WP_Query if we know there are things that won't match.
				$args['bypass_post_type_find'] = true;
			}
		}

		if ( ! empty( $args['order'] ) ) {
			$post_args['order'] = $args['order'];
		}

		if ( ! empty( $args['orderby'] ) ) {
			$post_args['orderby'] = $args['orderby'];
		}

		if ( ! empty( $args['count'] ) ) {
			$args['limit'] = 1;
		}

		if ( ! empty( $args['limit'] ) ) {
			$post_args['posts_per_page'] = (int) $args['limit'];
		}

		/**
		 * Filter the get_posts() arguments used for finding the objects for post type storage.
		 *
		 * @since 2.8.0
		 *
		 * @param array $args      Arguments to use.
		 *
		 * @param array $post_args Post arguments to use in get_posts() call.
		 */
		$post_args = apply_filters( 'pods_whatsit_storage_post_type_find_args', $post_args, $args );

		$post_args['fields'] = ( ! empty( $args['ids'] ) ) ? 'ids' : 'all';

		if ( empty( $post_args['meta_query'] ) ) {
			unset( $post_args['meta_query'] );
		}

		asort( $post_args );

		$current_language = pods_i18n()->get_current_language();

		$query        = null;
		$cache_key    = null;
		$use_cache    = false;
		$posts        = false;
		$post_objects = false;

		$cache_key_post_type = 'any';
		$cache_key_static_check = '';

		if ( ! empty( $post_args['post_type'] ) ) {
			$cache_key_post_type = $post_args['post_type'];
		}

		if ( empty( $args['bypass_cache'] ) && empty( $args['bypass_post_type_find'] ) ) {
			$use_cache = true;

			$post_args_encoded = wp_json_encode( $post_args );

			$cache_key_static_check = __METHOD__ . '/' . $post_args_encoded;

			$cache_key_parts = [
				'pods_whatsit_storage_post_type_find',
			];

			if ( ! empty( $args['count'] ) ) {
				$cache_key_parts[] = '_count';
			}

			if ( ! empty( $args['names'] ) ) {
				$cache_key_parts[] = '_names';
			}

			if ( ! empty( $args['names_ids'] ) ) {
				$cache_key_parts[] = '_namesids';
			}

			if ( ! empty( $args['ids'] ) ) {
				$cache_key_parts[] = '_ids';

				$cache_key_static_check .= '/ids';
			}

			$cache_key_parts[] = $current_language;
			$cache_key_parts[] = $post_args_encoded;

			/**
			 * Filter cache key parts used for generating the cache key.
			 *
			 * @since 2.8.0
			 *
			 * @param array $post_args       Post arguments to use in get_posts() call.
			 * @param array $args            Arguments to use.
			 *
			 * @param array $cache_key_parts Cache key parts used to build cache key.
			 */
			$cache_key_parts = apply_filters( 'pods_whatsit_storage_post_type_cache_key_parts', $cache_key_parts, $post_args, $args );

			$cache_key_parts = array_filter( $cache_key_parts );

			$cache_key = implode( '_', $cache_key_parts );

			if ( empty( $args['refresh'] ) ) {
				$posts = pods_static_cache_get( $cache_key_static_check, self::class . '/find_objects/' . $cache_key_post_type );
				$post_objects = pods_static_cache_get( $cache_key_static_check . '_objects', self::class . '/find_objects/' . $cache_key_post_type );

				// If we have no posts in static cache, we don't need to query again.
				if ( ! is_array( $posts ) || [] === $posts ) {
					$posts = pods_transient_get( $cache_key );
				}

				// If we have no posts in static cache, we don't need to query again.
				if ( ! is_array( $post_objects ) || [] === $post_objects ) {
					$post_objects = pods_cache_get( $cache_key . '_objects', 'pods_post_type_storage_' . $cache_key_post_type );
				}
			}
		}//end if

		if ( ! is_array( $posts ) ) {
			$posts        = [];
			$post_objects = false;

			if ( empty( $args['bypass_post_type_find'] ) ) {
				$no_conflict_post = pods_no_conflict_check( 'post' );
				$no_conflict_user = pods_no_conflict_check( 'user' );

				if ( ! $no_conflict_post ) {
					pods_no_conflict_on( 'post' );
				}

				if ( ! $no_conflict_user ) {
					pods_no_conflict_on( 'user' );
				}

				// Disable query cache when testing.
				if ( function_exists( 'codecept_debug' ) ) {
					$post_args['cache_results'] = false;
				}

				$query = new WP_Query();

				$post_by_id = $this->maybe_get_by_id( $post_args );

				if ( false === $post_by_id ) {
					$query->posts       = [];
					$query->found_posts = 0;
				} elseif ( null !== $post_by_id ) {
					$query->posts       = [
						$post_by_id,
					];
					$query->found_posts = 1;

					$posts = $query->posts;
				} else {
					$posts = $query->query( $post_args );
				}

				if ( ! $no_conflict_post ) {
					pods_no_conflict_off( 'post' );
				}

				if ( ! $no_conflict_user ) {
					pods_no_conflict_off( 'user' );
				}

				// We only receive the first post, so let's just override the posts with the count.
				if ( ! empty( $args['count'] ) ) {
					$posts = array_fill( 0, $query->found_posts, 'temp_count_holder' );
				} elseif ( 'ids' !== $post_args['fields'] ) {
					// This variable should always contain the post ID's.
					$posts = wp_list_pluck( $posts, 'ID' );
				}

				if ( $use_cache && empty( $args['bypass_cache'] ) ) {
					pods_static_cache_set( $cache_key_static_check, $posts, self::class . '/find_objects/' . $cache_key_post_type );
					pods_transient_set( $cache_key, $posts, WEEK_IN_SECONDS );
				}
			}
		}

		// Return the list of posts as they are if we are counting.
		if ( ! empty( $args['count'] ) ) {
			if ( $fallback_mode && ( empty( $args['status'] ) || in_array( 'publish', (array) $args['status'], true ) ) ) {
				$posts = array_merge( $posts, parent::find( $args ) );
			}

			return $posts;
		}

		if ( ! is_array( $post_objects ) ) {
			$post_objects = [];

			if ( ! empty( $posts ) ) {
				if ( ! empty( $args['ids'] ) ) {
					// Get a list of the post IDs in basic array form.
					$post_objects = array_map( static function ( $post_id ) {
						return (object) [
							'id' => (int) $post_id,
							'ID' => (int) $post_id,
						];
					}, $posts );
				} else {
					// Get the post objects.
					if ( $query instanceof WP_Query ) {
						$post_objects = $query->posts;
					} else {
						_prime_post_caches( $posts, false, false ); // Prevent separate queries for each iteration.
						$post_objects = array_map( 'get_post', $posts );
					}
				}
			}

			if ( $use_cache && empty( $args['bypass_post_type_find'] ) && empty( $args['bypass_cache'] ) ) {
				pods_static_cache_set( $cache_key_static_check . '_objects', $post_objects, self::class . '/find_objects/' . $cache_key_post_type );
				pods_cache_set( $cache_key . '_objects', $post_objects, 'pods_post_type_storage_' . $cache_key_post_type, WEEK_IN_SECONDS );
			}
		}

		// Use the objects as they are if we only need the IDs.
		if ( ! empty( $args['ids'] ) ) {
			// We set $post_objects as id => $post_id above already.
			$posts = $post_objects;
		} else {
			if ( ! empty( $args['names'] ) || ! empty( $args['names_ids'] ) ) {
				// Just do a quick setup of the data we need for names and names+ids return.
				$posts = array_map( static function( $post ) {
					return (object) [
						'id'    => $post->ID,
						'name'  => $post->post_name,
						'label' => $post->post_title,
					];
				}, $post_objects );
			} else {
				// Handle normal Whatsit object setup.

				// Prevent separate queries for each iteration.
				if ( wp_using_ext_object_cache() ) {
					update_postmeta_cache( $posts );
				}

				$posts = array_map( [ $this, 'to_object' ], $post_objects );
				$posts = array_filter( $posts );
			}

			$names = wp_list_pluck( $posts, 'name' );
			$posts = array_combine( $names, $posts );
		}

		if ( $fallback_mode && ( empty( $args['status'] ) || in_array( 'publish', (array) $args['status'], true ) ) ) {
			$other_configs = parent::find( $args );

			// Merge the other configs into the posts array but don't overwrite them.
			foreach ( $other_configs as $key => $config ) {
				if ( is_int( $key ) ) {
					$posts[] = $config;
				} elseif ( ! isset( $posts[ $key ] ) ) {
					$posts[ $key ] = $config;
				}
			}
		}

		if ( ! empty( $args['limit'] ) ) {
			$posts = array_slice( $posts, 0, $args['limit'], true );
		}

		return $posts;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function add_object( Whatsit $object ) {
		$post_data = [
			'post_title'   => $object->get_label(),
			'post_name'    => $object->get_name(),
			'post_content' => $object->get_description(),
			'post_parent'  => $object->get_parent_id(),
			'post_type'    => '_pods_' . $object->get_object_type(),
			'post_status'  => 'publish',
		];

		if ( '' === $post_data['post_title'] ) {
			$post_data['post_title'] = $post_data['post_name'];
		}

		$added = wp_insert_post( $post_data );

		if ( is_int( $added ) && 0 < $added ) {
			// Remove any other references.
			$object_collection = Store::get_instance();
			$object_collection->unregister_object( $object );

			$object->set_arg( 'id', $added );

			$this->save_args( $object );

			return parent::add_object( $object );
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function save_object( Whatsit $object ) {
		$id = $object->get_id();

		if ( empty( $id ) ) {
			return parent::save_object( $object );
		}

		$post_data = [
			'ID'           => $id,
			'post_title'   => $object->get_label(),
			'post_name'    => $object->get_name(),
			'post_content' => $object->get_description(),
			'post_parent'  => $object->get_parent_id(),
			'post_type'    => '_pods_' . $object->get_object_type(),
			'post_status'  => 'publish',
		];

		$saved = wp_update_post( $post_data );

		if ( is_int( $saved ) && 0 < $saved ) {
			// Remove any other references.
			$object_collection = Store::get_instance();
			$object_collection->unregister_object( $object );

			$object->set_arg( 'id', $saved );

			$this->save_args( $object );

			return parent::save_object( $object );
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_args( Whatsit $object ) {
		$id = $object->get_id();

		if ( empty( $id ) ) {
			return parent::get_args( $object );
		}

		$meta = get_post_meta( $id );

		$args = [];

		foreach ( $meta as $meta_key => $meta_value ) {
			if ( in_array( $meta_key, $this->primary_args, true ) ) {
				continue;
			}

			$meta_value = array_map( 'maybe_unserialize', $meta_value );

			if ( 1 === count( $meta_value ) ) {
				$meta_value = reset( $meta_value );
			}

			// Skip empties.
			if ( in_array( $meta_value, [ '', [] ], true ) ) {
				continue;
			}

			$args[ $meta_key ] = $meta_value;

			$object->set_arg( $meta_key, $meta_value );
		}

		return $args;
	}

	/**
	 * {@inheritdoc}
	 */
	public function save_args( Whatsit $object ) {
		$id = $object->get_id();

		if ( empty( $id ) ) {
			return parent::save_args( $object );
		}

		$args = $object->get_args();

		$excluded = [
			'object_type',
			'object_storage_type',
			'id',
			'name',
			'label',
			'description',
			'parent',
		];

		$excluded = array_merge( $excluded, array_values( $this->primary_args ) );

		foreach ( $excluded as $exclude ) {
			if ( isset( $args[ $exclude ] ) ) {
				unset( $args[ $exclude ] );
			}
		}

		if ( empty( $args ) ) {
			return false;
		}

		foreach ( $args as $arg => $value ) {
			update_post_meta( $id, $arg, $value );
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function delete_object( Whatsit $object ) {
		$id = $object->get_id();

		if ( empty( $id ) ) {
			return parent::delete_object( $object );
		}

		$deleted = wp_delete_post( $id, true );

		if ( false !== $deleted && ! is_wp_error( $deleted ) ) {
			return parent::delete_object( $object );
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function to_object( $value, $force_refresh = false ) {
		return $this->to_object_from_post( $value, $force_refresh );
	}

	/**
	 * Setup object from a Post ID or Post object.
	 *
	 * @param \WP_Post|array|int $post          Post object or ID of the object.
	 * @param bool               $force_refresh Whether to force the refresh of the object.
	 *
	 * @return Whatsit|null
	 */
	public function to_object_from_post( $post, $force_refresh = false ) {
		if ( null !== $post && ! $post instanceof \WP_Post ) {
			$post = get_post( $post );
		}

		if ( empty( $post ) ) {
			return null;
		}

		if ( is_wp_error( $post ) ) {
			return null;
		}

		$object_collection = Store::get_instance();

		// Check if we already have an object registered and available.
		$object = $object_collection->get_object( $post->ID );

		if ( $object instanceof Whatsit && $post->post_type === '_pods_' . $object->get_object_type() ) {
			if ( ! $force_refresh ) {
				return $object;
			}

			$object_collection->unregister_object( $object );
		}

		$args = [];

		foreach ( $this->primary_args as $object_arg => $arg ) {
			$args[ $arg ] = '';

			if ( isset( $post->{$object_arg} ) ) {
				$args[ $arg ] = $post->{$object_arg};
			}
		}

		$object_type = substr( $post->post_type, strlen( '_pods_' ) );

		$class_name = $object_collection->get_object_type( $object_type );

		if ( ! $class_name || ! class_exists( $class_name ) ) {
			return null;
		}

		/** @var Whatsit $object */
		$object = new $class_name( $args );

		$object->set_arg( 'object_storage_type', $this->get_object_storage_type() );

		$this->get_args( $object );

		if ( $object->is_valid() ) {
			$object_collection->register_object( $object );
		}

		return $object;
	}

}
