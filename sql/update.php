<?php
/**
 * @package Pods\Upgrade
 */

// Update to 2.0.3
if ( version_compare( $pods_version, '2.0.3', '<' ) ) {
    // Rename sister_field_id to sister_id
    pods_query( "DELETE FROM `@wp_postmeta` WHERE `meta_key` = 'sister_field_id'", false );

    update_option( 'pods_framework_version', '2.0.3' );
}

// Update to 2.3
if ( version_compare( $pods_version, '2.3', '<' ) ) {
    // Auto activate Advanced Content Types component
    $oldget = $_GET;

    $_GET[ 'toggle' ] = 1;

    PodsInit::$components->toggle( 'advanced-content-types' );
    PodsInit::$components->toggle( 'table-storage' );

    $_GET = $oldget;

    // Set autoload on all necessary options to avoid extra queries
    $autoload_options = array(
        'pods_framework_version' => '',
        'pods_framework_version_last' => '',
        'pods_framework_db_version' => '',
        'pods_framework_upgraded_1_x' => '0',
        'pods_version' => '',

        'pods_component_settings' => '',

        'pods_disable_file_browser' => '0',
        'pods_files_require_login' => '1',
        'pods_files_require_login_cap' => '',
        'pods_disable_file_upload' => '0',
        'pods_upload_require_login' => '1',
        'pods_upload_require_login_cap' => ''
    );

    foreach ( $autoload_options as $option_name => $default ) {
        $option_value = get_option( $option_name, $default );

        delete_option( $option_name );
        add_option( $option_name, $option_value, '', 'yes' );
    }
}