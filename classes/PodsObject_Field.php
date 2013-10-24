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
				'group' => ''
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
						$object[ 'group' ] = $parent->post_name;

						$group_pod = get_post( $parent->post_parent );

						if ( !empty( $group_pod ) && '_pods_pod' == $group_pod->post_type ) {
							$object[ 'pod' ] = $group_pod->post_name;
						}
					}
					elseif ( '_pods_pod' == $parent->post_type ) {
						$object[ 'pod' ] = $parent->post_name;
					}
				}
			}

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

		if ( !isset( $params->db ) ) {
			$params->db = true;
		}

		$api = pods_api();

		$params = apply_filters( 'pods_object_pre_save_' . $this->_action_type, $params, $this );

		// @todo Move API logic into PodsObjectField
		$id = $api->save_field( $params );

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
	public function delete( $delete_all = false ) {

		if ( !$this->is_valid() ) {
			return false;
		}

		$params = (object) array(
			'id' => $this->_object[ 'id' ],
			'name' => $this->_object[ 'name' ]
		);

		$params = apply_filters( 'pods_object_pre_delete_' . $this->_action_type, $params, $this, $delete_all );

		$success = false;

		if ( 0 < $params->id ) {
			// @todo Move API logic into PodsObjectField
			$success = pods_api()->delete_field( $params );
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