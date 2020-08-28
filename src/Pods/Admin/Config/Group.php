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
			'basic' => __( 'Group Details', 'pods' ),
		];

		$core_tabs['dfv-demo'] = __( 'DFV Demo (temp)', 'pods' );

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
			];
		}

		// @todo List all of demo fields variations.
		$options['dfv-demo'] = [
			[
				'name'        => 'dfv_demo_text',
				'label'       => 'Field: text',
				'description' => 'Some description text',
				'help'        => 'Some help text',
				'type'        => 'text',
			],
			[
				'name'        => 'dfv_demo_boolean',
				'label'       => 'Field: boolean',
				'description' => 'Some description boolean',
				'help'        => 'Some help boolean',
				'type'        => 'boolean',
			],
			[
				'name'        => 'dfv_demo_code',
				'label'       => 'Field: code',
				'description' => 'Some description code',
				'help'        => 'Some help code',
				'type'        => 'code',
			],
			[
				'name'        => 'dfv_demo_color',
				'label'       => 'Field: color',
				'description' => 'Some description color',
				'help'        => 'Some help color',
				'type'        => 'color',
			],
			[
				'name'        => 'dfv_demo_currency',
				'label'       => 'Field: currency',
				'description' => 'Some description currency',
				'help'        => 'Some help currency',
				'type'        => 'currency',
			],
			[
				'name'        => 'dfv_demo_date',
				'label'       => 'Field: date',
				'description' => 'Some description date',
				'help'        => 'Some help date',
				'type'        => 'date',
			],
			[
				'name'        => 'dfv_demo_datetime',
				'label'       => 'Field: datetime',
				'description' => 'Some description datetime',
				'help'        => 'Some help datetime',
				'type'        => 'datetime',
			],
			[
				'name'        => 'dfv_demo_email',
				'label'       => 'Field: email',
				'description' => 'Some description email',
				'help'        => 'Some help email',
				'type'        => 'email',
			],
			[
				'name'        => 'dfv_demo_file',
				'label'       => 'Field: file',
				'description' => 'Some description file',
				'help'        => 'Some help file',
				'type'        => 'file',
			],
			[
				'name'        => 'dfv_demo_heading',
				'label'       => 'Field: heading',
				'description' => 'Some description heading',
				'help'        => 'Some help heading',
				'type'        => 'heading',
			],
			[
				'name'        => 'dfv_demo_html',
				'label'       => 'Field: html',
				'description' => 'Some description html',
				'help'        => 'Some help html',
				'type'        => 'html',
			],
			[
				'name'        => 'dfv_demo_number',
				'label'       => 'Field: number',
				'description' => 'Some description number',
				'help'        => 'Some help number',
				'type'        => 'number',
			],
			[
				'name'        => 'dfv_demo_oembed',
				'label'       => 'Field: oembed',
				'description' => 'Some description oembed',
				'help'        => 'Some help oembed',
				'type'        => 'oembed',
			],
			[
				'name'        => 'dfv_demo_paragraph',
				'label'       => 'Field: paragraph',
				'description' => 'Some description paragraph',
				'help'        => 'Some help paragraph',
				'type'        => 'paragraph',
			],
			[
				'name'        => 'dfv_demo_paragraph',
				'label'       => 'Field: paragraph',
				'description' => 'Some description paragraph',
				'help'        => 'Some help paragraph',
				'type'        => 'paragraph',
			],
			[
				'name'        => 'dfv_demo_password',
				'label'       => 'Field: password',
				'description' => 'Some description password',
				'help'        => 'Some help password',
				'type'        => 'password',
			],
			[
				'name'        => 'dfv_demo_phone',
				'label'       => 'Field: phone',
				'description' => 'Some description phone',
				'help'        => 'Some help phone',
				'type'        => 'phone',
			],
			[
				'name'        => 'dfv_demo_pick',
				'label'       => 'Field: pick',
				'description' => 'Some description pick',
				'help'        => 'Some help pick',
				'type'        => 'pick',
			],
			[
				'name'        => 'dfv_demo_slug',
				'label'       => 'Field: slug',
				'description' => 'Some description slug',
				'help'        => 'Some help slug',
				'type'        => 'slug',
			],
			[
				'name'        => 'dfv_demo_time',
				'label'       => 'Field: time',
				'description' => 'Some description time',
				'help'        => 'Some help time',
				'type'        => 'time',
			],
			[
				'name'        => 'dfv_demo_website',
				'label'       => 'Field: website',
				'description' => 'Some description website',
				'help'        => 'Some help website',
				'type'        => 'website',
			],
			[
				'name'        => 'dfv_demo_wysiwyg',
				'label'       => 'Field: wysiwyg',
				'description' => 'Some description wysiwyg',
				'help'        => 'Some help wysiwyg',
				'type'        => 'wysiwyg',
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
