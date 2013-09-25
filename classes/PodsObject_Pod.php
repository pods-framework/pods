<?php
/**
 * @package Pods
 *
 * Class PodsObject_Pod
 */
class PodsObject_Pod extends PodsObject {

	/**
	 * Post type / meta key prefix for internal values
	 *
	 * @var string
	 */
	protected $_post_type = '_pods_pod';

	/**
	 * Deprecated keys / options
	 *
	 * @var array
	 */
	protected $_deprecated_keys = array(
		'ID' => 'id',
		'post_title' => 'label',
		'post_name' => 'name',
		'post_content' => 'description',
		'post_parent' => 'parent_id'
	);

	/**
	 * Method names for accessing internal keys
	 *
	 * @var array
	 */
	protected $_methods = array(
		'fields',
		'object_fields',
		'table_info'
	);

	/**
	 * Load the object
	 *
	 * @param string|array|WP_Post $name Get the Object by Name, or pass an array/WP_Post of Object
	 * @param int $id Get the Object by ID (overrides $name)
	 * @param mixed $parent Parent Object or ID
	 *
	 * @return int|bool $id The Object ID or false if Object not found
	 */
	public function load( $name = null, $id = 0, $parent = null ) {

		// Post Object
		$_object = false;

		// Custom Object
		$object = false;

		// Allow for refresh of object
		if ( null === $name && 0 == $id && null === $parent && $this->is_valid() ) {
			$id = $this->_object[ 'id' ];

			$this->destroy();
		}

		// Parent ID passed
		$parent_id = $parent;

		// Parent object passed
		if ( is_object( $parent_id ) && isset( $parent_id->id ) ) {
			$parent_id = $parent_id->id;
		}
		// Parent array passed
		elseif ( is_array( $parent_id ) && isset( $parent_id[ 'id' ] ) ) {
			$parent_id = $parent_id[ 'id' ];
		}

		$parent_id = (int) $parent_id;

		// Object ID passed
		if ( 0 < $id ) {
			$_object = get_post( (int) $id, ARRAY_A );

			// Fallback to Object name
			if ( empty( $_object ) || $this->_post_type != $_object[ 'post_type' ] ) {
				return $this->load( $name, 0 );
			}
		}
		// WP_Post of Object data passed
		elseif ( is_object( $name ) && 'WP_Post' == get_class( $name ) && $this->_post_type == $name->post_type ) {
			$_object = get_object_vars( $name );
		}
		// Fallback for pre-WP_Post
		elseif ( is_object( $name ) && isset( $name->post_type ) && $this->_post_type == $name->post_type ) {
			$_object = get_post( (int) $name->ID, ARRAY_A );
		}
		// Handle custom arrays
		elseif ( is_array( $name ) ) {
			$object = $name;
		}
		// Find Object by name
		else {
			$find_args = array(
				'name' => $name,
				'post_type' => $this->_post_type,
				'posts_per_page' => 1,
				'post_parent' => $parent_id
			);

			$find_object = get_posts( $find_args );

			// Object found
			if ( !empty( $find_object ) && is_array( $find_object ) ) {
				$_object = $find_object[ 0 ];

				if ( 'WP_Post' == get_class( $_object ) ) {

					/**
					 * @var WP_Post $_object
					 */
					$_object = $_object->to_array();
				}
				else {
					$_object = get_object_vars( $_object );
				}
			}
			// Fallback for core WP User object
			elseif ( 'user' == $name ) {
				$object = array(
					'name' => $name,
					'label' => __( 'Users', 'pods' ),
					'label_singular' => __( 'User', 'pods' ),
					'type' => $name
				);
			}
			// Fallback for core WP Media object
			elseif ( 'media' == $name ) {
				$object = array(
					'name' => $name,
					'label' => __( 'Media', 'pods' ),
					'label_singular' => __( 'Media', 'pods' ),
					'type' => $name
				);
			}
			// Fallback for core WP Comment object
			elseif ( 'comment' == $name ) {
				$object = array(
					'name' => $name,
					'label' => __( 'Comments', 'pods' ),
					'label_singular' => __( 'Comment', 'pods' ),
					'object' => $name,
					'type' => $name
				);
			}
			// Fallback for core WP Post Type / Taxonomy
			else {
				$post_type = get_post_type_object( $name );

				if ( empty( $post_type ) && 0 !== strpos( $name, 'post_type_' ) ) {
					$name = str_replace( 'post_type_', '', $name );

					$post_type = get_post_type_object( $name );
				}

				// Fallback for core WP Post Type
				if ( !empty( $post_type ) ) {
					$object = array(
						'name' => $name,
						'label' => $post_type->labels->name,
						'label_singular' => $post_type->labels->singular_name,
						'object' => $name,
						'type' => 'post_type'
					);

					// Add labels
					$object = array_merge( $object, get_object_vars( $post_type->labels ) );

					// @todo Import object settings and match up to Pod options
					/*unset( $post_type->name );
					unset( $post_type->labels );

					$object = array_merge( $object, get_object_vars( $post_type ) );*/
				}

				if ( empty( $object ) ) {
					$taxonomy = get_taxonomy( $name );

					if ( empty( $taxonomy ) && 0 !== strpos( $name, 'taxonomy_' ) ) {
						$name = str_replace( 'taxonomy_', '', $name );

						$taxonomy = get_taxonomy( $name );
					}

					// Fallback for core WP Taxonomy
					if ( !empty( $taxonomy ) ) {
						$object = array(
							'name' => $name,
							'label' => $taxonomy->labels->name,
							'label_singular' => $taxonomy->labels->singular_name,
							'object' => $name,
							'type' => 'taxonomy',
							'storage' => 'none'
						);

						// Add labels
						$object = array_merge( $object, get_object_vars( $taxonomy->labels ) );

						// @todo Import object settings and match up to Pod options
						/*unset( $taxonomy->name );
						unset( $taxonomy->labels );

						$object = array_merge( $object, get_object_vars( $taxonomy ) );*/
					}
				}

				// @todo For now, only support comment_{$comment_type}
				if ( empty( $object ) && 0 !== strpos( $name, 'comment_' ) ) {
					// @todo For now, only support comment_{$comment_type}
					$name = str_replace( 'comment_', '', $name );

					// @todo Eventually support the comment type objects when this function gets made
					//$comment = get_comment_object( $name );

					/*if ( empty( $comment ) && 0 !== strpos( $name, 'comment_' ) ) {
						$name = str_replace( 'comment_', '', $name );

						// @todo Eventually support the comment type objects when this function gets made
						//$comment = get_comment_object( $name );
					}*/

					// Fallback for core WP Comment type
					//if ( !empty( $comment ) ) {
						$label = __( ucwords( str_replace( array( '-', '_' ), ' ', $name ) ), 'pods' );

						$object = array(
							'name' => $name,
							'label' => $label,
							'label_singular' => $label,
							'object' => $name,
							'type' => 'comment'
						);

						// Add labels
						/*$object = array_merge( $object, get_object_vars( $comment->labels ) );

						// @todo Import object settings and match up to Pod options
						/*unset( $comment->name );
						unset( $comment->labels );

						$object = array_merge( $object, get_object_vars( $comment ) );*/
					//}
				}
			}
		}

		if ( !empty( $_object ) || !empty( $object ) ) {
			$defaults = array(
				'id' => 0,
				'name' => '',
				'label' => '',
				'label_singular' => '',
				'description' => '',
				'type' => 'post_type',
				'storage' => 'meta',
				'object' => '',
				'alias' => '',
				'show_in_menu' => 1,
				'parent_id' => $parent_id
			);

			if ( !empty( $_object ) ) {
				$object = array(
					'id' => $_object[ 'ID' ],
					'name' => $_object[ 'post_name' ],
					'label' => $_object[ 'post_title' ],
					'description' => $_object[ 'post_content' ],
				);
			}

			$object = array_merge( $defaults, $object );

			if ( strlen( $object[ 'label' ] ) < 1 ) {
				$object[ 'label' ] = $object[ 'name' ];
			}

			if ( strlen( $object[ 'label_singular' ] ) < 1 ) {
				$object[ 'label_singular' ] = $object[ 'label' ];
			}

			if ( 0 < $object[ 'id' ] ) {
				$meta = array(
					'type',
					'storage',
					'object',
					'alias',
					'show_in_menu'
				);

				foreach ( $meta as $meta_key ) {
					$value = $this->_meta( $meta_key, $object[ 'id' ], true );

					if ( null !== $value ) {
						$object[ $meta_key ] = $value;
					}
				}

				if ( empty( $object[ 'type' ] ) ) {
					$object[ 'type' ] = 'post_type';
				}

				if ( empty( $object[ 'storage' ] ) ) {
					$object[ 'storage' ] = 'meta';
				}
			}

			$this->_object = $object;

			return $this->_object[ 'id' ];
		}

		return false;

	}

