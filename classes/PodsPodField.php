<?php
/**
 * @package Pods
 *
 * Class PodsPodField
 */
class PodsPodField implements ArrayAccess {

	/**
	 * @var array Field data
	 */
	private $_field = array();

	/**
	 * @var array Additional Field data
	 */
	private $_addtl = array();

	/**
	 * @var array Table info for Pod field
	 */
	private $_table_info = array();

	/**
	 * @var bool Set to true to automatically save values in the DB when you $pod['option']='value'
	 */
	private $_live = false;

	/**
	 * @var string Meta key prefix for internal values
	 */
	private $_meta_prefix = '_pods_field_';

	/**
	 * Get the Pod Field
	 *
	 * @param PodsPod|WP_Post|int|string $pod PodsPod/int/string of Pod, or WP_Post object for field
	 * @param string $name Get the Field by Name
	 * @param int $id Get the Field by ID (overrides $name)
	 * @param bool $live Set to true to automatically save values in the DB when you $field['option']='value'
	 *
	 * @since 2.3.10
	 */
	public function __construct( $pod, $name = null, $id = 0, $live = false ) {

		$id = $this->init( $pod, $name, $id );

		if ( 0 < $id ) {
			$this->_live = $live;
		}

	}

	/**
	 * Init the object
	 *
	 * @param PodsPod|WP_Post|int|string $pod PodsPod/int/string of Pod, or WP_Post object for field
	 * @param string $name Get the Field by Name
	 * @param int $id Get the Field by ID (overrides $name)
	 * @param bool $live Set to true to automatically save values in the DB when you $field['option']='value'
	 *
	 * @return int|bool $id The Field ID or false if Field not found
	 */
	public function init( $pod = null, $name = null, $id = 0 ) {

		$_field = $field = false;

		// Allow for refresh of object
		if ( null === $pod && null === $name && 0 == $id && $this->is_valid() ) {
			$id = $this->_field[ 'id' ];

			$this->destroy();
		}

		// Pod ID passed
		$pod_id = $pod;

		// Pod object passed
		if ( is_object( $pod_id ) && isset( $pod_id->id ) ) {
			$pod_id = $pod_id->id;
		}
		// Pod array passed
		elseif ( is_array( $pod_id ) && isset( $pod_id[ 'id' ] ) ) {
			$pod_id = $pod_id[ 'id' ];
		}

		// Field ID passed
		if ( 0 < $id ) {
			$_field = get_post( $dummy = (int) $id, ARRAY_A );

			// Fallback to pod and field name
			if ( empty( $_field ) || '_pods_field' != $_field->post_type ) {
				return $this->init( $pod_id, $name, 0 );
			}
		}
		// WP_Post of Field data passed
		elseif ( is_object( $name ) && 'WP_Post' == get_class( $name ) && '_pods_field' == $name->post_type ) {
			$_field = get_object_vars( $name );
		}
		// Fallback for pre-WP_Post
		elseif ( is_object( $name ) && isset( $name->post_type ) && '_pods_field' == $name->post_type ) {
			$_field = get_post( $dummy = (int) $name->ID, ARRAY_A );
		}
		// Handle custom arrays
		elseif ( is_array( $name ) ) {
			$field = $name;
		}
		// Find Field by name
		elseif ( 0 < $pod_id ) {
			$find_args = array(
				'name' => $name,
				'post_type' => '_pods_field',
				'posts_per_page' => 1,
				'post_parent' => $pod_id
			);

			$field = get_posts( $find_args );

			if ( !empty( $field ) && is_array( $field ) ) {
				$_field = $field[ 0 ];
			}

			$field = false;
		}

		if ( !empty( $_field ) || !empty( $field ) ) {
			$defaults = array(
				'id' => 0,
				'name' => '',
				'label' => '',
				'description' => '',
				'type' => 'text',
				'weight' => 0,
				'pod_id' => $pod_id
			);

			if ( empty( $field ) ) {
				$field = array(
					'id' => $_field[ 'ID' ],
					'name' => $_field[ 'post_name' ],
					'label' => $_field[ 'post_title' ],
					'description' => $_field[ 'post_content' ],
					'weight' => $_field[ 'menu_order' ],
					'pod_id' => $_field[ 'post_parent' ],
					'type' => 'text'
				);
			}

			$field = array_merge( $defaults, $field );

			if ( strlen( $field[ 'label' ] ) < 1 ) {
				$field[ 'label' ] = $field[ 'name' ];
			}

			$tableless_meta = array(
				'pick_object',
				'pick_val',
				'sister_id'
			);

			if ( 0 < $field[ 'id' ] ) {
				$meta = array(
					'type'
				);

				foreach ( $meta as $meta_key ) {
					$value = $this->_meta( $meta_key, $field[ 'id' ], true );

					if ( null !== $value ) {
						$field[ $meta_key ] = $value;
					}
				}

				if ( empty( $field[ 'type' ] ) ) {
					$field[ 'type' ] = 'text';
				}

				if ( in_array( $field[ 'type' ], PodsForm::tableless_field_types() ) ) {
					foreach ( $tableless_meta as $meta_key ) {
						$value = $this->_meta( $meta_key, $field[ 'id' ], true );

						if ( null !== $value ) {
							$field[ $meta_key ] = $value;
						}
					}

					// Backwards compatibility
					if ( pods_allow_deprecated() && !isset( $field[ 'sister_id' ] ) ) {
						$meta_key = 'sister_field_id';

						$value = $this->_meta( $meta_key, $field[ 'id' ] );

						if ( null !== $value ) {
							$field[ $meta_key ] = $value;
						}
					}
				}
				else {
					foreach ( $tableless_meta as $meta_key ) {
						$field[ $meta_key ] = '';
					}
				}
			}

			if ( in_array( $field[ 'type' ], PodsForm::tableless_field_types() ) ) {
				if ( $field[ 'id' ] < 1 ) {
					// Backwards compatibility
					if ( pods_allow_deprecated() && !isset( $field[ 'sister_id' ] ) && isset( $field[ 'sister_field_id' ] ) ) {
						$field[ 'sister_id' ] = $field[ 'sister_field_id' ];

						unset( $field[ 'sister_field_id' ] );
					}
				}

				foreach ( $tableless_meta as $meta_key ) {
					if ( !isset( $tableless_meta[ $meta_key ] ) || empty( $field[ 'pick_object' ] ) ) {
						$field[ $meta_key ] = '';
					}
				}
			}

			$this->_field = $field;

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

		if ( !empty( $this->_field ) ) {
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

		if ( !empty( $this->_field ) && 0 < $this->_field[ 'id' ] ) {
			return true;
		}

		return false;

	}

	/**
	 * Destroy this pod object and invalidate it
	 */
	public function destroy() {

		$this->_field = array();
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
			if ( !empty( $this->_field ) ) {
				$id = $this->_field[ 'id' ];
			}
			else {
				return null;
			}
		}

		// @todo For 2.4 enable internal prefix
		if ( 1 == 0 && $internal && 0 !== strpos( $meta_key, $this->_meta_prefix ) ) {
			$meta_key = $this->_meta_prefix . $meta_key;
		}

		$value = get_post_meta( $id, $meta_key );

		// @todo For 2.4 enable fallback
		if ( 1 == 0 && pods_allow_deprecated() && is_array( $value ) && empty( $value ) ) {
			if ( 0 === strpos( $meta_key, $this->_meta_prefix ) ) {
				$meta_key = substr( $meta_key, strlen( $this->_meta_prefix ) );
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
	 * Get table info for a Pod
	 *
	 * @return array Table info
	 */
	public function table_info() {

		if ( !$this->is_valid() || !in_array( $this->_field[ 'type' ], PodsForm::tableless_field_types() ) ) {
			return array();
		}

		if ( empty( $this->_table_info ) ) {
			$this->table_info = pods_api()->get_table_info( $this->_field[ 'pick_object' ], $this->_field[ 'pick_val' ], null, null, $this->_field );
		}

		return $this->_table_info;

	}

	/**
     * Save a Field by giving an array of option data or set a specific option to a specific value.
     *
     * @param array|string $options Either an associative array of option information or a option name
     * @param mixed $value (optional) Value of the option, if $data is a option name
	 * @param bool $refresh (optional) Refresh the current object
     *
     * @return int|bool The Field ID or false if failed
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
			return $this->_field[ 'id' ];
		}

		$params = $options;

		$params[ 'id' ] = $this->_field[ 'id' ];

		// @todo Move API logic into PodsPodField
		$id = pods_api()->save_field( $params );

		// Refresh object
		if ( $refresh ) {
			$this->init( null, $id );
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
     * Duplicate a Field, optionally giving an array of option data or set a specific option to a specific value.
     *
     * @param array|string $options (optional) Either an associative array of option information or a option name
     * @param mixed $value (optional) Value of the option, if $data is a option name
	 * @param bool $replace (optional) Replace the current object
     *
     * @return int|bool The new Field ID or false if failed
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

		// @todo Move API logic into PodsPodField
		$id = pods_api()->duplicate_field( $params );

		if ( $replace ) {
			// Replace object
			$this->init( null, $id );
		}

		return $id;

	}

    /**
     * Delete the Field
     *
     * @return bool Whether the field was successfully deleted
     *
     * @since 2.3.10
     */
	public function delete() {

		$params = array(
			'id' => $this->_field[ 'id' ],
			'name' => $this->_field[ 'name' ]
		);

		$success = false;

		if ( 0 < $params[ 'id' ] ) {
			// @todo Move API logic into PodsPodField
			$success = pods_api()->delete_field( $params );
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
			'table_info'
		);

		// Deprecated keys and their correct keys
		$deprecated_keys = array(
			'sister_field_id' => 'sister_id',
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
				$value = $this->_field;
			}
		}
		elseif ( isset( $this->_field[ $offset ] ) ) {
			$value = $this->_field[ $offset ];

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
				pods_deprecated( '$field[\'' . $offset .'\']', '2.0', '$field[\'' . $deprecated_keys[ $offset ] . '\']' );
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
				unset( $this->_field[ $offset ] );
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