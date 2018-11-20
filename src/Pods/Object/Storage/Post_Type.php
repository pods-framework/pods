<?php

/**
 * Pods_Object_Storage_Post_Type class.
 *
 * @since 2.8
 */
class Pods_Object_Storage_Post_Type extends Pods_Object_Storage {

	/**
	 * {@inheritdoc}
	 */
	protected static $type = 'post_type';

	/**
	 * @var array
	 */
	protected $primary_args = array(
		'ID'           => 'id',
		'post_name'    => 'name',
		'post_title'   => 'label',
		'post_content' => 'description',
		'post_parent'  => 'parent',
	);

	/**
	 * @var array
	 */
	protected $secondary_args = array(
		'type',
		'object',
		'group',
	);

	/**
	 * {@inheritdoc}
	 */
	public function get() {
		// @todo Get by ID? Get by identifier? Get by name?
		$id = 0;

		return $this->to_object( $id );
	}

	/**
	 * {@inheritdoc}
	 */
	public function find( array $args = array() ) {
		// Object type is required.
		if ( empty( $args['object_type'] ) ) {
			return array();
		}

		/**
		 * Filter the maximum number of posts to get for post type storage.
		 *
		 * @param int $limit
		 *
		 * @since 2.8
		 */
		$limit = apply_filters( 'pods_object_post_type_find_limit', 300 );

		$post_args = array(
			'order'          => 'ASC',
			'orderby'        => 'menu_order title',
			'posts_per_page' => $limit,
			'meta_query'     => array(),
			'post_type'      => $args['object_type'],
		);

		if ( ! empty( $args['type'] ) ) {
			$args['type'] = (array) $args['type'];
			$args['type'] = array_map( 'trim', $args['type'] );
			$args['type'] = array_unique( $args['type'] );
			$args['type'] = array_filter( $args['type'] );

			if ( $args['type'] ) {
				sort( $args['type'] );

				$post_args['meta_query'][] = array(
					'key'     => 'type',
					'value'   => $args['type'],
					'compare' => 'IN',
				);
			}
		}

		if ( ! empty( $args['object'] ) ) {
			$args['object'] = (array) $args['object'];
			$args['object'] = array_map( 'trim', $args['object'] );
			$args['object'] = array_unique( $args['object'] );
			$args['object'] = array_filter( $args['object'] );

			if ( $args['object'] ) {
				sort( $args['object'] );

				$post_args['meta_query'][] = array(
					'key'     => 'object',
					'value'   => $args['object'],
					'compare' => 'IN',
				);
			}
		}

		if ( ! empty( $args['ids'] ) ) {
			$args['ids'] = (array) $args['ids'];
			$args['ids'] = array_map( 'absint', $args['ids'] );
			$args['ids'] = array_unique( $args['ids'] );
			$args['ids'] = array_filter( $args['ids'] );

			if ( $args['ids'] ) {
				sort( $args['ids'] );

				$post_args['post__in'] = $args['ids'];
			}
		}

		/**
		 * Filter the get_posts() arguments used for finding the objects for post type storage.
		 *
		 * @param array $post_args Post arguments to use in get_posts() call.
		 * @param array $args      Arguments to use.
		 *
		 * @since 2.8
		 */
		$post_args = apply_filters( 'pods_object_post_type_find_args', $post_args, $args );

		$post_args['fields'] = 'ids';

		sort( $post_args );

		$cache_key = 'pods_object_post_type_find_' . json_encode( $post_args );

		$cached = pods_transient_get( $cache_key );

		if ( ! is_array( $cached ) ) {
			$posts = get_posts( $post_args );

			pods_transient_set( $cache_key, $posts );
		}

		$posts = array_map( array( $this, 'to_object' ), $posts );
		$posts = array_filter( $posts );

		return $posts;
	}

	/**
	 * {@inheritdoc}
	 */
	public function add( Pods_Object $object ) {
		$post_data = array(
			'post_title'   => $object->get_label(),
			'post_name'    => $object->get_name(),
			'post_content' => $object->get_description(),
			'post_parent'  => $object->get_parent_id(),
			// @todo Abstract post type
			'post_type'    => '_pods_object',
			'post_status'  => 'publish',
		);

		$added = wp_insert_post( $post_data );

		if ( is_int( $added ) && 0 < $added ) {
			// @todo Update Pods_Object_Collection id/identifier.

			$object->set_arg( 'id', $added );

			$this->save_args( $object );

			return $added;
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function save( Pods_Object $object ) {
		$post_data = array(
			'ID'           => $object->get_id(),
			'post_title'   => $object->get_label(),
			'post_name'    => $object->get_name(),
			'post_content' => $object->get_description(),
			'post_parent'  => $object->get_parent_id(),
			// @todo Abstract post type
			'post_type'    => '_pods_object',
			'post_status'  => 'publish',
		);

		$saved = wp_update_post( $post_data );

		if ( is_int( $saved ) && 0 < $saved ) {
			// @todo Update Pods_Object_Collection id/identifier.

			$object->set_arg( 'id', $saved );

			$this->save_args( $object );

			return $saved;
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function save_args( Pods_Object $object ) {
		$args = $object->get_args();

		$excluded = array(
			'name',
			'id',
			'parent',
			'label',
			'description',
		);

		foreach ( $excluded as $exclude ) {
			if ( isset( $args[ $exclude ] ) ) {
				unset( $args[ $exclude ] );
			}
		}

		if ( empty( $args ) ) {
			return false;
		}

		foreach ( $args as $arg => $value ) {
			update_post_meta( $object->get_id(), $arg, $value );
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function duplicate( Pods_Object $object ) {
		$duplicated_object = clone $object;

		$duplicated_object->set_arg( 'id', null );
		$duplicated_object->set_arg( 'name', $duplicated_object->get_name() . '_copy' );

		return $this->add( $duplicated_object );
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete( Pods_Object $object ) {
		$deleted = wp_delete_post( $object->get_id(), true );

		if ( false !== $deleted && ! is_wp_error( $deleted ) ) {
			return true;
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function reset( Pods_Object $object ) {
		return false;
	}

	/**
	 * Setup object from a Post ID or Post object.
	 *
	 * @param WP_Post|array|int $post Post object or ID of the object.
	 *
	 * @return Pods_Object|null
	 */
	public function to_object( $post ) {
		if ( null !== $post && ! $post instanceof WP_Post ) {
			$post = get_post( $post );
		}

		if ( empty( $post ) ) {
			return null;
		}

		if ( ! $post || is_wp_error( $post ) ) {
			return null;
		}

		$pods_object_collection = Pods_Object_Collection::get_instance();

		// Check if we already have an object registered and available.
		$object = $pods_object_collection->get_object( $post->ID );

		if ( $object ) {
			return $object;
		}

		foreach ( $this->primary_args as $object_arg => $arg ) {
			$args[ $arg ] = '';

			if ( isset( $post->{$object_arg} ) ) {
				$args[ $arg ] = $post->{$object_arg};
			}
		}

		foreach ( $this->secondary_args as $arg ) {
			$args[ $arg ] = get_post_meta( $post->ID, $arg, true );
		}

		$object_type = substr( $post->post_type, strlen( '_pods_' ) );

		$class_name = $pods_object_collection->get_object_type( $object_type );

		if ( ! $class_name || ! class_exists( $class_name ) ) {
			return null;
		}

		/** @var Pods_Object $object */
		$object = new $class_name( $args );

		return $object;
	}

}
