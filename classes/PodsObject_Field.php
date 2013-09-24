<?php
/**
 * @package Pods
 *
 * Class PodsObject_Field
 */
class PodsObject_Field extends PodsObject {

	/**
	 * Post type / meta key prefix for internal values
	 *
	 * @var string
	 */
	protected $_post_type = '_pods_field';

	/**
	 * Deprecated keys / options
	 *
	 * @var array
	 */
	protected $_deprecated_keys = array(
		'pod_id' => 'parent_id',
		'sister_field_id' => 'sister_id',
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

				/**
				 * @var WP_Post $_object
				 */
				$_object = $find_object[ 0 ];

				if ( 'WP_Post' == get_class( $_object ) ) {
					$_object = $_object->to_array();
				}
				else {
					$_object = get_object_vars( $_object );
				}
			}
		}

		if ( !empty( $_object ) || !empty( $object ) ) {
			$defaults = array(
				'id' => 0,
				'name' => '',
				'label' => '',
				'description' => '',
				'type' => 'text',
				'weight' => 0,
				'parent_id' => $parent_id
			);

			if ( !empty( $_object ) ) {
				$object = array(
					'id' => $_object[ 'ID' ],
					'name' => $_object[ 'post_name' ],
					'label' => $_object[ 'post_title' ],
					'description' => $_object[ 'post_content' ],
					'parent_id' => $_object[ 'post_parent' ],
					'type' => 'text'
				);
			}

			$object = array_merge( $defaults, $object );

			if ( strlen( $object[ 'label' ] ) < 1 ) {
				$object[ 'label' ] = $object[ 'name' ];
			}

			$tableless_meta = array(
				'pick_object',
				'pick_val',
				'sister_id'
			);

			if ( 0 < $object[ 'id' ] ) {
				$meta = array(
					'type'
				);

				foreach ( $meta as $meta_key ) {
					$value = $this->_meta( $meta_key, $object[ 'id' ], true );

					if ( null !== $value ) {
						$object[ $meta_key ] = $value;
					}
				}

				if ( empty( $object[ 'type' ] ) ) {
					$object[ 'type' ] = 'text';
				}

				if ( in_array( $object[ 'type' ], PodsForm::tableless_field_types() ) ) {
					foreach ( $tableless_meta as $meta_key ) {
						$value = $this->_meta( $meta_key, $object[ 'id' ], true );

						if ( null !== $value ) {
							$object[ $meta_key ] = $value;
						}
					}

					// Backwards compatibility
					if ( pods_allow_deprecated() && !isset( $object[ 'sister_id' ] ) ) {
						$meta_key = 'sister_field_id';

						$value = $this->_meta( $meta_key, $object[ 'id' ] );

						if ( null !== $value ) {
							$object[ $meta_key ] = $value;
						}
					}
				}
				else {
					foreach ( $tableless_meta as $meta_key ) {
						$object[ $meta_key ] = '';
					}
				}
			}

			if ( in_array( $object[ 'type' ], PodsForm::tableless_field_types() ) ) {
				if ( $object[ 'id' ] < 1 ) {
					// Backwards compatibility
					if ( pods_allow_deprecated() && !isset( $object[ 'sister_id' ] ) && isset( $object[ 'sister_field_id' ] ) ) {
						$object[ 'sister_id' ] = $object[ 'sister_field_id' ];

						unset( $object[ 'sister_field_id' ] );
					}
				}

				foreach ( $tableless_meta as $meta_key ) {
					if ( !isset( $tableless_meta[ $meta_key ] ) || empty( $object[ 'pick_object' ] ) ) {
						$object[ $meta_key ] = '';
					}
				}
			}

			$this->_object = $object;

			return $this->_object[ 'id' ];
		}

		return false;

	}

	/**
	 * Get table info for a Pod
	 *
	 * @return array Table info
	 */
	public function table_info() {

		if ( !$this->is_valid() || !in_array( $this->_field[ 'type' ], PodsForm::tableless_field_types() ) ) {
			return array();
		}

		if ( empty( $this->_table_info ) ) {
			$this->_table_info = pods_api()->get_table_info( $this->_field[ 'pick_object' ], $this->_field[ 'pick_val' ], null, null, $this->_field );
		}

		return $this->_table_info;

	}

	/**
	 * Return a field input for a specific field
	 *
	 * @param array|string $field Input field name to use (overrides default name)
	 * @param null $input_name
	 * @param mixed $value Current value to use
	 * @param array $options
	 * @param null $pod
	 * @param null $id
	 *
	 * @return string Field Input HTML
	 *
	 * @since 2.3.10
	 */
	public function input( $field, $input_name = null, $value = null, $options = array(), $pod = null, $id = null ) {

		// Field data override
		if ( is_array( $field ) ) {
			$field_data = $field;
			$field = pods_var_raw( 'name', $field );
		}
		// Get field data from field name
		else {
			$field_data = $this->fields( $field );
		}

		if ( !empty( $field_data ) ) {
			$field_type = $field_data[ 'type' ];

			if ( empty( $input_name ) ) {
				$input_name = $field;
			}

			return PodsForm::field( $input_name, $value, $field_type, $field_data, $pod, $id );
		}

		return '';

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

		// @todo Move API logic into PodsObjectField
		$id = pods_api()->save_field( $params );

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

		// @todo Move API logic into PodsObjectField
		$id = pods_api()->duplicate_field( $params );

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
			// @todo Move API logic into PodsObjectField
			$success = pods_api()->delete_field( $params );
		}

		if ( $success ) {
			$this->_action( 'pods_object_delete', $_object[ 'id' ], $_object[ 'name' ], $_object[ 'parent' ], $_object );
		}

		// Can't destroy object, so let's destroy the data and invalidate the object
		$this->destroy();

		return $success;

	}
}