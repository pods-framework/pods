<?php

namespace Pods\Integrations\WPGraphQL;

/**
 * Settings specific functionality.
 *
 * @since 2.9.0
 */
class Settings {

	/**
	 * Add the class hooks.
	 *
	 * @since 2.9.0
	 */
	public function hook() {
		$supported_types = [
			'post_type',
			'taxonomy',
			'user',
			'media',
			'comment',
			/*'settings',
			'pod',*/
		];

		foreach ( $supported_types as $supported_type ) {
			add_filter( "pods_admin_setup_edit_tabs_{$supported_type}", [ $this, 'add_pod_tabs' ], 10, 2 );
			add_filter( "pods_admin_setup_edit_options_{$supported_type}", [ $this, 'add_pod_options' ], 10, 2 );
			add_filter( "pods_admin_setup_edit_field_tabs_{$supported_type}", [ $this, 'add_pod_tabs' ], 10, 2 );
			add_filter( "pods_admin_setup_edit_field_options_{$supported_type}", [
				$this,
				'add_field_options',
			], 10, 2 );
		}
	}

	/**
	 * Remove the class hooks.
	 *
	 * @since 2.9.0
	 */
	public function unhook() {
		$supported_types = [
			'post_type',
			'taxonomy',
			'user',
			'media',
			'comment',
			'setting',
			'pod',
		];

		foreach ( $supported_types as $supported_type ) {
			remove_filter( "pods_admin_setup_edit_tabs_{$supported_type}", [ $this, 'add_pod_tabs' ] );
			remove_filter( "pods_admin_setup_edit_options_{$supported_type}", [ $this, 'add_pod_options' ] );
			remove_filter( "pods_admin_setup_edit_field_tabs_{$supported_type}", [ $this, 'add_pod_tabs' ] );
			remove_filter( "pods_admin_setup_edit_field_options_{$supported_type}", [ $this, 'add_field_options' ] );
		}
	}

	/**
	 * Add the custom pod tab.
	 *
	 * @param array $tabs List of tabs.
	 * @param array $pod  The pod object.
	 *
	 * @return array List of tabs.
	 */
	public function add_pod_tabs( $tabs, $pod ) {
		$tabs['wpgraphql'] = __( 'WPGraphQL', 'pods' );

		return $tabs;
	}

	/**
	 * Add custom pod options.
	 *
	 * @since 2.9.0
	 *
	 * @param array $options List of options.
	 * @param array $pod     The pod object.
	 *
	 * @return array List of options.
	 */
	public function add_pod_options( $options, $pod ) {
		$options['wpgraphql'] = [
			'wpgraphql_enabled'            => [
				'label'             => __( 'Enable support in WPGraphQL', 'pods' ),
				'help'              => __( 'This will enable WPGraphQL requests for this content type.', 'pods' ),
				'default'           => filter_var( pods_v( 'pods_pro_wpgraphql_enabled', $pod, false ), FILTER_VALIDATE_BOOLEAN ),
				'type'              => 'boolean',
				'boolean_yes_label' => '',
			],
			'wpgraphql_singular_name'      => [
				'label'            => __( 'Custom WPGraphQL singular name', 'pods' ),
				'help'             => __( 'This will default to the singular label if you leave it blank. You can customize it if you wish to query against it completely differently through WPGraphQL. Whatever you enter here will be normalized to a lowercase, alphanumeric string. It must not start with a number, otherwise it will not show up.', 'pods' ),
				'default'           => pods_v( 'pods_pro_wpgraphql_singular_name', $pod, '' ),
				'type'             => 'slug',
				'slug_placeholder' => __( '(Default: Singular label)', 'pods' ),
				'depends-on'       => [
					'wpgraphql_enabled' => true,
				],
			],
			'wpgraphql_plural_name'        => [
				'label'            => __( 'Custom WPGraphQL plural name', 'pods' ),
				'help'             => __( 'This will default to the plural label with an "s" added to it at the end if you leave it blank. You can customize it if you wish to query against it completely differently through WPGraphQL. Whatever you enter here will be normalized to a lowercase, alphanumeric string. It must not start with a number, otherwise it will not show up.', 'pods' ),
				'default'           => pods_v( 'pods_pro_wpgraphql_plural_name', $pod, '' ),
				'type'             => 'slug',
				'slug_placeholder' => __( '(Default: Plural label)', 'pods' ),
				'depends-on'       => [
					'wpgraphql_enabled' => true,
				],
			],
			'wpgraphql_all_fields_enabled' => [
				'label'             => __( 'Show All fields (read-only)', 'pods' ),
				'help'              => __( 'This will override any off/on settings per field and allow all fields to be shown.', 'pods' ),
				'default'           => filter_var( pods_v( 'pods_pro_wpgraphql_all_fields_enabled', $pod, false ), FILTER_VALIDATE_BOOLEAN ),
				'type'              => 'boolean',
				'boolean_yes_label' => '',
				'depends-on'        => [
					'wpgraphql_enabled' => true,
				],
			],
			'wpgraphql_pick_format'        => [
				'label'             => __( 'Relationship format', 'pods' ),
				'help'              => __( 'This will override any relationship format settings per field. Connections will allow linking to resources on WPGraphQL.', 'pods' ),
				'default'           => pods_v( 'pods_pro_wpgraphql_pick_format', $pod, 'connection' ),
				'data'              => [
					'inherit'    => __( 'Use per-field setting', 'pods' ),
					'connection' => __( 'Connection', 'pods' ),
					'id'         => __( 'ID only', 'pods' ),
					'title'      => __( 'Title only', 'pods' ),
					'view-url'   => __( 'View URL only', 'pods' ),
				],
				'type'              => 'pick',
				'pick_format_single' => 'dropdown',
				'depends-on'        => [
					'wpgraphql_enabled' => true,
				],
			],
			'wpgraphql_file_format'        => [
				'label'             => __( 'File format', 'pods' ),
				'help'              => __( 'This will override any file format settings per field. Connections will allow linking to resources on WPGraphQL.', 'pods' ),
				'default'           => pods_v( 'pods_pro_wpgraphql_file_format', $pod, 'connection' ),
				'data'              => [
					'inherit'    => __( 'Use per-field setting', 'pods' ),
					'connection' => __( 'Connection', 'pods' ),
					'id'         => __( 'ID only', 'pods' ),
					'asset-url'  => __( 'Asset URL only', 'pods' ),
				],
				'type'              => 'pick',
				'pick_format_single' => 'dropdown',
				'depends-on'        => [
					'wpgraphql_enabled' => true,
				],
			],
		];

		return $options;
	}

