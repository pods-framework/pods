<?php
/**
 * @package Pods
 *
 * Class PodsObject
 */
class PodsObject implements ArrayAccess {

	/**
	 * Object data
	 *
	 * @var array
	 */
	protected $_object = array();

	/**
	 * Additional Object data
	 *
	 * @var array
	 */
	protected $_addtl = array();

	/**
	 * Table info for Object
	 *
	 * @var array
	 */
	protected $_table_info = array();

	/**
	 * Set to true to automatically save values in the DB when you $object['option']='value'
	 *
	 * @var bool
	 */
	protected $_live = false;

	/**
	 * Post type / meta key prefix for internal values
	 *
	 * @var string
	 */
	protected $_post_type;

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
	 * Get the Object
	 *
	 * @param string|array|WP_Post $name Get the Object by Name, or pass an array/WP_Post of Object
	 * @param int $id Get the Object by ID (overrides $name)
	 * @param bool $live Set to true to automatically save values in the DB when you $object['option']='value'
	 * @param mixed $parent Parent Object or ID
	 *
	 * @since 2.3.10
	 */
	public function __construct( $name, $id = 0, $live = false, $parent = null ) {

		$id = $this->init( $name, $id, $parent );

		if ( 0 < $id ) {
			$this->_live = $live;
		}

		add_action( 'switch_blog', array( $this, 'table_info_clear' ) );

	}

