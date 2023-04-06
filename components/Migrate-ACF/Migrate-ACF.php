<?php
/**
 * Name: Migrate: Import from the Advanced Custom Fields plugin
 *
 * Menu Name: Migrate ACF
 *
 * Description: Import Custom Post Types and Taxonomies from the Advanced Custom Fields (<a href="https://wordpress.org/plugins/advanced-custom-fields/">https://wordpress.org/plugins/advanced-custom-fields/</a>). This does NOT migrate custom fields or field groups from ACF.
 *
 * Category: Migration
 *
 * Version: 1.0
 *
 * Plugin: pods-migrate-acf/pods-migrate-acf.php
 *
 * @package    Pods\Components
 * @subpackage Migrate-ACF
 */

if ( class_exists( 'Pods_Migrate_ACF' ) ) {
	return;
}

/**
 * Class Pods_Migrate_ACF
 */
class Pods_Migrate_ACF extends PodsComponent {

	/** @var array
	 *
	 *  Support option names for multiple versions, list from newest to oldest
	 */
	private $post_option_name_list = [
		'cptui_post_types',
		'cpt_custom_post_types',
	];

	/** @var array
	 *
	 *  Support option names for multiple versions, list from newest to oldest
	 */
	private $taxonomy_option_name_list = [
		'cptui_taxonomies',
		'cpt_custom_tax_types',
	];

	private $api = null;

	private $post_option_name = null;

	private $taxonomy_option_name = null;

	private $post_types = null;

	private $taxonomies = null;

	/**
	 * {@inheritdoc}
	 */
	public function init() {
		// Nothing to do here.
	}

	/**
	 * Init the data from ACF only when needed.
	 */
	public function init_data() {
		if ( null !== $this->post_types ) {
			return;
		}

		$this->post_types = function_exists( 'acf_get_acf_post_types' ) ? acf_get_acf_post_types() : [];
		$this->taxonomies = function_exists( 'acf_get_acf_taxonomies' ) ? acf_get_acf_taxonomies() : [];
	}

	/**
	 * Enqueue styles
	 *
	 * @since 2.0.0
	 */
	public function admin_assets() {
		wp_enqueue_style( 'pods-wizard' );
	}

	/**
	 * Show the Admin
	 *
	 * @param $options
	 * @param $component
	 */
	public function admin( $options, $component ) {
		$this->init_data();

		$post_types = $this->post_types;
		$taxonomies = $this->taxonomies;

		$method = 'migrate';

		// ajax_migrate
		pods_view( PODS_DIR . 'components/Migrate-ACF/ui/wizard.php', compact( array_keys( get_defined_vars() ) ) );
	}

	/**
	 * Handle the Migration AJAX
	 *
	 * @param $params
	 */
	public function ajax_migrate( $params ) {
		$this->init_data();

		$post_types = (array) $this->post_types;
		$taxonomies = (array) $this->taxonomies;

		$migrate_post_types = [];

		if ( isset( $params->post_type ) && ! empty( $params->post_type ) ) {
			foreach ( $params->post_type as $post_type => $checked ) {
				if ( true === (boolean) $checked ) {
					$migrate_post_types[] = $post_type;
				}
			}
		}

		$migrate_taxonomies = [];

		if ( isset( $params->taxonomy ) && ! empty( $params->taxonomy ) ) {
			foreach ( $params->taxonomy as $taxonomy => $checked ) {
				if ( true === (boolean) $checked ) {
					$migrate_taxonomies[] = $taxonomy;
				}
			}
		}

		foreach ( $post_types as $k => $post_type ) {
			if ( ! in_array( pods_v( 'post_type', $post_type ), $migrate_post_types, true ) ) {
				continue;
			}

			$id = $this->migrate_post_type( $post_type );

			if ( 0 < $id ) {
				unset( $post_types[ $k ] );
			}
		}

		foreach ( $taxonomies as $k => $taxonomy ) {
			if ( ! in_array( pods_v( 'taxonomy', $taxonomy ), $migrate_taxonomies, true ) ) {
				continue;
			}

			$id = $this->migrate_taxonomy( $taxonomy );

			if ( 0 < $id ) {
				unset( $taxonomies[ $k ] );
			}
		}

		if ( 1 === (int) pods_v( 'cleanup', $params, 0 ) ) {
			foreach ( $migrate_post_types as $post_type ) {
				acf_delete_post_type( $post_type );
			}

			foreach ( $migrate_taxonomies as $taxonomy ) {
				acf_delete_taxonomy( $taxonomy );
			}
		}//end if
	}

