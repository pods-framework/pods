<?php
/**
 * @package Pods\Upgrade
 */
class PodsUpgrade_2_0_0 extends PodsUpgrade {

    /**
     * @var string
     */
    protected $version = '2.0.0';

    /**
     * @return array|bool|int|mixed|null|void
     */
    public function prepare_pods () {
        /**
         * @var $wpdb WPDB
         */
        global $wpdb;

        if ( !in_array( "{$wpdb->prefix}pod_types", $this->tables ) )
            return pods_error( __( 'Table not found, it cannot be migrated', 'pods' ) );

        $count = pods_query( "SELECT COUNT(*) AS `count` FROM `@wp_pod_types`", false );

        if ( !empty( $count ) )
            $count = (int) $count[ 0 ]->count;
        else
            $count = 0;

        return $count;
    }

    /**
     * @return array|bool|int|mixed|null|void
     */
    public function prepare_fields () {
        /**
         * @var $wpdb WPDB
         */
        global $wpdb;

        if ( !in_array( "{$wpdb->prefix}pod_fields", $this->tables ) )
            return pods_error( __( 'Table not found, it cannot be migrated', 'pods' ) );

        $count = pods_query( "SELECT COUNT(*) AS `count` FROM `@wp_pod_fields`", false );

        if ( !empty( $count ) )
            $count = (int) $count[ 0 ]->count;
        else
            $count = 0;

        return $count;
    }

    /**
     * @return array|bool|int|mixed|null|void
     */
    public function prepare_relationships () {
        /**
         * @var $wpdb WPDB
         */
        global $wpdb;

        if ( !in_array( "{$wpdb->prefix}pod_rel", $this->tables ) )
            return pods_error( __( 'Table not found, it cannot be migrated', 'pods' ) );

        $count = pods_query( "SELECT COUNT(*) AS `count` FROM `@wp_pod_rel`", false );

        if ( !empty( $count ) )
            $count = (int) $count[ 0 ]->count;
        else
            $count = 0;

        return $count;
    }

    /**
     * @return array|bool|int|mixed|null|void
     */
    public function prepare_index () {
        /**
         * @var $wpdb WPDB
         */
        global $wpdb;

        if ( !in_array( "{$wpdb->prefix}pod", $this->tables ) )
            return pods_error( __( 'Table not found, it cannot be migrated', 'pods' ) );

        $count = pods_query( "SELECT COUNT(*) AS `count` FROM `@wp_pod`", false );

        if ( !empty( $count ) )
            $count = (int) $count[ 0 ]->count;
        else
            $count = 0;

        return $count;
    }

    /**
     * @return array|bool|int|mixed|null|void
     */
    public function prepare_templates () {
        /**
         * @var $wpdb WPDB
         */
        global $wpdb;

        if ( !in_array( "{$wpdb->prefix}pod_templates", $this->tables ) )
            return pods_error( __( 'Table not found, it cannot be migrated', 'pods' ) );

        $count = pods_query( "SELECT COUNT(*) AS `count` FROM `@wp_pod_templates`", false );

        if ( !empty( $count ) )
            $count = (int) $count[ 0 ]->count;
        else
            $count = 0;

        return $count;
    }

    /**
     * @return array|bool|int|mixed|null|void
     */
    public function prepare_pages () {
        /**
         * @var $wpdb WPDB
         */
        global $wpdb;

        if ( !in_array( "{$wpdb->prefix}pod_pages", $this->tables ) )
            return pods_error( __( 'Table not found, it cannot be migrated', 'pods' ) );

        $count = pods_query( "SELECT COUNT(*) AS `count` FROM `@wp_pod_pages`", false );

        if ( !empty( $count ) )
            $count = (int) $count[ 0 ]->count;
        else
            $count = 0;

        return $count;
    }

