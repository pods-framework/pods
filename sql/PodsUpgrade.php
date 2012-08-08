<?php
/**
 * Created by JetBrains PhpStorm.
 * User: sclark
 * Date: 8/8/12
 * Time: 2:51 PM
 * To change this template use File | Settings | File Templates.
 */
class PodsUpgrade_2_0 {

    public $tables = array();

    function __construct () {
        global $wpdb;

        $tables = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}pod%'", ARRAY_N );

        if ( !empty( $tables ) ) {
            foreach ( $tables as $table ) {
                $this->tables[] = $table[ 0 ];
            }
        }
    }

    function install () {
        global $wpdb;

        $pods_version = get_option( 'pods_version' );

        if ( !empty( $pods_version ) && version_compare( $pods_version, '2.0.0', '<' ) ) {
            $sql = file_get_contents( PODS_DIR . 'sql/dump.sql' );
            $sql = apply_filters( 'pods_install_sql', $sql, PODS_VERSION, $pods_version );

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
        }
    }

    function ajax ( $params ) {
        if ( !isset( $params->step ) )
            return pods_error( __( 'Invalid upgrade process.', 'pods' ) );

        if ( !isset( $params->type ) )
            return pods_error( __( 'Invalid upgrade method.', 'pods' ) );

        if ( !method_exists( $this, $params->step . '_' . $params->type ) )
            return pods_error( __( 'Upgrade method not found.', 'pods' ) );

        return call_user_func( array( $this, $params->step . '_' . $params->type ), $params );
    }

    function prepare_pods () {
        global $wpdb;

        if ( !in_array( "{$wpdb->prefix}pod_types", $this->tables ) )
            return pods_error( __( 'Table not found, it cannot be migrated', 'pods' ) );

        $count = @count( (array) pods_query( "SELECT * FROM `@wp_pod_types`", false ) );

        return $count;
    }

    function prepare_fields () {
        global $wpdb;

        if ( !in_array( "{$wpdb->prefix}pod_fields", $this->tables ) )
            return pods_error( __( 'Table not found, it cannot be migrated', 'pods' ) );

        $count = @count( (array) pods_query( "SELECT * FROM `@wp_pod_fields`", false ) );

        return $count;
    }

    function prepare_relationships () {
        global $wpdb;

        if ( !in_array( "{$wpdb->prefix}pod_fields", $this->tables ) )
            return pods_error( __( 'Table not found, it cannot be migrated', 'pods' ) );

        $count = @count( (array) pods_query( "SELECT * FROM `@wp_pod_rel`", false ) );

        return $count;
    }

    function prepare_index () {
        global $wpdb;

        if ( !in_array( "{$wpdb->prefix}pod", $this->tables ) )
            return pods_error( __( 'Table not found, it cannot be migrated', 'pods' ) );

        $count = @count( (array) pods_query( "SELECT * FROM `@wp_pod`", false ) );

        return $count;
    }

    function prepare_templates () {
        global $wpdb;

        if ( !in_array( "{$wpdb->prefix}pod_templates", $this->tables ) )
            return pods_error( __( 'Table not found, it cannot be migrated', 'pods' ) );

        $count = @count( (array) pods_query( "SELECT * FROM `@wp_pod_templates`", false ) );

        return $count;
    }

    function prepare_pages () {
        global $wpdb;

        if ( !in_array( "{$wpdb->prefix}pod_pages", $this->tables ) )
            return pods_error( __( 'Table not found, it cannot be migrated', 'pods' ) );

        $count = @count( (array) pods_query( "SELECT * FROM `@wp_pod_pages`", false ) );

        return $count;
    }

    function prepare_helpers () {
        global $wpdb;

        if ( !in_array( "{$wpdb->prefix}pod_helpers", $this->tables ) )
            return pods_error( __( 'Table not found, it cannot be migrated', 'pods' ) );

        $count = @count( (array) pods_query( "SELECT * FROM `@wp_pod_helpers`", false ) );

        return $count;
    }

    function prepare_pod ( $params ) {
        global $wpdb;

        if ( !isset( $params->pod ) )
            return pods_error( __( 'Invalid Pod.', 'pods' ) );

        $pod = pods_clean_name( $params->pod );

        if ( !in_array( "{$wpdb->prefix}pod_tbl_{$pod}", $this->tables ) )
            return pods_error( __( 'Table not found, it cannot be migrated', 'pods' ) );


        $count = @count( (array) pods_query( "SELECT * FROM `@wp_pod_tbl_{$pod}`", false ) );

        return $count;
    }

    function migrate_pods () {
        // Grab old pods and fields, and create new ones via the API
        $api = pods_api();

        $pod_types = pods_query( "SELECT * FROM `@wp_pod_types`", false );

        $pod_ids = array();

        if ( empty( $pod_types ) )
            return $pod_ids;

        foreach ( $pod_types as $pod_type ) {
            $field_rows = pods_query( "SELECT * FROM `@wp_pod_fields` WHERE `datatype` = {$pod_type->id}" );

            $fields = array();

            foreach ( $field_rows as $row ) {
                $field_type = $row->coltype;

                if ( 'txt' == $field_type )
                    $field_type = 'text';
                elseif ( 'desc' == $field_type )
                    $field_type = 'paragraph';
                elseif ( 'bool' == $field_type )
                    $field_type = 'boolean';
                elseif ( 'num' == $field_type )
                    $field_type = 'number';

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

                $fields[ ] = $field_params;
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
            $pod_ids[ ] = $pod_id;

            pods_query( "DROP TABLE `@wp_pod_tbl_{$pod_type->name}`" );
        }

        pods_query( "DROP TABLE `@wp_pod_types`", false );
        pods_query( "DROP TABLE `@wp_pod_rel`", false );

        return $pod_ids;
    }

    function migrate_roles () {
        global $wpdb;

        $wp_roles = get_option( "{$wpdb->prefix}user_roles" );

        $old_roles = (array) @unserialize( get_option( 'pods_roles' ) );

        if ( !empty( $old_roles ) ) {
            foreach ( $old_roles as $role => $data ) {
                if ( $role == '_wpnonce' )
                    continue;

                $caps = $wp_roles[ $role ][ 'capabilities' ];

                foreach ( $data as $cap ) {
                    if ( 0 === strpos( 'manage_', $cap ) ) {
                        if ( in_array( $cap, array( 'manage_roles', 'manage_content' ) ) )
                            continue;

                        $cap = pods_str_replace( 'manage_', 'pods_', $cap, 1 );
                        $cap = pods_str_replace( 'pod_pages', 'pages', $cap, 1 );

                        $caps[ $cap ] = true;
                    }
                    elseif ( 0 === strpos( 'pod_', $cap ) ) {
                        $keys = array(
                            pods_str_replace( 'pod_', 'pods_new_', $cap, 1 ),
                            pods_str_replace( 'pod_', 'pods_edit_', $cap, 1 ),
                            pods_str_replace( 'pod_', 'pods_delete_', $cap, 1 ),
                        );

                        foreach ( $keys as $key ) {
                            $caps[ $key ] = true;
                        }
                    }
                }

                $wp_roles[ $role ][ 'capabilities' ] = $caps;
            }
        }

        update_option( "{$wpdb->prefix}user_roles", $wp_roles );

        delete_option( 'pods_roles' );

        return $wp_roles;
    }

    function migrate_templates () {
        $api = pods_api();

        $templates = pods_query( "SELECT * FROM `@wp_pod_templates`", false );

        $results = array();

        if ( empty( $templates ) )
            return $results;

        foreach ( $templates as $tpl ) {
            $params = array(
                'name' => $tpl->name,
                'code' => $tpl->code,
            );

            $results[ ] = $api->save_template( $params );
        }

        pods_query( "DROP TABLE `@wp_pod_templates`", false );

        return $results;
    }

    function migrate_helpers () {
        $api = pods_api();

        $helpers = pods_query( "SELECT * FROM `@wp_pod_helpers`", false );

        $results = array();

        if ( empty( $helpers ) )
            return $results;

        foreach ( $helpers as $hlpr ) {
            $params = array(
                'name' => $hlpr->name,
                'helper_type' => $hlpr->helper_type,
                'phpcode' => $hlpr->phpcode,
            );

            $results[ ] = $api->save_helper( $params );
        }

        pods_query( "DROP TABLE `@wp_pod_helpers`", false );

        return $results;
    }

    function migrate_pages () {
        $api = pods_api();

        $pages = pods_query( "SELECT * FROM `@wp_pod_pages`", false );

        $results = array();

        if ( empty( $pages ) )
            return $results;

        foreach ( $pages as $page ) {
            $results[ ] = $api->save_page( $page );
        }

        pods_query( "DROP TABLE `@wp_pod_pages`", false );

        return $results;
    }
}
