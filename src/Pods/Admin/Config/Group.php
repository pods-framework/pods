<?php

namespace Pods\Admin\Config;

/**
 * Group configuration class.
 *
 * @since 2.8.0
 */
class Group extends Base {

	/**
	 * Get list of tabs for the Group object.
	 *
	 * @since 2.8.0
	 *
	 * @param \Pods\Whatsit\Pod $pod The pod object.
	 *
	 * @return array List of tabs for the Group object.
	 */
	public function get_tabs( \Pods\Whatsit\Pod $pod ) {
		$core_tabs = [
			'basic'    => __( 'Group Details', 'pods' ),
			'advanced' => __( 'Advanced', 'pods' ),
		];

		// Only include kitchen sink if dev mode on and not running Codecept tests.
		if ( pods_developer() && ! function_exists( 'codecept_debug' ) ) {
			$core_tabs['kitchen-sink'] = __( 'Kitchen Sink (temp)', 'pods' );
		}

		$pod_type = $pod['type'];
		$pod_name = $pod['name'];

		$tabs = $core_tabs;

		/**
		 * Filter the Pod Group option tabs for a specific pod type and name.
		 *
		 * @since 2.8.0
		 *
		 * @param array             $core_tabs Tabs to set.
		 * @param \Pods\Whatsit\Pod $pod       Current Pods object.
		 */
		$tabs = (array) apply_filters( "pods_admin_setup_edit_group_tabs_{$pod_type}_{$pod_name}", $tabs, $pod );

		/**
		 * Filter the Pod Group option tabs for a specific pod type.
		 *
		 * @since 2.8.0
		 *
		 * @param array             $tabs Tabs to set.
		 * @param \Pods\Whatsit\Pod $pod  Current Pods object.
		 */
		$tabs = (array) apply_filters( "pods_admin_setup_edit_group_tabs_{$pod_type}", $tabs, $pod );

		/**
		 * Filter the Pod Group option tabs.
		 *
		 * @since 2.8.0
		 *
		 * @param array             $tabs Tabs to set.
		 * @param \Pods\Whatsit\Pod $pod  Current Pods object.
		 */
		$tabs = (array) apply_filters( 'pods_admin_setup_edit_group_tabs', $tabs, $pod );

		// Sort and then enforce the core tabs to be in front.
		uksort( $tabs, 'strnatcmp' );

		$tabs = array_merge( $core_tabs, $tabs );

		return $tabs;
	}

