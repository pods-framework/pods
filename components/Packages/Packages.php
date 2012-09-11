<?php
/**
 * Name: Packages
 *
 * Description: Import and Export your Pods, Fields, and other settings from one Pods site to any other
 *
 * Version: 2.0
 *
 * Developer Mode: on
 *
 * @package pods
 * @subpackage helpers
 */

class Pods_Packages extends PodsComponent {

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
        $method = 'import_export'; // ajax_import_export

        pods_view( PODS_DIR . 'components/Packages/ui/wizard.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Handle the Import/Export AJAX
     *
     * @param $params
     */
    public function ajax_import_export ( $params ) {

    }
}
