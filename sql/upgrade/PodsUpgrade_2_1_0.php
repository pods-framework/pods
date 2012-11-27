<?php
/**
 * @package Pods\Upgrade
 */
class PodsUpgrade_2_1_0 extends PodsUpgrade {

    /**
     * @var string
     */
    protected $version = '2.1.0';

    /**
     *
     */
    function __construct () {
    }

    /**
     * @return array|bool|int|mixed|null|void
     */
    public function prepare_relationships () {
        /**
         * @var $wpdb WPDB
         */
        global $wpdb;

        if ( !in_array( "{$wpdb->prefix}pod_fields", $this->tables ) )
            return pods_error( __( 'Table not found, it cannot be migrated', 'pods' ) );

        $count = pods_query( "SELECT COUNT(*) AS `count` FROM `@wp_pod_rel`", false );

        if ( !empty( $count ) )
            $count = (int) $count[ 0 ]->count;
        else
            $count = 0;

        return $count;
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
                $types[ $type->id ] = $this->api->load_pod( array( 'name' => $type->name ) );

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
    public function migrate_cleanup () {
        update_option( 'pods_framework_upgraded_2_1', 1 );

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

        delete_option( 'pods_framework_upgrade_2_1' );
        delete_option( 'pods_framework_upgraded_2_1' );
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
        delete_option( 'pods_framework_upgrade_2_1' );
        delete_option( 'pods_framework_upgraded_2_1' );

        delete_option( 'pods_disable_file_browser' );
        delete_option( 'pods_files_require_login' );
        delete_option( 'pods_files_require_login_cap' );
        delete_option( 'pods_disable_file_upload' );
        delete_option( 'pods_upload_require_login' );
        delete_option( 'pods_upload_require_login_cap' );

        pods_query( "DELETE FROM `@wp_postmeta` WHERE `meta_key` LIKE '_pods_1x_%'" );
    }
}
