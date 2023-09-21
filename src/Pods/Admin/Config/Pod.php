<?php

namespace Pods\Admin\Config;

use PodsForm;

/**
 * Pod configuration class.
 *
 * @since 2.8.0
 */
class Pod extends Base {

	/**
	 * Get list of tabs for the Pod object.
	 *
	 * @since 2.8.0
	 *
	 * @param \Pods\Whatsit\Pod $pod The pod object.
	 *
	 * @return array List of tabs for the Pod object.
	 */
	public function get_tabs( \Pods\Whatsit\Pod $pod ) {
		$labels      = false;
		$admin_ui    = false;
		$connections = false;
		$advanced    = false;

		$pod_type    = pods_v( 'type', $pod );
		$is_extended = $pod->is_extended();

		if ( 'post_type' === $pod_type && ! $is_extended ) {
			$labels      = true;
			$admin_ui    = true;
			$connections = true;
			$advanced    = true;
		} elseif ( 'taxonomy' === $pod_type && ! $is_extended ) {
			$labels      = true;
			$admin_ui    = true;
			$connections = true;
			$advanced    = true;
		} elseif ( 'pod' === $pod_type ) {
			$labels   = true;
			$admin_ui = true;
			$advanced = true;
		} elseif ( 'settings' === $pod_type ) {
			$labels   = true;
			$admin_ui = true;
		}

		$core_tabs = [];

		if ( $labels ) {
			$core_tabs['labels'] = __( 'Labels', 'pods' );
		}

		if ( $admin_ui ) {
			$core_tabs['admin-ui'] = __( 'Admin UI', 'pods' );
		}

		if ( $connections ) {
			$core_tabs['connections'] = __( 'Connections', 'pods' );
		}

		if ( $advanced ) {
			$core_tabs['advanced'] = __( 'Advanced Options', 'pods' );
		}

		// Only include kitchen sink if dev mode on and not running Codecept tests.
		if ( pods_developer() && ! function_exists( 'codecept_debug' ) ) {
			$core_tabs['kitchen-sink'] = __( 'Kitchen Sink (temp)', 'pods' );
		}

		$args = compact( [ 'labels', 'admin_ui', 'connections', 'advanced' ] );

		$pod_name = $pod['name'];

		$tabs = $core_tabs;

		/**
		 * Filter the Pod option tabs for a specific pod type and name.
		 *
		 * @param array             $core_tabs Tabs to set.
		 * @param \Pods\Whatsit\Pod $pod       Current Pods object.
		 * @param array             $args      Additional args.
		 */
		$tabs = (array) apply_filters( "pods_admin_setup_edit_tabs_{$pod_type}_{$pod_name}", $tabs, $pod, $args );

		/**
		 * Filter the Pod option tabs for a specific pod type.
		 *
		 * @param array             $tabs Tabs to set.
		 * @param \Pods\Whatsit\Pod $pod  Current Pods object.
		 * @param array             $args Additional args.
		 */
		$tabs = (array) apply_filters( "pods_admin_setup_edit_tabs_{$pod_type}", $tabs, $pod, $args );

		/**
		 * Filter the Pod option tabs.
		 *
		 * @param array             $tabs Tabs to set.
		 * @param \Pods\Whatsit\Pod $pod  Current Pods object.
		 * @param array             $args Additional args.
		 */
		$tabs = (array) apply_filters( 'pods_admin_setup_edit_tabs', $tabs, $pod, $args );

		// Sort and then enforce the core tabs to be in front.
		uksort( $tabs, 'strnatcmp' );

		$tabs = array_merge( $core_tabs, $tabs );

		return $tabs;
	}

