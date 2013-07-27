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

    update_option( 'pods_framework_version', '2.3' );
}

// Update to 2.3.4
if ( version_compare( $pods_version, '2.3.4', '<' ) ) {
    if ( function_exists( 'pods_page_flush_rewrites' ) )
        pods_page_flush_rewrites();

    update_option( 'pods_framework_version', '2.3.4' );
}

// Update to 2.3.5
if ( version_compare( $pods_version, '2.3.5', '<' ) ) {
    global $wpdb;

    $wpdb->query( "UPDATE `{$wpdb->postmeta}` SET `meta_value` = 'dMy' WHERE `meta_key` IN ( 'date_format', 'datetime_format' ) AND `meta_value` = 'dMd'" );
    $wpdb->query( "UPDATE `{$wpdb->postmeta}` SET `meta_value` = 'dMy_dash' WHERE `meta_key` IN ( 'date_format', 'datetime_format' ) AND `meta_value` = 'dMd_dash'" );

    $pods_object_ids = $wpdb->get_col( "SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_type` IN ( '_pods_pod', '_pods_field', '_pods_page', '_pods_template', '_pods_helper' )" );

    if ( !empty( $pods_object_ids ) ) {
        array_walk( $pods_object_ids, 'absint' );

        $wpdb->query( "DELETE FROM `{$wpdb->postmeta}` WHERE `post_id` IN ( " . implode( ', ', $pods_object_ids ) . " ) AND `meta_value` = ''" );
    }

    update_option( 'pods_framework_version', '2.3.5' );
}

// Update to 2.3.9
if ( version_compare( $pods_version, '2.3.9-a-1', '<' ) ) {
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

    update_option( 'pods_framework_version', '2.3.9-a-1' );
}