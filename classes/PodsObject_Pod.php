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

		/**
		 * @var $pods_init \PodsInit
		 */
		global $pods_init;

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
		// Handle code-registered types
		// ToDo [PGL] Can't call PodsMeta->get_object or we'll be stuck in an infinite loop.  Commented out for now
		/*
		elseif ( is_object( $pods_init ) && PodsInit::$meta->get_object( null, $name ) ) {
			return PodsInit::$meta->get_object( null, $name );
		}
		*/
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

				if ( empty( $post_type ) && 0 === strpos( $name, 'post_type_' ) ) {
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
					$object = array_merge( get_object_vars( $post_type->labels ), $object );

					// @todo Import object settings and match up to Pod options
					/*unset( $post_type->name );
					unset( $post_type->labels );

					$object = array_merge( $object, get_object_vars( $post_type ) );*/
				}

				if ( empty( $object ) ) {
					$taxonomy = get_taxonomy( $name );

					if ( empty( $taxonomy ) && 0 === strpos( $name, 'taxonomy_' ) ) {
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
				if ( empty( $object ) && 0 === strpos( $name, 'comment_' ) ) {
					// @todo For now, only support comment_{$comment_type}
					$name = str_replace( 'comment_', '', $name );

					// @todo Eventually support the comment type objects when this function gets made
					//$comment = get_comment_object( $name );

					/*
					 if ( empty( $comment ) && 0 === strpos( $name, 'comment_' ) ) {
						$name = str_replace( 'comment_', '', $name );

						// @todo Eventually support the comment type objects when this function gets made
						//$comment = get_comment_object( $name );
					}
					*/

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
					'show_in_menu',
					'label_singular'
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

					if ( 'taxonomy' == $object[ 'type' ] ) {
						$object[ 'storage' ] = 'none';
					}
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

		if ( empty( $this->_object_fields ) ) {
			if ( $this->is_custom() && isset( $this->_object[ 'object_fields' ] ) && !empty( $this->_object[ 'object_fields' ] ) ) {
				$object_fields = $this->_object[ 'object_fields' ];
			}
			else {
				$object_fields = pods_api()->get_wp_object_fields( $this->_object[ 'type' ], $this->_object );
			}

			$this->_object_fields = array();

			foreach ( $object_fields as $object_field ) {
				$object_field = pods_object_field( $object_field, 0, $this->_live, $this->_object[ 'id' ] );

				if ( $object_field->is_valid() ) {
					$this->_object_fields[ $object_field[ 'name' ] ] = $object_field;
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

		if ( null !== $value && !is_array( $options ) && !is_object( $options ) ) {
			$options = array(
				$options => $value
			);
		}

		if ( empty( $options ) ) {
			return $this->_object[ 'id' ];
		}

        $tableless_field_types = PodsForm::tableless_field_types();
        $simple_tableless_objects = PodsForm::field_method( 'pick', 'simple_objects' );

		$params = (object) $options;
		$params->id = $this->_object[ 'id' ];

		if ( !isset( $params->db ) ) {
			$params->db = true;
		}

		$api = pods_api();

		$pod =& $this;
		$pod_fields = $pod[ 'fields' ];

		$params = apply_filters( 'pods_object_pre_save_' . $this->_action_type, $params, $pod );

        $old_id = $old_name = $old_storage = null;

        $old_fields = $old_options = array();

        if ( isset( $params->name ) ) {
            $params->name = pods_clean_name( $params->name, true, false );
		}

        if ( !$this->is_custom() ) {
            if ( isset( $params->id ) && 0 < $params->id )
                $old_id = $params->id;

            $params->id = $pod[ 'id' ];

            $old_name = $pod[ 'name' ];
            $old_storage = $pod[ 'storage' ];
            $old_options = $pod->export();
            $old_fields = $old_options[ 'fields' ];

            if ( !isset( $params->name ) && empty( $params->name ) )
                $params->name = $pod[ 'name' ];

            if ( $old_name != $params->name && false !== $this->exists( $params->name ) )
                return pods_error( sprintf( __( 'Pod %s already exists, you cannot rename %s to that', 'pods' ), $params->name, $old_name ) );

            if ( $old_name != $params->name && in_array( $pod[ 'type' ], array( 'user', 'comment', 'media' ) ) && in_array( $pod[ 'object' ], array( 'user', 'comment', 'media' ) ) )
                return pods_error( sprintf( __( 'Pod %s cannot be renamed, it extends an existing WP Object', 'pods' ), $old_name ) );

            if ( $old_name != $params->name && in_array( $pod[ 'type' ], array( 'post_type', 'taxonomy' ) ) && !empty( $pod[ 'object' ] ) && $pod[ 'object' ] == $old_name )
                return pods_error( sprintf( __( 'Pod %s cannot be renamed, it extends an existing WP Object', 'pods' ), $old_name ) );

            if ( $old_id != $params->id ) {
                if ( $params->type == $pod[ 'type' ] && isset( $params->object ) && $params->object == $pod[ 'object' ] )
                    return pods_error( sprintf( __( 'Pod using %s already exists, you can not reuse an object across multiple pods', 'pods' ), $params->object ) );
                else
                    return pods_error( sprintf( __( 'Pod %s already exists', 'pods' ), $params->name ) );
            }
        }
		elseif ( in_array( $params->name, array( 'order','orderby','post_type' ) ) && 'post_type' == pods_var( 'type', $params ) ) {
			return pods_error( sprintf( 'There are certain names that a Custom Post Types cannot be named and unfortunately, %s is one of them.', $params->name ) );
		}
        else {
            $pod = pods_object_pod( array(
                'id' => 0,
                'name' => $params->name,
                'label' => $params->name,
                'description' => '',
                'type' => 'pod',
                'storage' => 'table',
                'object' => '',
                'alias' => ''
            ) );
        }

        // Blank out fields and options for AJAX calls (everything should be sent to it for a full overwrite)
		// @todo Does this still work with pods_object_pod?
        if ( isset( $params->fields ) && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            $pod_fields = array();
        }

        // Setup options
        $options = get_object_vars( $params );

        $options_ignore = array(
			'db',
			'method',
            'object_type',
            'object_name',
            'table',
            'meta_table',
            'pod_table',
            'field_id',
            'field_index',
            'field_slug',
            'field_type',
            'field_parent',
            'field_parent_select',
            'meta_field_id',
            'meta_field_index',
            'meta_field_value',
            'pod_field_id',
            'pod_field_index',
            'object_fields',
            'join',
            'where',
            'where_default',
            'orderby',
            'pod',
            'recurse',
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

		if ( isset( $options[ 'fields' ] ) ) {
			$pod_fields = $options[ 'fields' ];

			unset( $options[ 'fields' ] );
		}

        if ( pods_tableless() && !in_array( $pod[ 'type' ], array( 'settings', 'table' ) ) ) {
            if ( 'pod' == $pod[ 'type' ] )
                $pod[ 'type' ] = 'post_type';

            if ( 'table' == $pod[ 'storage' ] ) {
                if ( 'taxonomy' == $pod[ 'type' ] )
                    $pod[ 'storage' ] = 'none';
                else
                    $pod[ 'storage' ] = 'meta';
            }
        }

        $pod->override( $options );

		/**
		 * @var WP_Query
		 */
		global $wp_query;

		$reserved_query_vars = array_keys( $wp_query->fill_query_vars( array() ) );

		if ( !empty( $pod[ 'query_var_string' ] ) ) {
			if ( in_array( $pod[ 'query_var_string' ], $reserved_query_vars ) ) {
				$pod[ 'query_var_string' ] = $pod[ 'type' ] . '_' . $pod[ 'query_var_string' ];
			}
		}
		elseif ( !empty( $pod[ 'query_var' ] ) ) {
			if ( in_array( $pod[ 'query_var' ], $reserved_query_vars ) ) {
				$pod[ 'query_var' ] = $pod[ 'type' ] . '_' . $pod[ 'query_var' ];
			}
		}

        if ( strlen( $pod[ 'label' ] ) < 1 )
            $pod[ 'label' ] = $pod[ 'name' ];

        if ( 'post_type' == $pod[ 'type' ] ) {
            // Max length for post types are 20 characters
            $pod[ 'name' ] = substr( $pod[ 'name' ], 0, 20 );
        }
        elseif ( 'taxonomy' == $pod[ 'type' ] ) {
            // Max length for taxonomies are 32 characters
            $pod[ 'name' ] = substr( $pod[ 'name' ], 0, 32 );
        }

        $params->id = $pod[ 'id' ];
        $params->name = $pod[ 'name' ];

        if ( null !== $old_name && $old_name != $pod[ 'name' ] && empty( $pod[ 'object' ] ) ) {
            if ( 'post_type' == $pod[ 'type' ] ) {
                $check = get_post_type_object( $pod[ 'name' ] );

                if ( !empty( $check ) )
                    return pods_error( sprintf( __( 'Post Type %s already exists, you cannot rename %s to that', 'pods' ),$pod[ 'name' ], $old_name ) );
            }
            elseif ( 'taxonomy' == $pod[ 'type' ] ) {
                $check = get_taxonomy( $pod[ 'name' ] );

                if ( !empty( $check ) )
                    return pods_error( sprintf( __( 'Taxonomy %s already exists, you cannot rename %s to that', 'pods' ), $pod[ 'name' ], $old_name ) );
            }
        }

        $field_table_operation = true;

        // Add new pod
        if ( empty( $pod[ 'id' ] ) ) {
            if ( strlen( $pod[ 'name' ] ) < 1 )
                return pods_error( __( 'Pod name cannot be empty', 'pods' ) );

            $post_data = array(
                'post_name' => $pod[ 'name' ],
                'post_title' => $pod[ 'label' ],
                'post_content' => $pod[ 'description' ],
                'post_type' => '_pods_pod',
                'post_status' => 'publish'
            );

            if ( 'pod' == $pod[ 'type' ] && ( !is_array( $pod_fields ) || empty( $pod_fields ) ) ) {
                $pod_fields = array();

                $pod_fields[ 'name' ] = array(
                    'name' => 'name',
                    'label' => 'Name',
                    'type' => 'text',
                    'options' => array(
                        'required' => '1'
                    )
                );

                $pod_fields[ 'created' ] = array(
                    'name' => 'created',
                    'label' => 'Date Created',
                    'type' => 'datetime',
                    'options' => array(
                        'datetime_format' => 'ymd_slash',
                        'datetime_time_type' => '12',
                        'datetime_time_format' => 'h_mm_ss_A'
                    )
                );

                $pod_fields[ 'modified' ] = array(
                    'name' => 'modified',
                    'label' => 'Date Modified',
                    'type' => 'datetime',
                    'options' => array(
                        'datetime_format' => 'ymd_slash',
                        'datetime_time_type' => '12',
                        'datetime_time_format' => 'h_mm_ss_A'
                    )
                );

                $pod_fields[ 'author' ] = array(
                    'name' => 'author',
                    'label' => 'Author',
                    'type' => 'pick',
                    'pick_object' => 'user',
                    'options' => array(
                        'pick_format_type' => 'single',
                        'pick_format_single' => 'autocomplete',
                        'default_value' => '{@user.ID}'
                    )
                );

                $pod_fields[ 'permalink' ] = array(
                    'name' => 'permalink',
                    'label' => 'Permalink',
                    'type' => 'slug',
                    'description' => 'Leave blank to auto-generate from Name'
                );

                if ( !isset( $pod[ 'pod_index' ] ) ) {
                    $pod[ 'pod_index' ] = 'name';
				}
            }

			// @deprecated hook
			// ToDo [PGL]: $pod is set to this object instance; can't pass that as $name to pods_do_hook()
            //$pod = pods_do_hook( 'pods_api_save_pod_default_pod', $pod, $params );

            //$pod = pods_do_hook( 'pods_object_save_pod_default_pod', $pod, $params );

            $field_table_operation = false;
        }
        else {
            $post_data = array(
                'ID' => $pod[ 'id' ],
                'post_name' => $pod[ 'name' ],
                'post_title' => $pod[ 'label' ],
                'post_content' => $pod[ 'description' ],
                'post_status' => 'publish'
            );
        }

        if ( true === $params->db ) {
            if ( !has_filter( 'wp_unique_post_slug', array( $api, 'save_slug_fix' ) ) )
                add_filter( 'wp_unique_post_slug', array( $api, 'save_slug_fix' ), 100, 6 );

            $conflicted = false;

            // Headway compatibility fix
            if ( has_filter( 'wp_insert_post_data', 'headway_clean_slug', 0 ) ) {
                remove_filter( 'wp_insert_post_data', 'headway_clean_slug', 0 );

                $conflicted = true;
            }

			//$post_meta = $pod->export( 'data' );
			$changed_meta = $pod->changed();
			$pod->override_save();

            $params->id = $api->save_wp_object( 'post', $post_data, $changed_meta );

            if ( $conflicted )
                add_filter( 'wp_insert_post_data', 'headway_clean_slug', 0 );

            if ( false === $params->id )
                return pods_error( __( 'Cannot save Pod', 'pods' ) );
        }
        elseif ( empty( $params->id ) )
            $params->id = (int) $params->db;

        $pod[ 'id' ] = $params->id;

        // Setup / update tables
        if ( 'table' != $pod[ 'type' ] && 'table' == $pod[ 'storage' ] && $old_storage != $pod[ 'storage' ] && true === $params->db ) {
            $definitions = array( "`id` BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY" );

            $defined_fields = array();

            foreach ( $pod_fields as $field ) {
                if ( !is_array( $field ) || !isset( $field[ 'name' ] ) || in_array( $field[ 'name' ], $defined_fields ) )
                    continue;

                $defined_fields[] = $field[ 'name' ];

                if ( !in_array( $field[ 'type' ], $tableless_field_types ) || ( 'pick' == $field[ 'type' ] && in_array( pods_var( 'pick_object', $field ), $simple_tableless_objects ) ) ) {
                    $definition = $api->get_field_definition( $field[ 'type' ], array_merge( $field, pods_var_raw( 'options', $field, array() ) ) );

                    if ( 0 < strlen( $definition ) )
                        $definitions[] = "`{$field['name']}` " . $definition;
                }
            }

            pods_query( "DROP TABLE IF EXISTS `@wp_pods_{$params->name}`" );

            $result = pods_query( "CREATE TABLE `@wp_pods_{$params->name}` (" . implode( ', ', $definitions ) . ") DEFAULT CHARSET utf8" );

            if ( empty( $result ) )
                return pods_error( __( 'Cannot add Database Table for Pod', 'pods' ) );

        }
        elseif ( 'table' != $pod[ 'type' ] && 'table' == $pod[ 'storage' ] && $pod[ 'storage' ] == $old_storage && null !== $old_name && $old_name != $params->name && true === $params->db ) {
            $result = pods_query( "ALTER TABLE `@wp_pods_{$old_name}` RENAME `@wp_pods_{$params->name}`" );

            if ( empty( $result ) )
                return pods_error( __( 'Cannot update Database Table for Pod', 'pods' ), $this );
        }

        /**
         * @var $wpdb wpdb
         */
        global $wpdb;

		if ( null !== $old_name && $old_name != $params->name && true === $params->db ) {
        	// Rename items in the DB pointed at the old WP Object names
			if ( 'post_type' == $pod[ 'type' ] && empty( $pod[ 'object' ] ) ) {
				$api->rename_wp_object_type( 'post', $old_name, $params->name );
			}
			elseif ( 'taxonomy' == $pod[ 'type' ] && empty( $pod[ 'object' ] ) ) {
				$api->rename_wp_object_type( 'taxonomy', $old_name, $params->name );
			}
			elseif ( 'comment' == $pod[ 'type' ] && empty( $pod[ 'object' ] ) ) {
				$api->rename_wp_object_type( 'comment', $old_name, $params->name );
			}
			elseif ( 'settings' == $pod[ 'type' ] ) {
				$api->rename_wp_object_type( 'settings', $old_name, $params->name );
			}

        	// Sync any related fields if the name has changed
            $fields = pods_query( "
                SELECT `p`.`ID`
                FROM `{$wpdb->posts}` AS `p`
                LEFT JOIN `{$wpdb->postmeta}` AS `pm` ON `pm`.`post_id` = `p`.`ID`
                LEFT JOIN `{$wpdb->postmeta}` AS `pm2` ON `pm2`.`post_id` = `p`.`ID`
                WHERE
                    `p`.`post_type` = '_pods_field'
                    AND `pm`.`meta_key` = 'pick_object'
                    AND (
                    	`pm`.`meta_value` = 'pod'
                    	OR `pm`.`meta_value` = '" . $pod[ 'type' ] . "'
					)
                    AND `pm2`.`meta_key` = 'pick_val'
                    AND `pm2`.`meta_value` = '{$old_name}'
            " );

            if ( !empty( $fields ) ) {
                foreach ( $fields as $field ) {
                    update_post_meta( $field->ID, 'pick_object', $pod[ 'type' ] );
                    update_post_meta( $field->ID, 'pick_val', $params->name );
                }
            }

            $fields = pods_query( "
                SELECT `p`.`ID`
                FROM `{$wpdb->posts}` AS `p`
                LEFT JOIN `{$wpdb->postmeta}` AS `pm` ON `pm`.`post_id` = `p`.`ID`
                WHERE
                    `p`.`post_type` = '_pods_field'
                    AND `pm`.`meta_key` = 'pick_object'
                    AND (
                    	`pm`.`meta_value` = 'pod-{$old_name}'
                    	OR `pm`.`meta_value` = '" . $pod[ 'type' ] . "-{$old_name}'
					)
            " );

            if ( !empty( $fields ) ) {
                foreach ( $fields as $field ) {
                    update_post_meta( $field->ID, 'pick_object', $pod[ 'type' ] );
                    update_post_meta( $field->ID, 'pick_val', $params->name );
                }
            }
        }

        // Sync built-in options for post types and taxonomies
        if ( in_array( $pod[ 'type' ], array( 'post_type', 'taxonomy' ) ) && empty( $pod[ 'object' ] ) && true === $params->db ) {
            // Build list of 'built_in' for later
            $built_in = array();

            foreach ( $pod as $key => $val ) {
                if ( false === strpos( $key, 'built_in_' ) )
                    continue;
                elseif ( false !== strpos( $key, 'built_in_post_types_' ) )
                    $built_in_type = 'post_type';
                elseif ( false !== strpos( $key, 'built_in_taxonomies_' ) )
                    $built_in_type = 'taxonomy';
                else
                    continue;

                if ( $built_in_type == $pod[ 'type' ] )
                    continue;

                if ( !isset( $built_in[ $built_in_type ] ) )
                    $built_in[ $built_in_type ] = array();

                $built_in_object = str_replace( array( 'built_in_post_types_', 'built_in_taxonomies_' ), '', $key );

                $built_in[ $built_in_type ][ $built_in_object ] = (int) $val;
            }

            $lookup_option = $lookup_built_in = false;

            $lookup_name = $pod[ 'name' ];

            if ( 'post_type' == $pod[ 'type' ] ) {
                $lookup_option = 'built_in_post_types_' . $lookup_name;
                $lookup_built_in = 'taxonomy';
            }
            elseif ( 'taxonomy' == $pod[ 'type' ] ) {
                $lookup_option = 'built_in_taxonomies_' . $lookup_name;
                $lookup_built_in = 'post_type';
            }

            if ( !empty( $lookup_option ) && !empty( $lookup_built_in ) && isset( $built_in[ $lookup_built_in ] ) ) {
                foreach ( $built_in[ $lookup_built_in ] as $built_in_object => $val ) {
                    $search_val = 1;

                    if ( 1 == $val )
                        $search_val = 0;

                    $query = "SELECT p.ID FROM {$wpdb->posts} AS p
                                LEFT JOIN {$wpdb->postmeta} AS pm ON pm.post_id = p.ID AND pm.meta_key = '{$lookup_option}'
                                LEFT JOIN {$wpdb->postmeta} AS pm2 ON pm2.post_id = p.ID AND pm2.meta_key = 'type' AND pm2.meta_value = '{$lookup_built_in}'
                                LEFT JOIN {$wpdb->postmeta} AS pm3 ON pm3.post_id = p.ID AND pm3.meta_key = 'object' AND pm3.meta_value = ''
                                WHERE p.post_type = '_pods_pod' AND p.post_name = '{$built_in_object}'
                                    AND pm2.meta_id IS NOT NULL
                                    AND ( pm.meta_id IS NULL OR pm.meta_value = {$search_val} )";

                    $results = pods_query( $query );

                    if ( !empty( $results ) ) {
                        foreach ( $results as $the_pod ) {
                            delete_post_meta( $the_pod->ID, $lookup_option );

                            add_post_meta( $the_pod->ID, $lookup_option, $val );
                        }
                    }
                }
            }
        }

        $saved = array();
        $errors = array();

        $field_index_change = false;
        $field_index_id = 0;

        $id_required = false;

        $field_index = pods_var( 'pod_index', $pod, 'id', null, true );

        if ( 'pod' == $pod[ 'type' ] && !empty( $pod_fields ) && isset( $pod_fields[ $field_index ] ) )
            $field_index_id = $pod_fields[ $field_index ];

        if ( isset( $params->fields ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            $fields = array();

            if ( isset( $params->fields ) ) {
                $params->fields = (array) $params->fields;

                $weight = 0;

                foreach ( $params->fields as $field ) {
                    if ( !is_array( $field ) || !isset( $field[ 'name' ] ) )
                        continue;

                    if ( !isset( $field[ 'weight' ] ) ) {
                        $field[ 'weight' ] = $weight;

                        $weight++;
                    }

                    $fields[ $field[ 'name' ] ] = $field;
                }
            }

            $weight = 0;

            $saved_field_ids = array();

            foreach ( $pod_fields as $k => $field ) {
                if ( !empty( $old_id ) && ( !is_array( $field ) || !isset( $field[ 'name' ] ) || !isset( $fields[ $field[ 'name' ] ] ) ) ) {
                    // Iterative change handling for setup-edit.php
                    if ( !is_array( $field ) && isset( $old_fields[ $k ] ) )
                        $saved[ $old_fields[ $k ][ 'name' ] ] = true;

                    continue;
                }

                if ( !empty( $old_id ) )
                    $field = array_merge( $field, $fields[ $field[ 'name' ] ] );

                $field[ 'pod' ] = $pod;

                if ( !isset( $field[ 'weight' ] ) ) {
                    $field[ 'weight' ] = $weight;

                    $weight++;
                }

                if ( 0 < $field_index_id && pods_var( 'id', $field ) == $field_index_id )
                    $field_index_change = $field[ 'name' ];

                if ( 0 < pods_var( 'id', $field ) )
                    $id_required = true;

                if ( $id_required )
                    $field[ 'id_required' ] = true;

                $field_data = $field;

                $field = $api->save_field( $field_data, $field_table_operation, true, $params->db );

                if ( true === $params->db ) {
                    if ( !empty( $field ) && 0 < $field ) {
                        $saved[ $field_data[ 'name' ] ] = true;
                        $saved_field_ids[] = $field;
                    }
                    else
                        $errors[] = sprintf( __( 'Cannot save the %s field', 'pods' ), $field_data[ 'name' ] );
                }
				else {
                    $pod_fields[ $k ] = $field;
                    $saved_field_ids[] = $field[ 'id' ];
				}
            }

            if ( true === $params->db ) {
                foreach ( $old_fields as $field ) {
                    if ( isset( $pod_fields[ $field[ 'name' ] ] ) || isset( $saved[ $field[ 'name' ] ] ) || in_array( $field[ 'id' ], $saved_field_ids ) )
                        continue;

                    if ( $field[ 'id' ] == $field_index_id )
                        $field_index_change = 'id';
                    elseif ( $field[ 'name' ] == $field_index )
                        $field_index_change = 'id';

                    $api->delete_field( array(
                        'id' => (int) $field[ 'id' ],
                        'name' => $field[ 'name' ],
                        'pod' => $pod
                    ), $field_table_operation );
                }
            }

            // Update field index if the name has changed or the field has been removed
            if ( false !== $field_index_change ) {
				if ( true === $params->db ) {
                	update_post_meta( $pod[ 'id' ], 'pod_index', $field_index_change );
				}

				$this->offsetSet( 'pod_index', $field_index_change );
			}
        }

        if ( !empty( $errors ) )
            return pods_error( $errors );

		// Refresh fields
		$this->_fields = array();
		$this->fields();

        $api->cache_flush_pods( $pod );

        // Register Post Types / Taxonomies / Comment Types post-registration from PodsInit
        if ( did_action( 'pods_setup_content_types' ) && in_array( $pod[ 'type' ], array( 'post_type', 'taxonomy', 'comment' ) ) && empty( $pod[ 'object' ] ) ) {
            global $pods_init;

            $pods_init->setup_content_types( true );
        }

		$id = $pod[ 'id' ];

		if ( 0 < $id ) {
			$this->_action( 'pods_object_save', $pod[ 'id' ], $pod[ 'name' ], $pod[ 'parent' ], $pod );
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