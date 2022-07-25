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

		/**
		 * Allow filtering of whether the Pods SQL installation should be run. Return false to bypass.
		 *
		 * @param bool $run Whether the Pods SQL installation should be run.
		 */
		$run = apply_filters( 'pods_install_run', true, PODS_VERSION, $pods_version, $_blog_id );

		if ( false !== $run && ! pods_tableless() && 0 === (int) pods_v( 'pods_bypass_install' ) ) {
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
			$sql = array_map( 'trim', $sql );
			$sql = array_filter( $sql );

			foreach ( $sql as $query ) {
				pods_query( $query, 'Cannot setup SQL tables' );
			}

			// Auto activate component.
			if ( ! PodsInit::$components ) {
				if ( ! pods_light() ) {
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
	 * Handle dbDelta for Pods tables.
	 *
	 * @since 2.8.9
	 */
	public function delta_tables() {
		global $wpdb;

		if ( pods_tableless() ) {
			return;
		}

		$pods_version = get_option( 'pods_version' );

		$sql = file_get_contents( PODS_DIR . 'sql/dump.sql' );
		$sql = apply_filters( 'pods_install_sql', $sql, PODS_VERSION, $pods_version, get_current_blog_id() );

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

		// Remove empty lines and queries.
		$sql = array_map( 'trim', $sql );
		$sql = array_filter( $sql );

		// dbDelta will handle what we need.
		$sql = str_replace( 'CREATE TABLE IF NOT EXISTS', 'CREATE TABLE', $sql );

		if ( empty( $sql ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		pods_debug( $sql );

		pods_debug( dbDelta( $sql ) );
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
	 * @param null $x
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
	 * @param null $x
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
