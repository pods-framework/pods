<?php
/**
 * @package Pods
 *
 * Class PodsObject
 */
class PodsObject implements ArrayAccess {

	/**
	 * @var array Pod data
	 */
	private $_pod = array();

	/**
	 * @var array Additional Pod data
	 */
	private $_addtl = array();

	/**
	 * @var array Table info for Pod
	 */
	private $_table_info = array();

	/**
	 * @var bool Set to true to automatically save values in the DB when you $pod['option']='value'
	 */
	private $_live = false;

	/**
	 * @var string Post type / meta key prefix for internal values
	 */
	private $_post_type = '_pods_pod';

	/**
	 * Get the Pod
	 *
	 * @param WP_Post|string $name Get the Pod by Name
	 * @param int $id Get the Pod by ID (overrides $name)
	 * @param bool $live Set to true to automatically save values in the DB when you $pod['option']='value'
	 */
	public function __construct( $name, $id = 0, $live = false ) {

		$id = $this->init( $name, $id );

		if ( 0 < $id ) {
			$this->_live = $live;
		}

	}

	/**
	 * Init the object
	 *
	 * @param string $name Get the Pod by Name
	 * @param int $id Get the Pod by ID (overrides $name)
	 * @param bool $live Set to true to automatically save values in the DB when you $pod['option']='value'
	 *
	 * @return int|bool $id The Pod ID or false if Pod not found
	 */
	public function init( $name = null, $id = 0 ) {

		$_pod = $pod = false;

		// Allow for refresh of object
		if ( null === $name && 0 == $id && $this->is_valid() ) {
			$id = $this->_pod[ 'id' ];

			$this->destroy();
		}

		// Pod ID passed
		if ( 0 < $id ) {
			$_pod = get_post( $dummy = (int) $id, ARRAY_A );

			// Fallback to pod name
			if ( empty( $_pod ) || $this->_post_type != $_pod->post_type ) {
				return $this->init( $name, 0 );
			}
		}
		// WP_Post of Pod data passed
		elseif ( is_object( $name ) && 'WP_Post' == get_class( $name ) && $this->_post_type == $name->post_type ) {
			$_pod = get_object_vars( $name );
		}
		// Fallback for pre-WP_Post
		elseif ( is_object( $name ) && isset( $name->post_type ) && $this->_post_type == $name->post_type ) {
			$_pod = get_post( $dummy = (int) $name->ID, ARRAY_A );
		}
		// Handle custom arrays
		elseif ( is_array( $name ) ) {
			$pod = $name;
		}
		// Internal Pod object
		elseif ( '_pods_pod' == $name ) {
			$pod = array(
				'name' => '_pods_pod',
				'label' => __( 'Pods', 'pods' ),
				'label_singular' => __( 'Pod', 'pods' ),
				'show_in_menu' => 0
			);
		}
		// Internal Pod Field object
		elseif ( '_pods_field' == $name ) {
			$pod = array(
				'name' => '_pods_field',
				'label' => __( 'Pod Fields', 'pods' ),
				'label_singular' => __( 'Pod Field', 'pods' ),
				'show_in_menu' => 0
			);
		}
		// Find Pod by name
		else {
			$find_args = array(
				'name' => $name,
				'post_type' => $this->_post_type,
				'posts_per_page' => 1,
				'post_parent' => 0
			);

			$pod = get_posts( $find_args );

			if ( !empty( $pod ) && is_array( $pod ) ) {
				$_pod = $pod[ 0 ];
			}

			$pod = false;
		}

		if ( !empty( $_pod ) || !empty( $pod ) ) {
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
				'show_in_menu' => 1
			);

			if ( empty( $pod ) ) {
				$pod = array(
					'id' => $_pod[ 'ID' ],
					'name' => $_pod[ 'post_name' ],
					'label' => $_pod[ 'post_title' ],
					'description' => $_pod[ 'post_content' ],
				);
			}

			$pod = array_merge( $defaults, $pod );

			if ( strlen( $pod[ 'label' ] ) < 1 ) {
				$pod[ 'label' ] = $pod[ 'name' ];
			}

			if ( strlen( $pod[ 'label_singular' ] ) < 1 ) {
				$pod[ 'label_singular' ] = $pod[ 'label' ];
			}

			if ( 0 < $pod[ 'id' ] ) {
				$meta = array(
					'type',
					'storage',
					'object',
					'alias',
					'show_in_menu'
				);

				foreach ( $meta as $meta_key ) {
					$value = $this->_meta( $meta_key, $pod[ 'id' ], true );

					if ( null !== $value ) {
						$pod[ $meta_key ] = $value;
					}
				}

				if ( empty( $pod[ 'type' ] ) ) {
					$pod[ 'type' ] = 'post_type';
				}

				if ( empty( $pod[ 'storage' ] ) ) {
					$pod[ 'storage' ] = 'meta';
				}
			}

			$this->_pod = $pod;

			return $this->_pod[ 'id' ];
		}

