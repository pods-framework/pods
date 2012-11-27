<?php
/**
 * @package Pods\Upgrade
 */
class PodsUpgrade {

    /**
     * @var array
     */
    public $tables = array();

    /**
     * @var array
     */
    protected $progress = array();

    /**
     * @var PodsAPI
     */
    protected $api = null;

    /**
     *
     */
    function __construct () {
        $this->api = pods_api();

        $this->get_tables();
        $this->get_progress();
    }

    /**
     *
     */
    function get_tables () {
        /**
         * @var $wpdb WPDB
         */
        global $wpdb;

        $tables = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}pod%'", ARRAY_N );

        if ( !empty( $tables ) ) {
            foreach ( $tables as $table ) {
                $this->tables[] = $table[ 0 ];
            }
        }
    }

    /**
     *
     */
    function get_progress () {
        $methods = get_class_methods( $this );

        foreach ( $methods as $method ) {
            if ( 0 === strpos( $method, 'migrate_' ) )
                $this->progress[ str_replace( 'migrate_', '', $method ) ] = false;
        }

        $progress = (array) get_option( 'pods_framework_upgrade_2_0', array() );

        if ( !empty( $progress ) )
            $this->progress = array_merge( $this->progress, $progress );
    }

    /**
     *
     */
    function install () {
        /**
         * @var $wpdb WPDB
         */
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

    /**
     * @param $params
     *
     * @return mixed|void
     */
    public function ajax ( $params ) {
        if ( !isset( $params->step ) )
            return pods_error( __( 'Invalid upgrade process.', 'pods' ) );

        if ( !isset( $params->type ) )
            return pods_error( __( 'Invalid upgrade method.', 'pods' ) );

        if ( !method_exists( $this, $params->step . '_' . $params->type ) )
            return pods_error( __( 'Upgrade method not found.', 'pods' ) );

        return call_user_func( array( $this, $params->step . '_' . $params->type ), $params );
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

        /*
         * other options maybe not in 2.0
        delete_option( 'pods_page_precode_timing' );
        */
    }

    /**
     * @param $method
     * @param $v
     * @param null $x
     */
    public function update_progress ( $method, $v, $x = null ) {
        $method = str_replace( 'migrate_', '', $method );

        if ( null !== $x )
            $this->progress[ $method ][ $x ] = (boolean) $v;
        else
            $this->progress[ $method ] = $v;

        update_option( 'pods_framework_upgrade_2_0', $this->progress );
    }

    /**
     * @param $method
     * @param null $x
     *
     * @return bool
     */
    public function check_progress ( $method, $x = null ) {
        $method = str_replace( 'migrate_', '', $method );

        if ( isset( $this->progress[ $method ] ) ) {
            if ( null === $x )
                return $this->progress[ $method ];
            elseif ( isset( $this->progress[ $method ][ $x ] ) )
                return (boolean) $this->progress[ $method ][ $x ];
        }

        return false;
    }
}

function pods_upgrade ( $version ) {
    $class_name = str_replace( '.', '_', $version );
    $class_name = "PodsUpgrade_{$class_name}";

    if ( !class_exists( $class_name ) ) {
        $file = basename( $class_name ) . '.php';

        if ( ( 0 === strpos( $file, untrailingslashit( WP_CONTENT_DIR ) ) || 0 === strpos( $file, untrailingslashit( ABSPATH ) ) ) && file_exists( $file ) )
            include_once $file;
    }

    $class = false;

    if ( class_exists( $class_name ) )
        $class = new $class_name();

    return $class;
}