<?php
/**
 * Name: Migration: Import from Custom Post Types UI
 *
 * Description: Import from Custom Post Types UI to Pods 2.0 (<a href="http://webdevstudios.com/plugin/custom-post-type-ui/">http://webdevstudios.com/plugin/custom-post-type-ui/</a>)
 *
 * Version: 1.0
 *
 * @package pods
 * @subpackage migratecptui
 */
class MigrateCPTUI extends PodsComponent {
	private $cpt_post_types = null;
	private $cpt_taxonomies = null;

	/**
	 * Do things like register/enqueue scripts and stylesheets
	 *
	 * @since 2.0.0
	 */
	public function __construct () {
		$this->cpt_post_types = get_option( 'cpt_custom_post_types' );
		$this->cpt_taxonomies = get_option( 'cpt_custom_tax_types' );
	}


	/**
	 *
	 *
	 * @since 2.0.0
	 */
	function get_objects ( $object_type = 'post_type' ) {

		switch ( $object_type ) {

			case 'post_type':
				return $this->cpt_post_types;
				break;

			case 'taxonomy':
				return $this->cpt_taxonomies;
				break;
		}
	}

	/**
	 *
	 *
	 * @since 2.0.0
	 */
	public function migrate ( $object_type = 'post_type', $cptui_object, $storage = 'meta' ) {

		switch ($object_type) {
			case 'post_type':
				return $this->migrate_post_type( $cptui_object, $storage );
				break;

			case 'taxonomy':
				return $this->migrate_taxonomy( $cptui_object ); // Taxonomies are currently table-based only
				break;
		}
	}


	/**
	 *
	 *
	 * @since 2.0.0
	 */
	private function migrate_taxonomy ( $cptui_taxonomy ) {

		$params = array(
			'type'                => 'taxonomy',
			'storage'             => 'table',
			'object'              => '',
			'name'                => $cptui_taxonomy['name'],
			'label'               => $cptui_taxonomy['label'],
			'label_singular'      => $cptui_taxonomy['singular_label'],
			'public'              => 1,
			'show_ui'             => (int)$cptui_taxonomy['show_ui'],
			'hierarchical'        => (int)$cptui_taxonomy['hierarchical'],
			'query_var'           => (int)$cptui_taxonomy['query_var'],
			'rewrite'             => (int)$cptui_taxonomy['rewrite'],
			'rewrite_custom_slug' => $cptui_taxonomy['rewrite_slug'],
			'label_search_items'  => $cptui_taxonomy[0]['search_items'],
			'label_popular_items' => $cptui_taxonomy[0]['popular_items'],
			'label_all_items'     => $cptui_taxonomy[0]['all_items'],
			'label_parent'        => $cptui_taxonomy[0]['parent_item'],
			'label_parent_item_colon'          => $cptui_taxonomy[0]['parent_item_colon'],
			'label_edit'                       => $cptui_taxonomy[0]['edit_item'],
			'label_update_item'                => $cptui_taxonomy[0]['update_item'],
			'label_add_new'                    => $cptui_taxonomy[0]['add_new_item'],
			'label_new_item'                   => $cptui_taxonomy[0]['new_item_name'],
			'label_separate_items_with_commas' => $cptui_taxonomy[0]['separate_items_with_commas'],
			'label_add_or_remove_items'        => $cptui_taxonomy[0]['add_or_remove_items'],
			'label_choose_from_the_most_used'  => $cptui_taxonomy[0]['choose_from_most_used']
		);

		// Migrate attach-to
		$attach = $cptui_taxonomy[1];
		if ( is_array( $attach ) ) {
			foreach ( $attach as $type_name ) {
				$params['built_in_post_types_' . $type_name] = 1;
			}
		}


		return pods_api()->save_pod( $params );
	}