	/**
	 * Return object field array from Pod, a object field's data, or a object field option
	 *
	 * @param string|null $field Object Field name
	 * @param string|null $option Field option
	 *
	 * @return array|mixed
	 *
	 * @since 2.3.10
	 */
	public function object_fields( $field = null, $option = null ) {

		if ( !isset( $this->_object[ 'fields' ] ) ) {
			if ( $this->is_custom() && isset( $this->_object[ '_object_fields' ] ) && !empty( $this->_object[ '_object_fields' ] ) ) {
				$object_fields = $this->_object[ '_object_fields' ];
			}
			else {
				$object_fields = pods_api()->get_wp_object_fields( $this->_object[ 'type' ], $this->_object );
			}

			$this->_object[ '_object_fields' ] = array();

			foreach ( $object_fields as $object_field ) {
				$object_field = pods_object_field( $object_field, 0, $this->_live, $this->_object[ 'id' ] );

				if ( $object_field->is_valid() ) {
					$this->_object[ 'object_fields' ][ $object_field[ 'name' ] ] = $object_field;
				}
			}
		}

		return $this->_fields( 'object_fields', $field, $option );

	}

	/**
	 * Get table info for a Pod
	 *
	 * @return array Table info
	 */
	public function table_info() {

		if ( !$this->is_valid() ) {
			return array();
		}

		if ( empty( $this->_table_info ) ) {
			$this->_table_info = pods_api()->get_table_info( $this->_object[ 'type' ], $this->_object[ 'object' ], $this->_object[ 'name' ], $this->_object );
		}

		return $this->_table_info;

	}