    /**
     * @return array|bool|int|mixed|null|void
     */
    public function prepare_helpers () {
        /**
         * @var $wpdb WPDB
         */
        global $wpdb;

        if ( !in_array( "{$wpdb->prefix}pod_helpers", $this->tables ) )
            return pods_error( __( 'Table not found, it cannot be migrated', 'pods' ) );

        $count = pods_query( "SELECT COUNT(*) AS `count` FROM `@wp_pod_helpers`", false );

        if ( !empty( $count ) )
            $count = (int) $count[ 0 ]->count;
        else
            $count = 0;

        return $count;
    }

    /**
     * @param $params
     *
     * @return array|bool|int|mixed|null|void
     */
    public function prepare_pod ( $params ) {
        /**
         * @var $wpdb WPDB
         */
        global $wpdb;

        if ( !isset( $params->pod ) )
            return pods_error( __( 'Invalid Pod.', 'pods' ) );

        $pod = pods_sanitize( pods_clean_name( $params->pod ) );

        if ( !in_array( "{$wpdb->prefix}pod_tbl_{$pod}", $this->tables ) )
            return pods_error( __( 'Table not found, it cannot be migrated', 'pods' ) );

        $count = pods_query( "SELECT COUNT(*) AS `count` FROM `@wp_pod_tbl_{$pod}`", false );

        if ( !empty( $count ) )
            $count = (int) $count[ 0 ]->count;
        else
            $count = 0;

        $pod_type = pods_query( "SELECT `id` FROM `@wp_pod_types` WHERE `name` = '{$pod}'", false );

        if ( !empty( $pod_type ) )
            $pod_type = (int) $pod_type[ 0 ]->id;
        else
            return pods_error( __( 'Pod not found, it cannot be migrated', 'pods' ) );

        $fields = array( 'id' );

        $field_rows = pods_query( "SELECT `id`, `name`, `coltype` FROM `@wp_pod_fields` WHERE `datatype` = {$pod_type} ORDER BY `weight`, `name`" );

        if ( !empty( $field_rows ) ) {
            foreach ( $field_rows as $field ) {
                if ( !in_array( $field->coltype, array( 'pick', 'file' ) ) )
                    $fields[] = $field->name;
            }
        }

        $columns = PodsData::get_table_columns( "{$wpdb->prefix}pod_tbl_{$pod}" );

        $errors = array();

        foreach ( $columns as $column => $info ) {
            if ( !in_array( $column, $fields ) )
                $errors[] = "<strong>{$column}</strong> " . __( 'is a field in the table, but was not found in this pod - the field data will not be migrated.', 'pods' );
        }

        foreach ( $fields as $field ) {
            if ( !isset( $columns[ $field ] ) )
                $errors[] = "<strong>{$field}</strong> " . __( 'is a field in this pod, but was not found in the table - the field data will not be migrated.', 'pods' );
        }

        if ( !empty( $errors ) )
            return pods_error( implode( '<br />', $errors ) );

        return $count;
    }

    /**
     *
     */
    public function migrate_1_x () {
        $old_version = get_option( 'pods_version' );

        if ( 0 < strlen( $old_version ) ) {
            if ( false === strpos( $old_version, '.' ) )
                $old_version = pods_version_to_point( $old_version );

            // Last DB change was 1.11
            if ( version_compare( $old_version, '1.11', '<' ) ) {
                do_action( 'pods_update', PODS_VERSION, $old_version );

                if ( false !== apply_filters( 'pods_update_run', null, PODS_VERSION, $old_version ) )
                    include_once( PODS_DIR . 'sql/update-1.x.php' );

                do_action( 'pods_update_post', PODS_VERSION, $old_version );
            }
        }

        return '1';
    }

