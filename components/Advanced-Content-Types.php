<?php
/**
 * Name: Advanced Content Types
 *
 * Description: Create custom tables entirely separate from WordPress. You most likely don't need these and we recommend you use Custom Post Types or Custom Taxonomies instead.
 *
 * Version: 2.3
 *
 * Category: Advanced
 *
 * @package Pods\Components
 * @subpackage Advanced Content Types
 */

class Pods_Advanced_Content_Types extends PodsComponent {

    /**
     * Do things like register/enqueue scripts and stylesheets
     *
     * @since 2.3.0
     */
    public function __construct () {
        if ( !defined( 'PODS_TABLELESS' ) || !PODS_TABLELESS )
            add_filter( 'pods_admin_setup_add_create_pod_type', array( $this, 'add_pod_type' ) );
    }

    /**
     * Enable Advanced Content Type option in setup-add.php
     *
     * @param array $data Pod Type options
     *
     * @return array
     */
    public function add_pod_type ( $data ) {
        $data[ 'pod' ] = __( 'Advanced Content Type (separate from WP, blank slate, in its own table)', 'pods' );

        return $data;
    }

}