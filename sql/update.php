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
if ( version_compare( $pods_version, '2.3-a-11', '<' ) ) {
    $oldget = $_GET;

    $_GET[ 'toggle' ] = 1;

    PodsInit::$components->toggle( 'advanced-content-types' );

    $_GET = $oldget;
}