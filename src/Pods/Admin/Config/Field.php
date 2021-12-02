<?php

namespace Pods\Admin\Config;

use PodsForm;
use Tribe__Main;

/**
 * Field configuration class.
 *
 * @since 2.8.0
 */
class Field extends Base {

	/**
	 * Get list of tabs for the Field object.
	 *
	 * @since 2.8.0
	 *
	 * @param \Pods\Whatsit\Pod $pod The pod object.
	 *
	 * @return array List of tabs for the Field object.
	 */
	public function get_tabs( \Pods\Whatsit\Pod $pod ) {
		$core_tabs = [
			'basic' => __( 'Field Details', 'pods' ),
		];

		$field_types = PodsForm::field_types();

		foreach ( $field_types as $type => $field_type_data ) {
			$core_tabs[ 'additional-field-' . $type ] = [
				'name'       => 'additional-field-' . $type,
				/* translators: %s: Field type label. */
				'label'      => sprintf( _x( '%s Options', 'Field type options', 'pods' ), $field_type_data['label'] ),
				'depends-on' => [
					'type' => $type,
				],
			];
		}

		$core_tabs['advanced'] = __( 'Advanced', 'pods' );

		// Only include kitchen sink if dev mode on and not running Codecept tests.
		if ( pods_developer() && ! function_exists( 'codecept_debug' ) ) {
			$core_tabs['kitchen-sink'] = __( 'Kitchen Sink (temp)', 'pods' );
		}

		$pod_type = $pod['type'];
		$pod_name = $pod['name'];

		$tabs = $core_tabs;

		/**
		 * Filter the Pod Field option tabs for a specific pod type and name.
		 *
		 * @since 2.8.0
		 *
		 * @param array             $core_tabs Tabs to set.
		 * @param \Pods\Whatsit\Pod $pod       Current Pods object.
		 */
		$tabs = (array) apply_filters( "pods_admin_setup_edit_field_tabs_{$pod_type}_{$pod_name}", $tabs, $pod );

		/**
		 * Filter the Pod Field option tabs for a specific pod type.
		 *
		 * @since 2.8.0
		 *
		 * @param array             $tabs Tabs to set.
		 * @param \Pods\Whatsit\Pod $pod  Current Pods object.
		 */
		$tabs = (array) apply_filters( "pods_admin_setup_edit_field_tabs_{$pod_type}", $tabs, $pod );

		/**
		 * Filter the Pod Field option tabs.
		 *
		 * @since 2.8.0
		 *
		 * @param array             $tabs Tabs to set.
		 * @param \Pods\Whatsit\Pod $pod  Current Pods object.
		 */
		$tabs = (array) apply_filters( 'pods_admin_setup_edit_field_tabs', $tabs, $pod );

		// Sort and then enforce the core tabs to be in front.
		uksort( $tabs, 'strnatcmp' );

		$tabs = array_merge( $core_tabs, $tabs );

		return $tabs;
	}

