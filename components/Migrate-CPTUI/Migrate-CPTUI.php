<?php
/**
 * Name: Migrate: Import from the Custom Post Type UI plugin
 *
 * Menu Name: Migrate CPT UI
 *
 * Description: Import Custom Post Types and Taxonomies from Custom Post Type UI (<a href="http://webdevstudios.com/plugin/custom-post-type-ui/">http://webdevstudios.com/plugin/custom-post-type-ui/</a>)
 *
 * Category: Migration
 *
 * Version: 1.0
 *
 * Plugin: pods-migrate-custom-post-type-ui/pods-migrate-custom-post-type-ui.php
 *
 * @package Pods\Components
 * @subpackage Migrate-Cptui
 */

if ( class_exists( 'Pods_Migrate_CPTUI' ) )
    return;

class Pods_Migrate_CPTUI extends PodsComponent {

    private $api = null;

    private $post_types = null;

    private $taxonomies = null;

    /**
     * Do things like register scripts and stylesheets
     *
     * @since 2.0
     */
    public function __construct () {
        $this->post_types = (array) get_option( 'cpt_custom_post_types', array() );
        $this->taxonomies = (array) get_option( 'cpt_custom_tax_types', array() );
    }

    /**
     * Enqueue styles
     *
     * @since 2.0
     */
    public function admin_assets () {
        wp_enqueue_style( 'pods-wizard' );
    }

