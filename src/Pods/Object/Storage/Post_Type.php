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
	protected $type = 'post_type';

	/**
	 * {@inheritdoc}
	 */
	public function get() {
		// @todo Get by ID? Get by identifier? Get by name?
		$id = 0;

		$post = get_post( $id );

		if ( ! $post || is_wp_error( $post ) ) {
			return null;
		}

		$args = array(
			'name'        => $post->post_name,
			'id'          => $post->ID,
			'label'       => $post->post_title,
			'description' => $post->post_content,
			'parent'      => '',
			'group'       => '',
		);

		if ( 0 < $post->post_parent ) {
			$args['parent'] = $post->post_parent;
		}

		$group = get_post_meta( $post->ID, 'group', true );

		if ( 0 < strlen( $group ) ) {
			$args['group'] = $group;
		}

		$object_class = 'Pods_Object';

		return new $object_class( $args );
	}

	/**
	 * {@inheritdoc}
	 */
	public function find( array $args = array() ) {
		// @todo Find how?
		return array();
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

}