		return false;

	}

	/**
	 * Check if the pod is a valid
	 *
	 * @return bool
	 */
	public function is_valid() {

		if ( !empty( $this->_pod ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Check if the pod is a valid
	 *
	 * @return bool
	 */
	public function is_custom() {

		if ( !empty( $this->_pod ) && 0 < $this->_pod[ 'id' ] ) {
			return true;
		}

		return false;

	}

	/**
	 * Destroy this pod object and invalidate it
	 */
	public function destroy() {

		$this->_pod = array();
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
	 */
	private function _meta( $meta_key, $id = null, $internal = false ) {

		if ( !$this->is_valid() && null === $id ) {
			return null;
		}

		if ( null === $id ) {
			if ( !empty( $this->_pod ) ) {
				$id = $this->_pod[ 'id' ];
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
	 * Return field array from Pod, a field's data, or a field option
	 *
	 * @param string|null $object_field Object Field name
	 * @param string|null $option Field option
	 *
	 * @return array|mixed
	 *
	 * @since 2.3.10
	 */
	public function fields( $field = null, $option = null ) {

		if ( !isset( $this->_pod[ 'fields' ] ) ) {
			if ( $this->is_custom() ) {
				if ( isset( $this->_pod[ '_fields' ] ) && !empty( $this->_pod[ '_fields' ] ) ) {
					foreach ( $this->_pod[ '_fields' ] as $field ) {
						$field = pods_object_field( $this->_pod[ 'id' ], $field, 0, $this->_live );

						if ( $field->is_valid() ) {
							$this->_pod[ 'fields' ][ $field[ 'name' ] ] = $field;
						}
					}
				}
			}
			else {
				$find_args = array(
					'post_type' => '_pods_field',
					'posts_per_page' => -1,
					'nopaging' => true,
					'post_parent' => $this->_pod[ 'id' ],
					'orderby' => 'menu_order',
					'order' => 'ASC'
				);

				$fields = get_posts( $find_args );

				$this->_pod[ 'fields' ] = array();

				if ( !empty( $fields ) ) {
					foreach ( $fields as $field ) {
						$field = pods_object_field( $this->_pod[ 'id' ], $field, 0, $this->_live );

						if ( $field->is_valid() ) {
							$this->_pod[ 'fields' ][ $field[ 'name' ] ] = $field;
						}
					}
				}
			}
		}

		return $this->_fields( 'fields', $field, $option );

	}

	/**
	 * Return object field array from Pod, a object field's data, or a object field option
	 *
	 * @param string|null $object_field Object Field name
	 * @param string|null $option Field option
	 *
	 * @return array|mixed
	 *
	 * @since 2.3.10
	 */
	public function object_fields( $object_field = null, $option = null ) {

		if ( !isset( $this->_pod[ 'fields' ] ) ) {
			if ( $this->is_custom() ) {
				if ( isset( $this->_pod[ '_object_fields' ] ) && !empty( $this->_pod[ '_object_fields' ] ) ) {
					foreach ( $this->_pod[ '_object_fields' ] as $field ) {
						$this->_pod[ 'object_fields' ] = pods_object_field( $this->_pod[ 'id' ], $field, 0, $this->_live );
					}
				}
			}
			else {
				$this->_pod[ 'object_fields' ] = pods_api()->get_wp_object_fields( $this->_pod[ 'type' ], $this->_pod );
			}
		}

		return $this->_fields( 'object_fields', $object_field, $option );

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
	private function _fields( $fields, $field = null, $option = null, $alt = true ) {

		if ( !$this->is_valid() ) {
			return false;
		}

		$alt_fields = 'object_fields';

		if ( 'object_fields' == $fields ) {
			$alt_fields = 'fields';
		}

		// No fields found
		if ( empty( $this->_pod[ 'fields' ] ) ) {
			$field_data = array();

			// No fields and field not found, get alt field data
			if ( !empty( $field ) && $alt ) {
				$field_data = $this->_fields( $alt_fields, $field, $option, false );
			}
		}
		// Return all fields
		elseif ( empty( $field ) ) {
			$field_data = (array) $this->_pod[ 'fields' ];

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
		elseif ( !isset( $this->_pod[ 'fields' ][ $field ] ) ) {
			$field_data = array();

			// Field not found, get alt field data
			if ( $alt ) {
				$field_data = $this->_fields( $alt_fields, $field, $option, false );
			}
		}
		// Return all field data
		elseif ( empty( $option ) ) {
			$field_data = $this->_pod[ 'fields' ][ $field ];

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
			if ( 'data' == $option && in_array( pods_var_raw( 'type', $this->_pod[ $fields ][ $field ] ), PodsForm::tableless_field_types() ) ) {
				$field_data = PodsForm::field_method( 'pick', 'get_field_data', $this->_pod[ $fields ][ $field ] );
			}
			// Return option
			elseif ( isset( $this->_pod[ $fields ][ $field ][ $option ] ) ) {
				$field_data = $this->_pod[ $fields ][ $field ][ $option ];

				// i18n plugin integration
				if ( 'label' == $option || 0 === strpos( $option, 'label_' ) ) {
					$field_data = __( $field_data );
				}
			}
		}

		return $field_data;

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
			$this->table_info = pods_api()->get_table_info( $this->_pod[ 'type' ], $this->_pod[ 'object' ], $this->_pod[ 'name' ], $this->_pod );
		}

		return $this->_table_info;

	}

    /**
     * Save a Pod by giving an array of option data or set a specific option to a specific value.
     *
     * @param array|string $options Either an associative array of option information or a option name
     * @param mixed $value (optional) Value of the option, if $data is a option name
	 * @param bool $refresh (optional) Refresh the current object
     *
     * @return int|bool The Pod ID or false if failed
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
			return $this->_pod[ 'id' ];
		}

		$params = $options;

		$params[ 'id' ] = $this->_pod[ 'id' ];

		// @todo Move API logic into PodsObject
		$id = pods_api()->save_pod( $params );

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
     * Duplicate a Pod, optionally giving an array of option data or set a specific option to a specific value.
     *
     * @param array|string $options (optional) Either an associative array of option information or a option name
     * @param mixed $value (optional) Value of the option, if $data is a option name
	 * @param bool $replace (optional) Replace the current object
     *
     * @return int|bool The new Pod ID or false if failed
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
			return $this->_pod[ 'id' ];
		}

		$params = $options;

		$params[ 'id' ] = $this->_pod[ 'id' ];
		$params[ 'name' ] = $this->_pod[ 'name' ];

		// @todo Move API logic into PodsObject
		$id = pods_api()->duplicate_pod( $params );

		if ( $replace ) {
			// Replace object
			$id = $this->init( null, $id );
		}

		return $id;

	}

    /**
     * Delete the Pod
     *
     * @return bool Whether the Pod was successfully deleted
     *
     * @since 2.3.10
     */
	public function delete() {

		$params = array(
			'id' => $this->_pod[ 'id' ],
			'name' => $this->_pod[ 'name' ]
		);

		$success = false;

		if ( 0 < $params[ 'id' ] ) {
			// @todo Move API logic into PodsObject
			$success = pods_api()->delete_pod( $params );
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
	 * @since 2.3.10
	 */
	public function offsetSet( $offset, $value, $live = true ) {

		if ( $live && $this->_live ) {
			$this->save( $offset, $value );
		}
		else {
			$this->_pod[ $offset ] = $value;
		}

	}

	/**
	 * Get value from array usage $object['offset'];
	 *
	 * @param mixed $offset Used to get value of Array
	 *
	 * @return mixed|null
	 * @since 2.3.10
	 */
	public function offsetGet( $offset ) {

		// Keys that are methods
		$methods = array(
			'fields',
			'object_fields',
			'table_info'
		);

		// Deprecated keys and their correct keys
		$deprecated_keys = array(
			'ID' => 'id',
			'post_title' => 'label',
			'post_name' => 'name',
			'post_content' => 'description'
		);

		if ( in_array( $offset, $methods ) ) {
			$value = call_user_func( $this, $offset );
		}
		elseif ( 'options' == $offset ) {
			$value = null;

			if ( pods_allow_deprecated() ) {
				$value = $this->_pod;
			}
		}
		elseif ( isset( $this->_pod[ $offset ] ) ) {
			$value = $this->_pod[ $offset ];

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
		elseif ( isset( $deprecated_keys[ $offset ] ) ) {
			$value = null;

			if ( pods_allow_deprecated() ) {
				$value = $this->offsetGet( $deprecated_keys[ $offset ] );
			}
			else {
				pods_deprecated( '$pod[\'' . $offset .'\']', '2.0', '$pod[\'' . $deprecated_keys[ $offset ] . '\']' );
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

		if ( isset( $this->_pod[ $offset ] ) ) {
			if ( $live && $this->_live ) {
				$this->save( $offset, null );
			}
			else {
				unset( $this->_pod[ $offset ] );
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