    /**
     * Show the Admin
     */
    public function admin ( $options, $component ) {
        $post_types = (array) $this->post_types;
        $taxonomies = (array) $this->taxonomies;

        $method = 'migrate'; // ajax_migrate

        pods_view( PODS_DIR . 'components/Migrate-CPTUI/ui/wizard.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Handle the Migration AJAX
     *
     * @param $params
     */
    public function ajax_migrate ( $params ) {
        $post_types = (array) $this->post_types;
        $taxonomies = (array) $this->taxonomies;

        $migrate_post_types = array();

        if ( isset( $params->post_type ) && !empty( $params->post_type ) ) {
            foreach ( $params->post_type as $post_type => $checked ) {
                if ( true === (boolean) $checked )
                    $migrate_post_types[] = $post_type;
            }
        }

        $migrate_taxonomies = array();

        if ( isset( $params->taxonomy ) && !empty( $params->taxonomy ) ) {
            foreach ( $params->taxonomy as $taxonomy => $checked ) {
                if ( true === (boolean) $checked )
                    $migrate_taxonomies[] = $taxonomy;
            }
        }

        foreach ( $post_types as $k => $post_type ) {
            if ( !in_array( pods_var( 'name', $post_type ), $migrate_post_types ) )
                continue;

            $id = $this->migrate_post_type( $post_type );

            if ( 0 < $id )
                unset( $post_types[ $k ] );
        }

        foreach ( $taxonomies as $k => $taxonomy ) {
            if ( !in_array( pods_var( 'name', $taxonomy ), $migrate_taxonomies ) )
                continue;

            $id = $this->migrate_taxonomy( $taxonomy );

            if ( 0 < $id )
                unset( $taxonomies[ $k ] );
        }

        if ( 1 == pods_var( 'cleanup', $params, 0 ) ) {
            if ( !empty( $post_types ) )
                update_option( 'cpt_custom_post_types', $post_types );
            else
                delete_option( 'cpt_custom_post_types' );

            if ( !empty( $taxonomies ) )
                update_option( 'cpt_custom_tax_types', $taxonomies );
            else
                delete_option( 'cpt_custom_tax_types' );
        }
    }

    /**
     *
     *
     * @since 2.0
     */
    private function migrate_post_type ( $post_type ) {
        $params = array(
            'type' => 'post_type',
            'storage' => 'meta',
            'object' => '',
            'name' => pods_var_raw( 'name', $post_type ),
            'label' => pods_var_raw( 'label', $post_type ),
            'label_singular' => pods_var_raw( 'singular_label', $post_type ),
            'description' => pods_var_raw( 'description', $post_type ),
            'public' => pods_var_raw( 'public', $post_type ),
            'show_ui' => (int) pods_var_raw( 'show_ui', $post_type ),
            'has_archive' => (int) pods_var_raw( 'has_archive', $post_type ),
            'exclude_from_search' => (int) pods_var_raw( 'exclude_from_search', $post_type ),
            'capability_type' => pods_var_raw( 'capability_type', $post_type ), //--!! Needs sanity checking?
            'hierarchical' => (int) pods_var_raw( 'hierarchical', $post_type ),
            'rewrite' => (int) pods_var_raw( 'rewrite', $post_type ),
            'rewrite_custom_slug' => pods_var_raw( 'rewrite_slug', $post_type ),
            'query_var' => (int) pods_var_raw( 'query_var', $post_type ),
            'menu_position' => (int) pods_var_raw( 'menu_position', $post_type ),
            'show_in_menu' => (int) pods_var_raw( 'show_in_menu', $post_type ),
            'menu_string' => pods_var_raw( 'show_in_menu_string', $post_type ),

            // 'supports' argument to register_post_type()
            'supports_title' => ( is_array( $post_type[ 0 ] ) && in_array( 'title', $post_type[ 0 ] ) ),
            'supports_editor' => ( is_array( $post_type[ 0 ] ) && in_array( 'editor', $post_type[ 0 ] ) ),
            'supports_excerpt' => ( is_array( $post_type[ 0 ] ) && in_array( 'excerpt', $post_type[ 0 ] ) ),
            'supports_trackbacks' => ( is_array( $post_type[ 0 ] ) && in_array( 'trackbacks', $post_type[ 0 ] ) ),
            'supports_custom_fields' => ( is_array( $post_type[ 0 ] ) && in_array( 'custom-fields', $post_type[ 0 ] ) ),
            'supports_comments' => ( is_array( $post_type[ 0 ] ) && in_array( 'comments', $post_type[ 0 ] ) ),
            'supports_revisions' => ( is_array( $post_type[ 0 ] ) && in_array( 'revisions', $post_type[ 0 ] ) ),
            'supports_thumbnail' => ( is_array( $post_type[ 0 ] ) && in_array( 'thumbnail', $post_type[ 0 ] ) ),
            'supports_author' => ( is_array( $post_type[ 0 ] ) && in_array( 'author', $post_type[ 0 ] ) ),
            'supports_page_attributes' => ( is_array( $post_type[ 0 ] ) && in_array( 'page-attributes', $post_type[ 0 ] ) ),

            // 'labels' argument to register_post_type()
            'menu_name' => pods_var_raw( 'menu_name', $post_type[ 2 ] ),
            'label_add_new' => pods_var_raw( 'add_new', $post_type[ 2 ] ),
            'label_add_new_item' => pods_var_raw( 'add_new_item', $post_type[ 2 ] ),
            'label_edit' => pods_var_raw( 'edit', $post_type[ 2 ] ),
            'label_edit_item' => pods_var_raw( 'edit_item', $post_type[ 2 ] ),
            'label_new_item' => pods_var_raw( 'new_item', $post_type[ 2 ] ),
            'label_view' => pods_var_raw( 'view', $post_type[ 2 ] ),
            'label_view_item' => pods_var_raw( 'view_item', $post_type[ 2 ] ),
            'label_search_items' => pods_var_raw( 'search_items', $post_type[ 2 ] ),
            'label_not_found' => pods_var_raw( 'not_found', $post_type[ 2 ] ),
            'label_not_found_in_trash' => pods_var_raw( 'not_found_in_trash', $post_type[ 2 ] ),
            'label_parent' => pods_var_raw( 'parent', $post_type[ 2 ] ),
        );

        // Migrate built-in taxonomies
        $builtin = $post_type[ 1 ];
        if ( is_array( $builtin ) ) {
            foreach ( $builtin as $taxonomy_name ) {
                $params[ 'built_in_taxonomies_' . $taxonomy_name ] = 1;
            }
        }

        if ( !is_object( $this->api ) )
            $this->api = pods_api();

        $pod = $this->api->load_pod( array( 'name' => pods_clean_name( $params[ 'name' ] ) ), false );

        if ( !empty( $pod ) )
            return pods_error( sprintf( __( 'Pod with the name %s already exists', 'pods' ), pods_clean_name( $params[ 'name' ] ) ) );

        $id = (int) $this->api->save_pod( $params );

        if ( empty( $id ) )
            return false;

        $pod = $this->api->load_pod( array( 'id' => $id ), false );

        if ( empty( $pod ) )
            return false;

        if ( $pod[ 'name' ] != $params[ 'name' ] )
            $this->api->rename_wp_object( $params[ 'type ' ], $params[ 'name' ], $pod[ 'name' ] );

        return $id;
    }

    /**
     *
     *
     * @since 2.0
     */
    private function migrate_taxonomy ( $taxonomy ) {

        $params = array(
            'type' => 'taxonomy',
            'storage' => 'table',
            'object' => '',
            'name' => pods_var_raw( 'name', $taxonomy ),
            'label' => pods_var_raw( 'label', $taxonomy ),
            'label_singular' => pods_var_raw( 'singular_label', $taxonomy ),
            'public' => 1,
            'show_ui' => (int) pods_var_raw( 'show_ui', $taxonomy ),
            'hierarchical' => (int) pods_var_raw( 'hierarchical', $taxonomy ),
            'query_var' => (int) pods_var_raw( 'query_var', $taxonomy ),
            'rewrite' => (int) pods_var_raw( 'rewrite', $taxonomy ),
            'rewrite_custom_slug' => pods_var_raw( 'rewrite_slug', $taxonomy ),
            'label_search_items' => pods_var_raw( 'search_items', $taxonomy[ 0 ] ),
            'label_popular_items' => pods_var_raw( 'popular_items', $taxonomy[ 0 ] ),
            'label_all_items' => pods_var_raw( 'all_items', $taxonomy[ 0 ] ),
            'label_parent' => pods_var_raw( 'parent_item', $taxonomy[ 0 ] ),
            'label_parent_item_colon' => pods_var_raw( 'parent_item_colon', $taxonomy[ 0 ] ),
            'label_edit' => pods_var_raw( 'edit_item', $taxonomy[ 0 ] ),
            'label_update_item' => pods_var_raw( 'update_item', $taxonomy[ 0 ] ),
            'label_add_new' => pods_var_raw( 'add_new_item', $taxonomy[ 0 ] ),
            'label_new_item' => pods_var_raw( 'new_item_name', $taxonomy[ 0 ] ),
            'label_separate_items_with_commas' => pods_var_raw( 'separate_items_with_commas', $taxonomy[ 0 ] ),
            'label_add_or_remove_items' => pods_var_raw( 'add_or_remove_items', $taxonomy[ 0 ] ),
            'label_choose_from_the_most_used' => pods_var_raw( 'choose_from_most_used', $taxonomy[ 0 ] )
        );

        // Migrate attach-to
        $attach = $taxonomy[ 1 ];
        if ( is_array( $attach ) ) {
            foreach ( $attach as $type_name ) {
                $params[ 'built_in_post_types_' . $type_name ] = 1;
            }
        }

        if ( !is_object( $this->api ) )
            $this->api = pods_api();

        $pod = $this->api->load_pod( array( 'name' => pods_clean_name( $params[ 'name' ] ) ), false );

        if ( !empty( $pod ) )
            return pods_error( sprintf( __( 'Pod with the name %s already exists', 'pods' ), pods_clean_name( $params[ 'name' ] ) ) );

        $id = (int) $this->api->save_pod( $params );

        if ( empty( $id ) )
            return false;

        $pod = $this->api->load_pod( array( 'id' => $id ), false );

        if ( empty( $pod ) )
            return false;

        if ( $pod[ 'name' ] != $params[ 'name' ] )
            $this->api->rename_wp_object( $params[ 'type ' ], $params[ 'name' ], $pod[ 'name' ] );

        return $id;
    }

    /**
     *
     * @since 2.0
     */
    public function clean () {
        delete_option( 'cpt_custom_post_types' );
        delete_option( 'cpt_custom_tax_types' );
    }
}
