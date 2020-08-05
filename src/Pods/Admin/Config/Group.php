<?php

namespace Pods\Admin\Config;

/**
 * Group configuration class.
 *
 * @since 2.8
 */
class Group extends Base {

	/**
	 * Get list of tabs for the Group object.
	 *
	 * @since 2.8
	 *
	 * @param \Pods\Whatsit\Pod $pod The pod object.
	 *
	 * @return array List of tabs for the Group object.
	 */
	public function get_tabs( \Pods\Whatsit\Pod $pod ) {
		$core_tabs = [
			'basic'        => __( 'Basic', 'pods' ),
			'advanced_tab' => __( 'Advanced', 'pods' ),
			'rest-api'     => __( 'REST API', 'pods' ),
		];

		/**
		 * Filter the Group option tabs. Core tabs are added after this filter.
		 *
		 * @since 2.8
		 *
		 * @param array             $tabs Group option tabs.
		 * @param \Pods\Whatsit\Pod $pod  Current Pods object.
		 */
		$tabs = apply_filters( 'pods_admin_setup_edit_group_tabs', [], $pod );

		$tabs = array_merge( $core_tabs, $tabs );

		return $tabs;
	}

	/**
	 * Get list of fields for the Group object.
	 *
	 * @since 2.8
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
			'description' => [
				'name'    => 'description',
				'label'   => __( 'Description', 'pods' ),
				'help'    => __( 'help', 'pods' ),
				'type'    => 'text',
				'default' => '',
			],
			/*'type' => [
				'name'    => 'type',
				'label'   => __( 'Type', 'pods' ),
				'help'    => __( 'help', 'pods' ),
				'type'    => 'pick',
				'default' => '',
				'data'    => [],
			],*/
		];

		$options['advanced_tab'] = [
			'custom_field_1'    => [
				'name'    => 'custom_field_1',
				'label'   => __( 'Custom Field 1', 'pods' ),
				'help'    => __( 'help', 'pods' ),
				'type'    => 'text',
				'default' => '',
			],
			'custom_field_2'    => [
				'name'    => 'custom_field_2',
				'label'   => __( 'Custom Field 2', 'pods' ),
				'help'    => __( 'help', 'pods' ),
				'type'    => 'text',
				'default' => '',
			],
			'custom_field_3'    => [
				'name'        => 'custom_field_3',
				'label'       => __( 'Custom Field 3', 'pods' ),
				'help'        => __( 'Example help here', 'pods' ),
				'type'        => 'text',
				'default'     => '',
				'description' => 'Example description here',
			],
			'custom_field_4'    => [
				'name'    => 'custom_field_4',
				'label'   => __( 'Custom Field 4', 'pods' ),
				'help'    => __( 'help', 'pods' ),
				'type'    => 'text',
				'default' => '',
			],
			'custom_field_bool' => [
				'name'       => 'custom_field_bool',
				'label'      => __( 'Custom Field Boolean', 'pods' ),
				'help'       => __( 'help', 'pods' ),
				'type'       => 'boolean',
				'default'    => '',
				'dependency' => true,
			],
			'custom_field_5'    => [
				'name'       => 'custom_field_5',
				'label'      => __( 'Custom Field 5', 'pods' ),
				'help'       => __( 'help', 'pods' ),
				'type'       => 'text',
				'default'    => '',
				'depends-on' => [ 'custom_field_bool' => true ],
			],
		];

		$options['rest-api'] = [
			'custom_field_6' => [
				'name'    => 'custom_field_6',
				'label'   => __( 'Custom Field 6', 'pods' ),
				'help'    => __( 'help', 'pods' ),
				'type'    => 'text',
				'default' => '',
			],
			'custom_field_7' => [
				'name'    => 'custom_field_7',
				'label'   => __( 'Custom Field 7', 'pods' ),
				'help'    => __( 'help', 'pods' ),
				'type'    => 'text',
				'default' => '',
			],
			'custom_field_8' => [
				'name'    => 'custom_field_8',
				'label'   => __( 'Custom Field 8', 'pods' ),
				'help'    => __( 'help', 'pods' ),
				'type'    => 'text',
				'default' => '',
			],
			'custom_field_9' => [
				'name'    => 'custom_field_9',
				'label'   => __( 'Custom Field 9', 'pods' ),
				'help'    => __( 'help', 'pods' ),
				'type'    => 'text',
				'default' => '',
			],
		];

		/**
		 * Filter the Group options.
		 *
		 * @since 2.8
		 *
		 * @param array             $options Tabs, indexed by label.
		 * @param \Pods\Whatsit\Pod $pod     Pods object for the Pod this UI is for.
		 * @param array             $tabs    List of registered tabs.
		 */
		$options = apply_filters( 'pods_admin_setup_edit_group_options', $options, $pod, $tabs );

		return $options;
	}
}