	/**
	 *
	 *
	 * @since 2.0.0
	 */
	private function migrate_post_type ( $cptui_post_type, $storage ) {

		$params = array(
			'type'                => 'post_type',
			'storage'             => $storage,
			'object'              => '',
			'name'                => $cptui_post_type['name'],
			'label'               => $cptui_post_type['label'],
			'label_singular'      => $cptui_post_type['singular_label'],
			'description'         => $cptui_post_type['description'],
			'public'              => $cptui_post_type['public'],
			'show_ui'             => (int)$cptui_post_type['show_ui'],
			'has_archive'         => (int)$cptui_post_type['has_archive'],
			'exclude_from_search' => (int)$cptui_post_type['exclude_from_search'],
			'capability_type'     => $cptui_post_type['capability_type'], //--!! Needs sanity checking?
			'hierarchical'        => (int)$cptui_post_type['hierarchical'],
			'rewrite'             => (int)$cptui_post_type['rewrite'],
			'rewrite_custom_slug' => $cptui_post_type['rewrite_slug'],
			'query_var'           => (int)$cptui_post_type['query_var'],
			'menu_position'       => (int)$cptui_post_type['menu_position'],
			'show_in_menu'        => (int)$cptui_post_type['show_in_menu'],
			'menu_string'         => $cptui_post_type['show_in_menu_string'],

			// 'supports' argument to register_post_type()
  		'supports_title'           => (is_array($cptui_post_type[0]) && in_array('title', $cptui_post_type[0])),
		  'supports_editor'          => (is_array($cptui_post_type[0]) && in_array('editor', $cptui_post_type[0])),
		  'supports_excerpt'         => (is_array($cptui_post_type[0]) && in_array('excerpt', $cptui_post_type[0])),
		  'supports_trackbacks'      => (is_array($cptui_post_type[0]) && in_array('trackbacks', $cptui_post_type[0])),
		  'supports_custom_fields'   => (is_array($cptui_post_type[0]) && in_array('custom-fields', $cptui_post_type[0])),
		  'supports_comments'        => (is_array($cptui_post_type[0]) && in_array('comments', $cptui_post_type[0])),
		  'supports_revisions'       => (is_array($cptui_post_type[0]) && in_array('revisions', $cptui_post_type[0])),
		  'supports_thumbnail'       => (is_array($cptui_post_type[0]) && in_array('thumbnail', $cptui_post_type[0])),
		  'supports_author'          => (is_array($cptui_post_type[0]) && in_array('author', $cptui_post_type[0])),
		  'supports_page_attributes' => (is_array($cptui_post_type[0]) && in_array('page-attributes', $cptui_post_type[0])),

			// 'labels' argument to register_post_type()
			'menu_name'                => $cptui_post_type[2]['menu_name'],
			'label_add_new'            => $cptui_post_type[2]['add_new'],
			'label_add_new_item'       => $cptui_post_type[2]['add_new_item'],
			'label_edit'               => $cptui_post_type[2]['edit'],
			'label_edit_item'          => $cptui_post_type[2]['edit_item'],
			'label_new_item'           => $cptui_post_type[2]['new_item'],
			'label_view'               => $cptui_post_type[2]['view'],
			'label_view_item'          => $cptui_post_type[2]['view_item'],
			'label_search_items'       => $cptui_post_type[2]['search_items'],
			'label_not_found'          => $cptui_post_type[2]['not_found'],
			'label_not_found_in_trash' => $cptui_post_type[2]['not_found_in_trash'],
			'label_parent'             => $cptui_post_type[2]['parent'],
		);

		// Migrate built-in taxonomies
		$builtin = $cptui_post_type[1];
		if ( is_array( $builtin ) ) {
			foreach ( $builtin as $taxonomy_name ) {
				$params['built_in_taxonomies_' . $taxonomy_name] = 1;
			}
		}


		return pods_api()->save_pod( $params );
	}


	/**
	 *
	 * @since 2.0.0
	 */
	public function rename ( $object_type = 'post_type', $old_name, $new_name ) {
		// Probably not needed since this will be a direct PodsAPI call

		//PodsAPI::rename_wp_object( $object_type, $old_name, $new_name );

		/*
		switch ( $object_type ) {
			case 'post_type':
				pods_query( "UPDATE `{$wpdb->posts}` SET `post_type` = %s WHERE `post_type` = %s", array( $new_name, $old_name ) );
				break;

			case 'taxonomy':
				pods_query( "UPDATE `{$wpdb->term_taxonomy}` SET `taxonomy` = %s WHERE `taxonomy` = %s", array( $new_name, $old_name ) );
				break;
		}
		*/
	}

	/**
	 *
	 * @since 2.0.0
	 */
	public function clean () {
		delete_option( 'cpt_custom_post_types' );
		delete_option( 'cpt_custom_tax_types' );
	}

} // MigrateCPTUI
?>