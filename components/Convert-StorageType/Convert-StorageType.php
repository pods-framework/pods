<?php
/**
 * Name: Convert: Change Storage Type of Pod
 *
 * Menu Name: Convert Storage Type
 *
 * Description:
 *
 * Version: 1.0
 *
 * Developer Mode: on
 *
 * @package Pods\Components
 * @subpackage convert-storagetype
 */
class Pods_Convert_StorageType extends PodsComponent {

    private $api = null;

    /**
     * Do things like register scripts and stylesheets
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
     * Show the Admin
     */
    public function admin ( $options, $component ) {
        $method = 'convert'; // ajax_convert

        pods_view( PODS_DIR . 'components/Convert-StorageType/ui/wizard.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Handle the Conversion AJAX
     *
     * @param $params
     */
    public function ajax_convert ( $params ) {

    }

}
