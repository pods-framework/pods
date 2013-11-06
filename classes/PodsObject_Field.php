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

		if ( null === $name && 0 == $id && null === $parent ) {
			// Allow for refresh of object
			if ( $this->is_valid() ) {
				$id = $this->_object[ 'id' ];

				$this->destroy();
			}
			// Empty object
			else {
				return false;
			}
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
		elseif ( !is_object( $name ) ) {
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
		}

		if ( !empty( $_object ) || !empty( $object ) ) {
			$defaults = array(
				'id' => 0,
				'name' => '',
				'label' => '',
				'description' => '',
				'type' => 'text',
				'weight' => 0,
				'parent_id' => $parent_id,
				'pod' => '',
				'pod_id' => '',
				'group_id' => ''
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

			if ( !empty( $object[ 'parent_id' ] ) ) {
				$parent = get_post( $object[ 'parent_id' ] );

				if ( !empty( $parent ) ) {
					if ( '_pods_group' == $parent->post_type ) {
						$object[ 'group_id' ] = $parent->post_parent;

						$group_pod = get_post( $parent->post_parent );

						if ( !empty( $group_pod ) && '_pods_pod' == $group_pod->post_type ) {
							$object[ 'pod' ] = $group_pod->post_name;
							$object[ 'pod_id' ] = $group_pod->ID;
						}
					}
					elseif ( '_pods_pod' == $parent->post_type ) {
						$object[ 'pod' ] = $parent->post_name;
						$object[ 'pod_id' ] = $parent->ID;
					}
				}
			}

			if ( strlen( $object[ 'label' ] ) < 1 ) {
				$object[ 'label' ] = $object[ 'name' ];
			}

			$tableless_meta = array(
				'pick_object',
				'pick_val',
				'pick_simple',
				'sister_id'
			);

			if ( 0 < $object[ 'id' ] ) {
				$meta = array(
					'type',
					'group_id'
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

				$object[ 'group_id' ] = (int) $object[ 'group_id' ];
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
					if ( !isset( $object[ $meta_key ] ) || empty( $object[ 'pick_object' ] ) ) {
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
	 * Check if the object exists
	 *
	 * @param string|array|WP_Post $name Get the Object by Name, or pass an array/WP_Post of Object
	 * @param int $id Get the Object by ID (overrides $name)
	 * @param mixed $parent Parent Object or ID
	 *
	 * @return int|bool $id The Object ID or false if Object not found
	 *
	 * @since 2.4
	 */
	public function exists( $name = null, $id = 0, $parent = null ) {

		$field = pods_object_field( $name, $id, false, $parent );

		if ( !empty( $field ) && $field->is_valid() ) {
			return true;
		}

		return false;

	}

	/**
	 * Get table info for a Pod
	 *
	 * @return array Table info
	 */
	public function table_info() {

		if ( !$this->is_valid() || !in_array( $this->_object[ 'type' ], PodsForm::tableless_field_types() ) ) {
			return array();
		}

		if ( empty( $this->_table_info ) && !empty( $this->_object[ 'pick_object' ] ) ) {
			$this->_table_info = pods_api()->get_table_info( $this->_object[ 'pick_object' ], $this->_object[ 'pick_val' ], null, null, $this->_object );
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

        /**
         * @var $wpdb wpdb
         */
        global $wpdb;

		if ( null !== $value && !is_array( $options ) && !is_object( $options ) ) {
			$options = array(
				$options => $value
			);
		}

		if ( empty( $options ) ) {
			if ( $this->is_valid() ) {
				return $this->_object[ 'id' ];
			}

			return false;
		}
		elseif ( !is_array( $options ) && !is_object( $options ) ) {
			return false;
		}

		$tableless_field_types = PodsForm::tableless_field_types();
		$simple_tableless_objects = PodsForm::field_method( 'pick', 'simple_objects' );

		$params = (object) $options;

		if ( $this->is_valid() ) {
			$params->id = $this->_object[ 'id' ];
		}
		elseif ( !isset( $params->id ) ) {
			$params->id = 0;
		}

		if ( !isset( $params->table_operation ) ) {
			$params->table_operation = true;
		}

		if ( !isset( $params->db ) ) {
			$params->db = true;
		}
		elseif ( true !== $params->db ) {
			$params->table_operation = false;
		}

		$api = pods_api();

		$params = apply_filters( 'pods_object_pre_save_' . $this->_action_type, $params, $this );

		if ( isset( $params->pod_id ) ) {
			$params->pod_id = pods_absint( $params->pod_id );
		}

		if ( isset( $params->group_id ) ) {
			$params->group_id = pods_absint( $params->group_id );
		}

		$pod = null;
		$save_pod = false;
		$id_required = false;

		if ( isset( $params->id_required ) ) {
			unset( $params->id_required );

			$id_required = true;
		}

		if ( ( !isset( $params->pod ) || empty( $params->pod ) ) && ( !isset( $params->pod_id ) || empty( $params->pod_id ) ) ) {
			if ( $this->is_valid() ) {
				$pod = pods_object_pod( $this->_object[ 'pod' ], $this->_object[ 'parent_id' ] );

				if ( $pod->is_valid() ) {
					$params->pod_id = $pod[ 'id' ];
					$params->pod = $pod[ 'id' ];
				}
			}

			if ( empty( $pod ) || !$pod->is_valid() ) {
				return pods_error( __( 'Pod ID or name is required', 'pods' ), $this );
			}
		}

		if ( empty( $pod ) ) {
			if ( isset( $params->pod ) && ( is_array( $params->pod ) || is_object( $params->pod ) ) ) {
				$pod = $params->pod;

				$save_pod = true;
			}
			elseif ( ( !isset( $params->pod_id ) || empty( $params->pod_id ) ) && ( true === $params->db || 0 < $params->db ) ) {
				$pod = pods_object_pod( $params->pod );
			}
			elseif ( !isset( $params->pod ) && ( true === $params->db || 0 < $params->db ) ) {
				$pod = pods_object_pod( null, $params->pod_id );
			}
			elseif ( true === $params->db || 0 < $params->db ) {
				$pod = pods_object_pod( $params->pod, $params->pod_id );
			}

			if ( ( empty( $pod ) || !$pod->is_valid() ) && true === $params->db ) {
				return pods_error( __( 'Pod not found', 'pods' ), $this );
			}
		}

		$params->pod_id = $pod[ 'id' ];
		$params->pod = $pod[ 'name' ];

		if ( !isset( $params->name ) && isset( $params->label ) ) {
			$params->name = $params->label;
		}

		if ( !isset( $params->name ) ) {
			return pods_error( 'Pod field name is required', $this );
		}

		$params->name = pods_clean_name( $params->name, true, ( 'meta' == $pod[ 'storage' ] ? false : true ) );

		if ( !isset( $params->label ) ) {
			$params->label = $params->name;
		}

		if ( !isset( $params->id ) ) {
			$params->id = 0;
		}

		if ( empty( $params->name ) ) {
			return pods_error( 'Pod field name is required', $this );
		}

		$old_field = pods_object_field( $params->name, 0, false, $params->pod_id );

		$old_id = $old_name = $old_type = $old_definition = $old_options = $old_sister_id = null;

		$old_simple = false;

		if ( $old_field->is_valid() ) {
			if ( $old_field->is_custom() ) {
				return pods_error( sprintf( __( 'Field %s was registered through code, you cannot modify it.', 'pods' ), $params->name ) );
			}

			/*if ( isset( $params->id ) && 0 < $params->id ) {
				$old_id = $params->id;
			}*/

			$old_id = $old_field[ 'id' ];
			$old_name = pods_clean_name( $old_field[ 'name' ], true, ( 'meta' == $pod[ 'storage' ] ? false : true ) );
			$old_type = $old_field[ 'type' ];
			$old_options = $old_field->export();
			$old_sister_id = (int) pods_var( 'sister_id', $old_options, 0 );

			$old_simple = ( 'pick' == $old_type && in_array( pods_var( 'pick_object', $old_field ), $simple_tableless_objects ) );

			if ( isset( $params->name ) && !empty( $params->name ) ) {
				$old_field[ 'name' ] = $params->name;
			}

			if ( $old_name != $old_field[ 'name' ] && $this->exists( $old_field[ 'name' ] ) ) {
				return pods_error( sprintf( __( 'Field %s already exists, you cannot rename %s to that', 'pods' ), $old_field[ 'name' ], $old_name ), $this );
			}

			if ( ( $id_required || !empty( $params->id ) ) && ( empty( $old_id ) || $old_id != $params->id ) ) {
				return pods_error( sprintf( __( 'Field %s already exists', 'pods' ), $old_field[ 'name' ] ), $this );
			}

			if ( empty( $params->id ) ) {
				$params->id = $old_id;
			}

			if ( !in_array( $old_type, $tableless_field_types ) || $old_simple ) {
				$definition = $api->get_field_definition( $old_type, $old_options );

				if ( 0 < strlen( $definition ) ) {
					$old_definition = "`{$old_name}` " . $definition;
				}
			}

			$field =& $this;
		}
		else {
			$field = array(
				 'id' => 0,
				 'parent_id' => $params->pod_id,
				 'name' => $params->name,
				 'label' => $params->name,
				 'type' => 'text'
			);

			$field = pods_object_field( $field );
		}

		// Setup options
		$options = get_object_vars( $params );

		$options_ignore = array(
			'db',
			'table_operation',
			'method',
			'table_info',
			'attributes',
			'group',
			'grouped',
			'developer_mode',
			'dependency',
			'depends-on',
			'excludes-on'
		);

		foreach ( $options_ignore as $ignore ) {
			if ( isset( $options[ $ignore ] ) ) {
				unset( $options[ $ignore ] );
			}
		}

		if ( isset( $options[ 'options' ] ) ) {
			$options = array_merge( $options[ 'options' ], $options );

			unset( $options[ 'options' ] );
		}

		$field->override( $options );

		if ( strlen( $field[ 'label' ] ) < 1 ) {
			$field[ 'label' ] = $field[ 'name' ];
		}

		if ( in_array( $field[ 'type' ], $tableless_field_types ) ) {
			// Clean up special drop-down in field editor and save out pick_val
			if ( 0 === strpos( $field[ 'pick_object' ], 'pod-' ) ) {
				$field[ 'pick_val' ] = pods_str_replace( 'pod-', '', $field[ 'pick_object' ], 1 );
				$field[ 'pick_object' ] = 'pod';
			}
			elseif ( 0 === strpos( $field[ 'pick_object' ], 'post_type-' ) ) {
				$field[ 'pick_val' ] = pods_str_replace( 'post_type-', '', $field[ 'pick_object' ], 1 );
				$field[ 'pick_object' ] = 'post_type';
			}
			elseif ( 0 === strpos( $field[ 'pick_object' ], 'taxonomy-' ) ) {
				$field[ 'pick_val' ] = pods_str_replace( 'taxonomy-', '', $field[ 'pick_object' ], 1 );
				$field[ 'pick_object' ] = 'taxonomy';
			}
			elseif ( 'table' == $field[ 'pick_object' ] && 0 < strlen( $field[ 'pick_table' ] ) ) {
				$field[ 'pick_val' ] = $field[ 'pick_table' ];
				$field[ 'pick_object' ] = 'table';
			}
			elseif ( false === strpos( $field[ 'pick_object' ], '-' ) && !in_array( $field[ 'pick_object' ], array( 'pod', 'post_type', 'taxonomy' ) ) ) {
				$field[ 'pick_val' ] = '';
			}
			elseif ( 'custom-simple' == $field[ 'pick_object' ] ) {
                $field[ 'pick_val' ] = '';
			}

			$field[ 'sister_id' ] = (int) $field[ 'sister_id' ];
		}
		else {
			$field[ 'pick_val' ] = '';
			$field[ 'pick_object' ] = '';
			$field[ 'sister_id' ] = 0;
		}

		$object_fields = $pod[ 'object_fields' ];

		if ( 0 < $old_id && defined( 'PODS_FIELD_STRICT' ) && !PODS_FIELD_STRICT ) {
			$params->id = $field[ 'id' ] = $old_id;
		}

		// Add new field
		if ( !isset( $params->id ) || empty( $params->id ) || empty( $field ) ) {
			if ( $params->table_operation && in_array( $field[ 'name' ], array( 'created', 'modified' ) ) && !in_array( $field[ 'type' ], array( 'date', 'datetime' ) ) && ( !defined( 'PODS_FIELD_STRICT' ) || PODS_FIELD_STRICT ) ) {
				return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $field[ 'name' ] ), $this );
			}
			elseif ( $params->table_operation && 'author' == $field[ 'name' ] && 'pick' != $field[ 'type' ] && ( !defined( 'PODS_FIELD_STRICT' ) || PODS_FIELD_STRICT ) ) {
				return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $field[ 'name' ] ), $this );
			}
			elseif ( in_array( $field[ 'name' ], array( 'id', 'ID' ) ) ) {
				return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $field[ 'name' ] ), $this );
			}

			foreach ( $object_fields as $object_field => $object_field_opt ) {
				if ( $object_field == $field[ 'name' ] || in_array( $field[ 'name' ], $object_field_opt[ 'alias' ] ) ) {
					return pods_error( sprintf( __( '%s is reserved for internal WordPress or Pods usage, please try a different name. Also consider what WordPress and Pods provide you built-in.', 'pods' ), $field[ 'name' ] ), $this );
				}
			}

			// Reserved post_name values that can't be used as field names
			if ( in_array( $field[ 'name' ], array( 'rss' ) ) ) {
				$field[ 'name' ] .= '2';
			}

			if ( 'slug' == $field[ 'type' ] && true === $params->db ) {
				if ( in_array( $pod[ 'type' ], array( 'post_type', 'taxonomy', 'user' ) ) ) {
					return pods_error( __( 'This pod already has an internal WordPress permalink field', 'pods' ), $this );
				}

				$args = array(
					'post_type' => '_pods_field',
					'orderby' => 'menu_order',
					'order' => 'ASC',
					'posts_per_page' => 1,
					'post_parent' => $field[ 'pod_id' ],
					'meta_query' => array(
						array(
							'key' => 'type',
							'value' => 'slug'
						)
					)
				);

				$slug_field = get_posts( $args );

				if ( !empty( $slug_field ) ) {
					return pods_error( __( 'This pod already has a permalink field', 'pods' ), $this );
				}
			}

			// Sink the new field to the bottom of the list
			if ( null === $field[ 'weight' ] ) {
				$field[ 'weight' ] = 0;

				$args = array(
					'post_type' => '_pods_field',
					'orderby' => 'menu_order',
					'order' => 'DESC',
					'posts_per_page' => 1,
					'post_parent' => $field[ 'pod_id' ]
				);

				$bottom_most_field = get_posts( $args );

				if ( !empty( $bottom_most_field ) ) {
					$field[ 'weight' ] = pods_absint( $bottom_most_field[ 0 ]->menu_order ) + 1;
				}
			}

			$field[ 'weight' ] = pods_absint( $field[ 'weight' ] );

			$post_data = array(
				'post_name' => $field[ 'name' ],
				'post_title' => $field[ 'label' ],
				'post_content' => $field[ 'description' ],
				'post_parent' => $field[ 'pod_id' ],
				'post_type' => '_pods_field',
				'post_status' => 'publish',
				'menu_order' => $field[ 'weight' ]
			);
		}
		else {
			if ( in_array( $field[ 'name' ], array( 'id', 'ID' ) ) ) {
				if ( null !== $old_name ) {
					return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $field[ 'name' ] ), $this );
				}
				else {
					return pods_error( sprintf( __( '%s is not editable', 'pods' ), $field[ 'name' ] ), $this );
				}
			}

			if ( null !== $old_name && $field[ 'name' ] != $old_name && ( !defined( 'PODS_FIELD_STRICT' ) || PODS_FIELD_STRICT ) ) {
				if ( in_array( $field[ 'name' ], array( 'created', 'modified' ) ) && !in_array( $field[ 'type' ], array( 'date', 'datetime' ) ) ) {
					return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $field[ 'name' ] ), $this );
				}
				elseif ( 'author' == $field[ 'name' ] && 'pick' != $field[ 'type' ] ) {
					return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $field[ 'name' ] ), $this );
				}
			}

			foreach ( $object_fields as $object_field => $object_field_opt ) {
				if ( $object_field != $field[ 'name' ] && !in_array( $field[ 'name' ], $object_field_opt[ 'alias' ] ) ) {
					continue;
				}

				if ( null !== $old_name ) {
					return pods_error( sprintf( __( '%s is reserved for internal WordPress or Pods usage, please try a different name', 'pods' ), $field[ 'name' ] ), $this );
				}
				else {
					return pods_error( sprintf( __( '%s is not editable', 'pods' ), $field[ 'name' ] ), $this );
				}
			}

			$post_data = array(
				'ID' => $field[ 'id' ],
				'post_name' => $field[ 'name' ],
				'post_title' => $field[ 'label' ],
				'post_content' => $field[ 'description' ],
				'post_parent' => $field[ 'parent_id' ]
			);

			if ( null !== $field[ 'weight' ] ) {
				$field[ 'weight' ] = pods_absint( $field[ 'weight' ] );

				$post_data[ 'menu_order' ] = $field[ 'weight' ];
			}
		}

		if ( true === $params->db ) {
			if ( !has_filter( 'wp_unique_post_slug', array( $api, 'save_slug_fix' ) ) ) {
				add_filter( 'wp_unique_post_slug', array( $api, 'save_slug_fix' ), 100, 6 );
			}

			$conflicted = false;

			// Headway compatibility fix
			if ( has_filter( 'wp_insert_post_data', 'headway_clean_slug', 0 ) ) {
				remove_filter( 'wp_insert_post_data', 'headway_clean_slug', 0 );

				$conflicted = true;
			}

			$changed_meta = $field->changed();
			$field->override_save();

			$params->id = $api->save_wp_object( 'post', $post_data, $changed_meta );

			if ( $conflicted ) {
				add_filter( 'wp_insert_post_data', 'headway_clean_slug', 0 );
			}

			if ( false === $params->id ) {
				return pods_error( __( 'Cannot save Field', 'pods' ), $this );
			}
		}
		else {
			$params->id = $field[ 'name' ];
		}

		$id = $field[ 'id' ] = $params->id;

		$simple = ( 'pick' == $field[ 'type' ] && in_array( pods_var( 'pick_object', $field ), $simple_tableless_objects ) );

		$definition = false;

		if ( !in_array( $field[ 'type' ], $tableless_field_types ) || $simple ) {
			$field_definition = $api->get_field_definition( $field[ 'type' ], $field );

			if ( 0 < strlen( $field_definition ) ) {
				$definition = '`' . $field[ 'name' ] . '` ' . $field_definition;
			}
		}

		$sister_id = (int) $field[ 'sister_id' ];

		if ( $params->table_operation && 'table' == $pod[ 'storage' ] && !pods_tableless() ) {
			if ( !empty( $old_id ) ) {
				if ( ( $field[ 'type' ] != $old_type || $old_simple != $simple ) && empty( $definition ) ) {
					pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` DROP COLUMN `{$old_name}`", false );
				}
				elseif ( 0 < strlen( $definition ) ) {
					if ( $old_name != $field[ 'name' ] || $old_simple != $simple ) {
						$test = false;

						if ( 0 < strlen( $old_definition ) ) {
							$test = pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` CHANGE `{$old_name}` {$definition}", false );
						}

						// If the old field doesn't exist, continue to add a new field
						if ( false === $test ) {
							pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` ADD COLUMN {$definition}", __( 'Cannot create new field', 'pods' ) );
						}
					}
					elseif ( null !== $old_definition && $definition != $old_definition ) {
						$test = pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` CHANGE `{$old_name}` {$definition}", false );

						// If the old field doesn't exist, continue to add a new field
						if ( false === $test ) {
							pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` ADD COLUMN {$definition}", __( 'Cannot create new field', 'pods' ) );
						}
					}
				}
			}
			elseif ( 0 < strlen( $definition ) ) {
				$test = false;

				if ( 0 < strlen( $old_definition ) ) {
					$test = pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` CHANGE `" . $field[ 'name' ] . "` {$definition}", false );
				}

				// If the old field doesn't exist, continue to add a new field
				if ( false === $test ) {
					pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` ADD COLUMN {$definition}", __( 'Cannot create new field', 'pods' ) );
				}
			}
		}

		if ( !empty( $old_id ) && 'meta' == $pod[ 'storage' ] && $old_name != $field[ 'name' ] && $pod[ 'meta_table' ] != $pod[ 'table' ] ) {
			$prepare = array(
				$field[ 'name' ],
				$old_name
			);

			// Users don't have a type
			if ( !empty( $pod[ 'field_type' ] ) ) {
				$prepare[] = $pod[ 'name' ];
			}

			pods_query( "
                UPDATE `{$pod['meta_table']}` AS `m`
                LEFT JOIN `{$pod['table']}` AS `t`
                    ON `t`.`{$pod['field_id']}` = `m`.`{$pod['meta_field_id']}`
                SET
                    `m`.`{$pod['meta_field_index']}` = %s
                WHERE
                    `m`.`{$pod['meta_field_index']}` = %s
            " . ( !empty( $pod[ 'field_type' ] ) ? " AND `t`.`{$pod['field_type']}` = %s" : "" ), $prepare );
		}

		if ( $field[ 'type' ] != $old_type && in_array( $old_type, $tableless_field_types ) ) {
			delete_post_meta( $old_sister_id, 'sister_id' );

			if ( true === $params->db ) {
				pods_query( "
					DELETE pm
					FROM {$wpdb->postmeta} AS pm
					LEFT JOIN {$wpdb->posts} AS p
						ON p.post_type = '_pods_field'
						AND p.ID = pm.post_id
					WHERE
						p.ID IS NOT NULL
						AND pm.meta_key = 'sister_id'
						AND pm.meta_value = %d
				", array(
						$params->id
				   ) );

				if ( !pods_tableless() ) {
					pods_query( "DELETE FROM @wp_podsrel WHERE `field_id` = {$params->id}", false );

					pods_query( "
						UPDATE `@wp_podsrel`
						SET `related_field_id` = 0
						WHERE `field_id` = %d
					", array(
							$old_sister_id
					   ) );
				}
			}
		}
		elseif ( 0 < $sister_id ) {
			update_post_meta( $sister_id, 'sister_id', $params->id );

			if ( true === $params->db && ( !pods_tableless() ) ) {
				pods_query( "
					UPDATE `@wp_podsrel`
					SET `related_field_id` = %d
					WHERE `field_id` = %d
				", array(
						$params->id,
						$sister_id
				   ) );
			}
		}
		elseif ( 0 < $old_sister_id ) {
			delete_post_meta( $old_sister_id, 'sister_id' );

			if ( true === $params->db && ( !pods_tableless() ) ) {
				pods_query( "
					UPDATE `@wp_podsrel`
					SET `related_field_id` = 0
					WHERE `field_id` = %d
				", array(
						$old_sister_id
				   ) );
			}
		}

		if ( !empty( $old_id ) && $old_name != $field[ 'name' ] && true === $params->db ) {
			update_post_meta( $pod[ 'id' ], 'pod_index', $field[ 'name' ], $old_name );
		}

		if ( !$save_pod ) {
			$api->cache_flush_pods( $pod );
		}
		else {
			pods_transient_clear( 'pods_field_' . $pod[ 'name' ] . '_' . $field[ 'name' ] );

			if ( !empty( $old_id ) && $old_name != $field[ 'name' ] ) {
				pods_transient_clear( 'pods_field_' . $pod[ 'name' ] . '_' . $old_name );
			}
		}

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

		//$id = $field[ 'id' ];

		if ( 0 < $id ) {
			$this->_action( 'pods_object_save_' . $this->_action_type, $id, $this, $params );
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

		if ( is_object( $options ) ) {
			$options = get_object_vars( $options );
		}
		elseif ( null !== $value && !is_array( $options ) ) {
			$options = array(
				$options => $value
			);
		}
		elseif ( empty( $options ) || !is_array( $options ) ) {
			$options = array();
		}

		// Must duplicate from the original Pod object
		if ( isset( $options[ 'id' ] ) && 0 < $options[ 'id' ] ) {
			return false;
		}

		$built_in = array(
			'id' => '',
			'name' => '',
			'new_name' => ''
		);

		$custom_options = array_diff( $options, $built_in );

		$params = (object) $options;

		if ( !isset( $params->strict ) ) {
			$params->strict = pods_strict();
		}

		$params->name = $this->_object[ 'name' ];

		$params = apply_filters( 'pods_object_pre_duplicate_' . $this->_action_type, $params, $this );

		$field = $this->export();

		if ( in_array( $field[ 'type' ], array( 'avatar', 'slug' ) ) ) {
			if ( false !== $params->strict ) {
				return pods_error( __( 'Field not allowed to be duplicated', 'pods' ) );
			}

			return false;
		}

		$field[ 'object' ] = '';

		$field = array_merge( $field, $custom_options );

		unset( $field[ 'id' ] );

		if ( isset( $params->new_name ) ) {
			$field[ 'name' ] = $params->new_name;
		}

		$try = 2;

		$check_name = $field[ 'name' ] . $try;
		$new_label = $field[ 'label' ] . $try;

		while ( $this->exists( $check_name, 0, $this->_object[ 'parent' ] ) ) {
			$try++;

			$check_name = $field[ 'name' ] . $try;
			$new_label = $field[ 'label' ] . $try;
		}

		$field[ 'name' ] = $check_name;
		$field[ 'label' ] = $new_label;

		$new_field = pods_object_field();

		$id = $new_field->save( $field );

		if ( 0 < $id ) {
			$this->_action( 'pods_object_duplicate_' . $this->_action_type, $id, $this, $new_field, $params );

			if ( $replace) {
				// Replace object
				$id = $this->load( null, $id );
			}
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
     * @since 2.3.10
     */
	public function delete( $delete_all = false, $table_operation = true ) {

		if ( !$this->is_valid() ) {
			return false;
		}

		$params = (object) array(
			'id' => $this->_object[ 'id' ],
			'name' => $this->_object[ 'name' ]
		);

		$params = apply_filters( 'pods_object_pre_delete_' . $this->_action_type, $params, $this, $delete_all );

		$api = pods_api();

		$pod = null;
		$save_pod = false;

		if ( ( !isset( $params->pod ) || empty( $params->pod ) ) && ( !isset( $params->pod_id ) || empty( $params->pod_id ) ) ) {
			if ( $this->is_valid() ) {
				$pod = pods_object_pod( $this->_object[ 'pod' ], $this->_object[ 'parent_id' ] );

				if ( $pod->is_valid() ) {
					$params->pod_id = $pod[ 'id' ];
					$params->pod = $pod[ 'id' ];
				}
			}

			if ( empty( $pod ) || !$pod->is_valid() ) {
				return pods_error( __( 'Pod ID or name is required', 'pods' ), $this );
			}
		}

		if ( empty( $pod ) ) {
			if ( isset( $params->pod ) && ( is_array( $params->pod ) || is_object( $params->pod ) ) ) {
				$pod = $params->pod;

				$save_pod = true;
			}
			elseif ( !isset( $params->pod_id ) || empty( $params->pod_id ) ) {
				$pod = pods_object_pod( $params->pod );
			}
			elseif ( !isset( $params->pod ) ) {
				$pod = pods_object_pod( null, $params->pod_id );
			}
			else {
				$pod = pods_object_pod( $params->pod, $params->pod_id );
			}

			if ( ( empty( $pod ) || !$pod->is_valid() ) && true === $params->db ) {
				return pods_error( __( 'Pod not found', 'pods' ), $this );
			}
		}

		$success = false;

		if ( 0 < $params->id ) {
			/**
			 * @var $wpdb wpdb
			 */
			global $wpdb;

			$tableless_field_types = PodsForm::tableless_field_types();
			$simple_tableless_objects = PodsForm::field_method( 'pick', 'simple_objects' );

			$params->pod_id = $pod[ 'id' ];
			$params->pod = $pod[ 'name' ];

			$simple = ( 'pick' == $this->_object[ 'type' ] && in_array( $this->_object[ 'pick_object' ], $simple_tableless_objects ) );
			$simple = (boolean) apply_filters( 'pods_api_tableless_custom', $simple, $this, $this, array( $this->_object[ 'name' ] => $this ), $pod, $params, $api );

			if ( $table_operation && 'table' == $pod[ 'storage' ] && ( !in_array( $this->_object[ 'type' ], $tableless_field_types ) || $simple ) ) {
				pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` DROP COLUMN `{$params->name}`", false );
			}

			$success = wp_delete_post( $params->id );

			if ( !$success ) {
				return pods_error( __( 'Field unable to be deleted', 'pods' ), $this );
			}

			$wpdb->query( $wpdb->prepare( "DELETE pm FROM {$wpdb->postmeta} AS pm
				LEFT JOIN {$wpdb->posts} AS p
					ON p.post_type = '_pods_field' AND p.ID = pm.post_id
				WHERE p.ID IS NOT NULL AND pm.meta_key = 'sister_id' AND pm.meta_value = %d", $params->id ) );

			if ( ( !pods_tableless() ) && $table_operation ) {
				pods_query( "DELETE FROM `@wp_podsrel` WHERE (`pod_id` = {$params->pod_id} AND `field_id` = {$params->id}) OR (`related_pod_id` = {$params->pod_id} AND `related_field_id` = {$params->id})", false );
			}

			// @todo Delete tableless relationship meta

			if ( !$save_pod ) {
				$api->cache_flush_pods( $pod );
			}

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
     * @since 2.4
     */
	public function reset() {

		if ( !$this->is_valid() ) {
			return false;
		}

		$params = (object) array(
			'id' => $this->_object[ 'id' ],
			'name' => $this->_object[ 'name' ]
		);

		$params = apply_filters( 'pods_object_pre_reset_' . $this->_action_type, $params, $this );

		// @todo Get pod table info
		$table_info = $this->table_info();

		// @Todo Use Pod types / object to get proper meta table

        // Delete all posts/revisions from this post type
        if ( in_array( $this->_object[ 'type' ], array( 'post_type', 'media' ) ) ) {
            $type = $this->_object[ 'object' ];

			if ( empty( $type ) ) {
				$type = $this->_object[ 'name' ];
			}

            $sql = "
                DELETE `t`, `r`, `m`
                FROM `{$table_info['table']}` AS `t`
                LEFT JOIN `{$table_info['meta_table']}` AS `m`
                    ON `m`.`{$table_info['meta_field_id']}` = `t`.`{$table_info['field_id']}`
                LEFT JOIN `{$table_info['table']}` AS `r`
                    ON `r`.`post_parent` = `t`.`{$table_info['field_id']}` AND `r`.`post_status` = 'inherit'
                WHERE `t`.`{$table_info['field_type']}` = '{$type}'
            ";

            pods_query( $sql, false );
        }
        // Delete all terms from this taxonomy
        elseif ( 'taxonomy' == $this->_object[ 'type' ] ) {
            $sql = "
                DELETE FROM `{$table_info['table']}` AS `t`
                " . $table_info['join']['tt'] . "
                WHERE " . implode( ' AND ', $table_info['where'] ) . "
            ";

            pods_query( $sql, false );
        }
        // Delete all users except the current one
        elseif ( 'user' == $this->_object[ 'type' ] ) {
            $sql = "
                DELETE `t`, `m`
                FROM `{$table_info['table']}` AS `t`
                LEFT JOIN `{$table_info['meta_table']}` AS `m`
                    ON `m`.`{$table_info['meta_field_id']}` = `t`.`{$table_info['field_id']}`
                WHERE `t`.`{$table_info['field_id']}` != " . (int) get_current_user_id() . "
            ";

            pods_query( $sql, false );
        }
        // Delete all comments
        elseif ( 'comment' == $this->_object[ 'type' ] ) {
            $type = $this->_object[ 'object' ];

			if ( empty( $type ) ) {
				$type = $this->_object[ 'name' ];
			}

			$where = array(
				"`t`.`{$table_info['field_type']}` = '{$type}'"
			);

			if ( 'comment' == $type ) {
				$where[] = "`t`.`{$table_info['field_type']}` = ''";
			}

            $sql = "
                DELETE `t`, `m`
                FROM `{$table_info['table']}` AS `t`
                LEFT JOIN `{$table_info['meta_table']}` AS `m`
                    ON `m`.`{$table_info['meta_field_id']}` = `t`.`{$table_info['field_id']}`
                WHERE " . implode( ' AND ', $where ) . "
            ";

            pods_query( $sql, false );
        }

        pods_cache_clear( true ); // only way to reliably clear out cached data across an entire group

		$this->_action( 'pods_object_reset_' . $this->_action_type, $params, $this );

        return true;

	}
}