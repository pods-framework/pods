<?php
global $wpdb;

if ( defined( 'PODS_DEVELOPER' ) && PODS_DEVELOPER && isset( $_GET[ 'pods_upgrade_test' ] ) && 1 == $_GET[ 'pods_upgrade_test' ] ) {
    if ( version_compare( $pods_version, '2.0.0', '<' ) && version_compare( get_option( 'pods_version' ), '2.0.0', '<' ) ) {
        $sql = file_get_contents( PODS_DIR . 'sql/dump.sql' );
        $sql = apply_filters( 'pods_install_sql', $sql, PODS_VERSION, $pods_version, $_blog_id );

        $charset_collate = 'DEFAULT CHARSET utf8';

        if ( !empty( $wpdb->charset ) )
            $charset_collate = "DEFAULT CHARSET {$wpdb->charset}";

        if ( !empty( $wpdb->collate ) )
            $charset_collate .= " COLLATE {$wpdb->collate}";

        if ( 'DEFAULT CHARSET utf8' != $charset_collate )
            $sql = str_replace( 'DEFAULT CHARSET utf8', $charset_collate, $sql );

        $sql = explode( ";\n", str_replace( array( "\r", 'wp_' ), array( "\n", $wpdb->prefix ), $sql ) );

        for ( $i = 0, $z = count( $sql ); $i < $z; $i++ ) {
            pods_query( trim( $sql[ $i ] ), 'Cannot setup SQL tables' );
        }

        $pages = pods_2_migrate_pages();
        $helpers = pods_2_migrate_helpers();
        $templates = pods_2_migrate_templates();
        $pod_ids = pods_2_migrate_pods();
    }
}

if ( version_compare( '2.0.0-a-1', $pods_version, '<' ) && version_compare( $pods_version, '2.0.0-a-31', '<' ) ) {
    $pages = pods_2_alpha_migrate_pages();
    $helpers = pods_2_alpha_migrate_helpers();
    $templates = pods_2_alpha_migrate_templates();
    $pod_ids = pods_2_alpha_migrate_pods();

    pods_query( "DROP TABLE @wp_pods" );
    pods_query( "DROP TABLE @wp_pods_fields" );
    pods_query( "DROP TABLE @wp_pods_objects" );
}

function pods_2_migrate_pods () {
    // Grab old pods and fields, and create new ones via the API
    $api = pods_api();

    $pod_types = pods_query( "SELECT * FROM `@wp_pod_types`" );

    $pod_ids = array();

    foreach ( $pod_types as $pod_type ) {
        $field_rows = pods_query( "SELECT * FROM `@wp_pod_fields` WHERE `datatype` = {$pod_type->id}" );

        $fields = array();

        foreach ( $field_rows as $row ) {
            $field_type = $row->coltype;

            if ( 'txt' == $field_type )
                $field_type = 'text';
            elseif ( 'desc' == $field_type )
                $field_type = 'paragraph';

            $field_params = array(
                'name' => $row->name,
                'label' => $row->label,
                'type' => $field_type,
                'weight' => $row->weight,
                'options' => array(
                    'required' => $row->required,
                ),
            );

            if ( $row->coltype == 'pick' ) {
                $field_params[ 'pick_val' ] = $row->pickval;
                $field_params[ 'sister_field_id' ] = $row->sister_field_id;
            }

            $fields[] = $field_params;
        }

        $pod_params = array(
            'name' => $pod_type->name,
            'type' => 'pod',
            'storage' => 'table',
            'fields' => $fields,
            'options' => array(
                'pre_save_helpers' => $pod_type->pre_save_helpers,
                'post_save_helpers' => $pod_type->post_save_helpers,
                'pre_delete_helpers' => $pod_type->pre_drop_helpers,
                'post_delete_helpers' => $pod_type->post_drop_helpers,
                'show_in_menu' => $pod_type->is_toplevel,
                'detail_url' => $pod_type->detail_page,
            ),
        );

        $pod_id = $api->save_pod( $pod_params );
        $pod_ids[] = $pod_id;

        pods_query( "DROP TABLE `@wp_pod_tbl_{$pod_type->name}`" );
    }

    pods_query( "DROP TABLE `@wp_pod_types`" );
    pods_query( "DROP TABLE `@wp_pod_rel`" );

    return $pod_ids;
}

function pods_2_migrate_templates () {
    $api = pods_api();

    $templates = pods_query( "SELECT * FROM `@wp_pod_templates`" );

    $results = array();

    if ( 0 == count( $templates ) )
        return true;

    foreach ( $templates as $tpl ) {
        $params = array(
            'name' => $tpl->name,
            'code' => $tpl->code,
        );

        $results[] = $api->save_template( $params );
    }

    pods_query( "DROP TABLE `@wp_pod_templates`" );
    return $results;
}

function pods_2_migrate_helpers () {
    $api = pods_api();

    $helpers = pods_query( "SELECT * FROM `@wp_pod_helpers`" );

    $results = array();

    foreach ( $helpers as $hlpr ) {
        $params = array(
            'name' => $hlpr->name,
            'helper_type' => $hlpr->helper_type,
            'phpcode' => $hlpr->phpcode,
        );

        $results[] = $api->save_helper( $params );
    }

    pods_query( "DROP TABLE `@wp_pod_helpers`" );

    return $results;
}

function pods_2_migrate_pages () {
    $api = pods_api();

    $pages = pods_query( "SELECT * FROM `@wp_pod_pages`" );

    $results = array();

    foreach ( $pages as $page ) {
        $results[] = $api->save_page( $page );
    }

    pods_query( "DROP TABLE `@wp_pod_pages`" );

    return $results;
}

function pods_2_alpha_table_exists ( $tbl ) {
    try {
        $tbl = mysql_real_escape_string( $tbl );
        $rows = pods_query( "SELECT * FROM `{$tbl}` LIMIT 1" );
    }
    catch ( Exception $e ) {
        $rows = false;
    }

    return $rows;
}

function pods_2_alpha_migrate_pods () {
    $api = pods_api();

    $api->display_errors = true;

    $old_pods = pods_query( "SELECT * FROM `@wp_pods`" );

    $pod_ids = array();

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
                'options' => $field_opts,
            );

            $fields[] = $field_params;
        }

        $pod_params = array(
            'name' => $pod->name,
            'type' => $pod->type,
            'storage' => $pod->storage,
            'fields' => $fields,
            'options' => $pod_opts,
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
            pods_query( "DROP TABLE `@wp_pods_tbl_{$pod->name}`" );
            pods_query( "RENAME TABLE `@wp_pods_tb_{$pod->name}` TO `@wp_pods_tbl_{$pod->name}`" );
        }

        $pod_ids[] = $pod_id;
    }

    return $pod_ids;
}

function pods_2_alpha_migrate_helpers () {
    $api = pods_api();

    $helper_rows = pods_query( "SELECT * FROM `@wp_pods_objects` WHERE `type` = 'helper'" );

    $helper_ids = array();

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

    $page_rows = pods_query( "SELECT * FROM `@wp_pods_objects` WHERE `type` = 'page'" );

    $page_ids = array();

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

    $tpl_rows = pods_query( "SELECT * FROM `@wp_pods_objects` WHERE `type` = 'template'" );

    $tpl_ids = array();

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