	/**
	 * Get list of fields for the Field object.
	 *
	 * @since 2.8.0
	 *
	 * @param \Pods\Whatsit\Pod $pod  The pod object.
	 * @param array             $tabs The list of tabs for the pod object.
	 *
	 * @return array List of fields for the Field object.
	 */
	public function get_fields( \Pods\Whatsit\Pod $pod, array $tabs ) {
		$field_types           = PodsForm::field_types();
		$tableless_field_types = PodsForm::tableless_field_types();

		$options = [];

		$options['basic']    = [
			'label'       => [
				'name'     => 'label',
				'label'    => __( 'Label', 'pods' ),
				'type'     => 'text',
				'default'  => '',
				'help'     => 'help',
				'required' => true,
			],
			'name'        => [
				'name'       => 'name',
				'label'      => __( 'Name', 'pods' ),
				'type'       => 'slug',
				'default'    => '',
				'attributes' => [
					'maxlength' => 50,
				],
				'help'       => 'help',
				'required'   => true,
			],
			'description' => [
				'name'    => 'description',
				'label'   => __( 'Description', 'pods' ),
				'type'    => 'text',
				'default' => '',
				'help'    => 'help',
			],
			'type'        => [
				'name'       => 'type',
				'label'      => __( 'Field Type', 'pods' ),
				'type'       => 'pick',
				'default'    => 'text',
				'required'   => true,
				'data'       => [],
				'dependency' => true,
				'help'       => 'help',
			],
			'pick_object' => [
				'name'       => 'pick_object',
				'label'      => __( 'Related Type', 'pods' ),
				'type'       => 'pick',
				'default'    => '',
				'required'   => true,
				'data'       => [],
				'pick_show_select_text'   => 0,
				'dependency' => true,
				'depends-on' => [
					'type' => 'pick',
				],
				'help'       => 'help',
			],
			'pick_custom' => [
				'name'       => 'pick_custom',
				'label'      => __( 'Custom Defined Options', 'pods' ),
				'type'       => 'paragraph',
				'default'    => '',
				'required'   => true,
				'depends-on' => [
					'type'        => 'pick',
					'pick_object' => 'custom-simple',
				],
				'help'       => __( 'One option per line, use <em>value|Label</em> for separate values and labels', 'pods' ),
			],
			'pick_table'  => [
				'name'       => 'pick_table',
				'label'      => __( 'Related Table', 'pods' ),
				'type'       => 'pick',
				'default'    => '',
				'data'       => [],
				'pick_show_select_text'   => 0,
				'depends-on' => [
					'type'        => 'pick',
					'pick_object' => 'table',
				],
				'help'       => 'help',
			],
			'sister_id'   => [
				'name'       => 'sister_id',
				'label'      => __( 'Bi-directional Field', 'pods' ),
				'type'       => 'pick',
				'default'    => '',
				'data'       => [],
				'depends-on' => [
					'type'        => 'pick',
					'pick_object' => PodsForm::field_method( 'pick', 'bidirectional_objects' ),
				],
				'help'       => __( 'Bi-directional fields will update their related field for any item you select. This feature is only available for two relationships between two Pods.<br /><br />For example, when you update a Parent pod item to relate to a Child item, when you go to edit that Child item you will see the Parent pod item selected.', 'pods' ),
			],
			'required'    => [
				'name'              => 'required',
				'label'             => __( 'Required', 'pods' ),
				'type'              => 'boolean',
				'default'           => 0,
				'boolean_yes_label' => '',
				'help'              => __( 'This will require a non-empty value to be entered.', 'pods' ),
			],
		];
		$options['advanced'] = [
			'visual'                  => [
				'name'  => 'visual',
				'label' => __( 'Visual', 'pods' ),
				'type'  => 'heading',
			],
			'class'                   => [
				'name'    => 'class',
				'label'   => __( 'Additional CSS Classes', 'pods' ),
				'help'    => __( 'help', 'pods' ),
				'type'    => 'text',
				'default' => '',
			],
			'values'                  => [
				'name'  => 'values',
				'label' => __( 'Values', 'pods' ),
				'type'  => 'heading',
			],
			'default_value'           => [
				'name'    => 'default_value',
				'label'   => __( 'Default Value', 'pods' ),
				'help'    => __( 'help', 'pods' ),
				'type'    => 'text',
				'default' => '',
				'options' => [
					'text_max_length' => - 1,
				],
			],
			'default_value_parameter' => [
				'name'    => 'default_value_parameter',
				'label'   => __( 'Set Default Value via Parameter', 'pods' ),
				'help'    => __( 'help', 'pods' ),
				'type'    => 'text',
				'default' => '',
			],
			'visibility'              => [
				'name'  => 'visibility',
				'label' => __( 'Visibility', 'pods' ),
				'type'  => 'heading',
			],
			'restrict_access'         => [
				'type'          => 'boolean_group',
				'name'          => 'restrict_access',
				'label'         => __( 'Restrict Access', 'pods' ),
				'boolean_group' => [
					'logged_in_only'      => [
						'name'       => 'logged_in_only',
						'label'      => __( 'Restrict access to Logged In Users', 'pods' ),
						'default'    => 0,
						'type'       => 'boolean',
						'dependency' => true,
						'help'       => __( 'This field will only be able to be edited by logged in users. This is not required to be on for the other Restrict Access options to work.', 'pods' ),
					],
					'admin_only'          => [
						'name'       => 'admin_only',
						'label'      => __( 'Restrict access to Admins', 'pods' ),
						'default'    => 0,
						'type'       => 'boolean',
						'dependency' => true,
						'help'       => __( 'This field will only be able to be edited by users with the ability to manage_options or delete_users, or super admins of a WordPress Multisite network', 'pods' ),
					],
					'restrict_role'       => [
						'name'       => 'restrict_role',
						'label'      => __( 'Restrict access by Role', 'pods' ),
						'default'    => 0,
						'type'       => 'boolean',
						'dependency' => true,
					],
					'restrict_capability' => [
						'name'       => 'restrict_capability',
						'label'      => __( 'Restrict access by Capability', 'pods' ),
						'default'    => 0,
						'type'       => 'boolean',
						'dependency' => true,
					],
					'hidden'              => [
						'name'    => 'hidden',
						'label'   => __( 'Hide field from UI', 'pods' ),
						'default' => 0,
						'type'    => 'boolean',
						'help'    => __( 'This option is overridden by access restrictions. If the user does not have access to edit this field, it will be hidden. If no access restrictions are set, this field will always be hidden.', 'pods' ),
					],
					'read_only'           => [
						'name'       => 'read_only',
						'label'      => __( 'Make field "Read Only" in UI', 'pods' ),
						'default'    => 0,
						'type'       => 'boolean',
						'help'       => __( 'This option is overridden by access restrictions. If the user does not have access to edit this field, it will be read only. If no access restrictions are set, this field will always be read only.', 'pods' ),
						'depends-on' => [
							'type' => [
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
								'website',
							],
						],
					],
				],
			],
			'roles_allowed'           => [
				'name'             => 'roles_allowed',
				'label'            => __( 'Role(s) Allowed', 'pods' ),
				'help'             => __( 'help', 'pods' ),
				'type'             => 'pick',
				'pick_object'      => 'role',
				'pick_format_type' => 'multi',
				'default'          => 'administrator',
				'depends-on'       => [
					'restrict_role' => true,
				],
				'help'             => __( 'If none are selected, this option will be ignored.', 'pods' ),
			],
			'capability_allowed'      => [
				'name'       => 'capability_allowed',
				'label'      => __( 'Capability Allowed', 'pods' ),
				'help'       => __( 'Comma-separated list of capabilities, for example add_podname_item, please see the Roles and Capabilities component for the complete list and a way to add your own.', 'pods' ),
				'type'       => 'text',
				'default'    => '',
				'depends-on' => [
					'restrict_capability' => true,
				],
				'help'       => __( 'If none are selected, this option will be ignored.', 'pods' ),
			],
		];

		$pick_tables = pods_transient_get( 'pods_tables' );

		if ( empty( $pick_tables ) ) {
			$pick_tables = [
				'' => __( '-- Select Table --', 'pods' ),
			];

			global $wpdb;

			$tables = $wpdb->get_results( 'SHOW TABLES', ARRAY_N );

			if ( ! empty( $tables ) ) {
				foreach ( $tables as $table ) {
					$pick_tables[ $table[0] ] = $table[0];
				}
			}

			pods_transient_set( 'pods_tables', $pick_tables, WEEK_IN_SECONDS );
		}

		$field_settings = [
			'field_types_select' => [],
			'pick_object'        => PodsForm::field_method( 'pick', 'related_objects', true ),
			'pick_table'         => $pick_tables,
			'sister_id'          => [
				'' => __( 'No Related Fields Found', 'pods' ),
			],
		];

		$pod_name = $pod['name'];
		$pod_type = $pod['type'];

		foreach ( $field_types as $type => $field_type_data ) {
			/**
			 * @var $field_type PodsField
			 */
			$field_type_object = PodsForm::field_loader( $type, $field_type_data['file'] );

			$field_type_vars = get_class_vars( get_class( $field_type_object ) );

			if ( ! isset( $field_type_vars['pod_types'] ) ) {
				$field_type_vars['pod_types'] = true;
			}

			$options[ 'additional-field-' . $type ] = [];

			// Only show supported field types
			if ( true !== $field_type_vars['pod_types'] ) {
				if ( empty( $field_type_vars['pod_types'] ) ) {
					continue;
				} elseif ( is_array( $field_type_vars['pod_types'] ) && ! in_array( $pod_type, $field_type_vars['pod_types'], true ) ) {
					continue;
				} elseif ( ! is_array( $field_type_vars['pod_types'] ) && $pod_type !== $field_type_vars['pod_types'] ) {
					continue;
				}
			}

			if ( ! empty( PodsForm::$field_group ) ) {
				if ( ! isset( $field_settings['field_types_select'][ PodsForm::$field_group ] ) ) {
					$field_settings['field_types_select'][ PodsForm::$field_group ] = [];
				}

				$field_settings['field_types_select'][ PodsForm::$field_group ][ $type ] = $field_type_data['label'];
			} else {
				if ( ! isset( $field_settings['field_types_select'][ __( 'Other', 'pods' ) ] ) ) {
					$field_settings['field_types_select'][ __( 'Other', 'pods' ) ] = [];
				}

				$field_settings['field_types_select'][ __( 'Other', 'pods' ) ][ $type ] = $field_type_data['label'];
			}

			$type_options = PodsForm::ui_options( $type );

			$dev_mode = pods_developer();

			if ( ! $dev_mode ) {
				foreach ( $type_options as $type_option => $option_data ) {
					if ( ! empty( $option_data['developer_mode'] ) ) {
						unset( $type_options[ $type_option ] );
					}
				}
			}

			/**
			 * Modify Additional Field Options tab
			 *
			 * @since 2.7.0
			 *
			 * @param array                  $type_options Additional field type options,
			 * @param string                 $type         Field type,
			 * @param array                  $options      Tabs, indexed by label,
			 * @param null|\Pods\Whatsit\Pod $pod          Pods object for the Pod this UI is for.
			 */
			$type_options = apply_filters( "pods_admin_setup_edit_{$type}_additional_field_options", $type_options, $type, $options, $pod );
			$type_options = apply_filters( 'pods_admin_setup_edit_additional_field_options', $type_options, $type, $options, $pod );

			$options[ 'additional-field-' . $type ] = $type_options;
		}//end foreach

		/**
		 * Allow filtering the field settings by pod name.
		 *
		 * @param array                  $field_settings List of field settings to use.
		 * @param null|\Pods\Whatsit\Pod $pod            Pods object for the Pod this UI is for.
		 * @param array                  $tabs           List of registered tabs
		 */
		$field_settings = apply_filters( "pods_field_settings_{$pod_name}", $field_settings, $pod );

		/**
		 * Allow filtering the field settings by pod name.
		 *
		 * @param array                  $field_settings List of field settings to use.
		 * @param null|\Pods\Whatsit\Pod $pod            Pods object for the Pod this UI is for.
		 * @param array                  $tabs           List of registered tabs
		 */
		$field_settings = apply_filters( 'pods_field_settings', $field_settings, $pod, $tabs );

		$options['basic']['type']['data']        = $field_settings['field_types_select'];
		$options['basic']['pick_object']['data'] = $field_settings['pick_object'];
		$options['basic']['pick_table']['data']  = $field_settings['pick_table'];

		// @todo Look into supporting these in the future.
		/*Tribe__Main::array_insert_after_key( 'visibility', $options['advanced'], [
			'search' => [
				'label'   => __( 'Include in searches', 'pods' ),
				'help'    => __( 'help', 'pods' ),
				'default' => 1,
				'type'    => 'boolean',
			],
		] );

		Tribe__Main::array_insert_after_key( 'validation', $options['advanced'], [
			'regex_validation' => [
				'label'   => __( 'RegEx Validation', 'pods' ),
				'help'    => __( 'help', 'pods' ),
				'type'    => 'text',
				'default' => '',
			],
			'message_regex'    => [
				'label'   => __( 'Message if field does not pass RegEx', 'pods' ),
				'help'    => __( 'help', 'pods' ),
				'type'    => 'text',
				'default' => '',
			],
			'message_required' => [
				'label'      => __( 'Message if field is blank', 'pods' ),
				'help'       => __( 'help', 'pods' ),
				'type'       => 'text',
				'default'    => '',
				'depends-on' => [ 'required' => true ],
			],
			'message_unique'   => [
				'label'      => __( 'Message if field is not unique', 'pods' ),
				'help'       => __( 'help', 'pods' ),
				'type'       => 'text',
				'default'    => '',
				'depends-on' => [ 'unique' => true ],
			],
		] );*/

		if ( 'table' === $pod['storage'] || 'pod' === $pod['type'] ) {
			$options['basic']['unique'] = [
				'name'              => 'unique',
				'label'             => __( 'Unique', 'pods' ),
				'type'              => 'boolean',
				'default'           => 0,
				'boolean_yes_label' => '',
				'help'              => __( 'This will require that the field value entered is unique and has not been saved before.', 'pods' ),
				'excludes-on' => [
					'type' => $tableless_field_types,
				],
			];
		}

		// Only include kitchen sink if dev mode on and not running Codecept tests.
		if ( pods_developer() && ! function_exists( 'codecept_debug' ) ) {
			$options['kitchen-sink'] = json_decode( file_get_contents( PODS_DIR . 'tests/codeception/_data/kitchen-sink-config.json' ), true );
		}

		$pod_type = $pod['type'];
		$pod_name = $pod['name'];

		/**
		 * Add admin fields to the Pod Fields editor for a specific Pod.
		 *
		 * @since 2.8.0
		 *
		 * @param array             $options The Options fields.
		 * @param \Pods\Whatsit\Pod $pod     Current Pods object.
		 * @param array             $tabs    List of registered tabs.
		 */
		$options = apply_filters( "pods_admin_setup_edit_field_options_{$pod_type}_{$pod_name}", $options, $pod, $tabs );

		/**
		 * Add admin fields to the Pod Fields editor for any Pod of a specific content type.
		 *
		 * @since 2.8.0
		 *
		 * @param array             $options The Options fields.
		 * @param \Pods\Whatsit\Pod $pod     Current Pods object.
		 * @param array             $tabs    List of registered tabs.
		 */
		$options = apply_filters( "pods_admin_setup_edit_field_options_{$pod_type}", $options, $pod, $tabs );

		/**
		 * Add admin fields to the Pod Fields editor for all Pods.
		 *
		 * @since 2.8.0
		 *
		 * @param array             $options The Options fields.
		 * @param \Pods\Whatsit\Pod $pod     Current Pods object.
		 * @param array             $tabs    List of registered tabs.
		 */
		$options = apply_filters( 'pods_admin_setup_edit_field_options', $options, $pod, $tabs );

		return $options;
	}
}