    /**
     * @return array|string
     */
    public function migrate_pods () {
        if ( true === $this->check_progress( __FUNCTION__ ) )
            return '1';

        $sister_ids = (array) get_option( 'pods_framework_upgrade_2_0_sister_ids', array() );

        $migration_limit = (int) apply_filters( 'pods_upgrade_pod_limit', 1 );
        $migration_limit = max( $migration_limit, 1 );

        $last_id = (int) $this->check_progress( __FUNCTION__ );

        $sql = "
            SELECT *
            FROM `@wp_pod_types`
            WHERE {$last_id} < `id`
            ORDER BY `id`
            LIMIT 0, {$migration_limit}
        ";

        $pod_types = pods_query( $sql );

        $last_id = true;

        if ( !empty( $pod_types ) ) {
            foreach ( $pod_types as $pod_type ) {
                $field_rows = pods_query( "SELECT * FROM `@wp_pod_fields` WHERE `datatype` = {$pod_type->id} ORDER BY `weight`, `name`" );

                $fields = array(
                    array(
                        'name' => 'name',
                        'label' => 'Name',
                        'type' => 'text',
                        'weight' => 0,
                        'options' => array(
                            'required' => 1,
                            'text_max_length' => 128
                        )
                    ),
                    array(
                        'name' => 'created',
                        'label' => 'Date Created',
                        'type' => 'datetime',
                        'options' => array(
                            'datetime_format' => 'ymd_slash',
                            'datetime_time_type' => '12',
                            'datetime_time_format' => 'h_mm_ss_A'
                        ),
                        'weight' => 1
                    ),
                    array(
                        'name' => 'modified',
                        'label' => 'Date Modified',
                        'type' => 'datetime',
                        'options' => array(
                            'datetime_format' => 'ymd_slash',
                            'datetime_time_type' => '12',
                            'datetime_time_format' => 'h_mm_ss_A'
                        ),
                        'weight' => 2
                    ),
                    array(
                        'name' => 'author',
                        'label' => 'Author',
                        'type' => 'pick',
                        'pick_object' => 'user',
                        'options' => array(
                            'pick_format_type' => 'single',
                            'pick_format_single' => 'autocomplete',
                            'default_value' => '{@user.ID}'
                        ),
                        'weight' => 3
                    )
                );

                $weight = 4;

                $found_fields = array();

                foreach ( $field_rows as $row ) {
                    if ( 'name' == $row->name )
                        continue;

                    $old_name = $row->name;

                    $row->name = pods_clean_name( $row->name );

                    if ( in_array( $row->name, array( 'created', 'modified', 'author' ) ) )
                        $row->name .= '2';

                    $field_type = $row->coltype;

                    if ( 'txt' == $field_type )
                        $field_type = 'text';
                    elseif ( 'desc' == $field_type )
                        $field_type = 'wysiwyg';
                    elseif ( 'code' == $field_type )
                        $field_type = 'paragraph';
                    elseif ( 'bool' == $field_type )
                        $field_type = 'boolean';
                    elseif ( 'num' == $field_type )
                        $field_type = 'number';
                    elseif ( 'date' == $field_type )
                        $field_type = 'datetime';

                    $field_params = array(
                        'name' => trim( $row->name ),
                        'label' => trim( $row->label ),
                        'description' => trim( $row->comment ),
                        'type' => $field_type,
                        'weight' => $weight,
                        'options' => array(
                            'required' => $row->required,
                            'unique' => $row->unique,
                            'input_helper' => $row->input_helper,
                            '_pods_1x_field_name' => $old_name,
                            '_pods_1x_field_id' => $row->id
                        )
                    );

                    if ( in_array( $field_params[ 'name' ], $found_fields ) )
                        continue;

                    $found_fields[] = $field_params[ 'name' ];

                    if ( 'pick' == $field_type ) {
                        $field_params[ 'pick_object' ] = 'pod-' . $row->pickval;

                        if ( 'wp_user' == $row->pickval )
                            $field_params[ 'pick_object' ] = 'user';
                        elseif ( 'wp_post' == $row->pickval )
                            $field_params[ 'pick_object' ] = 'post_type-post';
                        elseif ( 'wp_page' == $row->pickval )
                            $field_params[ 'pick_object' ] = 'post_type-page';
                        elseif ( 'wp_taxonomy' == $row->pickval )
                            $field_params[ 'pick_object' ] = 'taxonomy-category';

                        $field_params[ 'sister_id' ] = $row->sister_field_id;

                        $sister_ids[ $row->id ] = $row->sister_field_id; // Old Sister Field ID
                        $field_params[ 'options' ][ '_pods_1x_sister_id' ] = $row->sister_field_id;

                        $field_params[ 'options' ][ 'pick_filter' ] = $row->pick_filter;
                        $field_params[ 'options' ][ 'pick_orderby' ] = $row->pick_orderby;
                        $field_params[ 'options' ][ 'pick_display' ] = '';
                        $field_params[ 'options' ][ 'pick_size' ] = 'medium';

                        if ( 1 == $row->multiple ) {
                            $field_params[ 'options' ][ 'pick_format_type' ] = 'multi';
                            $field_params[ 'options' ][ 'pick_format_multi' ] = 'checkbox';
                            $field_params[ 'options' ][ 'pick_limit' ] = 0;
                        }
                        else {
                            $field_params[ 'options' ][ 'pick_format_type' ] = 'single';
                            $field_params[ 'options' ][ 'pick_format_single' ] = 'dropdown';
                            $field_params[ 'options' ][ 'pick_limit' ] = 1;
                        }
                    }
                    elseif ( 'file' == $field_type ) {
                        $field_params[ 'options' ][ 'file_format_type' ] = 'multi';
                        $field_params[ 'options' ][ 'file_type' ] = 'any';
                    }
                    elseif ( 'number' == $field_type )
                        $field_params[ 'options' ][ 'number_decimals' ] = 2;
                    elseif ( 'desc' == $row->coltype )
                        $field_params[ 'options' ][ 'wysiwyg_editor' ] = 'tinymce';
                    elseif ( 'text' == $field_type )
                        $field_params[ 'options' ][ 'text_max_length' ] = 128;

                    $fields[] = $field_params;

                    $weight++;
                }

                $pod_type->name = pods_sanitize( pods_clean_name( $pod_type->name ) );

                $pod_params = array(
                    'name' => $pod_type->name,
                    'label' => $pod_type->label,
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
                        'pod_index' => 'name',
                        '_pods_1x_pod_id' => $pod_type->id
                    ),
                );

                if ( empty( $pod_params[ 'label' ] ) )
                    $pod_params[ 'label' ] = ucwords( str_replace( '_', ' ', $pod_params[ 'name' ] ) );

                $pod_id = $this->api->save_pod( $pod_params );

                if ( 0 < $pod_id )
                    $last_id = $pod_type->id;
                else
                    pods_error( 'Error: ' . $pod_id );
            }
        }