	/**
	 * Get list of fields for the Group object.
	 *
	 * @since 2.8.0
	 *
	 * @param \Pods\Whatsit\Pod $pod  The pod object.
	 * @param array             $tabs The list of tabs for the pod object.
	 *
	 * @return array List of fields for the Group object.
	 */
	public function get_fields( \Pods\Whatsit\Pod $pod, array $tabs ) {
		$options = [];

		$options['basic'] = [
			'label'       => [
				'name'     => 'label',
				'label'    => __( 'Label', 'pods' ),
				'help'     => __( 'help', 'pods' ),
				'type'     => 'text',
				'required' => true,
				'default'  => '',
			],
			'name'        => [
				'name'     => 'name',
				'label'    => __( 'Name', 'pods' ),
				'help'     => __( 'help', 'pods' ),
				'type'     => 'slug',
				'required' => true,
				'default'  => '',
			],
			/*'description' => [
				'name'    => 'description',
				'label'   => __( 'Description', 'pods' ),
				'help'    => __( 'help', 'pods' ),
				'type'    => 'text',
				'default' => '',
			],*/
			/*'type' => [
				'name'    => 'type',
				'label'   => __( 'Type', 'pods' ),
				'help'    => __( 'help', 'pods' ),
				'type'    => 'pick',
				'default' => '',
				'data'    => [],
			],*/
		];

		$options['advanced'] = [
			'visibility'         => [
				'name'  => 'visibility',
				'label' => __( 'Visibility', 'pods' ),
				'type'  => 'heading',
			],
			'restrict_access'    => [
				'type'          => 'boolean_group',
				'name'          => 'restrict_access',
				'label'         => __( 'Restrict Access', 'pods' ),
				'boolean_group' => [
					'logged_in'           => [
						'name'       => 'logged_in',
						'label'      => __( 'Restrict access to Logged In Users', 'pods' ),
						'default'    => 0,
						'type'       => 'boolean',
						'dependency' => true,
						'help'       => __( 'This group of field will only be able to be edited by logged in users. This is not required to be on for the other Restrict Access options to work.', 'pods' ),
					],
					'admin_only'          => [
						'name'       => 'admin_only',
						'label'      => __( 'Restrict access to Admins', 'pods' ),
						'default'    => 0,
						'type'       => 'boolean',
						'dependency' => true,
						'help'       => __( 'This group of fields will only be able to be edited by users with the ability to manage_options or delete_users, or super admins of a WordPress Multisite network', 'pods' ),
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
				],
			],
			'roles_allowed'      => [
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
			'capability_allowed' => [
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

		$object_type = $pod->get_type();

		$is_post_type = 'post_type' === $object_type;
		$is_comment_type = 'comment' === $object_type;

		if ( $is_post_type || $is_comment_type ) {
			$options['basic']['meta_box_context'] = [
				'name'    => 'meta_box_context',
				'label'   => __( 'Meta Box Context', 'pods' ),
				'help'    => [
					__( 'See add_meta_box() documentation.', 'pods' ),
					'https://developer.wordpress.org/reference/functions/add_meta_box/#parameters',
				],
				'type'    => 'pick',
				'default' => 'normal',
				'data'    => [
					'normal'   => __( 'Normal', 'pods' ),
					'side'     => __( 'Side', 'pods' ),
					'advanced' => __( 'Advanced', 'pods' ),
				],
				'pick_format_single' => 'dropdown',
				'pick_show_select_text'   => 0,
			];

			if ( $is_comment_type ) {
				unset( $options['basic']['group_context']['data']['advanced'] );
			}

			$options['basic']['meta_box_priority'] = [
				'name'    => 'meta_box_priority',
				'label'   => __( 'Meta Box Priority', 'pods' ),
				'help'    => [
					__( 'See add_meta_box() documentation.', 'pods' ),
					'https://developer.wordpress.org/reference/functions/add_meta_box/#parameters',
				],
				'type'    => 'pick',
				'default' => 'default',
				'data'    => [
					'high'    => __( 'High', 'pods' ),
					'default' => __( 'Default', 'pods' ),
					'low'     => __( 'Low', 'pods' ),
				],
				'pick_format_single' => 'dropdown',
				'pick_show_select_text'   => 0,
			];
		}

		// Only include kitchen sink if dev mode on and not running Codecept tests.
		if ( pods_developer() && ! function_exists( 'codecept_debug' ) ) {
			$options['kitchen-sink'] = json_decode( file_get_contents( PODS_DIR . 'tests/codeception/_data/kitchen-sink-config.json' ), true );
		}

		$pod_type = $pod['type'];
		$pod_name = $pod['name'];

		/**
		 * Add admin fields to the Pod Groups editor for a specific Pod.
		 *
		 * @since 2.8.0
		 *
		 * @param array             $options The Options fields.
		 * @param \Pods\Whatsit\Pod $pod     Current Pods object.
		 * @param array             $tabs    List of registered tabs.
		 */
		$options = apply_filters( "pods_admin_setup_edit_group_options_{$pod_type}_{$pod_name}", $options, $pod, $tabs );

		/**
		 * Add admin fields to the Pod Groups editor for any Pod of a specific content type.
		 *
		 * @since 2.8.0
		 *
		 * @param array             $options The Options fields.
		 * @param \Pods\Whatsit\Pod $pod     Current Pods object.
		 * @param array             $tabs    List of registered tabs.
		 */
		$options = apply_filters( "pods_admin_setup_edit_group_options_{$pod_type}", $options, $pod, $tabs );

		/**
		 * Add admin fields to the Pod Groups editor for all Pods.
		 *
		 * @since 2.8.0
		 *
		 * @param array             $options The Options fields.
		 * @param \Pods\Whatsit\Pod $pod     Current Pods object.
		 * @param array             $tabs    List of registered tabs.
		 */
		$options = apply_filters( 'pods_admin_setup_edit_group_options', $options, $pod, $tabs );

		return $options;
	}
}