	/**
	 *
	 *
	 * @since 2.0.0
	 *
	 * @param $post_type
	 *
	 * @return bool|int|mixed
	 */
	private function migrate_post_type( $post_type ) {
		$supports = [];
		$custom_supports = [];

		if ( isset( $post_type['supports'] ) && is_array( $post_type['supports'] ) ) {
			$supports = $post_type['supports'];

			$core_supports = [
				'title' => true,
				'editor' => true,
				'excerpt' => true,
				'trackbacks' => true,
				'custom-fields' => true,
				'comments' => true,
				'revisions' => true,
				'thumbnail' => true,
				'author' => true,
				'page-attributes' => true,
				'post-formats' => true,
			];

			foreach ( $supports as $support ) {
				// Skip support options that are WP core related.
				if ( isset( $core_supports[ $support ] ) ) {
					continue;
				}

				// Add to the custom supports.
				$custom_supports[] = $support;
			}
		}

		$taxonomies = [];

		if ( isset( $post_type['taxonomies'] ) && is_array( $post_type['taxonomies'] ) ) {
			$taxonomies = $post_type['taxonomies'];
		}

		$labels = [];

		if ( isset( $post_type['labels'] ) && is_array( $post_type['labels'] ) ) {
			$labels = $post_type['labels'];
		}

		$capability_type = [];

		if ( ! empty( $post_type['singular_capability_name'] ) ) {
			$capability_type[] = $post_type['singular_capability_name'];

			if ( ! empty( $post_type['plural_capability_name'] ) ) {
				$capability_type[] = $post_type['plural_capability_name'];
			}
		}

		$params = [
			'type'                           => 'post_type',
			'storage'                        => 'meta',
			'object'                         => '',
			'name'                           => pods_v( 'post_type', $post_type ),
			'label'                          => pods_v( 'title', $post_type ),
			'label_singular'                 => pods_v( 'singular_name', $labels ),
			'description'                    => pods_v( 'description', $post_type ),

			// Supports arguments.
			'supports_title'                 => in_array( 'title', $supports, true ),
			'supports_editor'                => in_array( 'editor', $supports, true ),
			'supports_excerpt'               => in_array( 'excerpt', $supports, true ),
			'supports_trackbacks'            => in_array( 'trackbacks', $supports, true ),
			'supports_custom_fields'         => in_array( 'custom-fields', $supports, true ),
			'supports_comments'              => in_array( 'comments', $supports, true ),
			'supports_revisions'             => in_array( 'revisions', $supports, true ),
			'supports_thumbnail'             => in_array( 'thumbnail', $supports, true ),
			'supports_author'                => in_array( 'author', $supports, true ),
			'supports_page_attributes'       => in_array( 'page-attributes', $supports, true ),
			'supports_post_formats'          => in_array( 'post-formats', $supports, true ),
			'supports_custom'                => implode( ',', $custom_supports ),

			// Custom labels.
			'menu_name'                      => pods_v( 'menu_name', $labels ),
			'label_all_items'                => pods_v( 'all_items', $labels ),
			'label_add_new'                  => pods_v( 'add_new', $labels ),
			'label_add_new_item'             => pods_v( 'add_new_item', $labels ),
			'label_edit_item'                => pods_v( 'edit_item', $labels ),
			'label_new_item'                 => pods_v( 'new_item', $labels ),
			'label_view_item'                => pods_v( 'view_item', $labels ),
			'label_view_items'               => pods_v( 'view_items', $labels ),
			'label_search_items'             => pods_v( 'search_items', $labels ),
			'label_not_found'                => pods_v( 'not_found', $labels ),
			'label_not_found_in_trash'       => pods_v( 'not_found_in_trash', $labels ),
			'label_parent'                   => pods_v( 'parent', $labels ),
			'label_featured_image'           => pods_v( 'featured_image', $labels ),
			'label_set_featured_image'       => pods_v( 'set_featured_image', $labels ),
			'label_remove_featured_image'    => pods_v( 'remove_featured_image', $labels ),
			'label_use_featured_image'       => pods_v( 'use_featured_image', $labels ),
			'label_archives'                 => pods_v( 'archives', $labels ),
			'label_insert_into_item'         => pods_v( 'insert_into_item', $labels ),
			'label_uploaded_to_this_item'    => pods_v( 'uploaded_to_this_item', $labels ),
			'label_filter_items_list'        => pods_v( 'filter_items_list', $labels ),
			'label_items_list_navigation'    => pods_v( 'items_list_navigation', $labels ),
			'label_items_list'               => pods_v( 'items_list', $labels ),
			'label_attributes'               => pods_v( 'attributes', $labels ),
			'label_name_admin_bar'           => pods_v( 'name_admin_bar', $labels ),
			'label_item_published'           => pods_v( 'item_published', $labels ),
			'label_item_published_privately' => pods_v( 'item_published_privately', $labels ),
			'label_item_reverted_to_draft'   => pods_v( 'item_reverted_to_draft', $labels ),
			'label_item_scheduled'           => pods_v( 'item_scheduled', $labels ),
			'label_item_updated'             => pods_v( 'item_updated', $labels ),
			'label_parent_item_colon'        => pods_v( 'parent_item_colon', $labels ),

			// Custom enter title here functionality.
			'placeholder_enter_title_here'   => pods_v( 'enter_title_here', $labels ),

			// Other settings.
			'public'                         => (int) pods_v( 'public', $post_type ),
			'publicly_queryable'             => (int) pods_v( 'publicly_queryable', $post_type ),
			'show_ui'                        => (int) pods_v( 'show_ui', $post_type ),
			'show_in_nav_menus'              => (int) pods_v( 'show_in_nav_menus', $post_type ),
			'delete_with_user'               => (int) pods_v( 'delete_with_user', $post_type ),
			'show_in_rest'                   => (int) pods_v( 'show_in_rest', $post_type ),
			'rest_base'                      => pods_v( 'rest_base', $post_type ),
			'rest_controller_class'          => pods_v( 'rest_controller_class', $post_type ), // Not currently used.
			'rest_namespace'                 => pods_v( 'rest_namespace', $post_type ),
			'has_archive'                    => (int) pods_v( 'has_archive', $post_type ),
			'has_archive_string'             => pods_v( 'has_archive_slug', $post_type ),
			'exclude_from_search'            => (int) pods_v( 'exclude_from_search', $post_type ),
			'capability_type'                => implode( ',', $capability_type ),
			'hierarchical'                   => (int) pods_v( 'hierarchical', $post_type ),
			'can_export'                     => (int) pods_v( 'can_export', $post_type ),
			'rewrite'                        => (int) ( 'no_permalink' !== pods_v( 'permalink_rewrite', pods_v( 'rewrite', $post_type ) ) ),
			'rewrite_custom_slug'            => pods_v( 'slug', pods_v( 'rewrite', $post_type ) ),
			'rewrite_with_front'             => (int) pods_v( 'with_front', pods_v( 'rewrite', $post_type ) ),
			'query_var'                      => (int) ( 'none' !== pods_v( 'query_var', $post_type ) ),
			'query_var_string'               => pods_v( 'query_var_name', $post_type ),
			'menu_position'                  => pods_v( 'menu_position', $post_type ),
			'show_in_menu'                   => (int) pods_v( 'show_in_menu', $post_type ),
			'menu_location_custom'           => pods_v( 'admin_menu_parent', $post_type ),
			'menu_icon'                      => pods_v( 'menu_icon', $post_type ),
			'register_meta_box_cb'           => pods_v( 'register_meta_box_cb', $post_type ), // Not currently used.

			'import_source'                  => 'acf',
		];

		// Migrate built-in taxonomies
		$builtin = $taxonomies;

		foreach ( $builtin as $taxonomy_name ) {
			$params[ 'built_in_taxonomies_' . $taxonomy_name ] = 1;
		}

		if ( ! is_object( $this->api ) ) {
			$this->api = pods_api();
		}

		$pod = $this->api->load_pod( [ 'name' => pods_clean_name( $params['name'] ) ], false );

		if ( ! empty( $pod ) ) {
			return pods_error( sprintf( __( 'Pod with the name %s already exists', 'pods' ), pods_clean_name( $params['name'] ) ) );
		}

		$id = (int) $this->api->save_pod( $params );

		if ( empty( $id ) ) {
			return false;
		}

		$pod = $this->api->load_pod( [ 'id' => $id ], false );

		if ( empty( $pod ) ) {
			return false;
		}

		if ( $pod['name'] !== $params['name'] ) {
			$this->api->rename_wp_object_type( $params['type'], $params['name'], $pod['name'] );
		}

		return $id;
	}

