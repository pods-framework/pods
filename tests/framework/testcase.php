<?php
namespace Pods_Unit_Tests;

require dirname( __FILE__ ) . '/factory.php';

class Pods_UnitTestCase extends \WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
		$this->factory = new Pods_UnitTest_Factory;
	}

	public function clean_up_global_scope() {
		parent::clean_up_global_scope();
	}

	public function assertPreConditions() {
		parent::assertPreConditions();
	}

	public function go_to( $url ) {
		$GLOBALS['_SERVER']['REQUEST_URI'] = $url = str_replace( network_home_url(), '', $url );

		$_GET = $_POST = array();

		foreach ( array( 'query_string', 'id', 'postdata', 'authordata', 'day', 'currentmonth', 'page', 'pages', 'multipage', 'more', 'numpages', 'pagenow' ) as $v ) {
			if ( isset( $GLOBALS[ $v ] ) ) unset( $GLOBALS[ $v ] );
		}

		$parts = parse_url($url);

		if ( isset( $parts['scheme'] ) ) {
			$req = $parts['path'];
			if ( isset( $parts['query'] ) ) {
				$req .= '?' . $parts['query'];
				parse_str( $parts['query'], $_GET );
			}
		} else {
			$req = $url;
		}

		if ( ! isset( $parts['query'] ) ) {
			$parts['query'] = '';
		}

		// Scheme
		if ( 0 === strpos( $req, '/wp-admin' ) && force_ssl_admin() ) {
			$_SERVER['HTTPS'] = 'on';
		} else {
			unset( $_SERVER['HTTPS'] );
		}

		$_SERVER['REQUEST_URI'] = $req;
		unset($_SERVER['PATH_INFO']);

		$this->flush_cache();

		unset($GLOBALS['wp_query'], $GLOBALS['wp_the_query']);

		$GLOBALS['wp_the_query'] =& new WP_Query();
		$GLOBALS['wp_query'] =& $GLOBALS['wp_the_query'];
		$GLOBALS['wp'] =& new WP();

		foreach ( $GLOBALS['wp']->public_query_vars as $v ) {
			unset( $GLOBALS[ $v ] );
		}
		foreach ( $GLOBALS['wp']->private_query_vars as $v ) {
			unset( $GLOBALS[ $v ] );
		}

		$GLOBALS['wp']->main( $parts['query'] );
	}

	public function set_current_user( $user_id ) {
		wp_set_current_user( $user_id );
	}
}