        update_option( 'pods_framework_upgrade_2_0_sister_ids', $sister_ids );

        $this->update_progress( __FUNCTION__, $last_id );

        if ( $migration_limit == count( $pod_types ) )
            return '-2';
        else
            return '1';
    }

    /**
     * @return string
     */
    public function migrate_fields () {
        if ( true === $this->check_progress( __FUNCTION__ ) )
            return '1';

        $sister_ids = (array) get_option( 'pods_framework_upgrade_2_0_sister_ids', array() );

        foreach ( $sister_ids as $old_field_id => $old_sister_id ) {
            $old_field_id = (int) $old_field_id;
            $old_sister_id = (int) $old_sister_id;

            $new_field_id = pods_query( "SELECT `post_id` FROM `@wp_postmeta` WHERE `meta_key` = '_pods_1x_field_id' AND `meta_value` = '{$old_field_id}' LIMIT 1" );

            if ( !empty( $new_field_id ) ) {
                $new_field_id = (int) $new_field_id[ 0 ]->post_id;

                $new_sister_id = pods_query( "SELECT `post_id` FROM `@wp_postmeta` WHERE `meta_key` = '_pods_1x_field_id' AND `meta_value` = '{$old_sister_id}' LIMIT 1" );

                if ( !empty( $new_sister_id ) ) {
                    $new_sister_id = (int) $new_sister_id[ 0 ]->post_id;

                    update_post_meta( $new_field_id, 'sister_id', $new_sister_id );
                }
                else
                    delete_post_meta( $new_field_id, 'sister_id' );
            }
        }

        // We were off the grid, so let's flush and allow for resync
        $this->api->cache_flush_pods();

        $this->update_progress( __FUNCTION__, true );

        return '1';
    }

    /**
     * @return string
     */
    public function migrate_relationships () {
        if ( true === $this->check_progress( __FUNCTION__ ) )
            return '1';

        $migration_limit = (int) apply_filters( 'pods_upgrade_item_limit', 1500 );
        $migration_limit = max( $migration_limit, 100 );

        $last_id = (int) $this->check_progress( __FUNCTION__ );

        $sql = "
            SELECT `r`.*, `p`.`tbl_row_id` AS `real_id`, `p`.`datatype`
            FROM `@wp_pod_rel` AS `r`
            LEFT JOIN `@wp_pod` AS `p` ON `p`.`id` = `r`.`pod_id`
            WHERE {$last_id} < `r`.`id`
                AND `r`.`pod_id` IS NOT NULL
                AND `r`.`field_id` IS NOT NULL
                AND `p`.`id` IS NOT NULL
            ORDER BY `r`.`id`
            LIMIT 0, {$migration_limit}
        ";

        $rel = pods_query( $sql );

        $last_id = true;

        $pod_types = pods_query( "SELECT `id`, `name` FROM `@wp_pod_types` ORDER BY `id`" );

        $types = array();

        $x = 0;

        if ( !empty( $rel ) && !empty( $pod_types ) ) {
            foreach ( $pod_types as $type ) {
                $type->name = pods_clean_name( $type->name );

                $types[ $type->id ] = $this->api->load_pod( array( 'name' => $type->name ), false );

                if ( empty( $types[ $type->id ] ) )
                    return pods_error( sprintf( __( 'Pod <strong>%s</strong> not found, relationships cannot be migrated', 'pods' ), $type->name ) );

                $pod_fields = pods_query( "SELECT `id`, `name` FROM `@wp_pod_fields` WHERE `datatype` = {$type->id} ORDER BY `id`" );

                $types[ $type->id ][ 'old_fields' ] = array();

                foreach ( $pod_fields as $field ) {
                    // Handle name changes
                    if ( in_array( $field->name, array( 'created', 'modified', 'author' ) ) )
                        $field->name .= '2';

                    $types[ $type->id ][ 'old_fields' ][ $field->id ] = $field->name;
                }
            }

            foreach ( $rel as $r ) {
                $r->pod_id = (int) $r->pod_id;

                if ( !isset( $types[ $r->datatype ] ) || !isset( $types[ $r->datatype ][ 'old_fields' ][ $r->field_id ] ) )
                    continue;

                if ( !isset( $types[ $r->datatype ][ 'fields' ][ $types[ $r->datatype ][ 'old_fields' ][ $r->field_id ] ] ) )
                    continue;

                $field = $types[ $r->datatype ][ 'fields' ][ $types[ $r->datatype ][ 'old_fields' ][ $r->field_id ] ];

                if ( !in_array( $field[ 'type' ], array( 'pick', 'file' ) ) )
                    continue;

                $pod_id = $types[ $r->datatype ][ 'id' ];
                $field_id = $field[ 'id' ];
                $item_id = $r->real_id;

                $related_pod_id = 0;
                $related_field_id = 0;
                $related_item_id = $r->tbl_row_id;

                if ( 'pick' == $field[ 'type' ] ) {
                    $old_sister_id = (int) pods_var( '_pods_1x_sister_id', $field[ 'options' ], 0 );

                    if ( 0 < $old_sister_id ) {
                        $sql = "
                            SELECT `f`.`id`, `f`.`name`, `t`.`name` AS `pod`
                            FROM `@wp_pod_fields` AS `f`
                            LEFT JOIN `@wp_pod_types` AS `t` ON `t`.`id` = `f`.`datatype`
                            WHERE `f`.`id` = " . $old_sister_id . " AND `t`.`id` IS NOT NULL
                            ORDER BY `f`.`id`
                            LIMIT 1
                        ";

                        $old_field = pods_query( $sql );

                        if ( empty( $old_field ) )
                            continue;

                        $old_field = $old_field[ 0 ];

                        $related_field = $this->api->load_field( array( 'name' => $old_field->name, 'pod' => $old_field->pod ) );

                        if ( empty( $related_field ) )
                            continue;

                        $related_pod_id = $related_field[ 'pod_id' ];
                        $related_field_id = $related_field[ 'id' ];
                    }
                    elseif ( 'pod' == $field[ 'pick_object' ] && 0 < strlen( $field[ 'pick_val' ] ) ) {
                        $related_pod = $this->api->load_pod( array( 'name' => $field[ 'pick_val' ] ), false );

                        if ( empty( $related_pod ) )
                            continue;

                        $related_pod_id = $related_pod[ 'id' ];
                    }
                }

                $r->id = (int) $r->id;
                $pod_id = (int) $pod_id;
                $field_id = (int) $field_id;
                $item_id = (int) $item_id;
                $related_pod_id = (int) $related_pod_id;
                $related_field_id = (int) $related_field_id;
                $related_item_id = (int) $related_item_id;
                $r->weight = (int) $r->weight;

                $table_data = array(
                    'id' => $r->id,
                    'pod_id' => $pod_id,
                    'field_id' => $field_id,
                    'item_id' => $item_id,
                    'related_pod_id' => $related_pod_id,
                    'related_field_id' => $related_field_id,
                    'related_item_id' => $related_item_id,
                    'weight' => $r->weight,
                );

                $table_formats = array_fill( 0, count( $table_data ), '%d' );

                $sql = PodsData::insert_on_duplicate( "@wp_podsrel", $table_data, $table_formats );

                pods_query( $sql );

                $last_id = $r->id;

                $x++;

                if ( 10 < $x ) {
                    $this->update_progress( __FUNCTION__, $last_id );

                    $x = 0;
                }
            }
        }

        $this->update_progress( __FUNCTION__, $last_id );

        if ( $migration_limit == count( $rel ) )
            return '-2';
        else
            return '1';
    }

    /**
     * @return string
     */
    public function migrate_settings () {
        return $this->migrate_roles();
    }

    /**
     * @return string
     */
    public function migrate_roles () {
        if ( true === $this->check_progress( __FUNCTION__ ) )
            return '1';

        /**
         * @var $wpdb WPDB
         */
        global $wpdb;

        $wp_roles = get_option( "{$wpdb->prefix}user_roles" );

        $old_roles = get_option( 'pods_roles' );

        if ( !is_array( $old_roles ) && !empty( $old_roles ) )
            $old_roles = @unserialize( $old_roles );

        if ( !is_array( $old_roles ) )
            $old_roles = array();

        if ( !empty( $old_roles ) ) {
            foreach ( $old_roles as $role => $data ) {
                if ( $role == '_wpnonce' )
                    continue;

                if ( !isset( $wp_roles[ $role ] ) )
                    continue;

                $caps = $wp_roles[ $role ][ 'capabilities' ];

                foreach ( $data as $cap ) {
                    if ( 0 === strpos( 'manage_', $cap ) ) {
                        if ( in_array( $cap, array( 'manage_roles' ) ) )
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

        $this->update_progress( __FUNCTION__, true );

        return '1';
    }

    /**
     * @return array|string
     */
    public function migrate_templates () {
        if ( true === $this->check_progress( __FUNCTION__ ) )
            return '1';

        $templates = pods_query( "SELECT * FROM `@wp_pod_templates`", false );

        $results = array();

        if ( !empty( $templates ) ) {
            foreach ( $templates as $template ) {
                unset( $template->id );

                $results[] = $this->api->save_template( $template );
            }
        }

        $this->update_progress( __FUNCTION__, true );

        return '1';
    }

    /**
     * @return array|string
     */
    public function migrate_pages () {
        if ( true === $this->check_progress( __FUNCTION__ ) )
            return '1';

        $pages = pods_query( "SELECT * FROM `@wp_pod_pages`", false );

        if ( !empty( $pages ) ) {
            foreach ( $pages as $page ) {
                unset( $page->id );

                $this->api->save_page( $page );
            }
        }

        $this->update_progress( __FUNCTION__, true );

        return '1';
    }

    /**
     * @return array|string
     */
    public function migrate_helpers () {
        if ( true === $this->check_progress( __FUNCTION__ ) )
            return '1';

        $helpers = pods_query( "SELECT * FROM `@wp_pod_helpers`", false );

        $notice = false;

        if ( !empty( $helpers ) ) {
            foreach ( $helpers as $helper ) {
                unset( $helper->id );

                if ( 'input' == $helper->helper_type ) {
                    $helper->status = 'draft';

                    $notice = true;
                }

                $this->api->save_helper( $helper );
            }
        }

        $this->update_progress( __FUNCTION__, true );

        if ( $notice )
            return pods_error( __( 'Input Helpers may not function in our new forms, we have imported and disabled them for your review.', 'pods' ) );

        return '1';
    }

    /**
     * @param $params
     *
     * @return mixed|string|void
     */
    public function migrate_pod ( $params ) {
        /**
         * @var $wpdb WPDB
         */
        global $wpdb;

        if ( !isset( $params->pod ) )
            return pods_error( __( 'Invalid Pod.', 'pods' ) );

        $pod = pods_sanitize( pods_clean_name( $params->pod ) );

        if ( !in_array( "{$wpdb->prefix}pod_tbl_{$pod}", $this->tables ) )
            return pods_error( __( 'Table not found, items cannot be migrated', 'pods' ) );

        if ( !in_array( "{$wpdb->prefix}pods_{$pod}", $this->tables ) )
            return pods_error( __( 'New table not found, items cannot be migrated', 'pods' ) );

        if ( !in_array( "{$wpdb->prefix}pod_types", $this->tables ) )
            return pods_error( __( 'Pod Types table not found, items cannot be migrated', 'pods' ) );

        if ( !in_array( "{$wpdb->prefix}pod", $this->tables ) )
            return pods_error( __( 'Pod table not found, items cannot be migrated', 'pods' ) );

        if ( true === $this->check_progress( __FUNCTION__, $pod ) )
            return '1';

        $pod_data = $this->api->load_pod( array( 'name' => $pod ), false );

        if ( empty( $pod_data ) )
            return pods_error( sprintf( __( 'Pod <strong>%s</strong> not found, items cannot be migrated', 'pods' ), $pod ) );

        $columns = array();
        $old_columns = array();

        foreach ( $pod_data[ 'fields' ] as $field ) {
            if ( !in_array( $field[ 'name' ], array( 'created', 'modified', 'author' ) ) && !in_array( $field[ 'type' ], array( 'file', 'pick' ) ) ) {
                $columns[] = pods_sanitize( $field[ 'name' ] );
                $old_columns[] = pods_var( '_pods_1x_field_name', $field[ 'options' ], $field[ 'name' ], null, false );
            }
        }

        $into = '`id`';
        $select = '`t`.`id`';

        if ( !empty( $columns ) ) {
            $into .= ', `' . implode( '`, `', $columns ) . '`';
            $select .= ', `t`.`' . implode( '`, `t`.`', $old_columns ) . '`';
        }

        // Copy content from the old table into the new
        $sql = "
            REPLACE INTO `@wp_pods_{$pod}`
                ( {$into} )
                ( SELECT {$select}
                  FROM `@wp_pod_tbl_{$pod}` AS `t` )
        ";

        pods_query( $sql );

        // Copy index data from the old index table into the new individual table
        $sql = "
            UPDATE `@wp_pods_{$pod}` AS `t`
            LEFT JOIN `@wp_pod_types` AS `x` ON `x`.`name` = '{$pod}'
            LEFT JOIN `@wp_pod` AS `p` ON `p`.`datatype` = `x`.`id` AND `p`.`tbl_row_id` = `t`.`id`
            SET `t`.`created` = `p`.`created`, `t`.`modified` = `p`.`modified`
            WHERE `x`.`id` IS NOT NULL AND `p`.`id` IS NOT NULL
        ";

        pods_query( $sql );

        // Copy name data from the old index table into the new individual table (if name empty in indiv table)
        $sql = "
            UPDATE `@wp_pods_{$pod}` AS `t`
            LEFT JOIN `@wp_pod_types` AS `x` ON `x`.`name` = '{$pod}'
            LEFT JOIN `@wp_pod` AS `p` ON `p`.`datatype` = `x`.`id` AND `p`.`tbl_row_id` = `t`.`id`
            SET `t`.`name` = `p`.`name`
            WHERE ( `t`.`name` IS NULL OR `t`.`name` = '' ) AND `x`.`id` IS NOT NULL AND `p`.`id` IS NOT NULL
        ";

        pods_query( $sql );

        $this->update_progress( __FUNCTION__, true, $pod );

        return '1';
    }

    /**
     * @return string
     */
    public function migrate_cleanup () {
        update_option( 'pods_framework_upgraded_1_x', 1 );

        $oldget = $_GET;

        $_GET[ 'toggle' ] = 1;

        PodsInit::$components->toggle( 'templates' );
        PodsInit::$components->toggle( 'pages' );
        PodsInit::$components->toggle( 'helpers' );

        $_GET = $oldget;

        $this->api->cache_flush_pods();

        return '1';
    }

    /**
     *
     */
    public function restart () {
        /**
         * @var $wpdb WPDB
         */
        global $wpdb;

        foreach ( $this->tables as $table ) {
            if ( false !== strpos( $table, "{$wpdb->prefix}pods" ) )
                pods_query( "TRUNCATE `{$table}`", false );
        }

        delete_option( 'pods_framework_upgrade_2_0' );
        delete_option( 'pods_framework_upgrade_2_0_sister_ids' );
        delete_option( 'pods_framework_upgraded_1_x' );
    }

    /**
     *
     */
    public function cleanup () {
        /**
         * @var $wpdb WPDB
         */
        global $wpdb;

        foreach ( $this->tables as $table ) {
            if ( false !== strpos( $table, "{$wpdb->prefix}pod_" ) || "{$wpdb->prefix}pod" == $table )
                pods_query( "DROP TABLE `{$table}`", false );
        }

        delete_option( 'pods_roles' );
        delete_option( 'pods_version' );
        delete_option( 'pods_framework_upgrade_2_0' );
        delete_option( 'pods_framework_upgrade_2_0_sister_ids' );
        delete_option( 'pods_framework_upgraded_1_x' );

        delete_option( 'pods_disable_file_browser' );
        delete_option( 'pods_files_require_login' );
        delete_option( 'pods_files_require_login_cap' );
        delete_option( 'pods_disable_file_upload' );
        delete_option( 'pods_upload_require_login' );
        delete_option( 'pods_upload_require_login_cap' );

        pods_query( "DELETE FROM `@wp_postmeta` WHERE `meta_key` LIKE '_pods_1x_%'" );
    }
}