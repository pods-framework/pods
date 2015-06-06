<?php
/**
 * @package Pods
 * @category Object Types
 */

/**
 * Class Pods_Object
 *
 * @property int|string $id Object ID
 * @property string $name Object Name
 * @property string $label Object Label
 * @property string $description Object Description
 * @property int|string $parent_id Parent ID
 */
class Pods_Object implements
	ArrayAccess,
	Serializable {

	/**
	 * Object data
	 *
	 * @var array
	 */
	protected $_object = array();

	/**
	 * Object meta
	 *
	 * @var array
	 */
	protected $_meta = array();

	/**
	 * Additional overrides
	 *
	 * @var array
	 */
	protected $_override = array();

	/**
	 * Keys that have changed
	 *
	 * @var array
	 */
	protected $_changed = array();

	/**
	 * Object fields
	 *
	 * @var array
	 */
	protected $_fields = array();

	/**
	 * Object fields for WP objects
	 *
	 * @var array
	 */
	protected $_object_fields = array();

	/**
	 * Object Groups
	 *
	 * @var array
	 */
	protected $_groups = array();

	/**
	 * Table info for Object
	 *
	 * @var array
	 */
	protected $_table_info = array();

	/**
	 * Options for Object
	 *
	 * @var array
	 */
	protected $_options = array();

	/**
	 * Options for Pods_Form
	 *
	 * @var array
	 */
	protected $_form_options = array(
		'default'        => null,

		'attributes'     => array(),

		'group'          => 0,
		'grouped'        => 0,

		'dependency'     => false,
		'depends-on'     => array(),
		'excludes-on'    => array(),

		'developer_mode' => false
	);

	/**
	 * Set to true to automatically save values in the DB when you $object['option']='value'
	 *
	 * @var bool
	 */
	protected $_live = false;

	/**
	 * Array of actions and their associated methods / number of args for safely running actions on
	 *
	 * @var array
	 */
	protected $_live_actions = array(
		'pods_object_save'   => array(
			'method' => '_update',
			'args'   => 3
		),
		'pods_object_delete' => array(
			'method' => '_delete',
			'args'   => 3
		)
	);

	/**
	 * Post type / meta key prefix for internal values
	 *
	 * @var string
	 */
	protected $_post_type;

	/**
	 * Action type for internal actions/filters
	 *
	 * @var string
	 */
	protected $_action_type;

	/**
	 * Deprecated keys / options
	 *
	 * @var array
	 */
	protected $_deprecated_keys = array(
		'ID'           => 'id',
		'post_title'   => 'label',
		'post_name'    => 'name',
		'post_content' => 'description',
		'post_parent'  => 'parent_id'
	);

	/**
	 * List of core fields utilized for Post Type
	 *
	 * @var array
	 */
	protected $_core_fields = array(
		'ID',
		'post_title',
		'post_name',
		'post_content',
		'post_parent'
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
	 * @param string|array|WP_Post $name   Get the Object by Name, or pass an array/WP_Post of Object
	 * @param int                  $id     Get the Object by ID (overrides $name)
	 * @param bool                 $live   Set to true to automatically save values in the DB when you $object['option']='value'
	 * @param mixed                $parent Parent Object or ID
	 *
	 * @since 3.0.0
	 */
	public function __construct( $name, $id = 0, $live = false, $parent = null ) {

		if ( is_serialized( $name ) ) {
			$this->unserialize( $name );

			if ( $this->is_valid() ) {
				$id = $this->_object[ 'id' ];
			}
		} else {
			$id = $this->load( $name, $id, $parent );
		}

		if ( 0 < $id ) {
			$this->_live = $live;
		}

		add_action( 'switch_blog', array( $this, 'table_info_clear' ) );

		$this->_action_type = str_replace( '_pods_', '', $this->_post_type );

		foreach ( $this->_live_actions as $action => $_options ) {
			add_action( $action . '_' . $this->_action_type, array( $this, $_options['method'] ), 10, $_options['args'] );
		}

	}

	/**
	 * Load the object
	 *
	 * @param string|array|WP_Post $name   Get the Object by Name, or pass an array/WP_Post of Object
	 * @param int                  $id     Get the Object by ID (overrides $name)
	 * @param mixed                $parent Parent Object or ID
	 *
	 * @return int|bool $id The Object ID or false if Object not found
	 *
	 * @since 3.0.0
	 */
	public function load( $name = null, $id = 0, $parent = null ) {

		// Post Object
		$_object = false;

		// Custom Object
		$object = false;

		// Allow for refresh of object
		if ( null === $name && 0 == $id && null === $parent && $this->is_valid() ) {
			$id = $this->_object['id'];

			$this->destroy();
		}

		// Empty object
		if ( null === $name && 0 == $id && null === $parent ) {
			return false;
		}

		// Parent ID passed
		$parent_id = $parent;

		// Parent object passed
		if ( is_object( $parent_id ) && isset( $parent_id->id ) ) {
			$parent_id = $parent_id->id;
		} // Parent array passed
		elseif ( is_array( $parent_id ) && isset( $parent_id['id'] ) ) {
			$parent_id = $parent_id['id'];
		}

		$parent_id = (int) $parent_id;

		// Object ID passed
		if ( 0 < $id ) {
			$_object = get_post( (int) $id, ARRAY_A );

			// Fallback to Object name
			if ( empty( $_object ) || $this->_post_type != $_object['post_type'] ) {
				return $this->load( $name, 0 );
			}
		} // WP_Post of Object data passed
		elseif ( is_object( $name ) && 'WP_Post' == get_class( $name ) && $this->_post_type == $name->post_type ) {
			$_object = get_object_vars( $name );
		} // Fallback for pre-WP_Post
		elseif ( is_object( $name ) && isset( $name->post_type ) && $this->_post_type == $name->post_type ) {
			$_object = get_post( (int) $name->ID, ARRAY_A );
		} // Handle custom arrays
		elseif ( is_array( $name ) ) {
			$object = $name;
		} // Find Object by name
		elseif ( ! is_object( $name ) && 0 < strlen( $name ) ) {
			$find_args = array(
				'name'           => $name,
				'post_type'      => $this->_post_type,
				'posts_per_page' => 1,
				'post_parent'    => $parent_id
			);

			$find_object = get_posts( $find_args );

			// Object found
			if ( ! empty( $find_object ) && is_array( $find_object ) ) {
				$_object = $find_object[0];

				if ( 'WP_Post' == get_class( $_object ) ) {

					/**
					 * @var WP_Post $_object
					 */
					$_object = $_object->to_array();
				} else {
					$_object = get_object_vars( $_object );
				}
			}
		}

		if ( ! empty( $_object ) || ! empty( $object ) ) {
			$defaults = array(
				'id'          => 0,
				'name'        => '',
				'label'       => '',
				'description' => '',
				'parent_id'   => $parent_id
			);

			if ( ! empty( $_object ) ) {
				$object = array(
					'id'          => $_object['ID'],
					'name'        => $_object['post_name'],
					'label'       => $_object['post_title'],
					'description' => $_object['post_content'],
					'parent_id'   => $_object['post_parent'],
				);
			}

			$object = array_merge( $defaults, $object );

			if ( strlen( $object['label'] ) < 1 ) {
				$object['label'] = $object['name'];
			}

			$this->_object = $object;

			return $this->_object['id'];
		}

		return false;

	}

	/**
	 * Check if the object exists
	 *
	 * @param string|array|WP_Post $name   Get the Object by Name, or pass an array/WP_Post of Object
	 * @param int                  $id     Get the Object by ID (overrides $name)
	 * @param mixed                $parent Parent Object or ID
	 *
	 * @return int|bool $id The Object ID or false if Object not found
	 *
	 * @since 3.0.0
	 */
	public function exists( $name = null, $id = 0, $parent = null ) {

		$pod = pods_object( $name, $id, false, $parent );

		if ( ! empty( $pod ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Check if the Object is a valid
	 *
	 * @param bool $strict
	 *
	 * @return bool
	 *
	 * @since 3.0.0
	 */
	public function is_valid( $strict = false ) {

		if ( ! empty( $this->_object ) && ( ! $strict || ! $this->is_custom() ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Check if the Object is custom
	 *
	 * @return bool
	 *
	 * @since 3.0.0
	 */
	public function is_custom() {

		$custom = false;

		if ( empty( $this->_object ) || ! isset( $this->_object['id'] ) || $this->_object['id'] < 1 ) {
			$custom = true;
		}

		return $custom;

	}

	/**
	 * Destroy this Object and invalidate it
	 *
	 * @since 3.0.0
	 */
	public function destroy() {

		$this->_object     = array();
		$this->_meta       = array();
		$this->_table_info = array();

		$this->_live = false;

	}

	/**
	 * Get a list of all meta keys that have changed
	 *
	 * @return array
	 *
	 * @since 3.0.0
	 */
	public function changed() {

		$changed = array();

		foreach ( $this->_changed as $field ) {
			$changed[ $field ] = $this->offsetGet( $field );
		}

		return $changed;

	}

	/**
	 * Merge overrides of options for Objects
	 *
	 * @param array|object|Pods_Object $data Data override
	 *
	 * @return $this
	 *
	 * @since 3.0.0
	 */
	public function override( $data ) {

		if ( is_object( $data ) ) {
			if ( 0 === strpos( get_class( $data ), 'Pods_Object' ) ) {
				$data = $data->export();
			} else {
				$data = get_object_vars( $data );
			}
		}

		foreach ( $data as $field => $field_data ) {
			if ( $this->offsetGet( $field ) != $field_data ) {
				if ( ! in_array( $field, $this->_core_fields ) && ! in_array( $field, $this->_deprecated_keys ) && ! in_array( $field, $this->_changed ) ) {
					$this->_changed[] = $field;
				}

				$this->_override[ $field ] = $field_data;
			}
		}

		return $this;

	}

	/**
	 * Save overrides of options for Objects
	 *
	 * @since 3.0.0
	 */
	public function override_save() {

		foreach ( $this->_override as $field => $field_data ) {
			unset( $this->_override[ $field ] );

			$this->offsetSet( $field, $field_data );
		}

		return $this;

	}

	/**
	 * Merge default options for Objects
	 *
	 * @param array|object|Pods_Object $data Data override
	 *
	 * @return $this
	 *
	 * @since 3.0.0
	 */
	public function defaults( $data ) {

		if ( is_object( $data ) ) {
			if ( 0 === strpos( get_class( $data ), 'Pods_Object' ) ) {
				$data = $data->export();
			} else {
				$data = get_object_vars( $data );
			}
		}

		foreach ( $data as $field => $field_data ) {
			if ( ! isset( $this->_object[ $field ] ) && ! isset( $this->_meta[ $field ] ) && null === $this->offsetGet( $field ) ) {
				if ( ! in_array( $field, $this->_changed ) ) {
					$this->_changed[] = $field;
				}

				$this->_override[ $field ] = $field_data;
			}

		}

		return $this;

	}

	/**
	 * Safely perform an action for multiple object instances
	 *
	 * @param $action
	 *
	 * @since 3.0.0
	 */
	public function _action( $action ) {

		$args    = func_get_args();
		$args[0] = $action . '_' . $this->_action_type;

		$_live_action = false;

		if ( isset( $this->_live_actions[ $action ] ) ) {
			$_live_action = $this->_live_actions[ $action ];
		}

		if ( ! empty( $_live_action ) ) {
			remove_action( $args[0], array( $this, $_live_action['method'] ), 10 );
		}

		call_user_func_array( 'do_action', $args );

		if ( ! empty( $_live_action ) ) {
			add_action( $args[0], array( $this, $_live_action['method'] ), 10, $_live_action['args'] );
		}

	}

	/**
	 * Update object if in live mode, and other object was updated
	 *
	 * @param int    $id     Object ID
	 * @param string $name   Object name
	 * @param int    $parent Object Parent ID
	 *
	 * @since 3.0.0
	 */
	public function _update( $id, $name, $parent ) {

		if ( $this->is_valid( true ) && $this->_live ) {
			if ( 0 < $id ) {
				if ( $id == $this->_object['id'] ) {
					$this->destroy();

					$this->load( null, $id );
				}
			} elseif ( 0 < strlen( $name ) ) {
				if ( $name == $this->_object['name'] && $parent == $this->_object['parent'] ) {
					$this->destroy();

					$this->load( $name );
				}
			}
		}

	}

	/**
	 * Update object if in live mode, and other object was deleted
	 *
	 * @param int    $id     Object ID
	 * @param string $name   Object name
	 * @param int    $parent Object Parent ID
	 *
	 * @since 3.0.0
	 */
	public function _delete( $id, $name, $parent ) {

		if ( $this->is_valid( true ) && $this->_live ) {
			if ( 0 < $id ) {
				if ( $id == $this->_object['id'] ) {
					$this->destroy();
				}
			} elseif ( 0 < strlen( $name ) ) {
				if ( $name == $this->_object['name'] && $parent == $this->_object['parent'] ) {
					$this->destroy();
				}
			}
		}

	}

	/**
	 * Get meta from the object
	 *
	 * @param string   $meta_key Meta key name
	 * @param null|int $id       Object ID
	 * @param bool     $internal If this is an internal meta value
	 * @param bool     $strict   Whether to enforce null returns for more cases
	 *
	 * @return array|mixed|null
	 *
	 * @since 3.0.0
	 */
	public function _meta( $meta_key = null, $id = null, $internal = false, $strict = false ) {

		if ( (int) $id < 1 ) {
			if ( ! $this->is_valid() ) {
				return null;
			} elseif ( $this->is_custom() && isset( $this->_object[ $meta_key ] ) ) {
				return $this->_object[ $meta_key ];
			}
		}

		if ( 'post_type' == $meta_key ) {
			return $this->_post_type;
		} elseif ( isset( $this->_object[ $meta_key ] ) && 0 < strlen( $this->_object[ $meta_key ] ) ) {
			return $this->_object[ $meta_key ];
		}

		if ( (int) $id < 1 ) {
			$id = $this->_object['id'];
		}

		if ( null === $meta_key ) {
			$values = get_post_meta( $id );

			if ( ! empty( $values ) ) {
				foreach ( $values as $key => $value ) {
					if ( ! isset( $this->_object[ $key ] ) && ! isset( $this->_meta[ $key ] ) ) {
						// Field not found
						if ( empty( $value ) || ( $strict && 1 == count( $value ) && '' === $value[0] ) ) {
							$value = null;
						} else {
							foreach ( $value as $k => $v ) {
								if ( ! is_array( $v ) ) {
									$value[ $k ] = maybe_unserialize( $v );
								}
							}

							if ( 1 == count( $value ) ) {
								$value = current( array_values( $value ) );
							}
						}

						if ( null !== $value ) {
							if ( isset( $this->_options[ $key ] ) ) {
								$tableless_field_types = Pods_Form::tableless_field_types();

								if ( in_array( $this->_options[ $key ]['type'], $tableless_field_types ) ) {
									$this->_options[ $key ]['pick_simple'] = 1;

									$value = Pods_Form::field_method( 'pick', 'simple_value', $key, $value, $this->_options[ $key ], null, 0, true );
								}

								$value = Pods_Form::value( $this->_options[ $key ]['type'], $value, $key, $this->_options[ $key ], null, 0 );
							}

							$this->_meta[ $key ] = $value;
						}
					}
				}
			}

			return $this->_meta;
		}

		// @todo For 3.0 enable internal prefix
		if ( 1 == 0 && $internal && 0 !== strpos( $meta_key, '_pods_' ) ) {
			$meta_key = '_pods_' . $meta_key;
		}

		$value = get_post_meta( $id, $meta_key );

		// @todo For 3.0 enable fallback
		if ( 1 == 0 && pods_allow_deprecated() && is_array( $value ) && empty( $value ) ) {
			if ( 0 === strpos( $meta_key, '_pods_' ) ) {
				$meta_key = substr( $meta_key, strlen( '_pods_' ) );
			}

			$value = get_post_meta( $id, $meta_key );
		}

		if ( is_array( $value ) ) {
			// Field not found
			if ( empty( $value ) || ( $strict && 1 == count( $value ) && '' === $value[0] ) ) {
				$value = null;
			} else {
				foreach ( $value as $k => $v ) {
					if ( ! is_array( $v ) ) {
						$value[ $k ] = maybe_unserialize( $v );
					}
				}

				if ( 1 == count( $value ) ) {
					$value = current( array_values( $value ) );
				}
			}
		} // Field not found
		elseif ( $strict && false === $value ) {
			$value = null;
		} else {
			$value = maybe_unserialize( $value );
		}

		if ( isset( $this->_options[ $meta_key ] ) ) {
			$tableless_field_types = Pods_Form::tableless_field_types();

			if ( in_array( $this->_options[ $meta_key ]['type'], $tableless_field_types ) ) {
				$this->_options[ $meta_key ]['pick_simple'] = 1;

				$value = Pods_Form::field_method( 'pick', 'simple_value', $meta_key, $value, $this->_options[ $meta_key ], null, 0, true );
			}

			$value = Pods_Form::value( $this->_options[ $meta_key ]['type'], $value, $meta_key, $this->_options[ $meta_key ], null, 0 );
		}

		return $value;

	}

	/**
	 * Return field array from $fields, a field's data, or a field option
	 *
	 * @param string      $fields Field key name
	 * @param string|null $field  Field name
	 * @param string|null $option Field option
	 * @param bool        $alt    Set to true to check alternate fields array
	 *
	 * @return bool|mixed
	 *
	 * @since 3.0.0
	 */
	public function _fields( $fields, $field = null, $option = null, $alt = true ) {

		if ( ! $this->is_valid() ) {
			if ( null === $field && null === $option ) {
				return array();
			}

			return false;
		}

		$all_fields =& $this->_fields;
		$alt_fields = 'object_fields';

		if ( 'object_fields' == $fields ) {
			$all_fields =& $this->_object_fields;
			$alt_fields = 'fields';
		} elseif ( 'groups' == $fields ) {
			$all_fields =& $this->_groups;
			$alt_fields = null;
		}

		// No fields found
		if ( empty( $all_fields ) ) {
			$field_data = array();

			// No fields and field not found, get alt field data
			if ( ! empty( $field ) && $alt && ! empty( $alt_fields ) ) {
				$field_data = $this->_fields( $alt_fields, $field, $option, false );
			}
		} // Return all fields
		elseif ( empty( $field ) ) {
			$field_data =& $all_fields;

			if ( ! $this->_live ) {
				foreach ( $field_data as $field_name => $fields ) {
					foreach ( $fields as $field_option => $field_value ) {
						// i18n plugin integration
						if ( 'label' == $field_option || 0 === strpos( $field_option, 'label_' ) ) {
							$field_data[ $field_name ][ $field_option ] = __( $field_value );
						}
					}
				}
			}
		} // Field not found
		elseif ( ! isset( $all_fields[ $field ] ) ) {
			$field_data = false;

			// Field not found, get alt field data
			if ( $alt && ! empty( $alt_fields ) ) {
				$field_data = $this->_fields( $alt_fields, $field, $option, false );
			}
		} // Return all field data
		elseif ( empty( $option ) ) {
			$field_data =& $all_fields[ $field ];

			if ( ! $this->_live ) {
				foreach ( $field_data as $field_option => $field_value ) {
					// i18n plugin integration
					if ( 'label' == $field_option || 0 === strpos( $field_option, 'label_' ) ) {
						$field_data[ $field_option ] = __( $field_value );
					}
				}
			}
		} // Get an option from a field
		else {
			$field_data = null;

			// Get a list of available items from a relationship field
			if ( 'data' == $option && in_array( pods_v( 'type', $this->_object[ $fields ][ $field ] ), Pods_Form::tableless_field_types() ) ) {
				$field_data = Pods_Form::field_method( 'pick', 'get_field_data', $this->_object[ $fields ][ $field ] );
			} // Return option
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
	 * @since 3.0.0
	 */
	public function _fill() {

		// @todo Fill in all built-in fields

		// @todo Pull in custom meta keys

		foreach ( $this->_methods as $method ) {
			call_user_func( array( $this, $method ) );
		}

	}

	/**
	 * Export the object data into a normal array
	 *
	 * @param array|string $export_types Export type: all|data|fields|object_fields|table_info
	 *
	 * @return array Exported array of all object data
	 *
	 * @since 3.0.0
	 */
	public function export( $export_types = 'all' ) {

		$export = array();

		if ( 'all' == $export_types ) {
			$export_types = array(
				'data',
				'fields',
				'object_fields',
				//'table_info',
				'groups'
			);

			$export_types = array_merge( $export_types, $this->_methods );
			$export_types = array_unique( $export_types );
		} else {
			$export_types = (array) $export_types;
		}

		if ( in_array( 'data', $export_types ) ) {
			$this->_meta( null, null, false, true );

			$export = array_merge( $this->_object, $this->_meta, $this->_override );
		}

		foreach ( $this->_methods as $method ) {
			if ( ! in_array( $method, $export_types ) ) {
				continue;
			}

			if ( method_exists( $this, $method . '_export' ) ) {
				$export[ $method ] = call_user_func( array( $this, $method . '_export' ) );
			} else {
				$export[ $method ] = call_user_func( array( $this, $method ) );
			}

			if ( null === $export[ $method ] ) {
				unset( $export[ $method ] );
			}
		}

		if ( 1 == count( $export_types ) ) {
			$export_type = current( array_values( $export_types ) );

			if ( 'data' != $export_type ) {
				if ( isset( $export[ $export_type ] ) ) {
					$export = $export[ $export_type ];
				} else {
					$export = array();
				}
			}
		}

		return $export;

	}

	/**
	 * Export field array from Object
	 *
	 * @return array|mixed
	 *
	 * @since 3.0.0
	 */
	public function fields_export() {

		$fields = $this->fields( null, null );

		/**
		 * @var Pods_Object_Field $field_object
		 */
		foreach ( $fields as $field => $field_object ) {
			if ( is_object( $field_object ) ) {
				$fields[ $field ] = $field_object->export();
			}
		}

		return $fields;

	}

	/**
	 * Export object field array from Object
	 *
	 * @return array|mixed
	 *
	 * @since 3.0.0
	 */
	public function object_fields_export() {

		$object_fields = $this->object_fields( null, null );

		/**
		 * @var Pods_Object_Field $field_object
		 */
		foreach ( $object_fields as $field => $field_object ) {
			if ( is_object( $field_object ) ) {
				$object_fields[ $field ] = $field_object->export();
			}
		}

		return $object_fields;

	}

	/**
	 * Export object field array from Object
	 *
	 * @return array|mixed
	 *
	 * @since 3.0.0
	 */
	public function table_info_export() {

		$table_info = $this->table_info();

		if ( empty( $table_info ) ) {
			return null;
		}

		$exportable = array(
			//'pod',
			//'object_fields'
		);

		foreach ( $exportable as $key ) {
			if ( isset( $table_info[ $key ] ) ) {
				if ( is_array( $table_info[ $key ] ) ) {
					foreach ( $table_info[ $key ] as $field => $field_data ) {
						if ( is_object( $field_data ) ) {
							$table_info[ $key ][ $field ] = $field_data->export();
						}
					}
				} elseif ( is_object( $table_info[ $key ] ) ) {
					$table_info[ $key ] = $table_info[ $key ]->export();
				}
			}
		}

		return $table_info;

	}

	/**
	 * Export group array from Object
	 *
	 * @return array|mixed
	 *
	 * @since 3.0.0
	 */
	public function groups_export() {

		$groups = $this->groups();

		foreach ( $groups as $group => $group_object ) {
			if ( is_object( $group_object ) ) {
				$groups[ $group ] = $group_object->export();
			}
		}

		return $groups;

	}

	/**
	 * Return field array from Object, a field's data, or a field option
	 *
	 * @param string|null $field  Object Field name
	 * @param string|null $option Field option
	 * @param bool        $alt    Set to true to check alternate fields array
	 *
	 * @return array|mixed
	 *
	 * @since 3.0.0
	 */
	public function fields( $field = null, $option = null, $alt = true ) {

		if ( ! $this->is_valid() ) {
			return array();
		}

		if ( empty( $this->_fields ) ) {
			$this->_fields = array();

			if ( $this->is_custom() ) {
				if ( ! empty( $this->_object['_fields'] ) ) {
					foreach ( $this->_object['_fields'] as $object_field ) {
						$object_field = pods_object_field( $object_field, 0, $this->_live, $this->_object['id'] );

						if ( $object_field->is_valid() ) {
							$this->_fields[ $object_field['name'] ] = $object_field;
						}
					}
				}
			} else {
				$find_args = array(
					'post_type'      => '_pods_field',
					'posts_per_page' => -1,
					'nopaging'       => true,
					'post_parent'    => $this->_object['id'],
					'orderby'        => 'menu_order',
					'order'          => 'ASC'
				);

				$fields = get_posts( $find_args );

				$this->_fields = array();

				if ( ! empty( $fields ) ) {
					foreach ( $fields as $object_field ) {
						$object_field = pods_object_field( $object_field, 0, $this->_live, $this->_object['id'] );

						if ( $object_field->is_valid() ) {
							$this->_fields[ $object_field['name'] ] = $object_field;
						}
					}
				}
			}
		}

		return $this->_fields( 'fields', $field, $option, $alt );

	}

	/**
	 * Return total number of fields
	 *
	 * @return int
	 *
	 * @since 3.0.0
	 */
	public function field_count() {

		$total_fields = 0;

		if ( ! $this->is_valid() ) {
			return $total_fields;
		}

		if ( ! empty( $this->_fields ) ) {
			$total_fields = count( $this->_fields );
		} elseif ( $this->is_custom() ) {
			if ( ! empty( $this->_object['_fields'] ) ) {
				$total_fields = count( $this->_object['fields'] );
			}
		} else {
			$find_args = array(
				'post_type'      => '_pods_field',
				'posts_per_page' => 1,
				'post_parent'    => $this->_object['id'],
			);

			$query = new WP_Query( $find_args );

			$total_fields = $query->found_posts;
		}

		return $total_fields;

	}

	/**
	 * Return object field array from Object, a object field's data, or a object field option
	 *
	 * @param string|null $field  Object Field name
	 * @param string|null $option Field option
	 * @param bool        $alt    Set to true to check alternate fields array
	 *
	 * @return array|mixed
	 *
	 * @since 3.0.0
	 */
	public function object_fields( $field = null, $option = null, $alt = true ) {

		if ( ! $this->is_valid() ) {
			return array();
		}

		if ( empty( $this->_object_fields ) ) {
			$object_fields = array();

			if ( $this->is_custom() && isset( $this->_object['object_fields'] ) && ! empty( $this->_object['object_fields'] ) ) {
				$object_fields = $this->_object['object_fields'];
			}

			$this->_object_fields = array();

			foreach ( $object_fields as $object_field ) {
				$object_field = pods_object_field( $object_field, 0, $this->_live, $this->_object['id'] );

				if ( $object_field->is_valid() ) {
					$this->_object_fields[ $object_field['name'] ] = $object_field;
				}
			}
		}

		return $this->_fields( 'object_fields', $field, $option, $alt );

	}

	/**
	 * Return group array from Object, or a group's object
	 *
	 * @param string|null $group Object Group name
	 * @param bool        $alt   Set to true to check alternate fields array
	 *
	 * @return array|mixed
	 *
	 * @since 3.0.0
	 */
	public function groups( $group = null, $alt = true ) {

		if ( ! $this->is_valid() ) {
			return array();
		}

		if ( empty( $this->_groups ) ) {
			if ( $this->is_custom() ) {
				if ( isset( $this->_object['_groups'] ) && ! empty( $this->_object['_groups'] ) ) {
					$this->_groups = array();

					foreach ( $this->_object['_groups'] as $object_group ) {
						$object_group = pods_object_group( $object_group, 0, $this->_live, $this->_object['id'] );

						if ( $object_group->is_valid() ) {
							$this->_groups[ $object_group['name'] ] = $object_group;
						}
					}
				}
			} else {
				$find_args = array(
					'post_type'      => '_pods_group',
					'posts_per_page' => - 1,
					'nopaging'       => true,
					'post_parent'    => $this->_object['id'],
					'orderby'        => 'menu_order',
					'order'          => 'ASC'
				);

				$groups = get_posts( $find_args );

				$this->_groups = array();

				if ( ! empty( $groups ) ) {
					foreach ( $groups as $object_group ) {
						$object_group = pods_object_group( $object_group, 0, $this->_live, $this->_object['id'] );

						if ( $object_group->is_valid() ) {
							$this->_groups[ $object_group['name'] ] = $object_group;
						}
					}
				}
			}
		}

		return $this->_fields( 'groups', $group, null, $alt );

	}

	/**
	 * Get table info for an Object
	 *
	 * @return array Table info
	 *
	 * @since 3.0.0
	 */
	public function table_info() {

		if ( ! $this->is_valid() ) {
			return array();
		}

		return $this->_table_info;

	}

	/**
	 * Clear Table info for object, used when switching blogs
	 *
	 * @since 3.0.0
	 */
	public function table_info_clear() {

		$this->_table_info = array();

	}

	/**
	 * Save a Object by giving an array of option data or set a specific option to a specific value.
	 *
	 * @param array|string $options Either an associative array of option information or a option name
	 * @param mixed        $value   (optional) Value of the option, if $data is a option name
	 * @param bool         $refresh (optional) Refresh the current object
	 *
	 * @return int|bool The Object ID or false if failed
	 *
	 * @since 3.0.0
	 */
	public function save( $options = null, $value = null, $refresh = true ) {

		if ( ! $this->is_valid() ) {
			return false;
		}

		if ( null !== $value || ! is_array( $options ) ) {
			$options = array(
				$options => $value
			);
		}

		if ( empty( $options ) ) {
			return $this->_object['id'];
		}

		$params = (object) $options;

		$params->id = $this->_object['id'];

		$params = apply_filters( 'pods_object_pre_save_' . $this->_action_type, $params, $this );

		// @todo Handle generalized saving
		$id = $params->id;

		// Refresh object
		if ( $refresh ) {
			$id = $this->load( null, $id );
		} // Just update options
		else {
			$options = get_object_vars( $params );

			foreach ( $options as $option => $value ) {
				if ( 'id' != $option ) {
					$this->offsetSet( $option, $value );
				}
			}
		}

		if ( 0 < $id ) {
			$this->_action( 'pods_object_save_' . $this->_action_type, $id, $this, $params );
		}

		return $id;

	}

	/**
	 * Duplicate a Object, optionally giving an array of option data or set a specific option to a specific value.
	 *
	 * @param array|string $options (optional) Either an associative array of option information or a option name
	 * @param mixed        $value   (optional) Value of the option, if $data is a option name
	 * @param bool         $replace (optional) Replace the current object
	 *
	 * @return int|bool The new Object ID or false if failed
	 *
	 * @since 3.0.0
	 */
	public function duplicate( $options = null, $value = null, $replace = false ) {

		if ( ! $this->is_valid() ) {
			return false;
		}

		if ( is_object( $options ) ) {
			$options = get_object_vars( $options );
		} elseif ( null !== $value && ! is_array( $options ) ) {
			$options = array(
				$options => $value
			);
		} elseif ( empty( $options ) || ! is_array( $options ) ) {
			$options = array();
		}

		// Must duplicate from the original Pod object
		if ( isset( $options['id'] ) && 0 < $options['id'] ) {
			return false;
		}

		$built_in = array(
			'id'       => '',
			'name'     => '',
			'new_name' => ''
		);

		$custom_options = array_diff( $options, $built_in );

		$params       = (object) $options;
		$params->name = $this->_object['name'];

		$params = apply_filters( 'pods_object_pre_duplicate_' . $this->_action_type, $params, $this );

		$object = clone $this;

		$object->override( $custom_options );

		$object = $object->export();

		unset( $object['id'] );

		if ( isset( $object['object_fields'] ) ) {
			unset( $object['object_fields'] );
		}

		if ( isset( $params->new_name ) ) {
			$pod['name'] = $params->new_name;
		}

		$try = 2;

		$check_name = $object['name'] . $try;
		$new_label  = $object['label'] . $try;

		while ( $this->exists( $check_name ) ) {
			$try ++;

			$check_name = $object['name'] . $try;
			$new_label  = $object['label'] . $try;
		}

		$object['name']  = $check_name;
		$object['label'] = $new_label;

		foreach ( $object['fields'] as $field => $field_data ) {
			unset( $object['fields'][ $field ]['id'] );
		}

		$new_object = pods_object();

		$id = $new_object->save( $object );

		if ( $replace ) {
			// Replace object
			$id = $this->load( null, $id );
		}

		if ( 0 < $id ) {
			$this->_action( 'pods_object_duplicate_' . $this->_action_type, $id, $this, $new_object, $params );
		}

		return $id;

	}

	/**
	 * Delete the Object
	 *
	 * @param bool $delete_all (optional) Whether to delete all content
	 *
	 * @return bool Whether the Object was successfully deleted
	 *
	 * @since 3.0.0
	 */
	public function delete( $delete_all = false ) {

		if ( ! $this->is_valid() ) {
			return false;
		}

		$params = (object) array(
			'id'   => $this->_object['id'],
			'name' => $this->_object['name']
		);

		$params = apply_filters( 'pods_object_pre_delete_' . $this->_action_type, $params, $this, $delete_all );

		$success = false;

		if ( 0 < $params['id'] ) {
			// @todo Handle generalized deleting
			$success = true;
		}

		if ( $success ) {
			$this->_action( 'pods_object_delete_' . $this->_action_type, $params, $this, $delete_all );

			// Can't destroy object, so let's destroy the data and invalidate the object
			$this->destroy();
		}

		return $success;

	}

	/**
	 * Delete all content
	 *
	 * @return bool Whether the Content was successfully deleted
	 *
	 * @since 3.0.0
	 */
	public function reset() {

		if ( ! $this->is_valid() ) {
			return false;
		}

		return false;

	}

	/**
	 * Set value from array usage $object['offset'] = 'value';
	 *
	 * @param mixed $offset Used to set index of Array or Variable name on Object
	 * @param mixed $value  Value to be set
	 * @param bool  $live   Set to false to override current live object saving
	 *
	 * @return mixed|void
	 *
	 * @since 3.0.0
	 */
	public function offsetSet( $offset, $value, $live = true ) {

		if ( $live && $this->_live ) {
			$this->save( $offset, $value );
		} elseif ( 'fields' == $offset ) {
			$this->_fields = $value;
		} elseif ( 'object_fields' == $offset ) {
			$this->_object_fields = $value;
		} elseif ( isset( $this->_deprecated_keys[ $offset ] ) ) {
			if ( pods_allow_deprecated() ) {
				if ( ! in_array( $offset, $this->_core_fields ) && ! in_array( $this->_deprecated_keys[ $offset ], $this->_changed ) && $this->_object[ $this->_deprecated_keys[ $offset ] ] != $value ) {
					$this->_changed[] = $this->_deprecated_keys[ $offset ];
				}

				$this->_object[ $this->_deprecated_keys[ $offset ] ] = $value;
			} else {
				pods_deprecated( '$object[\'' . $offset . '\']', '2.0', '$object[\'' . $this->_deprecated_keys[ $offset ] . '\']' );
			}
		} elseif ( isset( $this->_object[ $offset ] ) ) {
			if ( ! in_array( $offset, $this->_core_fields ) && ! in_array( $offset, $this->_changed ) && $this->_object[ $offset ] != $value ) {
				$this->_changed[] = $offset;
			}

			$this->_object[ $offset ] = $value;
		} else {
			if ( ! in_array( $offset, $this->_changed ) && $this->offsetGet( $offset ) != $value ) {
				$this->_changed[] = $offset;
			}

			$this->_meta[ $offset ] = $value;
		}

		if ( isset( $this->_override[ $offset ] ) ) {
			unset( $this->_override[ $offset ] );
		}

	}

	/**
	 * Get value from array usage $object['offset'];
	 *
	 * @param mixed $offset Used to get value of Array
	 * @param bool  $strict
	 *
	 * @return mixed|null
	 *
	 * @since 3.0.0
	 */
	public function offsetGet( $offset, $strict = false ) {

		// Special methods (fields, object_fields, table_info, etc)
		if ( ! empty( $this->_methods ) && in_array( $offset, $this->_methods ) ) {
			$value = call_user_func( array( $this, $offset ) );
		} // @deprecated Options (pre Pods 3.0 style)
		elseif ( 'options' == $offset ) {
			$value = null;

			if ( pods_allow_deprecated() ) {
				$value = $this->export();
			}
		} // Overrides
		elseif ( isset( $this->_override[ $offset ] ) ) {
			$value = $this->_override[ $offset ];

			if ( ! $this->_live ) {
				// i18n plugin integration
				if ( 'label' == $offset || 0 === strpos( $offset, 'label_' ) ) {
					$value = __( $value );
				}
			}
		} // Object fields
		elseif ( isset( $this->_object[ $offset ] ) ) {
			$value = $this->_object[ $offset ];

			if ( ! $this->_live ) {
				// i18n plugin integration
				if ( 'label' == $offset || 0 === strpos( $offset, 'label_' ) ) {
					$value = __( $value );
				}
			}
		} // Meta fields
		elseif ( isset( $this->_meta[ $offset ] ) ) {
			$value = $this->_meta[ $offset ];

			if ( ! $this->_live ) {
				// i18n plugin integration
				if ( 'label' == $offset || 0 === strpos( $offset, 'label_' ) ) {
					$value = __( $value );
				}
			}
		} // Form fields
		elseif ( isset( $this->_form_options[ $offset ] ) ) {
			$value = $this->_form_options[ $offset ];
		} // Table info fields
		elseif ( $this->table_info() && isset( $this->_table_info[ $offset ] ) ) {
			$value = $this->_table_info[ $offset ];

			if ( ! $this->_live ) {
				// i18n plugin integration
				if ( 'label' == $offset || 0 === strpos( $offset, 'label_' ) ) {
					$value = __( $value );
				}
			}
		} // @deprecated Deprecated keys
		elseif ( isset( $this->_deprecated_keys[ $offset ] ) ) {
			$value = null;

			if ( pods_allow_deprecated() || isset( $this->_core_fields[ $offset ] ) ) {
				$value = $this->offsetGet( $this->_deprecated_keys[ $offset ], $strict );
			} else {
				pods_deprecated( '$object[\'' . $offset . '\']', '2.0', '$object[\'' . $this->_deprecated_keys[ $offset ] . '\']' );
			}
		} // Fallback to fetch from meta
		else {
			$value = $this->_meta( $offset, null, false, $strict );

			if ( null !== $value ) {
				$this->_meta[ $offset ] = $value;
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
	 * @since 3.0.0
	 */
	public function offsetExists( $offset ) {

		if ( null !== $this->offsetGet( $offset, true ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Get value from array usage $object['offset'];
	 *
	 * @param mixed $offset Used to unset index of Array
	 * @param bool  $live   Set to false to override current live object saving
	 *
	 * @since 3.0.0
	 */
	public function _offsetUnset( $offset, $live = true ) {

		if ( isset( $this->_object[ $offset ] ) ) {
			if ( $live && $this->_live ) {
				$this->save( $offset, null );
			} else {
				unset( $this->_object[ $offset ] );
			}
		}

	}

	/**
	 * Get value from array usage $object['offset'];
	 *
	 * @param mixed $offset Used to unset index of Array
	 *
	 * @since 3.0.0
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
	 * @see   offsetSet
	 *
	 * @since 3.0.0
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
	 * @see   offsetGet
	 *
	 * @since 3.0.0
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
	 * @see   offsetExists
	 *
	 * @since 3.0.0
	 */
	public function __isset( $offset ) {

		return $this->offsetExists( $offset );

	}

	/**
	 * Mapping >> offsetUnset for Object access
	 *
	 * @var mixed $offset
	 *
	 * @see   offsetUnset
	 *
	 * @since 3.0.0
	 */
	public function __unset( $offset ) {

		$this->offsetUnset( $offset );

	}

	/**
	 * Serialize Pods_Object
	 *
	 * @return string Serialized string
	 *
	 * @see   serialize
	 *
	 * @since 3.0.0
	 */
	public function serialize() {

		$array = array(
			'_object' => $this->_object,
			'_meta'   => $this->_meta
		);

		return serialize( $array );

	}

	/**
	 * Unserialize Pods_Object
	 *
	 * @param string $data Serialized string
	 *
	 * @see   unserialize
	 *
	 * @since 3.0.0
	 */
	public function unserialize( $data ) {

		$object = maybe_unserialize( $data );

		if ( ! empty( $object ) && ( is_object( $object ) || is_array( $object ) ) ) {
			$object = (object) $object;

			if ( isset( $object->_object ) ) {
				$this->_object = $object->_object;
			}

			if ( isset( $object->_meta ) ) {
				$this->_meta = $object->_meta;
			}
		}

	}

}