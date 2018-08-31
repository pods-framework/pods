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
	 * @var string
	 */
	protected $version = null;

	/**
	 *
	 */
	public function __construct() {

		$this->api = pods_api();

		$this->get_tables();
		$this->get_progress();
	}

	/**
	 * @param null $_blog_id Blog ID to install.
	 */
	public function install( $_blog_id = null ) {

		/**
		 * @var $wpdb WPDB
		 */
		global $wpdb;

		// Switch DB table prefixes
		if ( null !== $_blog_id && $_blog_id !== $wpdb->blogid ) {
			switch_to_blog( pods_absint( $_blog_id ) );
		} else {
			$_blog_id = null;
		}

		$pods_version = get_option( 'pods_version' );

		do_action( 'pods_install', PODS_VERSION, $pods_version, $_blog_id );

		if ( ( ! pods_tableless() ) && false !== apply_filters( 'pods_install_run', null, PODS_VERSION, $pods_version, $_blog_id ) && 0 === (int) pods_v( 'pods_bypass_install' ) ) {
			$sql = file_get_contents( PODS_DIR . 'sql/dump.sql' );
			$sql = apply_filters( 'pods_install_sql', $sql, PODS_VERSION, $pods_version, $_blog_id );

			$charset_collate = 'DEFAULT CHARSET utf8';

			if ( ! empty( $wpdb->charset ) ) {
				$charset_collate = "DEFAULT CHARSET {$wpdb->charset}";
			}

			if ( ! empty( $wpdb->collate ) ) {
				$charset_collate .= " COLLATE {$wpdb->collate}";
			}

			if ( 'DEFAULT CHARSET utf8' !== $charset_collate ) {
				$sql = str_replace( 'DEFAULT CHARSET utf8', $charset_collate, $sql );
			}

			$sql = explode( ";\n", str_replace( array( "\r", 'wp_' ), array( "\n", $wpdb->prefix ), $sql ) );

			$z = count( $sql );
			for ( $i = 0; $i < $z; $i ++ ) {
				$query = trim( $sql[ $i ] );

				if ( empty( $query ) ) {
					continue;
				}

				pods_query( $query, 'Cannot setup SQL tables' );
			}

			// Auto activate component.
			if ( ! PodsInit::$components ) {
				if ( ! defined( 'PODS_LIGHT' ) || ! PODS_LIGHT ) {
					PodsInit::$components = pods_components();
				}
			}

			if ( PodsInit::$components ) {
				PodsInit::$components->activate_component( 'templates' );
			}
		}//end if

		do_action( 'pods_install_post', PODS_VERSION, $pods_version, $_blog_id );
	}

	/**
	 *
	 */
	public function get_tables() {

		/**
		 * @var $wpdb WPDB
		 */
		global $wpdb;

		$tables = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}pod%'", ARRAY_N );

		if ( ! empty( $tables ) ) {
			foreach ( $tables as $table ) {
				$this->tables[] = $table[0];
			}
		}
	}

	/**
	 *
	 */
	public function get_progress() {

		$methods = get_class_methods( $this );

		foreach ( $methods as $method ) {
			if ( 0 === strpos( $method, 'migrate_' ) ) {
				$this->progress[ str_replace( 'migrate_', '', $method ) ] = false;
			}
		}

		$progress = (array) get_option( 'pods_framework_upgrade_' . str_replace( '.', '_', $this->version ), array() );

		if ( ! empty( $progress ) ) {
			$this->progress = array_merge( $this->progress, $progress );
		}
	}

	/**
	 * @param $params
	 *
	 * @return mixed|void
	 */
	public function ajax( $params ) {

		if ( ! isset( $params->step ) ) {
			return pods_error( __( 'Invalid upgrade process.', 'pods' ) );
		}

		if ( ! isset( $params->type ) ) {
			return pods_error( __( 'Invalid upgrade method.', 'pods' ) );
		}

		if ( ! method_exists( $this, $params->step . '_' . $params->type ) ) {
			return pods_error( __( 'Upgrade method not found.', 'pods' ) );
		}

		return call_user_func( array( $this, $params->step . '_' . $params->type ), $params );
	}

	/**
	 * @param      $method
	 * @param      $v
	 * @param null   $x
	 */
	public function update_progress( $method, $v, $x = null ) {

		if ( empty( $this->version ) ) {
			return;
		}

		$method = str_replace( 'migrate_', '', $method );

		if ( null !== $x ) {
			$this->progress[ $method ][ $x ] = (boolean) $v;
		} else {
			$this->progress[ $method ] = $v;
		}

		update_option( 'pods_framework_upgrade_' . str_replace( '.', '_', $this->version ), $this->progress );
	}

	/**
	 * @param      $method
	 * @param null   $x
	 *
	 * @return bool
	 */
	public function check_progress( $method, $x = null ) {

		$method = str_replace( 'migrate_', '', $method );

		if ( isset( $this->progress[ $method ] ) ) {
			if ( null === $x ) {
				return $this->progress[ $method ];
			} elseif ( isset( $this->progress[ $method ][ $x ] ) ) {
				return (boolean) $this->progress[ $method ][ $x ];
			}
		}

		return false;
	}

	/**
	 *
	 */
	public function upgraded() {

		if ( empty( $this->version ) ) {
			return;
		}

		$upgraded = get_option( 'pods_framework_upgraded' );

		if ( empty( $upgraded ) || ! is_array( $upgraded ) ) {
			$upgraded = array();
		}

		delete_option( 'pods_framework_upgrade_' . str_replace( '.', '_', $this->version ) );

		if ( ! in_array( $this->version, $upgraded, true ) ) {
			$upgraded[] = $this->version;
		}

		update_option( 'pods_framework_upgraded', $upgraded );
	}

	/**
	 *
	 */
	public function cleanup() {

		/**
		 * @var $wpdb WPDB
		 */
		global $wpdb;

		foreach ( $this->tables as $table ) {
			if ( false !== strpos( $table, "{$wpdb->prefix}pod_" ) || "{$wpdb->prefix}pod" === $table ) {
				pods_query( "DROP TABLE `{$table}`", false );
			}
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
