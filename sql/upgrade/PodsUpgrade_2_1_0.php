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

        if ( !defined( 'PODS_TABLELESS' ) || !PODS_TABLELESS ) {
            if ( !in_array( "{$wpdb->prefix}podsrel", $this->tables ) )
                return pods_error( __( 'Table not found, it cannot be migrated', 'pods' ) );

            $count = pods_query( "SELECT COUNT(*) AS `count` FROM `@wp_podsrel`", false );

            if ( !empty( $count ) )
                $count = (int) $count[ 0 ]->count;
            else
                $count = 0;
        }
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

        // get list of all relationship fields

        // loop through all relationship field meta for items that are pods

        // if serialized (or array), save as individual meta items and save new order meta key

        $last_id = true;

        $rel = array();

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
        $this->upgraded();

        $this->api->cache_flush_pods();

        return '1';
    }

    /**
     *
     */
    public function restart () {
        $upgraded = get_option( 'pods_framework_upgraded' );

        if ( empty( $upgraded ) || !is_array( $upgraded ) )
            $upgraded = array();

        delete_option( 'pods_framework_upgrade_' . str_replace( '.', '_', $this->version ) );

        if ( in_array( $this->version, $upgraded ) )
            unset( $upgraded[ array_search( $this->version, $upgraded ) ] );

        update_option( 'pods_framework_upgraded', $upgraded );
    }

    /**
     *
     */
    public function cleanup () {
        $this->restart();
    }
}