	/**
	 * Add custom pod field options.
	 *
	 * @since 2.9.0
	 *
	 * @param array $options List of options.
	 * @param array $pod     The pod object.
	 *
	 * @return array List of options.
	 */
	public function add_field_options( $options, $pod ) {
		$options['wpgraphql'] = [
			'wpgraphql_enabled'       => [
				'label'             => __( 'Show this field in WPGraphQL (read-only)', 'pods' ),
				'help'              => __( 'This will enable WPGraphQL requests for this field.', 'pods' ),
				'default'           => false,
				'type'              => 'boolean',
				'boolean_yes_label' => '',
			],
			'wpgraphql_singular_name' => [
				'label'            => __( 'Custom WPGraphQL singular name', 'pods' ),
				'help'             => __( 'This will default to the field name if you leave it blank. You can customize it if you wish to query against it completely differently through WPGraphQL. Whatever you enter here will be normalized to a lowercase, alphanumeric string. It must not start with a number, otherwise it will not show up.', 'pods' ),
				'type'             => 'slug',
				'slug_placeholder' => __( '(Default: Field name)', 'pods' ),
				'depends-on'       => [
					'wpgraphql_enabled' => true,
				],
			],
			'wpgraphql_plural_name'   => [
				'label'            => __( 'Custom WPGraphQL plural name', 'pods' ),
				'help'             => __( 'This will default to the field name with an "s" added to it at the end if you leave it blank. You can customize it if you wish to query against it completely differently through WPGraphQL. Whatever you enter here will be normalized to a lowercase, alphanumeric string. It must not start with a number, otherwise it will not show up.', 'pods' ),
				'type'             => 'slug',
				'slug_placeholder' => __( '(Default: Field name with an "s" added)', 'pods' ),
				'depends-on'       => [
					'wpgraphql_enabled' => true,
				],
			],
			'wpgraphql_pick_format'   => [
				'label'             => __( 'Relationship format', 'pods' ),
				'help'              => __( 'Connections will allow linking to resources on WPGraphQL.', 'pods' ),
				'default'           => 'connection',
				'data'              => [
					'connection' => __( 'Connection', 'pods' ),
					'id'         => __( 'ID only', 'pods' ),
					'title'      => __( 'Title only', 'pods' ),
					'view-url'   => __( 'View URL only', 'pods' ),
				],
				'type'              => 'pick',
				'pick_format_single' => 'dropdown',
				'depends-on'        => [
					'type'              => 'pick',
					'wpgraphql_enabled' => true,
				],
				'wildcard-on'       => [
					'pick_object' => [
						'^post_type-.*$',
						'^taxonomy-.*$',
						'^pod-.*$',
						'^user$',
						'^role$',
						'^media$',
						'^comment$',
						'^nav_menu$',
						'^theme$',
						'^plugin$',
						'^post-types$',
						'^taxonomies$',
					],
				],
			],
			'wpgraphql_file_format'   => [
				'label'             => __( 'File format', 'pods' ),
				'help'              => __( 'Connections will allow linking to resources on WPGraphQL.', 'pods' ),
				'default'           => 'connection',
				'data'              => [
					'connection' => __( 'Connection', 'pods' ),
					'id'         => __( 'ID only', 'pods' ),
					'asset-url'  => __( 'Asset URL only', 'pods' ),
				],
				'type'              => 'pick',
				'pick_format_single' => 'dropdown',
				'depends-on'        => [
					'type'              => 'file',
					'wpgraphql_enabled' => true,
				],
			],
		];

		return $options;
	}

}