	/**
	 *
	 *
	 * @since 2.0.0
	 *
	 * @param $taxonomy
	 *
	 * @return bool|int|mixed
	 */
	private function migrate_taxonomy( $taxonomy ) {
		$labels = [];

		if ( isset( $taxonomy['labels'] ) && is_array( $taxonomy['labels'] ) ) {
			$labels = $taxonomy['labels'];
		}

		$post_types = [];

		if ( isset( $taxonomy['object_type'] ) && is_array( $taxonomy['object_type'] ) ) {
			$post_types = $taxonomy['object_type'];
		}

		$default_term_enabled = (int) pods_v( 'default_term_enabled', $taxonomy );

		$params = [
			'type'                             => 'taxonomy',
			'storage'                          => 'meta',
			'object'                           => '',
			'name'                             => pods_v( 'taxonomy', $taxonomy ),
			'label'                            => pods_v( 'title', $taxonomy ),
			'label_singular'                   => pods_v( 'singular_name', $labels ),
			'description'                      => pods_v( 'description', $taxonomy ),

			// Custom labels.
			'label_menu_name'                  => pods_v( 'menu_name', $labels ),
			'label_all_items'                  => pods_v( 'all_items', $labels ),
			'label_edit_item'                  => pods_v( 'edit_item', $labels ),
			'label_view_item'                  => pods_v( 'view_item', $labels ),
			'label_update_item'                => pods_v( 'update_item', $labels ),
			'label_add_new_item'               => pods_v( 'add_new_item', $labels ),
			'label_new_item_name'              => pods_v( 'new_item_name', $labels ),
			'label_parent_item'                => pods_v( 'parent_item', $labels ),
			'label_parent_item_colon'          => pods_v( 'parent_item_colon', $labels ),
			'label_search_items'               => pods_v( 'search_items', $labels ),
			'label_popular_items'              => pods_v( 'popular_items', $labels ),
			'label_separate_items_with_commas' => pods_v( 'separate_items_with_commas', $labels ),
			'label_add_or_remove_items'        => pods_v( 'add_or_remove_items', $labels ),
			'label_choose_from_most_used'      => pods_v( 'choose_from_most_used', $labels ),
			'label_not_found'                  => pods_v( 'not_found', $labels ),
			'label_no_terms'                   => pods_v( 'no_terms', $labels ),
			'label_items_list_navigation'      => pods_v( 'items_list_navigation', $labels ),
			'label_items_list'                 => pods_v( 'items_list', $labels ),
			'label_back_to_items'              => pods_v( 'back_to_items', $labels ),
			'label_parent_field_description'   => pods_v( 'parent_field_description', $labels ),
			'label_slug_field_description'     => pods_v( 'slug_field_description', $labels ),
			'label_desc_field_description'     => pods_v( 'desc_field_description', $labels ),

			// Other settings.
			'public'                           => (int) pods_v( 'public', $taxonomy, 1 ),
			'publicly_queryable'               => (int) pods_v( 'publicly_queryable', $taxonomy, 1 ),
			'hierarchical'                     => (int) pods_v( 'hierarchical', $taxonomy ),
			'show_ui'                          => (int) pods_v( 'show_ui', $taxonomy, 1 ),
			'show_in_menu'                     => (int) pods_v( 'show_in_menu', $taxonomy, 1 ),
			'show_in_nav_menus'                => (int) pods_v( 'show_in_nav_menus', $taxonomy, 1 ),
			'rewrite'                          => (int) ( 'no_permalink' !== pods_v( 'permalink_rewrite', pods_v( 'rewrite', $taxonomy ) ) ),
			'rewrite_custom_slug'              => pods_v( 'slug', pods_v( 'rewrite', $taxonomy ) ),
			'rewrite_with_front'               => (int) pods_v( 'with_front', pods_v( 'rewrite', $taxonomy ) ),
			'rewrite_hierarchical'             => (int) pods_v( 'rewrite_hierarchical', pods_v( 'rewrite', $taxonomy ) ),
			'query_var'                        => (int) ( 'none' !== pods_v( 'query_var', $taxonomy ) ),
			'query_var_string'                 => pods_v( 'query_var_name', $taxonomy ),
			'rewrite_hierarchical'             => (int) pods_v( 'rewrite_hierarchical', $taxonomy ),
			'show_admin_column'                => (int) pods_v( 'show_admin_column', $taxonomy ),
			'show_in_rest'                     => (int) pods_v( 'show_in_rest', $taxonomy ),
			'show_tagcloud'                    => (int) pods_v( 'show_tagcloud', $taxonomy ),
			'sort'                             => (int) pods_v( 'sort', $taxonomy ),
			'show_in_quick_edit'               => (int) pods_v( 'show_in_quick_edit', $taxonomy ),
			'rest_base'                        => pods_v( 'rest_base', $taxonomy ),
			'rest_controller_class'            => pods_v( 'rest_controller_class', $taxonomy ), // Not currently used.
			'rest_namespace'                   => pods_v( 'rest_namespace', $taxonomy ),
			'register_meta_box_cb'             => pods_v( 'meta_box_cb', $taxonomy ), // Not currently used.
			'default_term_name'                => 1 === $default_term_enabled ? pods_v( 'default_term_name', $taxonomy ) : null,
			'default_term_slug'                => 1 === $default_term_enabled ? pods_v( 'default_term_slug', $taxonomy ) : null,
			'default_term_description'         => 1 === $default_term_enabled ? pods_v( 'default_term_description', $taxonomy ) : null,

			'import_source' => 'acf',
		];

		// Migrate attach-to
		$attach = $post_types;

		foreach ( $attach as $type_name ) {
			$params[ 'built_in_post_types_' . $type_name ] = 1;
		}

		if ( ! is_object( $this->api ) ) {
			$this->api = pods_api();
		}

		$pod = $this->api->load_pod( [ 'name' => pods_clean_name( $params['name'] ) ], false );

		if ( ! empty( $pod ) ) {
			return pods_error( sprintf( __( 'Pod with the name %s already exists', 'pods' ), pods_clean_name( $params['name'] ) ) );
		}

		$id = (int) $this->api->save_pod( $params );

		if ( empty( $id ) ) {
			return false;
		}

		$pod = $this->api->load_pod( [ 'id' => $id ], false );

		if ( empty( $pod ) ) {
			return false;
		}

		if ( $pod['name'] != $params['name'] ) {
			$this->api->rename_wp_object_type( $params['type'], $params['name'], $pod['name'] );
		}

		return $id;
	}

}
