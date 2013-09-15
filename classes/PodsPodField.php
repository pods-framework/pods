<?php
/**
 * @package Pods
 *
 * Class PodsPod
 */
class PodsPod implements ArrayAccess {

	/**
	 * @var array Pod data
	 */
	private $_pod = array();

	/**
	 * @var array Table info for Pod
	 */
	private $_table_info = array();

	/**
	 * @var bool Set to true to automatically save values in the DB when you $pod['option']='value'
	 */
	private $_live = false;

	/**
	 * Get the Pod
	 *
	 * @param string $pod_name Get the Pod by Name
	 * @param int $pod_id Get the Pod by ID (overrides $pod_name)
	 * @param bool $live Set to true to automatically save values in the DB when you $pod['option']='value'
	 */
	public function __construct( $pod_name, $pod_id = 0, $live = false ) {

		$_pod = $pod = false;

		// Get meta from DB for the Pod
		$meta = true;

		$api = pods_api();

		if ( 0 < $pod_id ) {
			$_pod = get_post( $dummy = (int) $pod_id, ARRAY_A );
		}
		elseif ( '_pods_pod' == $pod_name ) {
			$pod = array(
				'name' => '_pods_pod',
				'label' => __( 'Pods', 'pods' ),
				'label_singular' => __( 'Pod', 'pods' ),
				'show_in_menu' => 0
			);

			$meta = false;
		}
		else {
			$pod = get_posts( array(
								   'name' => $pod_name,
								   'post_type' => '_pods_pod',
								   'posts_per_page' => 1
							  ) );

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
					'description' => $_pod[ 'post_content' ]
				);
			}

			$pod = array_merge( $defaults, $pod );

			if ( strlen( $pod[ 'label' ] ) < 1 ) {
				$pod[ 'label' ] = $pod[ 'name' ];
			}

			if ( strlen( $pod[ 'label_singular' ] ) < 1 ) {
				$pod[ 'label_singular' ] = $pod[ 'label' ];
			}

			if ( $meta ) {
				$meta = get_post_meta( $pod[ 'id' ] );

				foreach ( $meta as $option => $value ) {
					if ( is_array( $value ) ) {
						foreach ( $value as $k => $v ) {
							if ( !is_array( $v ) ) {
								$value[ $k ] = maybe_unserialize( $v );
							}
						}

						if ( 1 == count( $value ) ) {
							$value = current( $value );
						}
					}
					else {
						$value = maybe_unserialize( $value );
					}

					$meta[ $option ] = $value;
				}

				$pod = array_merge( $meta, $pod );
			}

			$pod[ 'fields' ] = array();

			$pod[ 'object_fields' ] = array();

			if ( 'pod' != $pod[ 'type' ] ) {
				$pod[ 'object_fields' ] = $api->get_wp_object_fields( $pod[ 'type' ], $pod );
			}

			$fields = get_posts( array(
									  'post_type' => '_pods_field',
									  'posts_per_page' => -1,
									  'nopaging' => true,
									  'post_parent' => $pod[ 'id' ],
									  'orderby' => 'menu_order',
									  'order' => 'ASC'
								 ) );

			if ( !empty( $fields ) ) {
				foreach ( $fields as $field ) {
					$field->pod = $pod[ 'name' ];

					$field = $api->load_field( $field );

					$pod[ 'fields' ][ $field[ 'name' ] ] =& $field;
				}
			}
		}

		if ( !empty( $this->_pod ) ) {
			$this->_live = $live;
		}

	}

	/**
	 * Check if the pod is a valid
	 *
	 * @return bool
	 */
	public function valid() {

		if ( !empty( $this->_pod ) ) {
			return true;
		}

		return false;

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
			$this->_pod[ 'fields' ] = pods_api()->get_wp_object_fields( $this->_pod[ 'type' ], $this->_pod );
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
			$this->_pod[ 'object_fields' ] = pods_api()->get_wp_object_fields( $this->_pod[ 'type' ], $this->_pod );
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

		if ( empty( $this->_table_info ) ) {
			$this->table_info = pods_api()->get_table_info( $this->_pod[ 'type' ], $this->_pod[ 'object' ], $this->_pod[ 'name' ], $this->_pod );
		}

		return $this->_table_info;

	}

	/**
	 * Set value from array usage $object['offset'] = 'value';
	 *
	 * @param mixed $offset Used to set index of Array or Variable name on Object
	 * @param mixed $value Value to be set
	 *
	 * @return mixed|void
	 * @since 2.3.10
	 */
	public function offsetSet( $offset, $value ) {

		$this->_pod[ $offset ] = $value;

		if ( $this->_live ) {
			pods_api()->save_pod( array( 'name' => $this->_pod[ 'name' ], $offset => $value ) );
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

		$value = null;

		// Keys that are methods
		$methods = array(
			'fields',
			'object_fields',
			'table_info'
		);

		if ( in_array( $offset, $methods ) ) {
			$value = call_user_func( $this, $offset );
		}
		elseif ( 'options' == $offset ) {
			$value = $this->_pod;
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

		return isset( $this->_pod[ $offset ] );

	}

	/**
	 * Get value from array usage $object['offset'];
	 *
	 * @param mixed $offset Used to unset index of Array
	 *
	 * @since 2.3.10
	 */
	public function offsetUnset( $offset ) {

		if ( isset( $this->_pod[ $offset ] ) ) {
			unset( $this->_pod[ $offset ] );

			if ( $this->_live ) {
				pods_api()->save_pod( array( 'name' => $this->_pod[ 'name' ], $offset => null ) );
			}
		}

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