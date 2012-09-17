<?php
/**
 * Name: Migrate: Import Pods 1.x Packages
 *
 * Menu Name: Migrate Packages
 *
 * Description: Import your Pods, Fields, and other settings from any Pods 1.x site
 *
 * Version: 2.0
 *
 * Developer Mode: on
 *
 * @package Pods\Components
 * @subpackage Migrate-ImportPackages
 */

class Pods_Migrate_ImportPackages extends PodsComponent {

    /**
     * Do things like register/enqueue scripts and stylesheets
     *
     * @since 2.0.0
     */
    public function __construct () {

    }

    /**
     * Enqueue styles
     *
     * @since 2.0.0
     */
    public function admin_assets () {
        wp_enqueue_style( 'pods-wizard' );
    }

    /**
     * Build admin area
     *
     * @param $options
     *
     * @since 2.0.0
     */
    public function admin ( $options, $component ) {
        $method = 'import'; // ajax_import

        pods_view( PODS_DIR . 'components/Packages/ui/wizard.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Handle the Import/Export AJAX
     *
     * @param $params
     */
    public function ajax_import ( $params ) {

    }
}
