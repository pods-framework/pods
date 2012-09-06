<?php
global $wpdb;

if ( !empty( $pods_version ) && version_compare( '2.0.0-a-1', $pods_version, '<' ) && version_compare( $pods_version, '2.0.0-a-31', '<' ) ) {
    $pages = pods_2_alpha_migrate_pages();
    $helpers = pods_2_alpha_migrate_helpers();
    $templates = pods_2_alpha_migrate_templates();
    $pod_ids = pods_2_alpha_migrate_pods();

    pods_query( "DROP TABLE @wp_pods", false );
    pods_query( "DROP TABLE @wp_pods_fields", false );
    pods_query( "DROP TABLE @wp_pods_objects", false );
}

if ( !empty( $pods_version ) && version_compare( '2.0.0-a-1', $pods_version, '<' ) && version_compare( $pods_version, '2.0.0-b-10', '<' ) ) {
    $author_fields = $wpdb->get_results( "SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_name` = 'author' AND `post_type` = '_pods_field'" );

    if ( !empty( $author_fields ) ) {
        foreach ( $author_fields as $author ) {
            update_post_meta( $author->ID, 'pick_format_type', 'single' );
            update_post_meta( $author->ID, 'pick_format_single', 'autocomplete' );
            update_post_meta( $author->ID, 'default_value', '{@user.ID}' );
        }
    }
}

if ( !empty( $pods_version ) && version_compare( '2.0.0-a-1', $pods_version, '<' ) && version_compare( $pods_version, '2.0.0-b-11', '<' ) ) {
    $date_fields = $wpdb->get_results( "SELECT `ID` FROM `{$wpdb->posts}` WHERE ( `post_name` = 'created' OR `post_name` = 'modified' ) AND `post_type` = '_pods_field'" );

    if ( !empty( $date_fields ) ) {
        foreach ( $date_fields as $date ) {
            update_post_meta( $date->ID, 'date_format_type', 'datetime' );
            update_post_meta( $date->ID, 'date_format', 'ymd_slash' );
            update_post_meta( $date->ID, 'date_time_type', '12' );
            update_post_meta( $date->ID, 'date_time_format', 'h_mm_ss_A' );
        }
    }
}

function pods_2_alpha_migrate_pods () {
    $api = pods_api();

    $api->display_errors = true;

    $old_pods = pods_query( "SELECT * FROM `@wp_pods`", false );

    $pod_ids = array();

    if ( empty( $old_pods ) )
        return $pod_ids;

    foreach ( $old_pods as $pod ) {
        $api->cache_flush_pods( array( 'name' => $pod->name ) );

        $pod_opts = json_decode( $pod->options, true );

        $field_rows = pods_query( "SELECT * FROM `@wp_pods_fields` where `pod_id` = {$pod->id}" );

        $fields = array();

        foreach ( $field_rows as $row ) {
            $field_opts = json_decode( $row->options, true );

            $field_params = array(
                'name' => $row->name,
                'label' => $row->label,
                'type' => $row->type,
                'pick_object' => $row->pick_object,
                'pick_val' => $row->pick_val,
                'sister_field_id' => $row->sister_field_id,
                'weight' => $row->weight,
                'options' => $field_opts
            );

            $fields[] = $field_params;
        }

        $pod_params = array(
            'name' => $pod->name,
            'type' => $pod->type,
            'storage' => $pod->storage,
            'fields' => $fields,
            'options' => $pod_opts
        );

        $renamed = false;

        if ( $pod->storage == 'table' ) {
            try {
                pods_query( "RENAME TABLE `@wp_pods_tbl_{$pod->name}` TO `@wp_pods_tb_{$pod->name}`" );
                $renamed = true;
            }
            catch ( Exception $e ) {
                $renamed = false;
            }
        }

        $pod_id = $api->save_pod( $pod_params );

        if ( $pod->storage == 'table' && $renamed ) {
            pods_query( "DROP TABLE `@wp_pods_tbl_{$pod->name}`", false );
            pods_query( "RENAME TABLE `@wp_pods_tb_{$pod->name}` TO `@wp_pods_tbl_{$pod->name}`" );
        }

        $pod_ids[] = $pod_id;
    }

    return $pod_ids;
}

function pods_2_alpha_migrate_helpers () {
    $api = pods_api();

    $helper_rows = pods_query( "SELECT * FROM `@wp_pods_objects` WHERE `type` = 'helper'", false );

    $helper_ids = array();

    if ( empty( $helper_rows ) )
        return $helper_ids;

    foreach ( $helper_rows as $row ) {
        $opts = json_decode( $row->options );

        $helper_params = array(
            'name' => $row->name,
            'helper_type' => $opts->helper_type,
            'phpcode' => $opts->phpcode,
        );

        $helper_ids[] = $api->save_helper( $helper_params );
    }

    return $helper_ids;
}

function pods_2_alpha_migrate_pages () {
    $api = pods_api();

    $page_rows = pods_query( "SELECT * FROM `@wp_pods_objects` WHERE `type` = 'page'", false );

    $page_ids = array();

    if ( empty( $page_rows ) )
        return $page_ids;

    foreach ( $page_rows as $row ) {
        $opts = json_decode( $row->options );

        $page_params = array(
            'uri' => $row->name,
            'phpcode' => $opts->phpcode,
        );

        $page_ids[] = $api->save_page( $page_params );
    }

    return $page_ids;
}

function pods_2_alpha_migrate_templates () {
    $api = pods_api();

    $tpl_rows = pods_query( "SELECT * FROM `@wp_pods_objects` WHERE `type` = 'template'", false );

    $tpl_ids = array();

    if ( empty( $tpl_rows ) )
        return $tpl_ids;

    foreach ( $tpl_rows as $row ) {
        $opts = json_decode( $row->options );

        $tpl_params = array(
            'name' => $row->name,
            'code' => $opts->code,
        );

        $tpl_ids[] = $api->save_template( $tpl_params );
    }

    return $tpl_ids;
}
