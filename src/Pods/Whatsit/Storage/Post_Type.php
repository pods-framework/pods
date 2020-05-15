<?php

namespace Pods\Whatsit\Storage;

use Pods\Whatsit;
use Pods\Whatsit\Store;

/**
 * Post_Type class.
 *
 * @since 2.8
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
		'ID'           => 'id',
		'post_name'    => 'name',
		'post_title'   => 'label',
		'post_content' => 'description',
		'post_parent'  => 'parent',
		'menu_order'   => 'weight',
	];

	/**
	 * @var array
	 */
	protected $secondary_args = [
		'type',
		'object',
		'group',
	];

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
	public function find( array $args = [] ) {
		// Object type OR parent is required.
		if ( empty( $args['object_type'] ) && empty( $args['parent'] ) ) {
			return [];
		}

		if ( ! isset( $args['bypass_cache'] ) ) {
			$api_cache = pods_api_cache();

			if ( ! $api_cache ) {
				$args['bypass_cache'] = true;
			}
		}

		/**
		 * Filter the maximum number of posts to get for post type storage.
		 *
		 * @since 2.8
		 *
		 * @param int $limit
		 *
		 */
		$limit = apply_filters( 'pods_whatsit_storage_post_type_find_limit', 300 );

		$post_args = [
			'order'            => 'ASC',
			'orderby'          => 'title',
			'posts_per_page'   => $limit,
			'meta_query'       => [],
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

		foreach ( $this->secondary_args as $arg ) {
			if ( ! isset( $args[ $arg ] ) ) {
				continue;
			}

			$args['args'][ $arg ] = $args[ $arg ];
		}

		foreach ( $args['args'] as $arg => $value ) {
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
			$value = array_filter( $value );

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
			}
		}

		if ( ! empty( $args['name'] ) ) {
			$args['name'] = (array) $args['name'];
			$args['name'] = array_map( 'trim', $args['name'] );
			$args['name'] = array_unique( $args['name'] );
			$args['name'] = array_filter( $args['name'] );

			if ( $args['name'] ) {
				$post_args['post_name__in'] = $args['name'];
			}
		}

		if ( ! empty( $args['parent'] ) ) {
			$args['parent'] = (array) $args['parent'];
			$args['parent'] = array_map( 'absint', $args['parent'] );
			$args['parent'] = array_unique( $args['parent'] );
			$args['parent'] = array_filter( $args['parent'] );

			if ( $args['parent'] ) {
				$post_args['post_parent__in'] = $args['parent'];
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
			}
		}

		if ( ! empty( $args['order'] ) ) {
			$post_args['order'] = $args['order'];
		}

		if ( ! empty( $args['orderby'] ) ) {
			$post_args['orderby'] = $args['orderby'];
		}

		if ( ! empty( $args['limit'] ) ) {
			$post_args['posts_per_page'] = (int) $args['limit'];
		}

		/**
		 * Filter the get_posts() arguments used for finding the objects for post type storage.
		 *
		 * @since 2.8
		 *
		 * @param array $args      Arguments to use.
		 *
		 * @param array $post_args Post arguments to use in get_posts() call.
		 */
		$post_args = apply_filters( 'pods_whatsit_storage_post_type_find_args', $post_args, $args );

		$post_args['fields'] = 'ids';

		if ( empty( $post_args['meta_query'] ) ) {
			unset( $post_args['meta_query'] );
		}

		asort( $post_args );

		$current_language = false;

		// Get current language data
		$lang_data = \PodsInit::$i18n->get_current_language_data();

		if ( $lang_data && ! empty( $lang_data['language'] ) ) {
			$current_language = $lang_data['language'];
		}

		$cache_key = null;
		$posts     = false;

		if ( empty( $args['bypass_cache'] ) ) {
			$cache_key_parts = [
				'pods_whatsit_storage_post_type_find',
				$current_language,
				wp_json_encode( $post_args ),
			];

			/**
			 * Filter cache key parts used for generating the cache key.
			 *
			 * @since 2.8
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
				$posts = pods_transient_get( $cache_key );
			}
		}//end if

		if ( ! is_array( $posts ) ) {
			$posts = get_posts( $post_args );

			if ( empty( $args['bypass_cache'] ) ) {
				pods_transient_set( $cache_key, $posts );
			}
		}

		$posts = array_map( [ $this, 'to_object' ], $posts );
		$posts = array_filter( $posts );

		$names = wp_list_pluck( $posts, 'name' );

		$posts = array_combine( $names, $posts );

		if ( empty( $args['status'] ) || \in_array( 'publish', $args['status'], true ) ) {
			$posts = array_merge( $posts, parent::find( $args ) );
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
			'storage_type',
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
	 * Setup object from a Post ID or Post object.
	 *
	 * @param \WP_Post|array|int $post          Post object or ID of the object.
	 * @param bool               $force_refresh Whether to force the refresh of the object.
	 *
	 * @return Whatsit|null
	 */
	public function to_object( $post, $force_refresh = false ) {
		if ( null !== $post && ! $post instanceof \WP_Post ) {
			$post = get_post( $post );
		}

		if ( empty( $post ) ) {
			return null;
		}

		if ( ! $post || is_wp_error( $post ) ) {
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

		$this->get_args( $object );

		$object->set_arg( 'storage_type', $this->get_storage_type() );

		if ( $object->is_valid() ) {
			$object_collection->register_object( $object );
		}

		return $object;
	}

}
