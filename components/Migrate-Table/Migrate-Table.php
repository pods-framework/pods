<?php
/**
 * Name: Migrate: Import from Custom Tables
 *
 * Menu Name: Migrate Tables
 *
 * Description: Import from Custom Tables
 *
 * Version: 1.0
 *
 * Developer Mode: on
 *
 * @package pods
 * @subpackage migrate-table
 */
class Pods_Migrate_Table extends PodsComponent {

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
        $method = 'migrate'; // ajax_migrate

        pods_view( PODS_DIR . 'components/Migrate-Table/ui/wizard.php', compact( array_keys( get_defined_vars() ) ) );
    }
}