	/**
	 * Init the object
	 *
	 * @param string|array|WP_Post $name Get the Object by Name, or pass an array/WP_Post of Object
	 * @param int $id Get the Object by ID (overrides $name)
	 * @param mixed $parent Parent Object or ID
	 *
	 * @return int|bool $id The Object ID or false if Object not found
	 *
	 * @since 2.3.10
	 */
	public function init( $name = null, $id = 0, $parent = null ) {

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
			$_object = get_post( $dummy = (int) $id, ARRAY_A );

			// Fallback to Object name
			if ( empty( $_object ) || $this->_post_type != $_object->post_type ) {
				return $this->init( $name, 0 );
			}
		}
		// WP_Post of Object data passed
		elseif ( is_object( $name ) && 'WP_Post' == get_class( $name ) && $this->_post_type == $name->post_type ) {
			$_object = get_object_vars( $name );
		}
		// Fallback for pre-WP_Post
		elseif ( is_object( $name ) && isset( $name->post_type ) && $this->_post_type == $name->post_type ) {
			$_object = get_post( $dummy = (int) $name->ID, ARRAY_A );
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
			}
		}

		if ( !empty( $_object ) || !empty( $object ) ) {
			$defaults = array(
				'id' => 0,
				'name' => '',
				'label' => '',
				'description' => '',
				'parent_id' => $parent_id
			);

			if ( !empty( $_object ) ) {
				$object = array(
					'id' => $_object[ 'ID' ],
					'name' => $_object[ 'post_name' ],
					'label' => $_object[ 'post_title' ],
					'description' => $_object[ 'post_content' ],
					'parent_id' => $_object[ 'post_parent' ],
				);
			}

			$object = array_merge( $defaults, $object );

			if ( strlen( $object[ 'label' ] ) < 1 ) {
				$object[ 'label' ] = $object[ 'name' ];
			}

			$this->_object = $object;

			return $this->_object[ 'id' ];
		}

		return false;

	}

	/**
	 * Check if the Object is a valid
	 *
	 * @return bool
	 *
	 * @since 2.3.10
	 */
	public function is_valid() {

		if ( !empty( $this->_object ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Check if the Object is custom
	 *
	 * @return bool
	 *
	 * @since 2.3.10
	 */
	public function is_custom() {

		if ( !empty( $this->_object ) && 0 < $this->_object[ 'id' ] ) {
			return true;
		}

		return false;

	}

	/**
	 * Destroy this Object and invalidate it
	 *
	 * @since 2.3.10
	 */
	public function destroy() {

		$this->_object = array();
		$this->_addtl = array();
		$this->_table_info = array();

		$this->_live = false;

	}

	/**
	 * Get meta from the object
	 *
	 * @param string $meta_key Meta key name
	 * @param null|int $id Object ID
	 * @param bool $internal If this is an internal meta value
	 *
	 * @return array|mixed|null
	 *
	 * @since 2.3.10
	 */
	protected function _meta( $meta_key, $id = null, $internal = false ) {

		if ( !$this->is_valid() && null === $id ) {
			return null;
		}

		if ( null === $id ) {
			if ( !empty( $this->_object ) ) {
				$id = $this->_object[ 'id' ];
			}
			else {
				return null;
			}
		}

		// @todo For 2.4 enable internal prefix
		if ( 1 == 0 && $internal && 0 !== strpos( $meta_key, $this->_post_type . '_' ) ) {
			$meta_key = $this->_post_type . '_' . $meta_key;
		}

		$value = get_post_meta( $id, $meta_key );

		// @todo For 2.4 enable fallback
		if ( 1 == 0 && pods_allow_deprecated() && is_array( $value ) && empty( $value ) ) {
			if ( 0 === strpos( $meta_key, $this->_post_type . '_' ) ) {
				$meta_key = substr( $meta_key, strlen( $this->_post_type . '_' ) );
			}

			$value = get_post_meta( $id, $meta_key );
		}

		if ( is_array( $value ) ) {
			// Field not found
			if ( empty( $value ) ) {
				$value = null;
			}
			else {
				foreach ( $value as $k => $v ) {
					if ( !is_array( $v ) ) {
						$value[ $k ] = maybe_unserialize( $v );
					}
				}

				if ( 1 == count( $value ) ) {
					$value = current( $value );
				}
			}
		}
		else {
			$value = maybe_unserialize( $value );
		}

		return $value;

	}

	/**
	 * Return field array from $fields, a field's data, or a field option
	 *
	 * @param string $fields Field key name
	 * @param string|null $field Field name
	 * @param string|null $option Field option
	 * @param bool $alt Set to true to check alternate fields array
	 *
	 * @return bool|mixed
	 *
	 * @since 2.3.10
	 */
	protected function _fields( $fields, $field = null, $option = null, $alt = true ) {

		if ( !$this->is_valid() ) {
			return false;
		}

		$alt_fields = 'object_fields';

		if ( 'object_fields' == $fields ) {
			$alt_fields = 'fields';
		}

		// No fields found
		if ( empty( $this->_object[ 'fields' ] ) ) {
			$field_data = array();

			// No fields and field not found, get alt field data
			if ( !empty( $field ) && $alt ) {
				$field_data = $this->_fields( $alt_fields, $field, $option, false );
			}
		}
		// Return all fields
		elseif ( empty( $field ) ) {
			$field_data = (array) $this->_object[ 'fields' ];

			if ( !$this->_live ) {
				foreach ( $field_data as $field_name => $fields ) {
					foreach ( $fields as $field_option => $field_value ) {
						// i18n plugin integration
						if ( 'label' == $field_option || 0 === strpos( $field_option, 'label_' ) ) {
							$field_data[ $field_name ][ $field_option ] = __( $field_value );
						}
					}
				}
			}
		}
		// Field not found
		elseif ( !isset( $this->_object[ 'fields' ][ $field ] ) ) {
			$field_data = array();

			// Field not found, get alt field data
			if ( $alt ) {
				$field_data = $this->_fields( $alt_fields, $field, $option, false );
			}
		}
		// Return all field data
		elseif ( empty( $option ) ) {
			$field_data = $this->_object[ 'fields' ][ $field ];

			if ( !$this->_live ) {
				foreach ( $field_data as $field_option => $field_value ) {
					// i18n plugin integration
					if ( 'label' == $field_option || 0 === strpos( $field_option, 'label_' ) ) {
						$field_data[ $field_option ] = __( $field_value );
					}
				}
			}
		}
		// Get an option from a field
		else {
			$field_data = null;

			// Get a list of available items from a relationship field
			if ( 'data' == $option && in_array( pods_var_raw( 'type', $this->_object[ $fields ][ $field ] ), PodsForm::tableless_field_types() ) ) {
				$field_data = PodsForm::field_method( 'pick', 'get_field_data', $this->_object[ $fields ][ $field ] );
			}
			// Return option
			elseif ( isset( $this->_object[ $fields ][ $field ][ $option ] ) ) {
				$field_data = $this->_object[ $fields ][ $field ][ $option ];

				// i18n plugin integration
				if ( 'label' == $option || 0 === strpos( $option, 'label_' ) ) {
					$field_data = __( $field_data );
				}
			}
		}

		return $field_data;

	}

	/**
	 * Fill in object data that may not be set yet
	 *
	 * @since 2.3.10
	 */
	protected function _fill() {

		// @todo Fill in all built-in fields

		// @todo Pull in custom meta keys

		foreach ( $this->_methods as $method ) {
			call_user_func( $this, $method );
		}

	}

	/**
	 * Export the object data into a normal array
	 *
	 * @return array Exported array of all object data
	 *
	 * @since 2.3.10
	 */
	public function export() {

		$this->_fill();

		$export = array_merge( $this->_addtl, $this->_object );

		foreach ( $this->_methods as $method ) {
			$export[ $method ] = call_user_func( $this, $method );
		}

		return $export;

	}

	/**
	 * Return field array from Object, a field's data, or a field option
	 *
	 * @param string|null $object_field Object Field name
	 * @param string|null $option Field option
	 *
	 * @return array|mixed
	 *
	 * @since 2.3.10
	 */
	public function fields( $field = null, $option = null ) {

		if ( !isset( $this->_object[ 'fields' ] ) ) {
			if ( $this->is_custom() ) {
				if ( isset( $this->_object[ '_fields' ] ) && !empty( $this->_object[ '_fields' ] ) ) {
					foreach ( $this->_object[ '_fields' ] as $field ) {
						$field = pods_object_field( $this->_object[ 'id' ], $field, 0, $this->_live );

						if ( $field->is_valid() ) {
							$this->_object[ 'fields' ][ $field[ 'name' ] ] = $field;
						}
					}
				}
			}
			else {
				$find_args = array(
					'post_type' => '_pods_field',
					'posts_per_page' => -1,
					'nopaging' => true,
					'post_parent' => $this->_object[ 'id' ],
					'orderby' => 'menu_order',
					'order' => 'ASC'
				);

				$fields = get_posts( $find_args );

				$this->_object[ 'fields' ] = array();

				if ( !empty( $fields ) ) {
					foreach ( $fields as $field ) {
						$field = pods_object_field( $this->_object[ 'id' ], $field, 0, $this->_live );

						if ( $field->is_valid() ) {
							$this->_object[ 'fields' ][ $field[ 'name' ] ] = $field;
						}
					}
				}
			}
		}

		return $this->_fields( 'fields', $field, $option );

	}

	/**
	 * Return object field array from Object, a object field's data, or a object field option
	 *
	 * @param string|null $object_field Object Field name
	 * @param string|null $option Field option
	 *
	 * @return array|mixed
	 *
	 * @since 2.3.10
	 */
	public function object_fields( $object_field = null, $option = null ) {

		if ( !isset( $this->_object[ 'fields' ] ) ) {
			if ( $this->is_custom() ) {
				if ( isset( $this->_object[ '_object_fields' ] ) && !empty( $this->_object[ '_object_fields' ] ) ) {
					foreach ( $this->_object[ '_object_fields' ] as $field ) {
						$this->_object[ 'object_fields' ] = pods_object_field( $this->_object[ 'id' ], $field, 0, $this->_live );
					}
				}
			}
		}

		return $this->_fields( 'object_fields', $object_field, $option );

	}

	/**
	 * Get table info for an Object
	 *
	 * @return array Table info
	 *
	 * @since 2.3.10
	 */
	public function table_info() {

		if ( !$this->is_valid() ) {
			return array();
		}

		return $this->_table_info;

	}

	/**
	 * Clear Table info for object, used when switching blogs
	 *
	 * @since 2.3.10
	 */
	public function table_info_clear() {

		$this->_table_info = array();

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

		// @todo Handle generalized saving
		$id = $params[ 'id' ];

		// Refresh object
		if ( $refresh ) {
			$id = $this->init( null, $id );
		}
		// Just update options
		else {
			foreach ( $params as $option => $value ) {
				if ( 'id' != $option ) {
					$this->offsetSet( $option, $value );
				}
			}
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

		// @todo Handle generalized duplicating
		$id = $params[ 'id' ];

		if ( $replace ) {
			// Replace object
			$id = $this->init( null, $id );
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

		$params = array(
			'id' => $this->_object[ 'id' ],
			'name' => $this->_object[ 'name' ]
		);

		$success = false;

		if ( 0 < $params[ 'id' ] ) {
			// @todo Handle generalized deleting
			$success = true;
		}

		// Can't destroy object, so let's destroy the data and invalidate the object
		$this->destroy();

		return $success;

	}

	/**
	 * Set value from array usage $object['offset'] = 'value';
	 *
	 * @param mixed $offset Used to set index of Array or Variable name on Object
	 * @param mixed $value Value to be set
	 * @param bool $live Set to false to override current live object saving
	 *
	 * @return mixed|void
	 *
	 * @since 2.3.10
	 */
	public function offsetSet( $offset, $value, $live = true ) {

		if ( $live && $this->_live ) {
			$this->save( $offset, $value );
		}
		else {
			$this->_object[ $offset ] = $value;
		}

	}

	/**
	 * Get value from array usage $object['offset'];
	 *
	 * @param mixed $offset Used to get value of Array
	 *
	 * @return mixed|null
	 *
	 * @since 2.3.10
	 */
	public function offsetGet( $offset ) {

		if ( !empty( $this->_methods ) && in_array( $offset, $this->_methods ) ) {
			$value = call_user_func( $this, $offset );
		}
		elseif ( 'options' == $offset ) {
			$value = null;

			if ( pods_allow_deprecated() ) {
				$value = $this->_object;
			}
		}
		elseif ( isset( $this->_object[ $offset ] ) ) {
			$value = $this->_object[ $offset ];

			if ( !$this->_live ) {
				// i18n plugin integration
				if ( 'label' == $offset || 0 === strpos( $offset, 'label_' ) ) {
					$value = __( $value );
				}
			}
		}
		elseif ( isset( $this->_addtl[ $offset ] ) ) {
			$value = $this->_addtl[ $offset ];

			if ( !$this->_live ) {
				// i18n plugin integration
				if ( 'label' == $offset || 0 === strpos( $offset, 'label_' ) ) {
					$value = __( $value );
				}
			}
		}
		// Deprecated keys
		elseif ( isset( $this->_deprecated_keys[ $offset ] ) ) {
			$value = null;

			if ( pods_allow_deprecated() ) {
				$value = $this->offsetGet( $this->_deprecated_keys[ $offset ] );
			}
			else {
				pods_deprecated( '$object[\'' . $offset .'\']', '2.0', '$object[\'' . $this->_deprecated_keys[ $offset ] . '\']' );
			}
		}
		else {
			$value = $this->_meta( $offset );

			if ( null !== $value ) {
				$this->_addtl[ $offset ] = $value;
			}
		}

		return $value;

	}

	/**
	 * Get value from array usage $object['offset'];
	 *
	 * @param mixed $offset Used to get value of Array
	 *
	 * @return bool
	 *
	 * @since 2.3.10
	 */
	public function offsetExists( $offset ) {

		if ( null !== $this->offsetGet( $offset ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Get value from array usage $object['offset'];
	 *
	 * @param mixed $offset Used to unset index of Array
	 * @param bool $live Set to false to override current live object saving
	 *
	 * @since 2.3.10
	 */
	public function _offsetUnset( $offset, $live = true ) {

		if ( isset( $this->_object[ $offset ] ) ) {
			if ( $live && $this->_live ) {
				$this->save( $offset, null );
			}
			else {
				unset( $this->_object[ $offset ] );
			}
		}

	}

	/**
	 * Get value from array usage $object['offset'];
	 *
	 * @param mixed $offset Used to unset index of Array
	 * @param bool $live Set to false to override current live object saving
	 *
	 * @since 2.3.10
	 */
	public function offsetUnset( $offset ) {

		$this->_offsetUnset( $offset );

	}

	/**
	 * Mapping >> offsetSet for Object access
	 *
	 * @var mixed $offset
	 * @var mixed $value
	 *
	 * @return mixed
	 *
	 * @see offsetSet
	 * @since 2.3.10
	 */
	public function __set( $offset, $value ) {

		return $this->offsetSet( $offset, $value );

	}

	/**
	 * Mapping >> offsetGet for Object access
	 *
	 * @var mixed $offset
	 *
	 * @return mixed
	 *
	 * @see offsetGet
	 * @since 2.3.10
	 */
	public function __get( $offset ) {

		return $this->offsetGet( $offset );

	}

	/**
	 * Mapping >> offsetExists for Object access
	 *
	 * @var mixed $offset
	 *
	 * @return bool
	 *
	 * @see offsetExists
	 * @since 2.3.10
	 */
	public function __isset( $offset ) {

		return $this->offsetExists( $offset );

	}

	/**
	 * Mapping >> offsetUnset for Object access
	 *
	 * @var mixed $offset
	 *
	 * @see offsetUnset
	 * @since 2.3.10
	 */
	public function __unset( $offset ) {

		$this->offsetUnset( $offset );

	}
}