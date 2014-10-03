<?php
namespace Pods_Unit_Tests;

/**
 * @group pods_ajax
 */
class Tests_AJAX extends Pods_UnitTestCase {
	protected $_post = null;

	protected $_last_response;

	public function setUp() {
		parent::setUp();

		$_actions = array(
			'pods_remove_from_cart', 'pods_add_to_cart', 'pods_apply_discount', 'checkout_login',
			'checkout_register', 'get_download_title', 'pods_local_tax_opt_in', 'pods_local_tax_opt_out',
			'pods_check_for_download_price_variations'
		);

		foreach ( $_actions as $action ) {
			if ( function_exists( 'wp_ajax_' . $action ) )
				add_action( 'wp_ajax_' . $action, $action, 1 );
		}

		if ( ! defined( 'DOING_AJAX' ) )
			define( 'DOING_AJAX', true );
		set_current_screen( 'ajax' );

		add_action( 'clear_auth_cookie', array( $this, 'logout' ) );

		$this->factory->post->create_many( 5 );

		error_reporting( 0 & ~E_WARNING );
	}

	public function tearDown() {
		parent::tearDown();
		$_POST = array();
		remove_filter( 'wp_die_ajax_handler', array( $this, 'getDieHandler' ), 1, 1 );
		remove_action( 'clear_auth_cookie', array( $this, 'logout' ) );
		set_current_screen( 'front' );
		Pods()->session->set( 'pods_cart', null );
	}

	public function logout() {
		unset( $GLOBALS['current_user'] );
		$cookies = array(AUTH_COOKIE, SECURE_AUTH_COOKIE, LOGGED_IN_COOKIE, USER_COOKIE, PASS_COOKIE);
		foreach ( $cookies as $c )
			unset( $_COOKIE[ $c ] );
	}

	protected function _setRole( $role ) {
		$post = $_POST;
		$user_id = $this->factory->user->create( array( 'role' => $role ) );
		wp_set_current_user( $user_id );
		$_POST = array_merge( $_POST, $post );
	}

	protected function _handleAjax($action) {
		// Start output buffering
		ini_set( 'implicit_flush', false );
		ob_start();

		// Build the request
		$_POST['action'] = $action;
		$_REQUEST = $_POST;

		// Call the hooks
		do_action( 'wp_ajax_' . $_REQUEST['action'] );

		// Save the output
		$buffer = ob_get_clean();

		if ( ! empty( $buffer ) )
			$this->_last_response = $buffer;

		return $buffer;
	}

	public function test_add_item_to_cart() {
		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'publish' ) );

		$_variable_pricing = array(
			array(
				'name' => 'Simple',
				'amount' => 20
			),
			array(
				'name' => 'Advanced',
				'amount' => 100
			)
		);

		$_download_files = array(
			array(
				'name' => 'File 1',
				'file' => 'http://localhost/file1.jpg',
				'condition' => 0
			),
			array(
				'name' => 'File 2',
				'file' => 'http://localhost/file2.jpg',
				'condition' => 'all'
			)
		);

		$meta = array(
			'pods_price' => '0.00',
			'_variable_pricing' => 1,
			'_pods_price_options_mode' => 'on',
			'pods_variable_prices' => array_values( $_variable_pricing ),
			'pods_download_files' => array_values( $_download_files ),
			'_pods_download_limit' => 20,
			'_pods_hide_purchase_link' => 1,
			'pods_product_notes' => 'Purchase Notes',
			'_pods_product_type' => 'default',
			'_pods_download_earnings' => 129.43,
			'_pods_download_sales' => 59,
			'_pods_download_limit_override_1' => 1
		);
		foreach( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		$this->_setRole( 'administrator' );

		$_POST = array(
			'download_id' => $post_id,
			'variable_price' => 'yes',
			'price_mode' => 'single',
			'price_ids' => array(
				1
			),
			'nonce' => wp_create_nonce( 'pods_ajax_nonce' ),
		);

		$out = $this->_handleAjax( 'pods_add_to_cart' );
		$this->markTestIncomplete( 'This test needs to be rewritten per #600.');
	}

	public function test_remove_item_from_cart() {
		$this->_setRole( 'administrator' );

		$_POST = array(
			'cart_item' => 0,
			'nonce' => wp_create_nonce( 'pods_ajax_nonce' ),
		);

		$this->assertEquals( '{"removed":1,"subtotal":"$0.00"}', $this->_handleAjax( 'pods_remove_from_cart' ) );
	}

	public function test_checkout_register_fields() {
		$this->_handleAjax( 'nopriv_checkout_register' );
		$this->markTestIncomplete( 'This test needs to be rewritten per #600.');
	}

	public function test_get_download_title() {
		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'publish' ) );
		$_POST = array(
			'download_id' => $post_id
		);
		$this->_handleAjax( 'pods_get_download_title' );
		$this->assertEquals( 'Test Download', $this->_last_response );
	}

	public function test_check_for_download_price_variations() {
		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'draft' ) );

		$_variable_pricing = array(
			array(
				'name' => 'Simple',
				'amount' => 20
			),
			array(
				'name' => 'Advanced',
				'amount' => 100
			)
		);

		$_download_files = array(
			array(
				'name' => 'File 1',
				'file' => 'http://localhost/file1.jpg',
				'condition' => 0
			),
			array(
				'name' => 'File 2',
				'file' => 'http://localhost/file2.jpg',
				'condition' => 'all'
			)
		);

		$meta = array(
			'pods_price' => '0.00',
			'_variable_pricing' => 1,
			'_pods_price_options_mode' => 'on',
			'pods_variable_prices' => array_values( $_variable_pricing ),
			'pods_download_files' => array_values( $_download_files ),
			'_pods_download_limit' => 20,
			'_pods_hide_purchase_link' => 1,
			'pods_product_notes' => 'Purchase Notes',
			'_pods_product_type' => 'default',
			'_pods_download_earnings' => 129.43,
			'_pods_download_sales' => 59,
			'_pods_download_limit_override_1' => 1
		);
		foreach( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		$_POST = array(
			'nonce' => wp_create_nonce( 'pods_add_downloads_to_purchase_nonce' ),
			'download_id' => $post_id
		);
		$this->_handleAjax( 'pods_check_for_download_price_variations' );

		$expected = '<select class="pods_price_options_select pods-select pods-select"><option value="0">Simple</option><option value="1">Advanced</option></select>';
		$this->assertEquals( $expected, $this->_last_response );
	}
}
