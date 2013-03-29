<?php
/**
 * Name: Table Storage
 *
 * Description: Enable a custom database table for your custom fields on Post Types, Media, Taxonomies, Users, and Comments.
 *
 * Version: 2.3
 *
 * Category: Advanced
 *
 * Tableless Mode: No
 *
 * @package Pods\Components
 * @subpackage Advanced Content Types
 */

class Pods_Table_Storage extends PodsComponent {

    /**
     * Do things like register/enqueue scripts and stylesheets
     *
     * @since 2.3.0
     */
    public function __construct () {
        if ( !defined( 'PODS_TABLELESS' ) || !PODS_TABLELESS ) {
            add_filter( 'pods_admin_setup_add_create_storage', '__return_true' );
            add_filter( 'pods_admin_setup_add_create_taxonomy_storage', '__return_true' );

            add_filter( 'pods_admin_setup_add_extend_storage', '__return_true' );
            add_filter( 'pods_admin_setup_add_extend_taxonomy_storage', '__return_true' );

            add_filter( 'pods_admin_setup_add_extend_pod_type', array( $this, 'add_pod_type' ) );
        }
    }

    /**
     * Enable Taxonomy extending option in setup-add.php
     *
     * @param array $data Pod Type options
     *
     * @return array
     */
    public function add_pod_type ( $data ) {
        $data[ 'taxonomy' ] = __( 'Taxonomies (Categories, Tags, etc..)', 'pods' );

        return $data;
    }

}