    /**
     * Save a Object by giving an array of option data or set a specific option to a specific value.
     *
     * @param array|string $options Either an associative array of option information or a option name
     * @param mixed $value (optional) Value of the option, if $data is a option name
	 * @param bool $refresh (optional) Refresh the current object
     *
     * @return int|bool The Object ID or false if failed
     *
     * @since 2.3.10
	 */
	public function save( $options = null, $value = null, $refresh = true ) {

		if ( !$this->is_valid() ) {
			return false;
		}

		if ( null !== $value || !is_array( $options ) ) {
			$options = array(
				$options => $value
			);
		}

		if ( empty( $options ) ) {
			return $this->_object[ 'id' ];
		}

		$params = $options;

		$params[ 'id' ] = $this->_object[ 'id' ];

		// For use later in actions
		$_object = $this->_object;

		$params = apply_filters( 'pods_object_pre_save_' . $this->_action_type, $params, $_object );

		// @todo Move API logic into PodsObject
		$id = pods_api()->save_pod( $params );

		// Refresh object
		if ( $refresh ) {
			$id = $this->load( null, $id );
		}
		// Just update options
		else {
			foreach ( $params as $option => $value ) {
				if ( 'id' != $option ) {
					$this->offsetSet( $option, $value );
				}
			}
		}

		if ( 0 < $id ) {
			$this->_action( 'pods_object_save', $_object[ 'id' ], $_object[ 'name' ], $_object[ 'parent' ], $_object );
		}

		return $id;

	}

    /**
     * Duplicate a Object, optionally giving an array of option data or set a specific option to a specific value.
     *
     * @param array|string $options (optional) Either an associative array of option information or a option name
     * @param mixed $value (optional) Value of the option, if $data is a option name
	 * @param bool $replace (optional) Replace the current object
     *
     * @return int|bool The new Object ID or false if failed
     *
     * @since 2.3.10
	 */
	public function duplicate( $options = null, $value = null, $replace = false ) {

		if ( !$this->is_valid() ) {
			return false;
		}

		if ( null !== $value && !is_array( $options ) ) {
			$options = array(
				$options => $value
			);
		}

		if ( empty( $options ) ) {
			return $this->_object[ 'id' ];
		}

		$params = $options;

		$params[ 'id' ] = $this->_object[ 'id' ];
		$params[ 'name' ] = $this->_object[ 'name' ];

		// For use later in actions
		$_object = $this->_object;

		$params = apply_filters( 'pods_object_pre_duplicate_' . $this->_action_type, $params, $_object );

		// @todo Move API logic into PodsObject
		$id = pods_api()->duplicate_pod( $params );

		if ( $replace ) {
			// Replace object
			$id = $this->load( null, $id );
		}

		if ( 0 < $id ) {
			$this->_action( 'pods_object_duplicate', $id, $_object[ 'id' ], $_object[ 'name' ], $_object[ 'parent' ], $_object );
		}

		return $id;

	}

    /**
     * Delete the Object
     *
     * @return bool Whether the Object was successfully deleted
     *
     * @since 2.3.10
     */
	public function delete() {

		if ( !$this->is_valid() ) {
			return false;
		}

		$params = array(
			'id' => $this->_object[ 'id' ],
			'name' => $this->_object[ 'name' ]
		);

		// For use later in actions
		$_object = $this->_object;

		$params = apply_filters( 'pods_object_pre_delete_' . $this->_action_type, $params, $_object );

		$success = false;

		if ( 0 < $params[ 'id' ] ) {
			// @todo Move API logic into PodsObject
			$success = pods_api()->delete_pod( $params );
		}

		if ( $success ) {
			$this->_action( 'pods_object_delete', $_object[ 'id' ], $_object[ 'name' ], $_object[ 'parent' ], $_object );
		}

		// Can't destroy object, so let's destroy the data and invalidate the object
		$this->destroy();

		return $success;

	}
}