<?php
/**
 * @package Pods
 * @category Object Types
 */

/**
 * Class Pods_Object_Group
 *
 * @property Pods_Object_Field[] $fields Fields
 */
class Pods_Object_Group extends
	Pods_Object {

	/**
	 * Post type / meta key prefix for internal values
	 *
	 * @var string
	 */
	protected $_post_type = '_pods_group';

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
	 * Method names for accessing internal keys
	 *
	 * @var array
	 */
	protected $_methods = array(
		'fields'
	);

	/**
	 * {@inheritDocs}
	 */
	public function fields( $field = null, $option = null, $alt = true ) {

		if ( ! $this->is_valid() ) {
			return array();
		}

		if ( empty( $this->_fields ) ) {
			if ( $this->is_custom() ) {
				if ( isset( $this->_object['_fields'] ) && ! empty( $this->_object['_fields'] ) ) {
					$this->_fields = array();

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
					'posts_per_page' => - 1,
					'nopaging'       => true,
					'post_parent'    => $this->_object['parent_id'],
					'meta_query'     => array(
						array(
							'key'   => 'group_id',
							'value' => $this->_object['id']
						)
					),
					'orderby'        => 'menu_order',
					'order'          => 'ASC'
				);

				$fields = get_posts( $find_args );

				$this->_fields = array();

				if ( ! empty( $fields ) ) {
					foreach ( $fields as $object_field ) {
						$object_field = pods_object_field( $object_field, 0, $this->_live, $this->_object['parent_id'] );

						if ( $object_field->is_valid() ) {
							$this->_fields[ $object_field['name'] ] = $object_field;
						}
					}
				}
			}
		}

		return $this->_fields( 'fields', $field, $option );

	}

	/**
	 * Get list of Pod option tabs for Admin UI
	 *
	 * @return array
	 */
	public function admin_tabs() {

		$group =& $this;

		$pod = pods_object_pod( null, $this->_object['parent_id'] );

		$tabs = array();

		if ( ! $this->is_custom() ) {
			$tabs['manage-fields'] = __( 'Fields', 'pods' );
		}

		$tabs['rules']    = __( 'Visibility Rules', 'pods' );
		$tabs['advanced'] = __( 'Advanced', 'pods' );

		$tabs = apply_filters( 'pods_admin_setup_edit_group_tabs_' . $pod['type'] . '_' . $pod['name'], $tabs, $pod, $group );
		$tabs = apply_filters( 'pods_admin_setup_edit_group_tabs_' . $pod['type'], $tabs, $pod, $group );
		$tabs = apply_filters( 'pods_admin_setup_edit_group_tabs', $tabs, $pod, $group );

		return $tabs;

	}

	/**
	 * Get list of Pod options for Admin UI
	 *
	 * @return array
	 */
	public function admin_options() {

		$group =& $this;

		$pod = pods_object_pod( null, $this->_object['parent_id'] );

		$options = array(
			'rules'    => array(),
			'advanced' => array()
		);

		if ( 'settings' != $pod['type'] ) {
			$options['rules']['rules_new_old'] = array(
				'label'            => __( 'Show Group based on Editor', 'pods' ),
				'help'             => __( 'help', 'pods' ),
				'type'             => 'pick',
				'data'             => array(
					''     => __( 'Both Add New and Edit', 'pods' ),
					'add'  => __( 'Add New', 'pods' ),
					'edit' => __( 'Edit', 'pods' )
				),
				'pick_format_type' => 'single'
			);
		}

		if ( 'post_type' == $pod['type'] ) {
			$options['rules']['rules_post_status'] = array(
				'label'             => __( 'Show Group based on Post Status', 'pods' ),
				'help'              => __( 'help', 'pods' ),
				'type'              => 'pick',
				'pick_object'       => 'post-status',
				'pick_format_type'  => 'multi',
				'pick_format_multi' => 'checkbox'
			);

			$post_type  = get_post_type_object( $pod['name'] );
			$taxonomies = $post_type->taxonomies;

			if ( ! empty( $taxonomies ) ) {
				$data = array();

				foreach ( $taxonomies as $k => $taxonomy ) {
					$taxonomy = get_taxonomy( $taxonomy );

					$data[ $taxonomy->name ] = $taxonomy->label;

					$taxonomies[ $k ] = $taxonomy;
				}

				$options['rules']['rules_taxonomy'] = array(
					'label'             => __( 'Show Group based on Taxonomy', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'pick',
					'data'              => $data,
					'pick_format_type'  => 'multi',
					'pick_format_multi' => 'checkbox',
					'dependency'        => true
				);

				foreach ( $taxonomies as $taxonomy ) {
					$options['rules'][ 'rules_taxonomy_' . $taxonomy->name ] = array(
						'label'             => sprintf( __( 'Show Group based on %s term', 'pods' ), $taxonomy->labels->singular_name ),
						'help'              => __( 'help', 'pods' ),
						'type'              => 'pick',
						'pick_object'       => 'taxonomy',
						'pick_val'          => $taxonomy->name,
						'pick_format_type'  => 'multi',
						'pick_format_multi' => 'autocomplete',
						'depends-on'        => array( 'rules_taxonomy' => $taxonomy->name )
					);
				}
			}

			$options['advanced']['context'] = array(
				'label'            => __( 'Meta Box Context', 'pods' ),
				'help'             => __( 'The context within the editor where the group should show', 'pods' ),
				'type'             => 'pick',
				'data'             => array(
					'normal'   => __( 'Normal', 'pods' ),
					'advanced' => __( 'Advanced', 'pods' )
				),
				'pick_format_type' => 'single',
				'default'          => 'normal'
			);

			$options['advanced']['priority'] = array(
				'label'            => __( 'Meta Box Priority', 'pods' ),
				'help'             => __( 'The priority within the context where the group should show', 'pods' ),
				'type'             => 'pick',
				'data'             => array(
					'default' => __( 'Default', 'pods' ),
					'low'     => __( 'Low', 'pods' ),
					'high'    => __( 'High', 'pods' )
				),
				'pick_format_type' => 'single',
				'default'          => 'default'
			);
		}

		$options = apply_filters( 'pods_admin_setup_edit_group_options_' . $pod['type'] . '_' . $pod['name'], $options, $pod, $group );
		$options = apply_filters( 'pods_admin_setup_edit_group_options_' . $pod['type'], $options, $pod, $group );
		$options = apply_filters( 'pods_admin_setup_edit_group_options', $options, $pod, $group );

		foreach ( $options as $option_group => $option_group_opts ) {
			foreach ( $option_group_opts as $option => $option_data ) {
				$this->_options[ $option ] = $option_data;
			}
		}

		return $options;

	}

	/**
	 * Get list of Pod field option tabs for Admin UI
	 *
	 * @return array
	 */
	public function admin_field_tabs() {

		$core_tabs = array(
			'basic'            => __( 'Basic', 'pods' ),
			'additional-field' => __( 'Additional Field Options', 'pods' ),
			'advanced'         => __( 'Advanced', 'pods' )
		);


		/**
		 * Fire off the setup for the Pods edit field tabs
		 *
		 * @param array $tabs The Pods edit field tabs setup
		 *
		 * @since 2.4.0
		 */
		$tabs = apply_filters( 'pods_admin_setup_edit_field_tabs', array(), $this );

		$tabs = array_merge( $core_tabs, $tabs );

		return $tabs;

	}

	/**
	 * Get list of Pod field options for Admin UI
	 *
	 * @return array
	 */
	public function admin_field_options() {

		$pod =& $this;

		$options = array();

		$options['additional-field'] = array();

		$field_types = Pods_Form::field_types();

		foreach ( $field_types as $type => $field_type_data ) {
			/**
			 * @var $field_type Pods_Field
			 */
			$field_type = Pods_Form::field_loader( $type, $field_type_data['file'] );

			$field_type_vars = get_class_vars( get_class( $field_type ) );

			if ( ! isset( $field_type_vars['pod_types'] ) ) {
				$field_type_vars['pod_types'] = true;
			}

			$options['additional-field'][ $type ] = array();

			// Only show supported field types
			if ( true !== $field_type_vars['pod_types'] ) {
				if ( empty( $field_type_vars['pod_types'] ) ) {
					continue;
				} elseif ( is_array( $field_type_vars['pod_types'] ) && ! in_array( $pod['type'], $field_type_vars['pod_types'] ) ) {
					continue;
				} elseif ( ! is_array( $field_type_vars['pod_types'] ) && $pod['type'] != $field_type_vars['pod_types'] ) {
					continue;
				}
			}

			$options['additional-field'][ $type ] = Pods_Form::ui_options( $type );
		}

		$input_helpers = array(
			'' => '-- Select --'
		);

		if ( class_exists( 'Pods_Helpers' ) ) {
			$helpers = pods_api()->load_helpers( array( 'options' => array( 'helper_type' => 'input' ) ) );

			foreach ( $helpers as $helper ) {
				$input_helpers[ $helper['name'] ] = $helper['name'];
			}
		}

		$options['advanced'] = array(
			__( 'Visual', 'pods' )     => array(
				'class'        => array(
					'name'    => 'class',
					'label'   => __( 'Additional CSS Classes', 'pods' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => ''
				),
				'placeholder'  => array(
					'name'       => 'placeholder',
					'label'      => __( 'Placeholder text', 'pods' ),
					'help'       => __( 'Placeholders are used by HTML5 compliant browsers to display text in empty fields in ways that help describe what kind of value a field might contain, or for describing the field itself.', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array(
						'type' => array(
							'color',
							'currency',
							'date',
							'datetime',
							'email',
							'number',
							'paragraph',
							'password',
							'phone',
							'slug',
							'text',
							'time',
							'website'
						)
					)
				),
				'input_helper' => array(
					'name'    => 'input_helper',
					'label'   => __( 'Input Helper', 'pods' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'pick',
					'default' => '',
					'data'    => $input_helpers
				)
			),
			__( 'Values', 'pods' )     => array(
				'default_value'           => array(
					'name'    => 'default_value',
					'label'   => __( 'Default Value', 'pods' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => ''
				),
				'default_value_parameter' => array(
					'name'    => 'default_value_parameter',
					'label'   => __( 'Set Default Value via Parameter', 'pods' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => ''
				)
			),
			__( 'Visibility', 'pods' ) => array(
				'restrict_access'    => array(
					'name'  => 'restrict_access',
					'label' => __( 'Restrict Access', 'pods' ),
					'group' => array(
						'admin_only'          => array(
							'name'       => 'admin_only',
							'label'      => __( 'Restrict access to Admins?', 'pods' ),
							'default'    => 0,
							'type'       => 'boolean',
							'dependency' => true,
							'help'       => __( 'This field will only be able to be edited by users with the ability to manage_options or delete_users, or super admins of a WordPress Multisite network', 'pods' )
						),
						'restrict_role'       => array(
							'name'       => 'restrict_role',
							'label'      => __( 'Restrict access by Role?', 'pods' ),
							'default'    => 0,
							'type'       => 'boolean',
							'dependency' => true
						),
						'restrict_capability' => array(
							'name'       => 'restrict_capability',
							'label'      => __( 'Restrict access by Capability?', 'pods' ),
							'default'    => 0,
							'type'       => 'boolean',
							'dependency' => true
						),
						'hidden'              => array(
							'name'    => 'hidden',
							'label'   => __( 'Hide field from UI', 'pods' ),
							'default' => 0,
							'type'    => 'boolean',
							'help'    => __( 'This option is overriden by access restrictions. If the user does not have access to edit this field, it will be hidden. If no access restrictions are set, this field will always be hidden.', 'pods' )
						),
						'read_only'           => array(
							'name'       => 'read_only',
							'label'      => __( 'Make field "Read Only" in UI', 'pods' ),
							'default'    => 0,
							'type'       => 'boolean',
							'help'       => __( 'This option is overriden by access restrictions. If the user does not have access to edit this field, it will be read only. If no access restrictions are set, this field will always be read only.', 'pods' ),
							'depends-on' => array(
								'type' => array(
									'boolean',
									'color',
									'currency',
									'date',
									'datetime',
									'email',
									'number',
									'paragraph',
									'password',
									'phone',
									'slug',
									'text',
									'time',
									'website'
								)
							)
						)
					)
				),
				'roles_allowed'      => array(
					'name'             => 'roles_allowed',
					'label'            => __( 'Role(s) Allowed', 'pods' ),
					'help'             => __( 'help', 'pods' ),
					'type'             => 'pick',
					'pick_object'      => 'role',
					'pick_format_type' => 'multi',
					'default'          => 'administrator',
					'depends-on'       => array(
						'restrict_role' => true
					)
				),
				'capability_allowed' => array(
					'name'       => 'capability_allowed',
					'label'      => __( 'Capability Allowed', 'pods' ),
					'help'       => __( 'Comma-separated list of cababilities, for example add_podname_item, please see the Roles and Capabilities component for the complete list and a way to add your own.', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => array(
						'restrict_capability' => true
					)
				)
				/*,
						'search' => array(
							'label' => __( 'Include in searches', 'pods' ),
							'help' => __( 'help', 'pods' ),
							'default' => 1,
							'type' => 'boolean',
						)*/
			)
			/*,
				__( 'Validation', 'pods' ) => array(
					'regex_validation' => array(
						'label' => __( 'RegEx Validation', 'pods' ),
						'help' => __( 'help', 'pods' ),
						'type' => 'text',
						'default' => ''
					),
					'message_regex' => array(
						'label' => __( 'Message if field does not pass RegEx', 'pods' ),
						'help' => __( 'help', 'pods' ),
						'type' => 'text',
						'default' => ''
					),
					'message_required' => array(
						'label' => __( 'Message if field is blank', 'pods' ),
						'help' => __( 'help', 'pods' ),
						'type' => 'text',
						'default' => '',
						'depends-on' => array( 'required' => true )
					),
					'message_unique' => array(
						'label' => __( 'Message if field is not unique', 'pods' ),
						'help' => __( 'help', 'pods' ),
						'type' => 'text',
						'default' => '',
						'depends-on' => array( 'unique' => true )
					)
				)*/
		);

		if ( ! class_exists( 'Pods_Helpers' ) ) {
			unset( $options['advanced-options']['input_helper'] );
		}

		$options = apply_filters( 'pods_admin_setup_edit_field_options', $options, $pod );

		return $options;
	}

	/**
	 * {@inheritDocs}
	 */
	public function save( $options = null, $value = null, $refresh = true ) {

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

		if ( ! isset( $params->parent_id ) && isset( $params->pod_id ) && ! empty( $params->pod_id ) ) {
			$params->parent_id = $params->pod_id;
		}

		if ( ( ! isset( $this->_object['parent_id'] ) || empty( $this->_object['parent_id'] ) ) && ( ! isset( $params->parent_id ) || empty( $params->parent_id ) ) ) {
			return pods_error( __( 'Field Groups require a parent to be set', 'pods' ) );
		} elseif ( ! isset( $params->parent_id ) ) {
			$params->parent_id = $this->_object['parent_id'];
		}

		if ( ! isset( $params->name ) && isset( $params->label ) ) {
			$params->name = $params->label;
		}

		if ( ! isset( $params->db ) ) {
			$params->db = true;
		}

		$api = pods_api();

		$group_fields = $this->fields();

		$params = apply_filters( 'pods_object_pre_save_' . $this->_action_type, $params, $this );

		$old_id = $old_name = null;

		$old_fields = array();

		if ( isset( $params->name ) ) {
			$params->name = pods_clean_name( $params->name, true, false );

			if ( ! isset( $params->label ) ) {
				$params->label = $params->name;
			}
		} else {
			return pods_error( __( 'Field Groups need a name to be set', 'pods' ) );
		}

		$old_group = pods_object_group( $params->name, 0, false, $params->parent_id );

		if ( $old_group->is_valid() ) {
			if ( $old_group->is_custom() ) {
				return pods_error( sprintf( __( 'Field Group %s was registered through code, you cannot modify it.', 'pods' ), $params->name ) );
			}

			/*if ( isset( $params->id ) && 0 < $params->id ) {
				$old_id = $params->id;
			}*/

			$old_id     = $old_group['id'];
			$old_name   = $old_group['name'];
			$old_fields = $old_group->fields();

			if ( ! isset( $params->name ) && empty( $params->name ) ) {
				$params->name = $old_group['name'];
			}

			if ( $old_name != $params->name && false !== $this->exists( $params->name ) ) {
				return pods_error( sprintf( __( 'Field Group %s already exists, you cannot rename %s to that', 'pods' ), $params->name, $old_name ) );
			}

			if ( $old_id != $params->id ) {
				return pods_error( sprintf( __( 'Field Group %s already exists', 'pods' ), $params->name ) );
			}

			$group =& $this;
		} else {
			$group = array(
				'id'          => 0,
				'name'        => $params->name,
				'label'       => $params->label,
				'description' => '',
				'parent_id'   => $params->parent_id
			);

			$group = pods_object_group( $group );
		}

		$pod = pods_object_pod( null, $group['parent_id'] );

		if ( ! $pod->is_valid() ) {
			return pods_error( __( 'Field Group parent does not exist', 'pods' ) );
		}

		// Blank out fields and options for AJAX calls (everything should be sent to it for a full overwrite)
		// @todo Does this still work with pods_object_pod?
		if ( isset( $params->fields ) && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$group_fields = array();
		}

		// Setup options
		$options = get_object_vars( $params );

		$options_ignore = array(
			'pod_id',
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

		if ( isset( $options['options'] ) ) {
			$options = array_merge( $options['options'], $options );

			unset( $options['options'] );
		}

		if ( isset( $options['fields'] ) ) {
			$group_fields = $options['fields'];

			unset( $options['fields'] );
		}

		$group->override( $options );

		if ( strlen( $group['label'] ) < 1 ) {
			$group['label'] = $group['name'];
		}

		$params->id   = $group['id'];
		$params->name = $group['name'];

		$field_table_operation = true;

		// Add new Field Group
		if ( empty( $group['id'] ) ) {
			if ( strlen( $group['name'] ) < 1 ) {
				return pods_error( __( 'Group name cannot be empty', 'pods' ) );
			}

			$post_data = array(
				'post_name'    => $group['name'],
				'post_title'   => $group['label'],
				'post_content' => $group['description'],
				'post_parent'  => $group['parent_id'],
				'post_type'    => $this->_post_type,
				'post_status'  => 'publish'
			);

			$field_table_operation = false;
		} else {
			$post_data = array(
				'ID'           => $group['id'],
				'post_name'    => $group['name'],
				'post_title'   => $group['label'],
				'post_content' => $group['description'],
				'post_parent'  => $group['parent_id'],
				'post_status'  => 'publish'
			);
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

			$changed_meta = $group->changed();
			$group->override_save();

			$params->id = $api->save_wp_object( 'post', $post_data, $changed_meta );

			if ( $conflicted ) {
				add_filter( 'wp_insert_post_data', 'headway_clean_slug', 0 );
			}

			if ( false === $params->id ) {
				return pods_error( __( 'Cannot save Field Group', 'pods' ) );
			}
		} elseif ( empty( $params->id ) ) {
			$params->id = (int) $params->db;
		}

		$group['id'] = $params->id;

		/**
		 * @var $wpdb wpdb
		 */
		global $wpdb;

		$saved  = array();
		$errors = array();

		$field_index_change = false;
		$field_index_id     = 0;

		$id_required = false;

		$field_index = pods_v( 'pod_index', $pod, 'id', true );

		if ( 'pod' == $pod['type'] && ! empty( $group_fields ) && isset( $group_fields[ $field_index ] ) ) {
			$field_index_id = $group_fields[ $field_index ];
		}

		if ( isset( $params->fields ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			$fields = array();

			if ( isset( $params->fields ) ) {
				$params->fields = (array) $params->fields;

				$weight = 0;

				foreach ( $params->fields as $field ) {
					if ( ! is_array( $field ) || ! isset( $field['name'] ) ) {
						continue;
					}

					if ( ! isset( $field['weight'] ) ) {
						$field['weight'] = $weight;

						$weight ++;
					}

					$fields[ $field['name'] ] = $field;
				}
			}

			$weight = 0;

			$saved_field_ids = array();

			foreach ( $group_fields as $k => $field ) {
				if ( ! empty( $old_id ) && ( ( ! is_array( $field ) && ! is_object( $field ) ) || ! isset( $field['name'] ) || ! isset( $fields[ $field['name'] ] ) ) ) {
					// Iterative change handling for setup-edit.php
					if ( ! is_array( $field ) && isset( $old_fields[ $k ] ) ) {
						$saved[ $old_fields[ $k ]['name'] ] = true;
					}

					continue;
				}

				if ( ! empty( $old_id ) ) {
					$field = array_merge( $field, $fields[ $field['name'] ] );
				}

				$field['pod']      = $pod;
				$field['group_id'] = $group['id'];

				if ( ! isset( $field['weight'] ) ) {
					$field['weight'] = $weight;

					$weight ++;
				}

				if ( 0 < $field_index_id && pods_v( 'id', $field ) == $field_index_id ) {
					$field_index_change = $field['name'];
				}

				if ( 0 < pods_v( 'id', $field ) ) {
					$id_required = true;
				}

				if ( $id_required ) {
					$field['id_required'] = true;
				}

				$field_data = $field;

				$field = $api->save_field( $field_data, $field_table_operation, true, $params->db );

				if ( true === $params->db ) {
					if ( ! empty( $field ) && 0 < $field ) {
						$saved[ $field_data['name'] ] = true;
						$saved_field_ids[]            = $field;
					} else {
						$errors[] = sprintf( __( 'Cannot save the %s field', 'pods' ), $field_data['name'] );
					}
				} else {
					$group_fields[ $k ] = $field;
					$saved_field_ids[]  = $field['id'];
				}
			}

			if ( true === $params->db ) {
				foreach ( $old_fields as $field ) {
					if ( isset( $group_fields[ $field['name'] ] ) || isset( $saved[ $field['name'] ] ) || in_array( $field['id'], $saved_field_ids ) ) {
						continue;
					}

					if ( $field['id'] == $field_index_id ) {
						$field_index_change = 'id';
					} elseif ( $field['name'] == $field_index ) {
						$field_index_change = 'id';
					}

					$delete_field = array(
						'id'   => (int) $field['id'],
						'name' => $field['name'],
						'pod'  => $pod
					);

					$api->delete_field( $delete_field, $field_table_operation );
				}
			}

			// Update field index if the name has changed or the field has been removed
			if ( false !== $field_index_change ) {
				if ( true === $params->db ) {
					update_post_meta( $pod['id'], 'pod_index', $field_index_change );
				}

				$pod['pod_index'] = $field_index_change;
			}
		}

		if ( ! empty( $errors ) ) {
			return pods_error( $errors );
		}

		// Refresh fields
		$this->_fields = array();
		$this->fields();

		$api->cache_flush_pods( $pod );

		$id = $group['id'];

		// Refresh object
		if ( $refresh ) {
			$id = $this->load( null, $id );
		}

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

		$pod = $this->export();

		if ( in_array( $pod['type'], array( 'media', 'user', 'comment' ) ) ) {
			if ( false !== $params->strict ) {
				return pods_error( __( 'Pod not allowed to be duplicated', 'pods' ) );
			}

			return false;
		}

		$pod['object'] = '';

		$pod = array_merge( $pod, $custom_options );

		unset( $pod['id'] );
		unset( $pod['object_fields'] );

		if ( isset( $params->new_name ) ) {
			$pod['name'] = $params->new_name;
		}

		$try = 2;

		$check_name = $pod['name'] . $try;
		$new_label  = $pod['label'] . $try;

		while ( $this->exists( $check_name ) ) {
			$try ++;

			$check_name = $pod['name'] . $try;
			$new_label  = $pod['label'] . $try;
		}

		$pod['name']  = $check_name;
		$pod['label'] = $new_label;

		foreach ( $pod['fields'] as $field => $field_data ) {
			unset( $pod['fields'][ $field ]['id'] );
		}

		$new_pod = pods_object_pod();

		$id = $new_pod->save( $pod );

		if ( 0 < $id ) {
			$this->_action( 'pods_object_duplicate_' . $this->_action_type, $id, $this, $new_pod, $params );

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

		if ( 0 < $params->id ) {
			$fields = $this->fields();

			/**
			 * @var $field Pods_Object_Field
			 */
			foreach ( $fields as $field ) {
				$field->delete();
			}

			// Only delete the post once the fields are taken care of, it's not required anymore
			$success = wp_delete_post( $params->id );

			if ( ! $success ) {
				return pods_error( __( 'Pod unable to be deleted', 'pods' ) );
			}

			pods_api()->cache_flush_pods( $this );

			$success = true;
		}

		if ( $success ) {
			$this->_action( 'pods_object_delete_' . $this->_action_type, $params, $this, $delete_all );

			// Can't destroy object, so let's destroy the data and invalidate the object
			$this->destroy();
		}

		return $success;

	}
}