<?php

namespace Pods\REST\V1;

use Pods\REST\Interfaces\Messages_Interface;
use WP_Post;

/**
 * Class Post_Repository
 *
 * @since 2.8.0
 */
class Post_Repository {

	/**
	 * A post type to get data request handler map.
	 *
	 * @var array
	 */
	protected $types_get_map = [];

	/**
	 * @var Messages_Interface
	 */
	protected $messages;

	/**
	 * Post_Repository constructor.
	 *
	 * @since 2.8.0
	 *
	 * @param Messages_Interface|null $messages The messages object.
	 */
	public function __construct( Messages_Interface $messages = null ) {
		$this->types_get_map = [
			'_pods_pod'   => [ $this, 'get_pod_data' ],
			'_pods_group' => [ $this, 'get_group_data' ],
			'_pods_field' => [ $this, 'get_field_data' ],
		];

		$this->messages = $messages ? $messages : pods_container( 'pods.rest-v1.messages' );
	}

	/**
	 * Retrieves an array representation of the object.
	 *
	 * @since 2.8.0
	 *
	 * @param int    $id      The ID.
	 * @param string $type    The type of content.
	 * @param string $context Context of data.
	 *
	 * @return array|WP_Error An array representation of the object or an WP_Error with the error message.
	 */
	public function get_data( $id, $type = '', $context = 'default' ) {
		$object = null;

		if ( empty( $type ) ) {
			$object = get_post( $id );

			if ( ! $object instanceof WP_Post ) {
				return [];
			}

			$type = $object->post_type;
		}

		if ( ! isset( $this->types_get_map[ $type ] ) ) {
			return (array) $object;
		}

		return call_user_func( $this->types_get_map[ $type ], $id, $object, $context );
	}

	/**
	 * Get pod data.
	 *
	 * @since 2.8.0
	 *
	 * @param int            $id      The ID.
	 * @param WP_Post|object $object  The object.
	 * @param string         $context The context.
	 *
	 * @return array|WP_Error
	 */
	public function get_pod_data( $id, $object = null, $context = 'default' ) {
		if ( null === $object ) {
			$object = get_post( $id );

			if ( ! $object instanceof WP_Post ) {
				return new WP_Error( 'pod-not-found', $this->messages->get_message( 'pod-not-found' ), [ 'status' => 404 ] );
			}
		}

		// @todo Fill this out.

		return [];
	}

	/**
	 * Get group data.
	 *
	 * @since 2.8.0
	 *
	 * @param int            $id      The ID.
	 * @param WP_Post|object $object  The object.
	 * @param string         $context The context.
	 *
	 * @return array|WP_Error
	 */
	public function get_group_data( $id, $object = null, $context = 'default' ) {
		if ( null === $object ) {
			$object = get_post( $id );

			if ( ! $object instanceof WP_Post ) {
				return new WP_Error( 'group-not-found', $this->messages->get_message( 'group-not-found' ), [ 'status' => 404 ] );
			}
		}

		// @todo Fill this out.

		return [];
	}

	/**
	 * Get field data.
	 *
	 * @since 2.8.0
	 *
	 * @param int            $id      The ID.
	 * @param WP_Post|object $object  The object.
	 * @param string         $context The context.
	 *
	 * @return array|WP_Error
	 */
	public function get_field_data( $id, $object = null, $context = 'default' ) {
		if ( null === $object ) {
			$object = get_post( $id );

			if ( ! $object instanceof WP_Post ) {
				return new WP_Error( 'group-not-found', $this->messages->get_message( 'group-not-found' ), [ 'status' => 404 ] );
			}
		}

		// @todo Fill this out.

		return [];
	}
}