	/**
	 * Get list of fields for the Pod object.
	 *
	 * @since 2.8.0
	 *
	 * @param \Pods\Whatsit\Pod $pod  The pod object.
	 * @param array             $tabs The list of tabs for the Pod object.
	 *
	 * @return array List of fields for the Pod object.
	 */
	public function get_fields( \Pods\Whatsit\Pod $pod, array $tabs ) {
		$pod_type    = pods_v( 'type', $pod );
		$pod_name    = pods_v( 'name', $pod );
		$is_extended = $pod->is_extended();

		$options = [];

		$tableless_field_types = PodsForm::tableless_field_types();

		if ( 'settings' !== $pod_type && ! $is_extended ) {
			$labels = [
				'label'                            => [
					'label'           => __( 'Label', 'pods' ),
					'help'            => __( 'help', 'pods' ),
					'type'            => 'text',
					'default'         => pods_v( 'label', $pod, ucwords( str_replace( '_', ' ', pods_v( 'name', $pod ) ) ) ),
					'text_max_length' => 30,
				],
				'label_singular'                   => [
					'label'           => __( 'Singular Label', 'pods' ),
					'help'            => __( 'help', 'pods' ),
					'type'            => 'text',
					'default'         => pods_v( 'label_singular', $pod, pods_v( 'label', $pod, ucwords( str_replace( '_', ' ', pods_v( 'name', $pod ) ) ) ) ),
					'text_max_length' => 30,
				],
				'placeholder_enter_title_here'     => [
					'label'       => __( 'New post title placeholder text', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'placeholder' => __( 'Add title' ),
					'object_type' => [ 'post_type' ],
				],
				'label_add_new'                    => [
					'label'       => __( 'Add New', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => [ 'post_type', 'pod' ],
				],
				'label_add_new_item'               => [
					'label'               => __( 'Add new %s', 'pods' ),
					'label_param'         => 'label_singular',
					'label_param_default' => __( 'Item', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
				],
				'label_new_item'                   => [
					'label'               => __( 'New %s', 'pods' ),
					'label_param'         => 'label_singular',
					'label_param_default' => __( 'Item', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'post_type', 'pod' ],
				],
				'label_new_item_name'              => [
					'label'               => __( 'New %s Name', 'pods' ),
					'label_param'         => 'label_singular',
					'label_param_default' => __( 'Item', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'taxonomy' ],
				],
				'label_edit'                       => [
					'label'       => __( 'Edit', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => [ 'pod' ],
				],
				'label_edit_item'                  => [
					'label'               => __( 'Edit %s', 'pods' ),
					'label_param'         => 'label_singular',
					'label_param_default' => __( 'Item', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
				],
				'label_update_item'                => [
					'label'               => __( 'Update %s', 'pods' ),
					'label_param'         => 'label_singular',
					'label_param_default' => __( 'Item', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'taxonomy', 'pod' ],
				],
				'label_duplicate'                  => [
					'label'       => __( 'Duplicate', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => [ 'pod' ],
				],
				'label_duplicate_item'             => [
					'label'               => __( 'Duplicate %s', 'pods' ),
					'label_param'         => 'label_singular',
					'label_param_default' => __( 'Item', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'pod' ],
				],
				'label_delete_item'                => [
					'label'               => __( 'Delete %s', 'pods' ),
					'label_param'         => 'label_singular',
					'label_param_default' => __( 'Item', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'pod' ],
				],
				'label_view'                       => [
					'label'       => __( 'View', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => [ 'pod' ],
				],
				'label_view_item'                  => [
					'label'               => __( 'View %s', 'pods' ),
					'label_param'         => 'label_singular',
					'label_param_default' => __( 'Item', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
				],
				'label_view_items'                 => [
					'label'               => __( 'View %s', 'pods' ),
					'label_param'         => 'label',
					'label_param_default' => __( 'Items', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'post_type' ],
				],
				'label_back_to_manage'             => [
					'label'       => __( 'Back to Manage', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => [ 'pod' ],
				],
				'label_manage'                     => [
					'label'       => __( 'Manage', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => [ 'pod' ],
				],
				'label_manage_items'               => [
					'label'               => __( 'Manage %s', 'pods' ),
					'label_param'         => 'label',
					'label_param_default' => __( 'Items', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'pod' ],
				],
				'label_reorder'                    => [
					'label'       => __( 'Reorder', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => [ 'pod' ],
				],
				'label_reorder_items'              => [
					'label'               => __( 'Reorder %s', 'pods' ),
					'label_param'         => 'label',
					'label_param_default' => __( 'Items', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'pod' ],
				],
				'label_all_items'                  => [
					'label'               => __( 'All %s', 'pods' ),
					'label_param'         => 'label',
					'label_param_default' => __( 'Items', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
				],
				'label_search'                     => [
					'label'       => __( 'Search', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => [ 'pod' ],
				],
				'label_search_items'               => [
					'label'               => __( 'Search %s', 'pods' ),
					'label_param'         => 'label',
					'label_param_default' => __( 'items', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
				],
				'label_popular_items'              => [
					'label'               => __( 'Popular %s', 'pods' ),
					'label_param'         => 'label',
					'label_param_default' => __( 'Items', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'taxonomy' ],
				],
				'label_parent'                     => [
					'label'               => __( 'Parent %s', 'pods' ),
					'label_param'         => 'label_singular',
					'label_param_default' => __( 'Item', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'post_type', 'pod' ],
				],
				'label_parent_item'                => [
					'label'               => __( 'Parent %s', 'pods' ),
					'label_param'         => 'label_singular',
					'label_param_default' => __( 'Item', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'taxonomy' ],
				],
				'label_parent_item_colon'          => [
					'label'               => __( 'Parent %s:', 'pods' ),
					'label_param'         => 'label_singular',
					'label_param_default' => __( 'Item', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
				],
				'label_not_found'                  => [
					'label'   => __( 'Not Found', 'pods' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => '',
				],
				'label_no_items_found'             => [
					'label'               => __( 'No %s Found', 'pods' ),
					'label_param'         => 'label_singular',
					'label_param_default' => __( 'Item', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'pod' ],
				],
				'label_not_found_in_trash'         => [
					'label'       => __( 'Not Found in Trash', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => [ 'post_type', 'pod' ],
				],
				'label_archives'                   => [
					'label'               => __( '%s Archives', 'pods' ),
					'label_param'         => 'label_singular',
					'label_param_default' => __( 'Item', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'post_type' ],
				],
				'label_attributes'                 => [
					'label'               => __( '%s Attributes', 'pods' ),
					'label_param'         => 'label_singular',
					'label_param_default' => __( 'Item', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'post_type' ],
				],
				'label_insert_into_item'           => [
					'label'               => __( 'Insert into %s', 'pods' ),
					'label_param'         => 'label_singular',
					'label_param_default' => __( 'Item', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'post_type' ],
				],
				'label_uploaded_to_this_item'      => [
					'label'               => __( 'Uploaded to this %s', 'pods' ),
					'label_param'         => 'label_singular',
					'label_param_default' => __( 'Item', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'post_type' ],
				],
				'label_featured_image'             => [
					'label'       => __( 'Featured Image', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					// 'depends-on' => array( 'supports_thumbnail' => true ), // @todo Dependency from other tabs not working
					'object_type' => [ 'post_type' ],
				],
				'label_set_featured_image'         => [
					'label'       => __( 'Set featured Image', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					// 'depends-on' => array( 'supports_thumbnail' => true ), // @todo Dependency from other tabs not working
					'object_type' => [ 'post_type' ],
				],
				'label_remove_featured_image'      => [
					'label'       => __( 'Remove featured Image', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					// 'depends-on' => array( 'supports_thumbnail' => true ), // @todo Dependency from other tabs not working
					'object_type' => [ 'post_type' ],
				],
				'label_use_featured_image'         => [
					'label'       => __( 'Use as featured Image', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					// 'depends-on' => array( 'supports_thumbnail' => true ), // @todo Dependency from other tabs not working
					'object_type' => [ 'post_type' ],
				],
				'label_filter_items_list'          => [
					'label'               => __( 'Filter %s', 'pods' ),
					'label_param'         => 'label',
					'label_param_default' => __( 'Items', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'post_type' ],
				],
				'label_items_list_navigation'      => [
					'label'               => __( '%s list navigation', 'pods' ),
					'label_param'         => 'label',
					'label_param_default' => __( 'Items', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'post_type', 'taxonomy' ],
				],
				'label_items_list'                 => [
					'label'               => __( '%s list', 'pods' ),
					'label_param'         => 'label',
					'label_param_default' => __( 'Items', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'post_type', 'taxonomy' ],
				],
				'label_back_to_items'              => [
					'label'               => __( 'Back to %s', 'pods' ),
					'label_param'         => 'label',
					'label_param_default' => __( 'Items', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'taxonomy' ],
				],
				'label_name_field_description'     => [
					'label'       => __( 'The name is how it appears on your site.', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'description' => __( 'Description for the Name field on Edit Tags screen.', 'pods' ),
					'object_type' => [ 'taxonomy' ],
				],
				'label_parent_field_description'   => [
					'label'       => __( 'Assign a parent term to create a hierarchy.', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'description' => __( 'Description for the Parent field on Edit Tags screen.', 'pods' ),
					'object_type' => [ 'taxonomy' ],
				],
				'label_slug_field_description'     => [
					'label'       => __( 'The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'description' => __( 'Description for the Slug field on Edit Tags screen.', 'pods' ),
					'object_type' => [ 'taxonomy' ],
				],
				'label_desc_field_description'     => [
					'label'       => __( 'The description is not prominent by default; however, some themes may show it.', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'description' => __( 'Description for the Description field on Edit Tags screen.', 'pods' ),
					'object_type' => [ 'taxonomy' ],
				],
				'label_separate_items_with_commas' => [
					'label'               => __( 'Separate %s with commas', 'pods' ),
					'label_param'         => 'label',
					'label_param_default' => __( 'items', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'taxonomy' ],
				],
				'label_add_or_remove_items'        => [
					'label'               => __( 'Add or remove %s', 'pods' ),
					'label_param'         => 'label',
					'label_param_default' => __( 'items', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'taxonomy' ],
				],
				'label_choose_from_the_most_used'  => [
					'label'               => __( 'Choose from the most used %s', 'pods' ),
					'label_param'         => 'label',
					'label_param_default' => __( 'items', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'taxonomy' ],
				],
				'label_no_terms'                   => [
					'label'               => __( 'No %s', 'pods' ),
					'label_param'         => 'label',
					'label_param_default' => __( 'items', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'taxonomy' ],
				],
				'label_item_published'             => [
					'label'               => __( '%s Published.', 'pods' ),
					'label_param'         => 'label_singular',
					'label_param_default' => __( 'Item', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'post_type' ],
				],
				'label_item_published_privately'   => [
					'label'               => __( '%s published privately.', 'pods' ),
					'label_param'         => 'label_singular',
					'label_param_default' => __( 'Item', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'post_type' ],
				],
				'label_item_reverted_to_draft'     => [
					'label'               => __( '%s reverted to draft.', 'pods' ),
					'label_param'         => 'label_singular',
					'label_param_default' => __( 'Item', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'post_type' ],
				],
				'label_item_scheduled'             => [
					'label'               => __( '%s scheduled.', 'pods' ),
					'label_param'         => 'label_singular',
					'label_param_default' => __( 'Item', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'post_type' ],
				],
				'label_item_updated'               => [
					'label'               => __( '%s updated.', 'pods' ),
					'label_param'         => 'label_singular',
					'label_param_default' => __( 'Item', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'post_type' ],
				],
				'filter_by_date'                   => [
					'label'       => __( 'Filter by date', 'pods' ),
					'help'        => __( 'help', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'object_type' => [ 'post_type' ],
				],
				'filter_by_item'                   => [
					'label'               => __( 'Filter by %s', 'pods' ),
					'label_param'         => 'label_singular',
					'label_param_default' => __( 'Item', 'pods' ),
					'help'                => __( 'help', 'pods' ),
					'type'                => 'text',
					'default'             => '',
					'object_type'         => [ 'taxonomy' ],
				],
			];

			$options['labels'] = [];

			/**
			 * Filter through all labels if they have an object_type set and match it against the current object type
			 */
			foreach ( $labels as $label => $label_data ) {
				if ( array_key_exists( 'object_type', $label_data ) ) {
					if ( in_array( $pod_type, $label_data['object_type'], true ) ) {
						// Do not add the object_type to the actual label data
						unset( $label_data['object_type'] );

						$options['labels'][ $label ] = $label_data;
					}
				} else {
					$options['labels'][ $label ] = $label_data;
				}
			}
		} elseif ( 'settings' === $pod_type ) {
			$options['labels'] = [
				'label'     => [
					'label'           => __( 'Page Title', 'pods' ),
					'help'            => __( 'help', 'pods' ),
					'type'            => 'text',
					'default'         => str_replace( '_', ' ', pods_v( 'name', $pod ) ),
					'text_max_length' => 30,
				],
				'menu_name' => [
					'label'           => __( 'Menu Name', 'pods' ),
					'help'            => __( 'help', 'pods' ),
					'type'            => 'text',
					'default'         => pods_v( 'label', $pod, ucwords( str_replace( '_', ' ', pods_v( 'name', $pod ) ) ) ),
					'text_max_length' => 30,
				],
			];
		}//end if

		if ( 'post_type' === $pod_type ) {
			$options['admin-ui'] = [
				'description'          => [
					'label'   => __( 'Post Type Description', 'pods' ),
					'help'    => __( 'A short descriptive summary of what the post type is.', 'pods' ),
					'type'    => 'text',
					'default' => '',
				],
				'show_ui'              => [
					'label'             => __( 'Show Admin UI', 'pods' ),
					'help'              => __( 'Whether to generate a default UI for managing this post type in the admin.', 'pods' ),
					'type'              => 'boolean',
					'default'           => pods_v( 'public', $pod, true ),
					'boolean_yes_label' => '',
				],
				'show_in_menu'         => [
					'label'             => __( 'Show Admin Menu in Dashboard', 'pods' ),
					'help'              => __( 'Whether to show the post type in the admin menu.', 'pods' ),
					'type'              => 'boolean',
					'default'           => pods_v( 'public', $pod, true ),
					'dependency'        => true,
					'boolean_yes_label' => '',
				],
				'menu_location_custom' => [
					'label'      => __( 'Parent Menu ID (optional)', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'depends-on' => [ 'show_in_menu' => true ],
				],
				'menu_name'            => [
					'label'      => __( 'Menu Name', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => [ 'show_in_menu' => true ],
				],
				'menu_position'        => [
					'label'              => __( 'Menu Position', 'pods' ),
					'help'               => __( 'This will be the position of the menu item. See <a href="https://developer.wordpress.org/themes/functionality/administration-menus/#top-level-menus" target="_blank" rel="noopener noreferrer">WordPress.org Developer Docs</a> for more details about how positioning works.', 'pods' ),
					'type'               => 'number',
					'number_decimals'    => 2,
					'number_format'      => '9999.99',
					'number_format_soft' => 1,
					'default'            => 0,
					'depends-on'         => [ 'show_in_menu' => true ],
				],
				'menu_icon'            => [
					'label'      => __( 'Menu Icon', 'pods' ),
					'help'       => __( 'URL or Dashicon name for the menu icon. You may specify the path to the icon using one of the <a href="https://docs.pods.io/displaying-pods/magic-tags/special-magic-tags/#Site_Tags" target="_blank" rel="noopener noreferrer">site tag</a> type <a href="https://docs.pods.io/displaying-pods/magic-tags/special-magic-tags/" target="_blank" rel="noopener noreferrer">special magic tags</a>. For example, for a file in your theme directory, use "{@template-url}/path/to/image.png". You may also use the name of a <a href="https://developer.wordpress.org/resource/dashicons/" target="_blank" rel="noopener noreferrer">Dashicon</a>. For example, to use the empty star icon, use "dashicons-star-empty".', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => [ 'show_in_menu' => true ],
				],
				'show_in_nav_menus'    => [
					'label'             => __( 'Show in Navigation Menus', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => '',
				],
				'show_in_admin_bar'    => [
					'label'             => __( 'Show in Admin Bar "New" Menu', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'dependency'        => true,
					'boolean_yes_label' => '',
				],
				'name_admin_bar'       => [
					'label'      => __( 'Admin bar name', 'pods' ),
					'help'       => __( 'Defaults to singular name', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => [ 'show_in_admin_bar' => true ],
				],
			];

			$post_type_name = pods_v( 'name', $pod, 'post_type', true );

			/**
			 * Allow filtering the default post status.
			 *
			 * @param string            $default_post_status The default post status.
			 * @param \Pods\Whatsit\Pod $pod                 Current Pods object.
			 */
			$default_post_status = apply_filters( "pods_api_default_status_{$post_type_name}", 'draft', $pod );

			$options['connections'] = [
				'post_type_built_in_taxonomies' => [
					'name'          => 'post_type_built_in_taxonomies',
					'label'         => __( 'Enable Connections to Taxonomy', 'pods' ),
					'help'          => __( 'You can enable the ability to select terms from these Taxonomies on any post for this Post Type. Once connected, posts from this Custom Post Type will appear in the Taxonomy archive page of the associated Taxonomies selected. Only Categories and Tag need to be specifically selected to be shown on Taxonomy archives on their own.', 'pods' ),
					'type'          => 'boolean_group',
					'boolean_group' => [],
					'dependency'    => true,
				],
			];

			// Only show this if it is a Custom Post Type.
			if ( ! $is_extended ) {
				$options['connections']['archive_show_in_taxonomies'] = [
					'name'           => 'archive_show_in_taxonomies',
					'label'          => __( 'Show in Taxonomy Archives', 'pods' ),
					'help'           => __( 'You can include posts from this Custom Post Type in the Taxonomy archive page for Categories and Tags. These Taxonomies operate differently in WordPress and require an opt-in to have Custom Post Types included.', 'pods' ),
					'type'           => 'boolean_group',
					'boolean_group'  => [],
					'depends-on-any' => [
						'built_in_taxonomies_category' => true,
						'built_in_taxonomies_post_tag' => true,
					],
				];
			}

			$options['connections']['register_custom_taxonomy'] = [
				'name'         => 'register_custom_taxonomy',
				'label'        => __( 'Add new connection', 'pods' ),
				'type'         => 'html',
				'html_content' => sprintf(
					'<a href="%1$s">%2$s</a>',
					esc_url( admin_url( 'admin.php?page=pods-add-new&create_extend=create&type=taxonomy' ) ),
					esc_html__( 'Create a new Custom Taxonomy', 'pods' )
				),
			];

			$options['advanced'] = [
				'public'                  => [
					'label'             => __( 'Public', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => '',
				],
				'publicly_queryable'      => [
					'label'             => __( 'Publicly Queryable', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => pods_v( 'public', $pod, true ),
					'boolean_yes_label' => '',
				],
				'exclude_from_search'     => [
					'label'             => __( 'Exclude from Search', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => ! pods_v( 'public', $pod, true ),
					'boolean_yes_label' => '',
				],
				'capability_type'         => [
					'label'                 => __( 'User Capability', 'pods' ),
					'help'                  => __( 'Uses these capabilities for access to this post type: edit_{capability}, read_{capability}, and delete_{capability}', 'pods' ),
					'type'                  => 'pick',
					'default'               => 'post',
					'data'                  => [
						'post'   => 'post',
						'page'   => 'page',
						'custom' => __( 'Custom Capability', 'pods' ),
					],
					'pick_format_single' => 'dropdown',
					'pick_show_select_text' => 0,
					'dependency'            => true,
				],
				'capability_type_custom'  => [
					'label'      => __( 'Custom User Capability', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'default'    => pods_v( 'name', $pod ),
					'depends-on' => [ 'capability_type' => 'custom' ],
				],
				'capability_type_extra'   => [
					'label'             => __( 'Additional User Capabilities', 'pods' ),
					'help'              => __( 'Enables additional capabilities for this Post Type including: delete_{capability}s, delete_private_{capability}s, delete_published_{capability}s, delete_others_{capability}s, edit_private_{capability}s, and edit_published_{capability}s', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => '',
				],
				'disable_create_posts'    => [
					'label'             => __( 'Disable Add New forms', 'pods' ),
					'help'              => __( 'If disabled, the Add New form will be disabled on the WordPress post editor".', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'dependency'        => true,
					'boolean_yes_label' => '',
				],
				'has_archive'             => [
					'label'             => __( 'Enable Archive Page', 'pods' ),
					'help'              => __( 'If enabled, creates an archive page with list of of items in this custom post type. Functions like a category page for posts. Can be controlled with a template in your theme called "archive-{$post-type}.php".', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'dependency'        => true,
					'boolean_yes_label' => '',
				],
				'has_archive_slug'        => [
					'label'         => __( 'Archive Page Slug Override', 'pods' ),
					'help'          => __( 'If archive page is enabled, you can override the slug used by WordPress, which defaults to the name of the post type.', 'pods' ),
					'type'          => 'text',
					'slug_fallback' => '-',
					'default'       => '',
					'depends-on'    => [ 'has_archive' => true ],
				],
				'hierarchical'            => [
					'label'             => __( 'Hierarchical', 'pods' ),
					'help'              => __( 'Allows for parent/ child relationships between items, just like with Pages. Note: To edit relationships in the post editor, you must enable "Page Attributes" in the "Supports" section below.', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'dependency'        => true,
					'boolean_yes_label' => '',
				],
				'can_export'                 => [
					'label'             => __( 'Allow Export', 'pods' ),
					'help'              => __( 'Allows you to export content for this post type in Tools > Export.', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'dependency'        => true,
					'boolean_yes_label' => '',
				],
				'rewrite'                 => [
					'label'             => __( 'Rewrite', 'pods' ),
					'help'              => __( 'Allows you to use pretty permalinks, if set in WordPress Settings->Permalinks. If not enabled, your links will be in the form of "example.com/?pod_name=post_slug" regardless of your permalink settings.', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'dependency'        => true,
					'boolean_yes_label' => '',
				],
				'rewrite_custom_slug'     => [
					'label'         => __( 'Custom Rewrite Slug', 'pods' ),
					'help'          => __( 'Changes the first segment of the URL, which by default is the name of the Pod. For example, if your Pod is called "foo", if this field is left blank, your link will be "example.com/foo/post_slug", but if you were to enter "bar" your link will be "example.com/bar/post_slug".', 'pods' ),
					'type'          => 'text',
					'slug_fallback' => '-',
					'default'       => '',
					'depends-on'    => [ 'rewrite' => true ],
				],
				'rewrite_with_front'      => [
					'label'             => __( 'Rewrite with Front', 'pods' ),
					'help'              => __( 'Allows permalinks to be prepended with your front base (example: if your permalink structure is /blog/, then your links will be: Unchecked->/news/, Checked->/blog/news/)', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'depends-on'        => [ 'rewrite' => true ],
					'boolean_yes_label' => '',
				],
				'rewrite_feeds'           => [
					'label'             => __( 'Rewrite Feeds', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'depends-on'        => [ 'rewrite' => true ],
					'boolean_yes_label' => '',
				],
				'rewrite_pages'           => [
					'label'             => __( 'Rewrite Pages', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'depends-on'        => [ 'rewrite' => true ],
					'boolean_yes_label' => '',
				],
				'query_var'               => [
					'label'             => __( 'Query Var', 'pods' ),
					'help'              => __( 'The Query Var is used in the URL and underneath the WordPress Rewrite API to tell WordPress what page or post type you are on. For a list of reserved Query Vars, read <a href="http://codex.wordpress.org/WordPress_Query_Vars">WordPress Query Vars</a> from the WordPress Codex.', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => '',
				],
				'can_export'              => [
					'label'             => __( 'Exportable', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => '',
				],
				'default_status'          => [
					'label'                 => __( 'Default Status', 'pods' ),
					'help'                  => __( 'help', 'pods' ),
					'type'                  => 'pick',
					'pick_object'           => 'post-status',
					'default'               => $default_post_status,
					'pick_format_single' => 'dropdown',
					'pick_show_select_text' => 0,
				],
				'post_type_supports'      => [
					'name'          => 'post_type_supports',
					'type'          => 'boolean_group',
					'label'         => __( 'Supports', 'pods' ),
					'boolean_group' => [
						'supports_title'           => [
							'name'  => 'supports_title',
							'label' => __( 'Title', 'pods' ),
							'type'  => 'boolean',
						],
						'supports_editor'          => [
							'name'  => 'supports_editor',
							'label' => __( 'Editor', 'pods' ),
							'type'  => 'boolean',
						],
						'supports_author'          => [
							'name'  => 'supports_author',
							'label' => __( 'Author', 'pods' ),
							'type'  => 'boolean',
						],
						'supports_thumbnail'       => [
							'name'       => 'supports_thumbnail',
							'label'      => __( 'Featured Image', 'pods' ),
							'type'       => 'boolean',
							'dependency' => true,
						],
						'supports_excerpt'         => [
							'name'  => 'supports_excerpt',
							'label' => __( 'Excerpt', 'pods' ),
							'type'  => 'boolean',
						],
						'supports_trackbacks'      => [
							'name'  => 'supports_trackbacks',
							'label' => __( 'Trackbacks', 'pods' ),
							'type'  => 'boolean',
						],
						'supports_custom_fields'   => [
							'name'  => 'supports_custom_fields',
							'label' => __( 'Manually Edit Custom Fields (can cause slow performance)', 'pods' ),
							'type'  => 'boolean',
						],
						'supports_comments'        => [
							'name'  => 'supports_comments',
							'label' => __( 'Comments', 'pods' ),
							'type'  => 'boolean',
						],
						'supports_revisions'       => [
							'name'  => 'supports_revisions',
							'label' => __( 'Revisions', 'pods' ),
							'type'  => 'boolean',
						],
						'supports_page_attributes' => [
							'name'  => 'supports_page_attributes',
							'label' => __( 'Page Attributes', 'pods' ),
							'type'  => 'boolean',
						],
						'supports_post_formats'    => [
							'name'  => 'supports_post_formats',
							'label' => __( 'Post Formats', 'pods' ),
							'type'  => 'boolean',
						],
					],
				],
				'supports_custom'         => [
					'name'  => 'supports_custom',
					'type'  => 'text',
					'label' => __( 'Advanced Supports', 'pods' ),
					'help'  => __( 'Comma-separated list of custom "supports" values to pass to register_post_type.', 'pods' ),
				],
				'revisions_to_keep_limit' => [
					'name'        => 'revisions_to_keep_limit',
					'type'        => 'number',
					'default'     => '0',
					'label'       => __( 'Maximum revisions to keep per post', 'pods' ),
					'description' => __( 'The default "0" will fallback to the normal WordPress default.', 'pods' ),
					'help'        => __( 'Enter -1 to keep ALL revisions. Enter any positive number to limit the number of revisions kept to that amount.', 'pods' ),
					'depends-on'  => [ 'supports_revisions' => true ],
				],
				'delete_with_user'        => [
					'label'             => __( 'Allow posts to be deleted when author is deleted', 'pods' ),
					'help'              => __( 'When you go to delete a user who is an author of any posts for this post type, you will be given an option to reassign all of their posts to a different author or to delete their posts. With this option on, it will be included in posts to delete upon choosing to delete all posts.', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => __( 'Include posts from this post type when deleting authors and choosing not to reassign posts to a new author.', 'pods' ),
				],
			];

			/**
			 * Allow filtering the list of supported features for the post type
			 *
			 * @since 2.8.0
			 *
			 * @param array             $supports The list of supported features for the post type.
			 * @param array             $options  The Options fields.
			 * @param \Pods\Whatsit\Pod $pod      Current Pods object.
			 * @param array             $tabs     List of registered tabs.
			 */
			$options['advanced']['post_type_supports']['boolean_group'] = apply_filters( 'pods_admin_config_pod_fields_post_type_supported_features', $options['advanced']['post_type_supports']['boolean_group'], $options, $pod, $tabs );

			$related_objects = PodsForm::field_method( 'pick', 'related_objects', true );

			$available_taxonomies = [];

			if ( ! empty( $related_objects[ __( 'Taxonomies', 'pods' ) ] ) ) {
				$available_taxonomies = (array) $related_objects[ __( 'Taxonomies', 'pods' ) ];
			}

			foreach ( $available_taxonomies as $taxonomy => $label ) {
				$taxonomy = pods_str_replace( 'taxonomy-', '', $taxonomy, 1 );

				$field_name = 'built_in_taxonomies_' . $taxonomy;

				$options['connections']['post_type_built_in_taxonomies']['boolean_group'][ $field_name ] = [
					'name'  => $field_name,
					'label' => $label,
					'type'  => 'boolean',
				];

				if ( 'category' === $taxonomy || 'post_tag' === $taxonomy ) {
					$field_name = 'archive_show_in_taxonomies_' . $taxonomy;

					$options['connections']['archive_show_in_taxonomies']['boolean_group'][ $field_name ] = [
						'name'  => $field_name,
						'label' => $label,
						'type'  => 'boolean',
					];
				}
			}
		} elseif ( 'taxonomy' === $pod_type ) {
			$options['admin-ui'] = [
				'description'           => [
					'label'   => __( 'Taxonomy Description', 'pods' ),
					'help'    => __( 'A short descriptive summary of what the taxonomy is.', 'pods' ),
					'type'    => 'text',
					'default' => '',
				],
				'show_ui'               => [
					'label'             => __( 'Show Admin UI', 'pods' ),
					'help'              => __( 'Whether to generate a default UI for managing this taxonomy.', 'pods' ),
					'type'              => 'boolean',
					'default'           => pods_v( 'public', $pod, true ),
					'dependency'        => true,
					'boolean_yes_label' => '',
				],
				'show_in_menu'          => [
					'label'             => __( 'Show Admin Menu in Dashboard', 'pods' ),
					'help'              => __( 'Whether to show the taxonomy in the admin menu.', 'pods' ),
					'type'              => 'boolean',
					'default'           => pods_v( 'public', $pod, true ),
					'dependency'        => true,
					'depends-on'        => [ 'show_ui' => true ],
					'boolean_yes_label' => '',
				],
				'menu_name'             => [
					'label'      => __( 'Menu Name', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => [ 'show_ui' => true ],
				],
				'menu_location'         => [
					'label'                 => __( 'Menu Location', 'pods' ),
					'help'                  => __( 'help', 'pods' ),
					'type'                  => 'pick',
					'default'               => 'default',
					'depends-on'            => [ 'show_ui' => true ],
					'data'                  => [
						'default'     => __( 'Default - Add to associated Post Type(s) menus', 'pods' ),
						'settings'    => __( 'Add a submenu item to Settings menu', 'pods' ),
						'appearances' => __( 'Add a submenu item to Appearances menu', 'pods' ),
						'submenu'     => __( 'Add a submenu item to another menu', 'pods' ),
						'objects'     => __( 'Make a new menu item', 'pods' ),
						'top'         => __( 'Make a new menu item below Settings', 'pods' ),
					],
					'pick_format_single' => 'dropdown',
					'pick_show_select_text' => 0,
					'dependency'            => true,
				],
				'menu_location_custom'  => [
					'label'      => __( 'Custom Menu Location', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'depends-on' => [ 'menu_location' => 'submenu' ],
				],
				'menu_position'         => [
					'label'              => __( 'Menu Position', 'pods' ),
					'help'               => __( 'This will be the position of the menu item. See <a href="https://developer.wordpress.org/themes/functionality/administration-menus/#top-level-menus" target="_blank" rel="noopener noreferrer">WordPress.org Developer Docs</a> for more details about how positioning works.', 'pods' ),
					'type'               => 'number',
					'number_decimals'    => 2,
					'number_format'      => '9999.99',
					'number_format_soft' => 1,
					'default'            => 0,
					'depends-on'         => [ 'menu_location' => [ 'objects', 'top' ] ],
				],
				'menu_icon'             => [
					'label'      => __( 'Menu Icon URL', 'pods' ),
					'help'       => __( 'URL or Dashicon name for the menu icon. You may specify the path to the icon using one of the <a href="https://docs.pods.io/displaying-pods/magic-tags/special-magic-tags/#Site_Tags" target="_blank" rel="noopener noreferrer">site tag</a> type <a href="https://docs.pods.io/displaying-pods/magic-tags/special-magic-tags/" target="_blank" rel="noopener noreferrer">special magic tags</a>. For example, for a file in your theme directory, use "{@template-url}/path/to/image.png". You may also use the name of a <a href="https://developer.wordpress.org/resource/dashicons/" target="_blank" rel="noopener noreferrer">Dashicon</a>. For example, to use the empty star icon, use "dashicons-star-empty".', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => [ 'menu_location' => [ 'objects', 'top' ] ],
				],
				'show_in_nav_menus'     => [
					'label'             => __( 'Show in Navigation Menus', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => pods_v( 'public', $pod, true ),
					'boolean_yes_label' => '',
				],
				'show_tagcloud'         => [
					'label'             => __( 'Allow in Tag Cloud Widget', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => pods_v( 'show_ui', $pod, pods_v( 'public', $pod, true ) ),
					'boolean_yes_label' => '',
				],
				'show_in_quick_edit'    => [
					'label'             => __( 'Allow in quick/bulk edit panel', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => pods_v( 'show_ui', $pod, pods_v( 'public', $pod, true ) ),
					'boolean_yes_label' => '',
				],
			];

			$options['admin-ui']['show_admin_column'] = [
				'label'             => __( 'Show Taxonomy column on Post Types', 'pods' ),
				'help'              => __( 'Whether to add a column for this taxonomy on the associated post types manage screens', 'pods' ),
				'type'              => 'boolean',
				'default'           => false,
				'boolean_yes_label' => '',
			];

			// Integration for Single Value Taxonomy UI
			if ( function_exists( 'tax_single_value_meta_box' ) ) {
				$options['admin-ui']['single_value'] = [
					'label'             => __( 'Single Value Taxonomy', 'pods' ),
					'help'              => __( 'Use a drop-down for the input instead of the WordPress default', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'boolean_yes_label' => '',
				];

				$options['admin-ui']['single_value_required'] = [
					'label'             => __( 'Single Value Taxonomy - Required', 'pods' ),
					'help'              => __( 'A term will be selected by default in the Post Editor, not optional', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'boolean_yes_label' => '',
				];
			}

			$options['connections'] = [
				'taxonomy_associated_post_types' => [
					'name'          => 'taxonomy_associated_post_types',
					'label'         => __( 'Enable Connections to Post Types', 'pods' ),
					'help'          => __( 'You can enable the ability to select posts from these post types on any term for this taxonomy.', 'pods' ),
					'type'          => 'boolean_group',
					'boolean_group' => [],
				],
				'register_custom_post_type'      => [
					'name'         => 'register_custom_post_type',
					'label'        => __( 'Add new connection', 'pods' ),
					'type'         => 'html',
					'html_content' => sprintf(
						'<a href="%1$s">%2$s</a>',
						esc_url( admin_url( 'admin.php?page=pods-add-new&create_extend=create&type=post_type' ) ),
						esc_html__( 'Create a new Custom Post Type', 'pods' )
					),
				],
			];

			$options['advanced'] = [
				'public'                   => [
					'label'             => __( 'Public', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => '',
				],
				'hierarchical'             => [
					'label'             => __( 'Hierarchical', 'pods' ),
					'help'              => __( 'Hierarchical taxonomies will have a list with checkboxes to select an existing category in the taxonomy admin box on the post edit page (like default post categories). Non-hierarchical taxonomies will just have an empty text field to type-in taxonomy terms to associate with the post (like default post tags).', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'dependency'        => true,
					'boolean_yes_label' => '',
				],
				'rewrite'                  => [
					'label'             => __( 'Rewrite', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'dependency'        => true,
					'boolean_yes_label' => '',
				],
				'rewrite_custom_slug'      => [
					'label'         => __( 'Custom Rewrite Slug', 'pods' ),
					'help'          => __( 'help', 'pods' ),
					'type'          => 'text',
					'slug_fallback' => '-',
					'default'       => '',
					'depends-on'    => [ 'rewrite' => true ],
				],
				'rewrite_with_front'       => [
					'label'             => __( 'Rewrite with Front', 'pods' ),
					'help'              => __( 'Allows permalinks to be prepended with your front base (example: if your permalink structure is /blog/, then your links will be: Unchecked->/news/, Checked->/blog/news/)', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => '',
					'depends-on'        => [ 'rewrite' => true ],
				],
				'rewrite_hierarchical'     => [
					'label'             => __( 'Hierarchical Permalinks', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => true,
					'boolean_yes_label' => '',
					'depends-on'        => [ 'rewrite' => true ],
				],
				'capability_type'          => [
					'label'                 => __( 'User Capability', 'pods' ),
					'help'                  => __( 'Uses WordPress term capabilities by default', 'pods' ),
					'type'                  => 'pick',
					'default'               => 'default',
					'data'                  => [
						'default' => 'Default',
						'custom'  => __( 'Custom Capability', 'pods' ),
					],
					'pick_format_single' => 'dropdown',
					'pick_show_select_text' => 0,
					'dependency'            => true,
				],
				'capability_type_custom'   => [
					'label'      => __( 'Custom User Capability', 'pods' ),
					'help'       => __( 'Enables additional capabilities for this Taxonomy including: manage_{capability}_terms, edit_{capability}_terms, assign_{capability}_terms, and delete_{capability}_terms', 'pods' ),
					'type'       => 'text',
					'default'    => pods_v( 'name', $pod ),
					'depends-on' => [ 'capability_type' => 'custom' ],
				],
				'query_var'                => [
					'label'             => __( 'Query Var', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'boolean_yes_label' => '',
				],
				'query_var'                => [
					'label'             => __( 'Query Var', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'dependency'        => true,
					'boolean_yes_label' => '',
				],
				'query_var_string'         => [
					'label'      => __( 'Custom Query Var Name', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => [ 'query_var' => true ],
				],
				'sort'                     => [
					'label'             => __( 'Remember order saved on Post Types', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'boolean_yes_label' => '',
				],
				'update_count_callback'    => [
					'label'   => __( 'Function to call when updating counts', 'pods' ),
					'help'    => __( 'help', 'pods' ),
					'type'    => 'text',
					'default' => '',
				],
				'default_term_name'        => [
					'label'      => __( 'Default term name', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'dependency' => true,
				],
				'default_term_slug'        => [
					'label'       => __( 'Default term slug', 'pods' ),
					'type'        => 'text',
					'default'     => '',
					'excludes-on' => [ 'default_term_name' => '' ],
				],
				'default_term_description' => [
					'label'       => __( 'Default term description', 'pods' ),
					'type'        => 'wysiwyg',
					'default'     => '',
					'excludes-on' => [ 'default_term_name' => '' ],
				],
			];

			$related_objects = PodsForm::field_method( 'pick', 'related_objects', true );

			$available_post_types = [];

			if ( ! empty( $related_objects[ __( 'Post Types', 'pods' ) ] ) ) {
				$available_post_types = (array) $related_objects[ __( 'Post Types', 'pods' ) ];
			}

			foreach ( $available_post_types as $post_type => $label ) {
				$post_type = pods_str_replace( 'post_type-', '', $post_type, 1 );

				$field_name = 'built_in_post_types_' . $post_type;

				$options['connections']['taxonomy_associated_post_types']['boolean_group'][ $field_name ] = [
					'name'  => $field_name,
					'label' => $label,
					'type'  => 'boolean',
				];
			}

			$options['connections']['taxonomy_associated_post_types']['boolean_group']['built_in_post_types_attachment'] = [
				'name'  => 'built_in_post_types_attachment',
				'label' => __( 'Media', 'pods' ) . ' (attachment)',
				'type'  => 'boolean',
			];
		} elseif ( 'settings' === $pod_type ) {
			$options['admin-ui'] = [
				'ui_style'             => [
					'label'      => __( 'Admin UI Style', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'pick',
					'default'    => 'settings',
					'data'       => [
						'settings'  => __( 'Normal Settings Form', 'pods' ),
						'post_type' => __( 'Classic Editor (Looks like the Classic Editor for Posts UI)', 'pods' ),
						'custom'    => __( 'Custom (hook into pods_admin_ui_custom or pods_admin_ui_custom_{podname} action)', 'pods' ),
					],
					'pick_format_single' => 'dropdown',
					'dependency' => true,
				],
				'menu_location'        => [
					'label'      => __( 'Menu Location', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'pick',
					'default'    => 'settings',
					'data'       => [
						'settings'    => __( 'Add a submenu item to Settings menu', 'pods' ),
						'appearances' => __( 'Add a submenu item to Appearances menu', 'pods' ),
						'submenu'     => __( 'Add a submenu item to another menu', 'pods' ),
						'top'         => __( 'Make a new menu item below Settings', 'pods' ),
					],
					'pick_format_single' => 'dropdown',
					'dependency' => true,
				],
				'menu_location_custom' => [
					'label'      => __( 'Custom Menu Location', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'depends-on' => [ 'menu_location' => 'submenu' ],
				],
				'menu_position'        => [
					'label'              => __( 'Menu Position', 'pods' ),
					'help'               => __( 'This will be the position of the menu item. See <a href="https://developer.wordpress.org/themes/functionality/administration-menus/#top-level-menus" target="_blank" rel="noopener noreferrer">WordPress.org Developer Docs</a> for more details about how positioning works.', 'pods' ),
					'type'               => 'number',
					'number_decimals'    => 2,
					'number_format'      => '9999.99',
					'number_format_soft' => 1,
					'default'            => 0,
					'depends-on'         => [ 'menu_location' => 'top' ],
				],
				'menu_icon'            => [
					'label'      => __( 'Menu Icon URL', 'pods' ),
					'help'       => __( 'URL or Dashicon name for the menu icon. You may specify the path to the icon using one of the <a href="https://docs.pods.io/displaying-pods/magic-tags/special-magic-tags/#Site_Tags" target="_blank" rel="noopener noreferrer">site tag</a> type <a href="https://docs.pods.io/displaying-pods/magic-tags/special-magic-tags/" target="_blank" rel="noopener noreferrer">special magic tags</a>. For example, for a file in your theme directory, use "{@template-url}/path/to/image.png". You may also use the name of a <a href="https://developer.wordpress.org/resource/dashicons/" target="_blank" rel="noopener noreferrer">Dashicon</a>. For example, to use the empty star icon, use "dashicons-star-empty".', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => [ 'menu_location' => 'top' ],
				],
			];

			// @todo fill this in
			$options['advanced'] = [
				'temporary' => 'This type has the fields hardcoded',
				// :(
			];
		} elseif ( 'pod' === $pod_type ) {
			$actions_enabled = [
				'add',
				'edit',
				'duplicate',
				'delete',
			];

			if ( 1 === (int) pods_v( 'ui_export', $pod ) ) {
				$actions_enabled = [
					'add',
					'edit',
					'duplicate',
					'delete',
					'export',
				];
			}

			$options['admin-ui'] = [
				'ui_style'             => [
					'label'      => __( 'Admin UI Style', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'pick',
					'default'    => 'post_type',
					'data'       => [
						'post_type' => __( 'Classic Editor (Looks like the Classic Editor for Posts UI)', 'pods' ),
						'custom'    => __( 'Custom (hook into pods_admin_ui_custom or pods_admin_ui_custom_{podname} action)', 'pods' ),
					],
					'pick_format_single' => 'dropdown',
					'dependency' => true,
				],
				'show_in_menu'         => [
					'label'             => __( 'Show Admin Menu in Dashboard', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'boolean_yes_label' => '',
					'dependency'        => true,
				],
				'use_submenu_fallback' => [
					'label'             => __( 'Fallback Edit in Dashboard', 'pods' ),
					'help'              => __( 'help', 'pods' ),
					'type'              => 'boolean',
					'default'           => false,
					'boolean_yes_label' => __( 'Use the fallback generic "Pods" content menu so content can be managed', 'pods' ),
					'depends-on'        => [
						'show_in_menu'    => false,
					],
				],
				'menu_location_custom' => [
					'label'      => __( 'Parent Menu ID (optional)', 'pods' ),
					'help'       => __( 'help', 'pods' ),
					'type'       => 'text',
					'depends-on' => [ 'show_in_menu' => true ],
				],
				'menu_position'        => [
					'label'              => __( 'Menu Position', 'pods' ),
					'help'               => __( 'This will be the position of the menu item. See <a href="https://developer.wordpress.org/themes/functionality/administration-menus/#top-level-menus" target="_blank" rel="noopener noreferrer">WordPress.org Developer Docs</a> for more details about how positioning works.', 'pods' ),
					'type'               => 'number',
					'number_decimals'    => 2,
					'number_format'      => '9999.99',
					'number_format_soft' => 1,
					'default'            => 0,
					'depends-on'         => [ 'show_in_menu' => true ],
				],
				'menu_icon'            => [
					'label'      => __( 'Menu Icon URL', 'pods' ),
					'help'       => __( 'URL or Dashicon name for the menu icon. You may specify the path to the icon using one of the <a href="https://docs.pods.io/displaying-pods/magic-tags/special-magic-tags/#Site_Tags" target="_blank" rel="noopener noreferrer">site tag</a> type <a href="https://docs.pods.io/displaying-pods/magic-tags/special-magic-tags/" target="_blank" rel="noopener noreferrer">special magic tags</a>. For example, for a file in your theme directory, use "{@template-url}/path/to/image.png". You may also use the name of a <a href="https://developer.wordpress.org/resource/dashicons/" target="_blank" rel="noopener noreferrer">Dashicon</a>. For example, to use the empty star icon, use "dashicons-star-empty".', 'pods' ),
					'type'       => 'text',
					'default'    => '',
					'depends-on' => [ 'show_in_menu' => true ],
				],
				'ui_icon'              => [
					'label'           => __( 'Header Icon', 'pods' ),
					'help'            => __( 'This is the icon shown to the left of the heading text at the top of the manage pages for this content type.', 'pods' ),
					'type'            => 'file',
					'default'         => '',
					'file_edit_title' => 0,
					'depends-on'      => [ 'show_in_menu' => true ],
				],
				'ui_actions_enabled'   => [
					'label'            => __( 'Actions Available', 'pods' ),
					'help'             => __( 'help', 'pods' ),
					'type'             => 'pick',
					'default'          => $actions_enabled,
					'data'             => [
						'add'       => __( 'Add New', 'pods' ),
						'edit'      => __( 'Edit', 'pods' ),
						'duplicate' => __( 'Duplicate', 'pods' ),
						'delete'    => __( 'Delete', 'pods' ),
						'reorder'   => __( 'Reorder', 'pods' ),
						'export'    => __( 'Export', 'pods' ),
					],
					'pick_format_type' => 'multi',
					'dependency'       => true,
				],
				'ui_reorder_field'     => [
					'label'      => __( 'Reorder Field', 'pods' ),
					'help'       => __( 'This is the field that will be reordered on, it should be numeric.', 'pods' ),
					'type'       => 'text',
					'default'    => 'menu_order',
					'depends-on' => [ 'ui_actions_enabled' => 'reorder' ],
				],
				'ui_fields_manage'     => [
					'label'            => __( 'Admin Table Columns', 'pods' ),
					'help'             => __( 'help', 'pods' ),
					'type'             => 'pick',
					'default'          => [],
					'data'             => [],
					'pick_format_type' => 'multi',
				],
				'ui_filters'           => [
					'label'            => __( 'Search Filters', 'pods' ),
					'help'             => __( 'help', 'pods' ),
					'type'             => 'pick',
					'default'          => [],
					'data'             => [],
					'pick_format_type' => 'multi',
				],
			];

			if ( ! empty( $pod['fields'] ) ) {
				if ( isset( $pod['fields'][ pods_v( 'pod_index', $pod, 'name' ) ] ) ) {
					$options['admin-ui']['ui_fields_manage']['default'][] = pods_v( 'pod_index', $pod, 'name' );
				}

				if ( isset( $pod['fields']['modified'] ) ) {
					$options['admin-ui']['ui_fields_manage']['default'][] = 'modified';
				}

				foreach ( $pod['fields'] as $field ) {
					$type = '';

					if ( isset( $field_types[ $field['type'] ] ) ) {
						$type = ' <small>(' . $field_types[ $field['type'] ]['label'] . ')</small>';
					}

					$options['admin-ui']['ui_fields_manage']['data'][ $field['name'] ] = $field['label'] . $type;
					$options['admin-ui']['ui_filters']['data'][ $field['name'] ]       = $field['label'] . $type;
				}

				$options['admin-ui']['ui_fields_manage']['data']['id'] = 'ID';
			} else {
				unset( $options['admin-ui']['ui_fields_manage'] );
				unset( $options['admin-ui']['ui_filters'] );
			}//end if

			$index_fields = [
				'id' => 'ID',
			];

			$hierarchical_fields = [];

			foreach ( $pod['fields'] as $field ) {
				if ( ! in_array( $field['type'], $tableless_field_types, true ) ) {
					$index_fields[ $field['name'] ] = $field['label'];
				}

				if ( 'pick' == $field['type'] && 'pod' === pods_v( 'pick_object', $field ) && $pod['name'] === pods_v( 'pick_val', $field ) && 'single' === pods_v( 'pick_format_type', $field ) ) {
					$hierarchical_fields[ $field['name'] ] = $field['label'];
				}
			}

			// Set empty message if none found.
			if ( empty( $hierarchical_fields ) ) {
				$hierarchical_fields = [
					'' => __( 'No Hierarchical Fields found', 'pods' ),
				];
			}

			$options['advanced'] = [
				'detail_url'   => [
					'label'            => __( 'Detail Page URL', 'pods' ),
					'help'             => __( 'This is the path relative to your WordPress site URL so you can call {@detail_url} to automatically link to it. Enter something like "my-pod-page/{@permalink}/" to automatically link to "https://mysite.com/my-pod-page/my-pod-item/".', 'pods' ),
					'type'             => 'text',
					'text_placeholder' => 'my-pod-page/{@permalink}/',
				],
				'pod_index'    => [
					'label'              => __( 'Title Field', 'pods' ),
					'help'               => __( 'If you delete the "name" field, we need to specify the field to use as your primary title field. This field will serve as an index of your content. Most commonly this field represents the name of a person, place, thing, or a summary field.', 'pods' ),
					'default'            => 'name',
					'type'               => 'pick',
					'data'               => $index_fields,
					'pick_format_single' => 'autocomplete',
				],
				'hierarchical' => [
					'label'             => __( 'Hierarchical', 'pods' ),
					'help'              => __( 'You can enable automatic hierarchical parent / child handling which shows a special built-in interface for data entry.', 'pods' ),
					'default'           => 0,
					'type'              => 'boolean',
					'dependency'        => true,
					'boolean_yes_label' => '',
				],
				'pod_parent'   => [
					'label'              => __( 'Hierarchical Parent Field', 'pods' ),
					'help'               => __( 'You can enable automatic hierarchical parent / child handling which shows a special built-in interface for data entry.', 'pods' ),
					'default'            => 'parent',
					'type'               => 'pick',
					'data'               => $hierarchical_fields,
					'depends-on'         => [
						'hierarchical' => true,
					],
					'pick_format_single' => 'autocomplete',
				],
			];
		}//end if

		// Only include kitchen sink if dev mode on and not running Codecept tests.
		if ( pods_developer() && ! function_exists( 'codecept_debug' ) ) {
			$options['kitchen-sink'] = json_decode( file_get_contents( PODS_DIR . 'tests/codeception/_data/kitchen-sink-config.json' ), true );
		}

		$pod_type = $pod['type'];
		$pod_name = $pod['name'];

		/**
		 * Add admin fields to the Pods editor for a specific Pod.
		 *
		 * @param array             $options The Options fields.
		 * @param \Pods\Whatsit\Pod $pod     Current Pods object.
		 * @param array             $tabs    List of registered tabs.
		 */
		$options = apply_filters( "pods_admin_setup_edit_options_{$pod_type}_{$pod_name}", $options, $pod, $tabs );

		/**
		 * Add admin fields to the Pods editor for any Pod of a specific content type.
		 *
		 * @param array             $options The Options fields.
		 * @param \Pods\Whatsit\Pod $pod     Current Pods object.
		 * @param array             $tabs    List of registered tabs.
		 */
		$options = apply_filters( "pods_admin_setup_edit_options_{$pod_type}", $options, $pod, $tabs );

		/**
		 * Add admin fields to the Pods editor for all Pods.
		 *
		 * @param array             $options The Options fields.
		 * @param \Pods\Whatsit\Pod $pod     Current Pods object.
		 * @param array             $tabs    List of registered tabs.
		 */
		$options = apply_filters( 'pods_admin_setup_edit_options', $options, $pod, $tabs );

		return $options;
	}
}
