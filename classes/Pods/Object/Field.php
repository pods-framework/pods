<?php
/**
 * @package Pods
 * @category Object Types
 */

/**
 * Class Pods_Object_Field
 *
 * @property int|string $sister_id Sister ID
 * @property Pods_Object_Field[] $fields Fields
 * @property array $table_info Table information for Object
 */
class Pods_Object_Field extends
	Pods_Object {

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
		'sister_field_id' => 'sister_id',
		'ID'              => 'id',
		'post_title'      => 'label',
		'post_name'       => 'name',
		'post_content'    => 'description',
		'post_parent'     => 'parent_id',
		'pod_id'          => 'parent_id'
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
	 * {@inheritDocs}
	 */
	public function load( $name = null, $id = 0, $parent = null ) {

		// Post Object
		$_object = false;

		// Custom Object
		$object = false;

		if ( null === $name && 0 == $id && null === $parent ) {
			// Allow for refresh of object
			if ( $this->is_valid() ) {
				$id = $this->_object['id'];

				$this->destroy();
			} // Empty object
			else {
				return false;
			}
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
		elseif ( ! is_object( $name ) ) {
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
				'help'        => '',
				'type'        => 'text',
				'weight'      => 0,
				'parent_id'   => $parent_id,
				'pod'         => '',
				'pod_id'      => '',
				'group_id'    => ''
			);

			if ( ! empty( $_object ) ) {
				$object = array(
					'id'          => $_object['ID'],
					'name'        => $_object['post_name'],
					'label'       => $_object['post_title'],
					'description' => $_object['post_content'],
					'parent_id'   => $_object['post_parent'],
					'type'        => 'text'
				);
			}

			$object = array_merge( $defaults, $object );

			if ( ! empty( $object['parent_id'] ) ) {
				$parent = get_post( $object['parent_id'] );

				if ( ! empty( $parent ) ) {
					if ( '_pods_group' == $parent->post_type ) {
						$object['group_id'] = $parent->post_parent;

						$group_pod = get_post( $parent->post_parent );

						if ( ! empty( $group_pod ) && '_pods_pod' == $group_pod->post_type ) {
							$object['pod']    = $group_pod->post_name;
							$object['pod_id'] = $group_pod->ID;
						}
					} elseif ( '_pods_pod' == $parent->post_type ) {
						$object['pod']    = $parent->post_name;
						$object['pod_id'] = $parent->ID;
					}
				}
			}

			if ( strlen( $object['label'] ) < 1 ) {
				$object['label'] = $object['name'];
			}

			$tableless_meta = array(
				'pick_object',
				'pick_val',
				'pick_simple',
				'sister_id'
			);

			$file_meta = array(
				'file_simple'
			);

			if ( 0 < $object['id'] ) {
				$meta = array(
					'type',
					'group_id',
					'default_value'
				);

				foreach ( $meta as $meta_key ) {
					$value = $this->_meta( $meta_key, $object['id'], true );

					if ( null !== $value ) {
						$object[ $meta_key ] = $value;
					}
				}

				if ( in_array( $object['type'], Pods_Form::tableless_field_types() ) ) {
					foreach ( $tableless_meta as $meta_key ) {
						$value = $this->_meta( $meta_key, $object['id'], true );

						if ( null !== $value ) {
							$object[ $meta_key ] = $value;
						}
					}

					// Backwards compatibility
					if ( pods_allow_deprecated() && ! isset( $object['sister_id'] ) ) {
						$value = $this->_meta( 'sister_field_id', $object['id'] );

						if ( null !== $value ) {
							$object['sister_id'] = $value;
						}
					}
				}

				if ( in_array( $object['type'], Pods_Form::file_field_types() ) ) {
					foreach ( $file_meta as $meta_key ) {
						$value = $this->_meta( $meta_key, $object['id'], true );

						if ( null !== $value ) {
							$object[ $meta_key ] = $value;
						}
					}
				}
			}

			// Force field type
			if ( empty( $object['type'] ) ) {
				$object['type'] = 'text';
			}

			$object['group_id'] = (int) $object['group_id'];

			if ( in_array( $object['type'], Pods_Form::tableless_field_types() ) ) {
				// Backwards compatibility
				if ( pods_allow_deprecated() && isset( $object['sister_field_id'] ) ) {
					if ( isset( $object['sister_id'] ) ) {
						$object['sister_id'] = $object['sister_field_id'];
					} else {
						$object['sister_id'] = '';
					}

					unset( $object['sister_field_id'] );
				}

				foreach ( $tableless_meta as $meta_key ) {
					if ( ! isset( $object[ $meta_key ] ) || empty( $object['pick_object'] ) ) {
						$object[ $meta_key ] = '';
					}
				}
			}

			if ( in_array( $object['type'], Pods_Form::file_field_types() ) ) {
				foreach ( $file_meta as $meta_key ) {
					if ( ! isset( $object[ $meta_key ] ) ) {
						$object[ $meta_key ] = '';
					}
				}
			}

			$this->_object = $object;

			// @todo Move this from Pods_Form::options_setup and Pods_Form::options
			//$options = apply_filters( 'pods_field_' . $type . '_options', (array) Pods_Form::field_method( $type, 'options' ), $type );

			return $this->_object['id'];
		}

		return false;

	}

	/**
	 * {@inheritDocs}
	 */
	public function exists( $name = null, $id = 0, $parent = null ) {

		$field = pods_object_field( $name, $id, false, $parent );

		if ( ! empty( $field ) && $field->is_valid() ) {
			return true;
		}

		return false;

	}

	/**
	 * {@inheritDocs}
	 */
	public function table_info() {

		if ( ! $this->is_valid() ) {
			return array();
		}

		if ( empty( $this->_table_info ) ) {
			$this->_table_info = null;

			// Load Attachment table info
			if ( in_array( $this->_object['type'], Pods_Form::file_field_types() ) ) {
				$this->_table_info = pods_api()->get_table_info( 'post_type', 'attachment' );
			} // Load Related object table info
			elseif ( in_array( $this->_object['type'], Pods_Form::tableless_field_types() ) ) {
				$simple_tableless_objects = Pods_Form::simple_tableless_objects();

				if ( 'taxonomy' == $this->_object['type'] ) {
					$this->_table_info = pods_api()->get_table_info( 'taxonomy', $this->_object['name'] );
				} elseif ( ! in_array( $this->_object['pick_object'], $simple_tableless_objects ) ) {
					$this->_table_info = pods_api()->get_table_info( $this->_object['pick_object'], $this->_object['pick_val'], null, null, $this );
				}
			}

			// Load Pod object table info
			if ( null === $this->_table_info ) {
				$this->_table_info = array();

				$pod = pods_object_pod( $this->_object['pod'], $this->_object['pod_id'] );

				if ( ! empty( $pod ) && $pod->is_valid() ) {
					$this->_table_info = $pod['table_info'];
				}
			}
		}

		return $this->_table_info;

	}

	/**
	 * Setup fields for traversal
	 *
	 * @param array  $fields Associative array of fields data
	 *
	 * @return array Traverse feed
	 *
	 * @param object $params (optional) Parameters from build()
	 *
	 * @since 3.0.0
	 */
	public function traverse_build( $fields = null, $params = null ) {

		if ( null === $fields ) {
			$fields = $this->fields;
		}

		$feed = array();

		foreach ( $fields as $field => $data ) {
			if ( ! is_array( $data ) ) {
				$field = $data;
			}

			if ( ! isset( $_GET[ 'filter_' . $field ] ) ) {
				continue;
			}

			$field_value = pods_v( 'filter_' . $field, 'get', false, true );

			if ( ! empty( $field_value ) || 0 < strlen( $field_value ) ) {
				$feed[ 'traverse_' . $field ] = array( $field );
			}
		}

		return $feed;

	}

	/**
	 * Recursively join tables based on fields
	 *
	 * @param array $traverse_recurse Array of traversal options
	 *
	 * @return array Array of table joins
	 *
	 * @since 3.0.0
	 */
	public function traverse_recurse( $traverse_recurse ) {

		global $wpdb;

		static $api = null;

		if ( ! $api ) {
			$api = pods_api();
		}

		$defaults = array(
			'pod'             => null,
			'fields'          => array(),
			'joined'          => 't',
			'depth'           => 0,
			'joined_id'       => 'id',
			'joined_index'    => 'id',
			'params'          => new stdClass(),
			'last_table_info' => array()
		);

		$traverse_recurse = array_merge( $defaults, $traverse_recurse );

		$joins = array();

		if ( 0 == $traverse_recurse['depth'] && ! empty( $traverse_recurse['pod'] ) && ! empty( $traverse_recurse ['last_table_info'] ) && isset( $traverse_recurse ['last_table_info']['id'] ) ) {
			$pod_data = $traverse_recurse ['last_table_info'];
		} elseif ( empty( $traverse_recurse['pod'] ) ) {
			if ( ! empty( $traverse_recurse['params'] ) && ! empty( $traverse_recurse['params']->table ) && 0 === strpos( $traverse_recurse['params']->table, $wpdb->prefix ) ) {
				if ( $wpdb->posts == $traverse_recurse['params']->table ) {
					$traverse_recurse['pod'] = 'post_type';
				} elseif ( $wpdb->terms == $traverse_recurse['params']->table ) {
					$traverse_recurse['pod'] = 'taxonomy';
				} elseif ( $wpdb->users == $traverse_recurse['params']->table ) {
					$traverse_recurse['pod'] = 'user';
				} elseif ( $wpdb->comments == $traverse_recurse['params']->table ) {
					$traverse_recurse['pod'] = 'comment';
				} else {
					return $joins;
				}

				$pod_data = array();

				if ( in_array( $traverse_recurse['pod'], array( 'user', 'comment' ) ) ) {
					$pod = $api->load_pod( array( 'name' => $traverse_recurse['pod'] ) );

					if ( ! empty( $pod ) && $pod['type'] == $pod ) {
						$pod_data = $pod;
					}
				}

				if ( empty( $pod_data ) ) {
					$pod_data = array(
						'id'            => 0,
						'name'          => '_table_' . $traverse_recurse['pod'],
						'type'          => $traverse_recurse['pod'],
						'storage'       => ( 'taxonomy' == $traverse_recurse['pod'] ? 'none' : 'meta' ),
						'fields'        => array(),
						'object_fields' => $api->get_wp_object_fields( $traverse_recurse['pod'] )
					);

					$pod_data = array_merge( $api->get_table_info( $traverse_recurse['pod'], '' ), $pod_data );
				}

				$traverse_recurse['pod'] = $pod_data['name'];
			} else {
				return $joins;
			}
		} else {
			$pod_data = $api->load_pod( array( 'name' => $traverse_recurse['pod'] ), __METHOD__ );

			if ( empty( $pod_data ) ) {
				return $joins;
			}
		}

		if ( isset( $pod_data['object_fields'] ) ) {
			$pod_data['fields'] = array_merge( $pod_data['fields'], $pod_data['object_fields'] );
		}

		$tableless_field_types    = Pods_Form::tableless_field_types();
		$simple_tableless_objects = Pods_Form::simple_tableless_objects();
		$file_field_types         = Pods_Form::file_field_types();

		if ( ! isset( $this->traversal[ $traverse_recurse['pod'] ] ) ) {
			$this->traversal[ $traverse_recurse['pod'] ] = array();
		}

		if ( ( empty( $pod_data['meta_table'] ) || $pod_data['meta_table'] == $pod_data['table'] ) && ( empty( $traverse_recurse['fields'] ) || ! isset( $traverse_recurse['fields'][ $traverse_recurse['depth'] ] ) || empty( $traverse_recurse['fields'][ $traverse_recurse['depth'] ] ) ) ) {
			return $joins;
		}

		$field = $traverse_recurse['fields'][ $traverse_recurse['depth'] ];

		$ignore_aliases = array(
			'wpml_languages',
			'polylang_languages'
		);

		$ignore_aliases = $this->do_hook( 'traverse_recurse_ignore_aliases', $ignore_aliases, $field, $traverse_recurse );

		if ( in_array( $field, $ignore_aliases ) ) {
			return $joins;
		}

		$meta_data_table = false;

		if ( ! isset( $pod_data['fields'][ $field ] ) && 'd' == $field && isset( $traverse_recurse['fields'][ $traverse_recurse['depth'] - 1 ] ) ) {
			$field = $traverse_recurse['fields'][ $traverse_recurse['depth'] - 1 ];

			$field_type = 'pick';

			if ( isset( $traverse_recurse['last_table_info']['pod']['fields'][ $field ] ) ) {
				$field_type = $traverse_recurse['last_table_info']['pod']['fields'][ $field ]['type'];
			} elseif ( isset( $traverse_recurse['last_table_info']['pod']['object_fields'][ $field ] ) ) {
				$field_type = $traverse_recurse['last_table_info']['pod']['object_fields'][ $field ]['type'];
			}

			$pod_data['fields'][ $field ] = array(
				'id'          => 0,
				'name'        => $field,
				'type'        => $field_type,
				'pick_object' => $traverse_recurse['last_table_info']['pod']['type'],
				'pick_val'    => $traverse_recurse['last_table_info']['pod']['name']
			);

			$meta_data_table = true;
		}

		// Fallback to meta table if the pod type supports it
		if ( ! isset( $pod_data['fields'][ $field ] ) ) {
			$last = end( $traverse_recurse['fields'] );

			if ( 'post_type' == $pod_data['type'] && ! isset( $pod_data['object_fields'] ) ) {
				$pod_data['object_fields'] = $api->get_wp_object_fields( 'post_type', $pod_data );
			}

			if ( 'post_type' == $pod_data['type'] && isset( $pod_data['object_fields'][ $field ] ) && in_array( $pod_data['object_fields'][ $field ]['type'], $tableless_field_types ) ) {
				$pod_data['fields'][ $field ] = $pod_data['object_fields'][ $field ];
			} elseif ( in_array( $pod_data['type'],
				array(
					'post_type',
					'media',
					'user',
					'comment'
				) ) && 'meta_value' == $last
			) {
				$pod_data['fields'][ $field ] = Pods_Form::field_setup( array( 'name' => $field ) );
			} else {
				if ( 'post_type' == $pod_data['type'] ) {
					$pod_data['object_fields'] = $api->get_wp_object_fields( 'post_type', $pod_data, true );

					if ( 'post_type' == $pod_data['type'] && isset( $pod_data['object_fields'][ $field ] ) && in_array( $pod_data['object_fields'][ $field ]['type'], $tableless_field_types ) ) {
						$pod_data['fields'][ $field ] = $pod_data['object_fields'][ $field ];
					} else {
						return $joins;
					}
				} else {
					return $joins;
				}
			}
		}

		$traverse = $pod_data['fields'][ $field ];

		if ( 'taxonomy' == $traverse['type'] ) {
			$traverse['table_info'] = $api->get_table_info( $traverse['type'], $traverse['name'] );
		} elseif ( in_array( $traverse['type'], $file_field_types ) ) {
			$traverse['table_info'] = $api->get_table_info( 'post_type', 'attachment' );
		} elseif ( ! in_array( $traverse['type'], $tableless_field_types ) ) {
			$traverse['table_info'] = $api->get_table_info( $pod_data['type'], $pod_data['name'], $pod_data['name'], $pod_data );
		} elseif ( empty( $traverse['table_info'] ) || ( in_array( $traverse['pick_object'], $simple_tableless_objects ) && ! empty( $traverse_recurse['last_table_info'] ) ) ) {
			if ( in_array( $traverse['pick_object'], $simple_tableless_objects ) && ! empty( $traverse_recurse['last_table_info'] ) ) {
				$traverse['table_info'] = $traverse_recurse['last_table_info'];

				if ( ! empty( $traverse['table_info']['meta_table'] ) ) {
					$meta_data_table = true;
				}
			} elseif ( ! in_array( $traverse['type'], $tableless_field_types ) && isset( $traverse_recurse['last_table_info'] ) && ! empty( $traverse_recurse['last_table_info'] ) && 0 == $traverse_recurse['depth'] ) {
				$traverse['table_info'] = $traverse_recurse['last_table_info'];
			} else {
				$traverse['table_info'] = $api->get_table_info( $traverse['pick_object'], $traverse['pick_val'], null, $traverse['pod'], $traverse );
			}
		}

		if ( isset( $this->traversal[ $traverse_recurse['pod'] ][ $traverse['name'] ] ) ) {
			$traverse = array_merge( $traverse, (array) $this->traversal[ $traverse_recurse['pod'] ][ $traverse['name'] ] );
		}

		$traverse = $this->do_hook( 'traverse', $traverse, compact( 'pod', 'fields', 'joined', 'depth', 'joined_id', 'params' ) );

		if ( empty( $traverse ) ) {
			return $joins;
		}

		$traverse['id']   = (int) $traverse['id'];
		$traverse['name'] = pods_sanitize( $traverse['name'] );

		if ( empty( $traverse['id'] ) ) {
			$traverse['id'] = $field;
		}

		$table_info = $traverse['table_info'];

		$this->traversal[ $traverse_recurse['pod'] ][ $field ] = $traverse;

		$field_joined = $field;

		if ( 0 < $traverse_recurse['depth'] && 't' != $traverse_recurse['joined'] ) {
			if ( $meta_data_table && ( 'pick' != $traverse['type'] || ! in_array( pods_v( 'pick_object', $traverse ), $simple_tableless_objects ) ) ) {
				$field_joined = $traverse_recurse['joined'] . '_d';
			} else {
				$field_joined = $traverse_recurse['joined'] . '_' . $field;
			}
		}

		$rel_alias = 'rel_' . $field_joined;

		if ( pods_v( 'search', $traverse_recurse['params'], false ) && empty( $traverse_recurse['params']->filters ) ) {
			if ( 0 < strlen( pods_v( 'filter_' . $field_joined ) ) ) {
				$val = absint( pods_v( 'filter_' . $field_joined ) );

				$search = "`{$field_joined}`.`{$table_info['field_id']}` = {$val}";

				if ( 'text' == $this->search_mode ) {
					$val = pods_v( 'filter_' . $field_joined );

					$search = "`{$field_joined}`.`{$traverse['name']}` = '{$val}'";
				} elseif ( 'text_like' == $this->search_mode ) {
					$val = pods_sanitize( pods_sanitize_like( pods_var_raw( 'filter_' . $field_joined ) ) );

					$search = "`{$field_joined}`.`{$traverse['name']}` LIKE '%{$val}%'";
				}

				$this->search_where[] = " {$search} ";
			}
		}

		$the_join = null;

		$joined_id    = $table_info['field_id'];
		$joined_index = $table_info['field_index'];

		if ( 'taxonomy' == $traverse['type'] ) {
			$rel_tt_alias = 'rel_tt_' . $field_joined;

			if ( $meta_data_table ) {
				$the_join = "
                    LEFT JOIN `{$table_info['pod_table']}` AS `{$field_joined}` ON
                        `{$field_joined}`.`{$table_info['pod_field_id']}` = `{$traverse_recurse['rel_alias']}`.`{$traverse_recurse['joined_id']}`
                ";
			} else {
				$the_join = "
                    LEFT JOIN `{$wpdb->term_relationships}` AS `{$rel_alias}` ON
                        `{$rel_alias}`.`object_id` = `{$traverse_recurse['joined']}`.`ID`

                    LEFT JOIN `{$wpdb->term_taxonomy}` AS `{$rel_tt_alias}` ON
                        `{$rel_tt_alias}`.`taxonomy` = '{$traverse['name']}'
                        AND `{$rel_tt_alias}`.`term_taxonomy_id` = `{$rel_alias}`.`term_taxonomy_id`

                    LEFT JOIN `{$table_info['table']}` AS `{$field_joined}` ON
                        `{$field_joined}`.`{$table_info['field_id']}` = `{$rel_tt_alias}`.`{$table_info['field_id']}`
                ";

				// Override $rel_alias
				$rel_alias = $field_joined;

				$joined_id    = $table_info['field_id'];
				$joined_index = $table_info['field_index'];
			}
		} elseif ( in_array( $traverse['type'], $tableless_field_types ) && ( 'pick' != $traverse['type'] || ! in_array( pods_v( 'pick_object', $traverse ), $simple_tableless_objects ) ) ) {
			if ( pods_tableless() ) {
				$the_join = "
                    LEFT JOIN `{$table_info['meta_table']}` AS `{$rel_alias}` ON
                        `{$rel_alias}`.`{$table_info['meta_field_index']}` = '{$traverse['name']}'
                        AND `{$rel_alias}`.`{$table_info['meta_field_id']}` = `{$traverse_recurse['joined']}`.`{$traverse_recurse['joined_id']}`

                    LEFT JOIN `{$table_info['meta_table']}` AS `{$field_joined}` ON
                        `{$field_joined}`.`{$table_info['meta_field_index']}` = '{$traverse['name']}'
                        AND `{$field_joined}`.`{$table_info['meta_field_id']}` = CONVERT( `{$rel_alias}`.`{$table_info['meta_field_value']}`, SIGNED )
                ";

				$joined_id    = $table_info['meta_field_id'];
				$joined_index = $table_info['meta_field_index'];
			} elseif ( $meta_data_table ) {
				$the_join = "
                    LEFT JOIN `{$table_info['pod_table']}` AS `{$field_joined}` ON
                        `{$field_joined}`.`{$table_info['pod_field_id']}` = `{$traverse_recurse['rel_alias']}`.`{$traverse_recurse['joined_id']}`
                ";
			} else {
				$the_join = "
                    LEFT JOIN `@wp_podsrel` AS `{$rel_alias}` ON
                        `{$rel_alias}`.`field_id` = {$traverse['id']}
                        AND `{$rel_alias}`.`item_id` = `{$traverse_recurse['joined']}`.`{$traverse_recurse['joined_id']}`

                    LEFT JOIN `{$table_info['table']}` AS `{$field_joined}` ON
                        `{$field_joined}`.`{$table_info['field_id']}` = `{$rel_alias}`.`related_item_id`
                ";
			}
		} elseif ( 'meta' == $pod_data['storage'] ) {
			if ( ( $traverse_recurse['depth'] + 2 ) == count( $traverse_recurse['fields'] ) && ( 'pick' != $traverse['type'] || ! in_array( pods_v( 'pick_object', $traverse ), $simple_tableless_objects ) ) && $table_info['meta_field_value'] == $traverse_recurse['fields'][ $traverse_recurse['depth'] + 1 ] ) {
				$the_join = "
                    LEFT JOIN `{$table_info['meta_table']}` AS `{$field_joined}` ON
                        `{$field_joined}`.`{$table_info['meta_field_index']}` = '{$traverse['name']}'
                        AND `{$field_joined}`.`{$table_info['meta_field_id']}` = `{$traverse_recurse['joined']}`.`{$traverse_recurse['joined_id']}`
                ";

				$table_info['recurse'] = false;
			} else {
				$the_join = "
                    LEFT JOIN `{$table_info['meta_table']}` AS `{$field_joined}` ON
                        `{$field_joined}`.`{$table_info['meta_field_index']}` = '{$traverse['name']}'
                        AND `{$field_joined}`.`{$table_info['meta_field_id']}` = `{$traverse_recurse['joined']}`.`{$traverse_recurse['joined_id']}`
                ";

				$joined_id    = $table_info['meta_field_id'];
				$joined_index = $table_info['meta_field_index'];
			}
		}

		$traverse_recursive = array(
			'pod'             => pods_var_raw( 'name', pods_v( 'pod', $table_info ) ),
			'fields'          => $traverse_recurse['fields'],
			'joined'          => $field_joined,
			'depth'           => ( $traverse_recurse['depth'] + 1 ),
			'joined_id'       => $joined_id,
			'joined_index'    => $joined_index,
			'params'          => $traverse_recurse['params'],
			'rel_alias'       => $rel_alias,
			'last_table_info' => $table_info
		);

		$the_join = $this->do_hook( 'traverse_the_join', $the_join, $traverse_recurse, $traverse_recursive );

		if ( empty( $the_join ) ) {
			return $joins;
		}

		$joins[ $traverse_recurse['pod'] . '_' . $traverse_recurse['depth'] . '_' . $traverse['id'] ] = $the_join;

		if ( ( $traverse_recurse['depth'] + 1 ) < count( $traverse_recurse['fields'] ) && ! empty( $traverse_recurse['pod'] ) && false !== $table_info['recurse'] ) {
			$joins = array_merge( $joins, $this->traverse_recurse( $traverse_recursive ) );
		}

		return $joins;

	}

	/**
	 * Recursively join tables based on fields
	 *
	 * @param array  $fields     Fields to recurse
	 * @param null   $all_fields (optional) If $fields is empty then traverse all fields, argument does not need to be passed
	 * @param object $params     (optional) Parameters from build()
	 *
	 * @return array Array of joins
	 */
	public function traverse( $fields = null, $all_fields = null, $params = null ) {

		$joins = array();

		if ( null === $fields ) {
			$fields = $this->traverse_build( $all_fields, $params );
		}

		foreach ( (array) $fields as $field_group ) {
			$traverse_recurse = array(
				'pod'             => $this->pod,
				'fields'          => $fields,
				'params'          => $params,
				'last_table_info' => $this->pod_data,
				'joined_id'       => $this->pod_data['field_id'],
				'joined_index'    => $this->pod_data['field_index']
			);

			if ( is_array( $field_group ) ) {
				$traverse_recurse['fields'] = $field_group;

				$joins = array_merge( $joins, $this->traverse_recurse( $traverse_recurse ) );
			} else {
				$joins = array_merge( $joins, $this->traverse_recurse( $traverse_recurse ) );
				$joins = array_filter( $joins );

				return $joins;
			}
		}

		$joins = array_filter( $joins );

		return $joins;

	}

	/**
	 * Return a field input for a specific field
	 *
	 * @param array|string $field Input field name to use (overrides default name)
	 * @param null         $input_name
	 * @param mixed        $value Current value to use
	 * @param array        $options
	 * @param null         $pod
	 * @param null         $id
	 *
	 * @return string Field Input HTML
	 *
	 * @since 3.0.0
	 */
	public function input( $field, $input_name = null, $value = null, $options = array(), $pod = null, $id = null ) {

		// Field data override
		if ( is_array( $field ) ) {
			$field_data = pods_object_field( $field, 0, false, $this->_object['paren_id'] );
			$field      = pods_v( 'name', $field );
		} // Get field data from field name
		else {
			$field_data = $this->fields( $field );
		}

		if ( ! empty( $field_data ) ) {
			$field_type = $field_data['type'];

			if ( empty( $input_name ) ) {
				$input_name = $field;
			}

			return Pods_Form::field( $input_name, $value, $field_type, $field_data, $pod, $id );
		}

		return '';

	}

	/**
	 * {@inheritDocs}
	 */
	public function save( $options = null, $value = null, $refresh = true ) {

		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		if ( null !== $value && ! is_array( $options ) && ! is_object( $options ) ) {
			$options = array(
				$options => $value
			);
		}

		if ( empty( $options ) ) {
			if ( $this->is_valid() ) {
				return $this->_object['id'];
			}

			return false;
		} elseif ( ! is_array( $options ) && ! is_object( $options ) ) {
			return false;
		}

		$tableless_field_types    = Pods_Form::tableless_field_types();
		$simple_tableless_objects = Pods_Form::simple_tableless_objects();

		$params = (object) $options;

		if ( $this->is_valid() ) {
			$params->id = $this->_object['id'];
		} elseif ( ! isset( $params->id ) ) {
			$params->id = 0;
		}

		if ( ! isset( $params->table_operation ) ) {
			$params->table_operation = true;
		}

		if ( ! isset( $params->db ) ) {
			$params->db = true;
		} elseif ( true !== $params->db ) {
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

		$pod         = null;
		$save_pod    = false;
		$id_required = false;

		if ( isset( $params->id_required ) ) {
			unset( $params->id_required );

			$id_required = true;
		}

		if ( ( ! isset( $params->pod ) || empty( $params->pod ) ) && ( ! isset( $params->pod_id ) || empty( $params->pod_id ) ) ) {
			if ( $this->is_valid() ) {
				$pod = pods_object_pod( $this->_object['pod'], $this->_object['parent_id'] );

				if ( $pod->is_valid() ) {
					$params->pod_id = $pod['id'];
					$params->pod    = $pod['id'];
				}
			}

			if ( empty( $pod ) || ! $pod->is_valid() ) {
				return pods_error( __( 'Pod ID or name is required', 'pods' ), $this );
			}
		}

		if ( empty( $pod ) ) {
			if ( isset( $params->pod ) && ( is_array( $params->pod ) || is_object( $params->pod ) ) ) {
				$pod = $params->pod;

				$save_pod = true;
			} elseif ( ( ! isset( $params->pod_id ) || empty( $params->pod_id ) ) && ( true === $params->db || 0 < $params->db ) ) {
				$pod = pods_object_pod( $params->pod );
			} elseif ( ! isset( $params->pod ) && ( true === $params->db || 0 < $params->db ) ) {
				$pod = pods_object_pod( null, $params->pod_id );
			} elseif ( true === $params->db || 0 < $params->db ) {
				$pod = pods_object_pod( $params->pod, $params->pod_id );
			}

			if ( ( empty( $pod ) || ! $pod->is_valid() ) && true === $params->db ) {
				return pods_error( __( 'Pod not found', 'pods' ), $this );
			}
		}

		$params->pod_id = $pod['id'];
		$params->pod    = $pod['name'];

		if ( ! isset( $params->name ) && isset( $params->label ) ) {
			$params->name = $params->label;
		}

		if ( ! isset( $params->name ) ) {
			return pods_error( 'Pod field name is required', $this );
		}

		$params->name = pods_clean_name( $params->name, true, ( 'meta' == $pod['storage'] ? false : true ) );

		if ( ! isset( $params->label ) ) {
			$params->label = $params->name;
		}

		if ( ! isset( $params->id ) ) {
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

			$old_id        = $old_field['id'];
			$old_name      = pods_clean_name( $old_field['name'], true, ( 'meta' == $pod['storage'] ? false : true ) );
			$old_type      = $old_field['type'];
			$old_options   = $old_field->export();
			$old_sister_id = (int) pods_v( 'sister_id', $old_options, 0 );

			$old_simple = ( 'pick' == $old_type && in_array( pods_v( 'pick_object', $old_field ), $simple_tableless_objects ) );

			if ( isset( $params->name ) && ! empty( $params->name ) ) {
				$old_field['name'] = $params->name;
			}

			if ( $old_name != $old_field['name'] && $this->exists( $old_field['name'] ) ) {
				return pods_error( sprintf( __( 'Field %s already exists, you cannot rename %s to that', 'pods' ), $old_field['name'], $old_name ), $this );
			}

			if ( ( $id_required || ! empty( $params->id ) ) && ( empty( $old_id ) || $old_id != $params->id ) ) {
				return pods_error( sprintf( __( 'Field %s already exists', 'pods' ), $old_field['name'] ), $this );
			}

			if ( empty( $params->id ) ) {
				$params->id = $old_id;
			}

			if ( ! in_array( $old_type, $tableless_field_types ) || $old_simple ) {
				$definition = $api->get_field_definition( $old_type, $old_options );

				if ( 0 < strlen( $definition ) ) {
					$old_definition = "`{$old_name}` " . $definition;
				}
			}

			$field =& $this;
		} else {
			$field = array(
				'id'        => 0,
				'parent_id' => $params->pod_id,
				'name'      => $params->name,
				'label'     => $params->name,
				'type'      => 'text'
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

		if ( isset( $options['options'] ) ) {
			$options = array_merge( $options['options'], $options );

			unset( $options['options'] );
		}

		$field->override( $options );

		if ( strlen( $field['label'] ) < 1 ) {
			$field['label'] = $field['name'];
		}

		if ( in_array( $field['type'], $tableless_field_types ) ) {
			// Clean up special drop-down in field editor and save out pick_val
			if ( 0 === strpos( $field['pick_object'], 'pod-' ) ) {
				$field['pick_val']    = pods_str_replace( 'pod-', '', $field['pick_object'], 1 );
				$field['pick_object'] = 'pod';
			} elseif ( 0 === strpos( $field['pick_object'], 'post_type-' ) ) {
				$field['pick_val']    = pods_str_replace( 'post_type-', '', $field['pick_object'], 1 );
				$field['pick_object'] = 'post_type';
			} elseif ( 0 === strpos( $field['pick_object'], 'taxonomy-' ) ) {
				$field['pick_val']    = pods_str_replace( 'taxonomy-', '', $field['pick_object'], 1 );
				$field['pick_object'] = 'taxonomy';
			} elseif ( 'table' == $field['pick_object'] && 0 < strlen( $field['pick_table'] ) ) {
				$field['pick_val']    = $field['pick_table'];
				$field['pick_object'] = 'table';
			} elseif ( false === strpos( $field['pick_object'], '-' ) && ! in_array( $field['pick_object'], array( 'pod', 'post_type', 'taxonomy' ) ) ) {
				$field['pick_val'] = '';
			} elseif ( 'custom-simple' == $field['pick_object'] ) {
				$field['pick_val'] = '';
			}

			$field['sister_id'] = (int) $field['sister_id'];
		} else {
			$field['pick_val']    = '';
			$field['pick_object'] = '';
			$field['sister_id']   = 0;
		}

		$object_fields = $pod['object_fields'];

		if ( 0 < $old_id && defined( 'PODS_FIELD_STRICT' ) && ! PODS_FIELD_STRICT ) {
			$params->id = $field['id'] = $old_id;
		}

		// Add new field
		if ( ! isset( $params->id ) || empty( $params->id ) || empty( $field ) ) {
			if ( $params->table_operation && in_array( $field['name'], array( 'created', 'modified' ) ) && ! in_array( $field['type'], array( 'date', 'datetime' ) ) && ( ! defined( 'PODS_FIELD_STRICT' ) || PODS_FIELD_STRICT ) ) {
				return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $field['name'] ), $this );
			} elseif ( $params->table_operation && 'author' == $field['name'] && 'pick' != $field['type'] && ( ! defined( 'PODS_FIELD_STRICT' ) || PODS_FIELD_STRICT ) ) {
				return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $field['name'] ), $this );
			} elseif ( in_array( $field['name'], array( 'id', 'ID' ) ) ) {
				return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $field['name'] ), $this );
			}

			foreach ( $object_fields as $object_field => $object_field_opt ) {
				if ( $object_field == $field['name'] || in_array( $field['name'], $object_field_opt['alias'] ) ) {
					return pods_error( sprintf( __( '%s is reserved for internal WordPress or Pods usage, please try a different name. Also consider what WordPress and Pods provide you built-in.', 'pods' ), $field['name'] ), $this );
				}
			}

			// Reserved post_name values that can't be used as field names
			if ( in_array( $field['name'], array( 'rss' ) ) ) {
				$field['name'] .= '2';
			}

			if ( 'slug' == $field['type'] && true === $params->db ) {
				if ( in_array( $pod['type'], array( 'post_type', 'taxonomy', 'user' ) ) ) {
					return pods_error( __( 'This pod already has an internal WordPress permalink field', 'pods' ), $this );
				}

				$args = array(
					'post_type'      => '_pods_field',
					'orderby'        => 'menu_order',
					'order'          => 'ASC',
					'posts_per_page' => 1,
					'post_parent'    => $field['pod_id'],
					'meta_query'     => array(
						array(
							'key'   => 'type',
							'value' => 'slug'
						)
					)
				);

				$slug_field = get_posts( $args );

				if ( ! empty( $slug_field ) ) {
					return pods_error( __( 'This pod already has a permalink field', 'pods' ), $this );
				}
			}

			// Sink the new field to the bottom of the list
			if ( null === $field['weight'] ) {
				$field['weight'] = 0;

				$args = array(
					'post_type'      => '_pods_field',
					'orderby'        => 'menu_order',
					'order'          => 'DESC',
					'posts_per_page' => 1,
					'post_parent'    => $field['pod_id']
				);

				$bottom_most_field = get_posts( $args );

				if ( ! empty( $bottom_most_field ) ) {
					$field['weight'] = pods_absint( $bottom_most_field[0]->menu_order ) + 1;
				}
			}

			$field['weight'] = pods_absint( $field['weight'] );

			$post_data = array(
				'post_name'    => $field['name'],
				'post_title'   => $field['label'],
				'post_content' => $field['description'],
				'post_parent'  => $field['pod_id'],
				'post_type'    => '_pods_field',
				'post_status'  => 'publish',
				'menu_order'   => $field['weight']
			);
		} else {
			if ( in_array( $field['name'], array( 'id', 'ID' ) ) ) {
				if ( null !== $old_name ) {
					return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $field['name'] ), $this );
				} else {
					return pods_error( sprintf( __( '%s is not editable', 'pods' ), $field['name'] ), $this );
				}
			}

			if ( null !== $old_name && $field['name'] != $old_name && ( ! defined( 'PODS_FIELD_STRICT' ) || PODS_FIELD_STRICT ) ) {
				if ( in_array( $field['name'], array( 'created', 'modified' ) ) && ! in_array( $field['type'], array( 'date', 'datetime' ) ) ) {
					return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $field['name'] ), $this );
				} elseif ( 'author' == $field['name'] && 'pick' != $field['type'] ) {
					return pods_error( sprintf( __( '%s is reserved for internal Pods usage, please try a different name', 'pods' ), $field['name'] ), $this );
				}
			}

			foreach ( $object_fields as $object_field => $object_field_opt ) {
				if ( $object_field != $field['name'] && ! in_array( $field['name'], $object_field_opt['alias'] ) ) {
					continue;
				}

				if ( null !== $old_name ) {
					return pods_error( sprintf( __( '%s is reserved for internal WordPress or Pods usage, please try a different name', 'pods' ), $field['name'] ), $this );
				} else {
					return pods_error( sprintf( __( '%s is not editable', 'pods' ), $field['name'] ), $this );
				}
			}

			$post_data = array(
				'ID'           => $field['id'],
				'post_name'    => $field['name'],
				'post_title'   => $field['label'],
				'post_content' => $field['description'],
				'post_parent'  => $field['parent_id']
			);

			if ( null !== $field['weight'] ) {
				$field['weight'] = pods_absint( $field['weight'] );

				$post_data['menu_order'] = $field['weight'];
			}
		}

		if ( true === $params->db ) {
			if ( ! has_filter( 'wp_unique_post_slug', array( $api, 'save_slug_fix' ) ) ) {
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
		} else {
			$params->id = $field['name'];
		}

		$id = $field['id'] = $params->id;

		$simple = ( 'pick' == $field['type'] && in_array( pods_v( 'pick_object', $field ), $simple_tableless_objects ) );

		$definition = false;

		if ( ! in_array( $field['type'], $tableless_field_types ) || $simple ) {
			$field_definition = $api->get_field_definition( $field['type'], $field );

			if ( 0 < strlen( $field_definition ) ) {
				$definition = '`' . $field['name'] . '` ' . $field_definition;
			}
		}

		$sister_id = (int) $field['sister_id'];

		if ( $params->table_operation && 'table' == $pod['storage'] && ! pods_tableless() ) {
			if ( ! empty( $old_id ) ) {
				if ( ( $field['type'] != $old_type || $old_simple != $simple ) && empty( $definition ) ) {
					pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` DROP COLUMN `{$old_name}`", false );
				} elseif ( 0 < strlen( $definition ) ) {
					if ( $old_name != $field['name'] || $old_simple != $simple ) {
						$test = false;

						if ( 0 < strlen( $old_definition ) ) {
							$test = pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` CHANGE `{$old_name}` {$definition}", false );
						}

						// If the old field doesn't exist, continue to add a new field
						if ( false === $test ) {
							pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` ADD COLUMN {$definition}", __( 'Cannot create new field', 'pods' ) );
						}
					} elseif ( null !== $old_definition && $definition != $old_definition ) {
						$test = pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` CHANGE `{$old_name}` {$definition}", false );

						// If the old field doesn't exist, continue to add a new field
						if ( false === $test ) {
							pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` ADD COLUMN {$definition}", __( 'Cannot create new field', 'pods' ) );
						}
					}
				}
			} elseif ( 0 < strlen( $definition ) ) {
				$test = false;

				if ( 0 < strlen( $old_definition ) ) {
					$test = pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` CHANGE `" . $field['name'] . "` {$definition}", false );
				}

				// If the old field doesn't exist, continue to add a new field
				if ( false === $test ) {
					pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` ADD COLUMN {$definition}", __( 'Cannot create new field', 'pods' ) );
				}
			}
		}

		if ( ! empty( $old_id ) && 'meta' == $pod['storage'] && $old_name != $field['name'] && $pod['meta_table'] != $pod['table'] ) {
			$prepare = array(
				$field['name'],
				$old_name
			);

			// Users don't have a type
			if ( ! empty( $pod['field_type'] ) ) {
				$prepare[] = $pod['name'];
			}

			pods_query( "
                UPDATE `{$pod['meta_table']}` AS `m`
                LEFT JOIN `{$pod['table']}` AS `t`
                    ON `t`.`{$pod['field_id']}` = `m`.`{$pod['meta_field_id']}`
                SET
                    `m`.`{$pod['meta_field_index']}` = %s
                WHERE
                    `m`.`{$pod['meta_field_index']}` = %s
            " . ( ! empty( $pod['field_type'] ) ? " AND `t`.`{$pod['field_type']}` = %s" : "" ),
				$prepare );
		}

		if ( $field['type'] != $old_type && in_array( $old_type, $tableless_field_types ) ) {
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
				",
				array(
					$params->id
				) );

				if ( ! pods_tableless() ) {
					pods_query( "DELETE FROM @wp_podsrel WHERE `field_id` = {$params->id}", false );

					pods_query( "
						UPDATE `@wp_podsrel`
						SET `related_field_id` = 0
						WHERE `field_id` = %d
					",
					array(
						$old_sister_id
					) );
				}
			}
		} elseif ( 0 < $sister_id ) {
			update_post_meta( $sister_id, 'sister_id', $params->id );

			if ( true === $params->db && ! pods_tableless() ) {
				pods_query( "
					UPDATE `@wp_podsrel`
					SET `related_field_id` = %d
					WHERE `field_id` = %d
				",
				array(
					$params->id,
					$sister_id
				) );
			}
		} elseif ( 0 < $old_sister_id ) {
			delete_post_meta( $old_sister_id, 'sister_id' );

			if ( true === $params->db && ! pods_tableless() ) {
				pods_query( "
					UPDATE `@wp_podsrel`
					SET `related_field_id` = 0
					WHERE `field_id` = %d
				",
				array(
					$old_sister_id
				) );
			}
		}

		if ( ! empty( $old_id ) && $old_name != $field['name'] && true === $params->db ) {
			update_post_meta( $pod['id'], 'pod_index', $field['name'], $old_name );
		}

		if ( ! $save_pod ) {
			$api->cache_flush_pods( $pod );
		} else {
			pods_transient_clear( 'pods_field_' . $pod['name'] . '_' . $field['name'] );

			if ( ! empty( $old_id ) && $old_name != $field['name'] ) {
				pods_transient_clear( 'pods_field_' . $pod['name'] . '_' . $old_name );
			}
		}

		// Refresh object
		if ( $refresh ) {
			$id = $this->load( null, $id );
		} // Just update options
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
	 * {@inheritDocs}
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

		$params = (object) $options;

		if ( ! isset( $params->strict ) ) {
			$params->strict = pods_strict();
		}

		$params->name = $this->_object['name'];

		$params = apply_filters( 'pods_object_pre_duplicate_' . $this->_action_type, $params, $this );

		$field = $this->export();

		if ( in_array( $field['type'], array( 'avatar', 'slug' ) ) ) {
			if ( false !== $params->strict ) {
				return pods_error( __( 'Field not allowed to be duplicated', 'pods' ) );
			}

			return false;
		}

		$field['object'] = '';

		$field = array_merge( $field, $custom_options );

		unset( $field['id'] );

		if ( isset( $params->new_name ) ) {
			$field['name'] = $params->new_name;
		}

		$try = 2;

		$check_name = $field['name'] . $try;
		$new_label  = $field['label'] . $try;

		while ( $this->exists( $check_name, 0, $this->_object['parent'] ) ) {
			$try ++;

			$check_name = $field['name'] . $try;
			$new_label  = $field['label'] . $try;
		}

		$field['name']  = $check_name;
		$field['label'] = $new_label;

		$new_field = pods_object_field();

		$id = $new_field->save( $field );

		if ( 0 < $id ) {
			$this->_action( 'pods_object_duplicate_' . $this->_action_type, $id, $this, $new_field, $params );

			if ( $replace ) {
				// Replace object
				$id = $this->load( null, $id );
			}
		}

		return $id;

	}

	/**
	 * {@inheritDocs}
	 */
	public function delete( $delete_all = false, $table_operation = true ) {

		if ( ! $this->is_valid() ) {
			return false;
		}

		$params = (object) array(
			'id'   => $this->_object['id'],
			'name' => $this->_object['name']
		);

		$params = apply_filters( 'pods_object_pre_delete_' . $this->_action_type, $params, $this, $delete_all );

		$api = pods_api();

		$pod      = null;
		$save_pod = false;

		if ( ( ! isset( $params->pod ) || empty( $params->pod ) ) && ( ! isset( $params->pod_id ) || empty( $params->pod_id ) ) ) {
			if ( $this->is_valid() ) {
				$pod = pods_object_pod( $this->_object['pod'], $this->_object['parent_id'] );

				if ( $pod->is_valid() ) {
					$params->pod_id = $pod['id'];
					$params->pod    = $pod['id'];
				}
			}

			if ( empty( $pod ) || ! $pod->is_valid() ) {
				return pods_error( __( 'Pod ID or name is required', 'pods' ), $this );
			}
		}

		if ( empty( $pod ) ) {
			if ( isset( $params->pod ) && ( is_array( $params->pod ) || is_object( $params->pod ) ) ) {
				$pod = $params->pod;

				$save_pod = true;
			} elseif ( ! isset( $params->pod_id ) || empty( $params->pod_id ) ) {
				$pod = pods_object_pod( $params->pod );
			} elseif ( ! isset( $params->pod ) ) {
				$pod = pods_object_pod( null, $params->pod_id );
			} else {
				$pod = pods_object_pod( $params->pod, $params->pod_id );
			}

			if ( ( empty( $pod ) || ! $pod->is_valid() ) && true === $params->db ) {
				return pods_error( __( 'Pod not found', 'pods' ), $this );
			}
		}

		$success = false;

		if ( 0 < $params->id ) {
			/**
			 * @var $wpdb wpdb
			 */
			global $wpdb;

			$tableless_field_types    = Pods_Form::tableless_field_types();
			$simple_tableless_objects = Pods_Form::simple_tableless_objects();

			$params->pod_id = $pod['id'];
			$params->pod    = $pod['name'];

			$simple = ( 'pick' == $this->_object['type'] && in_array( $this->_object['pick_object'], $simple_tableless_objects ) );
			$simple = (boolean) apply_filters( 'pods_api_tableless_custom', $simple, $this, $this, array( $this->_object['name'] => $this ), $pod, $params, $api );

			if ( $table_operation && 'table' == $pod['storage'] && ( ! in_array( $this->_object['type'], $tableless_field_types ) || $simple ) ) {
				pods_query( "ALTER TABLE `@wp_pods_{$params->pod}` DROP COLUMN `{$params->name}`", false );
			}

			$success = wp_delete_post( $params->id );

			if ( ! $success ) {
				return pods_error( __( 'Field unable to be deleted', 'pods' ), $this );
			}

			$wpdb->query( $wpdb->prepare( "DELETE pm FROM {$wpdb->postmeta} AS pm
				LEFT JOIN {$wpdb->posts} AS p
					ON p.post_type = '_pods_field' AND p.ID = pm.post_id
				WHERE p.ID IS NOT NULL AND pm.meta_key = 'sister_id' AND pm.meta_value = %d",
			$params->id ) );

			if ( ! pods_tableless() && $table_operation ) {
				pods_query( "DELETE FROM `@wp_podsrel` WHERE (`pod_id` = {$params->pod_id} AND `field_id` = {$params->id}) OR (`related_pod_id` = {$params->pod_id} AND `related_field_id` = {$params->id})", false );
			}

			if ( ! $save_pod ) {
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
	 * {@inheritDocs}
	 */
	public function reset() {

		if ( ! $this->is_valid() ) {
			return false;
		}

		$params = (object) array(
			'id'   => $this->_object['id'],
			'name' => $this->_object['name']
		);

		$params = apply_filters( 'pods_object_pre_reset_' . $this->_action_type, $params, $this );

		$pod = pods_object_pod( null, $this->_object['pod_id'] );

		// @todo Get pod table info
		$table_info = $pod->table_info();

		$default = '';

		// Delete content
		if ( 'meta' == $pod['storage'] ) {
			if ( $pod->object_fields( $params->name, null, false ) ) {
				$sql = "
					UPDATE `{$table_info['table']}`
					SET `{$params->name}` = %s
				";

				pods_query( $sql, array( $default ) );
			} else {
				$sql = "
					DELETE FROM `{$table_info['meta_table']}`
					WHERE `{$table_info['meta_field_index']}` = %s
				";

				pods_query( $sql, array( $params->name ) );
			}
		} elseif ( 'table' == $pod['storage'] ) {
			$sql = "
				UPDATE `{$table_info['pod_table']}`
				SET `{$params->name}` = %s
			";

			if ( $pod->object_fields( $params->name, null, false ) ) {
				$sql = "
					UPDATE `{$table_info['table']}`
					SET `{$params->name}` = %s
				";
			}

			pods_query( $sql, array( $default ) );
		}

		// Delete Relationships

		// Delete all posts/revisions from this post type
		if ( in_array( $this->_object['storage'], array( 'post_type', 'media' ) ) ) {
			$type = $this->_object['object'];

			if ( empty( $type ) ) {
				$type = $this->_object['name'];
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
		} // Delete all terms from this taxonomy
		elseif ( 'taxonomy' == $this->_object['type'] ) {
			$sql = "
                DELETE FROM `{$table_info['table']}` AS `t`
                " . $table_info['join']['tt'] . "
                WHERE " . implode( ' AND ', $table_info['where'] ) . "
            ";

			pods_query( $sql, false );
		} // Delete all users except the current one
		elseif ( 'user' == $this->_object['type'] ) {
			$sql = "
                DELETE `t`, `m`
                FROM `{$table_info['table']}` AS `t`
                LEFT JOIN `{$table_info['meta_table']}` AS `m`
                    ON `m`.`{$table_info['meta_field_id']}` = `t`.`{$table_info['field_id']}`
                WHERE `t`.`{$table_info['field_id']}` != " . (int) get_current_user_id() . "
            ";

			pods_query( $sql, false );
		} // Delete all comments
		elseif ( 'comment' == $this->_object['type'] ) {
			$type = $this->_object['object'];

			if ( empty( $type ) ) {
				$type = $this->_object['name'];
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