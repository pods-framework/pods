<?php
namespace Pods_Unit_Tests;

/**
 * @group pods_shortcode
 */
class Tests_Shortcode extends Pods_UnitTestCase {

	protected $_payment_id = null;

	protected $_post = null;

	protected $_payment_key = null;

	public function setUp() {
		parent::setUp();

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

		$this->_post = get_post( $post_id );

		/** Generate some sales */
		$user = get_userdata(1);

		$user_info = array(
			'id' => $user->ID,
			'email' => $user->user_email,
			'first_name' => $user->first_name,
			'last_name' => $user->last_name,
			'discount' => 'none'
		);

		$download_details = array(
			array(
				'id' => $this->_post->ID,
				'options' => array(
					'price_id' => 1
				)
			)
		);

		$price = '100.00';

		$total = 0;

		$prices = get_post_meta($download_details[0]['id'], 'pods_variable_prices', true);
		$item_price = $prices[1]['amount'];

		$total += $item_price;

		$cart_details = array(
			array(
				'name' => 'Test Download',
				'id' => $this->_post->ID,
				'item_number' => array(
					'id' => $this->_post->ID,
					'options' => array(
						'price_id' => 1
					)
				),
				'price' =>  100,
				'quantity' => 1
			)
		);

		$purchase_data = array(
			'price' => number_format( (float) $total, 2 ),
			'date' => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
			'purchase_key' => strtolower( md5( uniqid() ) ),
			'user_email' => $user_info['email'],
			'user_info' => $user_info,
			'currency' => 'USD',
			'downloads' => $download_details,
			'cart_details' => $cart_details,
			'status' => 'complete'
		);

		$_SERVER['REMOTE_ADDR'] = '10.0.0.0';
		$_SERVER['SERVER_NAME'] = 'pods_virtual';

		$this->_payment_id = pods_insert_payment( $purchase_data );

		update_post_meta( $this->_payment_id, '_pods_payment_user_id', $user->ID );

		$this->_payment_key = $purchase_data['purchase_key'];
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_shortcodes_are_registered() {
		global $shortcode_tags;

		$this->assertArrayHasKey( 'purchase_link', $shortcode_tags );
		$this->assertArrayHasKey( 'download_history', $shortcode_tags );
		$this->assertArrayHasKey( 'purchase_history', $shortcode_tags );
		$this->assertArrayHasKey( 'download_checkout', $shortcode_tags );
		$this->assertArrayHasKey( 'download_cart', $shortcode_tags );
		$this->assertArrayHasKey( 'pods_login', $shortcode_tags );
		$this->assertArrayHasKey( 'download_discounts', $shortcode_tags );
		$this->assertArrayHasKey( 'purchase_collection', $shortcode_tags );
		$this->assertArrayHasKey( 'downloads', $shortcode_tags );
		$this->assertArrayHasKey( 'pods_price', $shortcode_tags );
		$this->assertArrayHasKey( 'pods_receipt', $shortcode_tags );
		$this->assertArrayHasKey( 'pods_profile_editor', $shortcode_tags );
	}

	public function test_download_history() {
		$this->assertInternalType( 'string', pods_download_history( array() ) );
		$this->assertContains( '<table id="pods_user_history">', pods_download_history( array() ) );
	}

	public function test_purchase_history() {
		$this->assertInternalType( 'string', pods_purchase_history( array() ) );
		$this->assertContains( '<table id="pods_user_history">', pods_purchase_history( array() ) );
	}

	public function test_checkout_form_shortcode() {
		$this->assertInternalType( 'string', pods_checkout_form_shortcode() );
		$this->assertContains( '<div id="pods_checkout_wrap">', pods_checkout_form_shortcode() );
	}

	public function test_cart_shortcode() {
		$this->assertInternalType( 'string', pods_cart_shortcode() );
		$this->assertContains( '<ul class="pods-cart">', pods_cart_shortcode() );
	}

	public function test_login_form() {
		$this->assertInternalType( 'string', pods_login_form_shortcode() );
		$this->assertEquals( '<p class="pods-logged-in">You are already logged in</p>', pods_login_form_shortcode() );
	}

	public function test_discounts_shortcode() {
		$post_id = $this->factory->post->create( array( 'post_type' => 'pods_discount', 'post_status' => 'active' ) );

		$meta = array(
			'type' => 'percent',
			'amount' => '20',
			'code' => '20OFF',
			'product_condition' => 'all',
			'start' => '12/12/2000 00:00:00',
			'expiration' => '12/31/2050 23:59:59',
			'max_uses' => 10,
			'uses' => 54,
			'min_price' => 128,
			'is_not_global' => true,
			'is_single_use' => true
		);

		foreach( $meta as $key => $value ) {
			update_post_meta( $post_id, '_pods_discount_' . $key, $value );
		}

		$this->assertInternalType( 'string', pods_discounts_shortcode( array() ) );
		$this->assertEquals( '<ul id="pods_discounts_list"><li class="pods_discount"><span class="pods_discount_name">20OFF</span><span class="pods_discount_separator"> - </span><span class="pods_discount_amount">20%</span></li></ul>', pods_discounts_shortcode( array() ) );
	}

	public function test_purchase_collection_shortcode() {
		$this->assertInternalType( 'string', pods_purchase_collection_shortcode() );
		$this->assertEquals( '<a href="?pods_action=purchase_collection&taxonomy&terms" class="button blue pods-submit">Purchase All Items</a>', pods_purchase_collection_shortcode() );
	}

	public function test_downloads_query() {
		$post_id = $this->factory->post->create( array( 'post_type' => 'download', 'post_status' => 'publish' ) );
		$this->assertInternalType( 'string', pods_downloads_query() );
		$this->assertContains( '<div class="pods_downloads_list', pods_downloads_query() );
		$this->assertContains( '<div class="pods_download_inner">', pods_downloads_query() ); // pods_download_inner will only be found if products were returned successfully
	}

	public function test_download_price_shortcode() {
		$post_id = $this->factory->post->create( array( 'post_type' => 'download' ) );

		$meta = array(
			'pods_price' => '54.43',
		);

		foreach ( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		$this->assertInternalType( 'string', pods_download_price_shortcode( array( 'id' => $post_id ) ) );
		$this->assertEquals( '<span class="pods_price" id="pods_price_'. $post_id .'">&#36;54.43</span>', pods_download_price_shortcode( array( 'id' => $post_id ) ) );
	}

	public function test_receipt_shortcode() {
		$this->markTestIncomplete( 'This one needs to be fixed per #600. The purchase receipt is not retrieved for some reason.' );
		$this->assertInternalType( 'string', pods_receipt_shortcode( array( 'payment_key' => $this->_payment_key ) ) );
		$this->assertContains( '<table id="pods_purchase_receipt">', pods_receipt_shortcode( array( 'payment_key' => $this->_payment_key ) ) );
	}

	public function test_profile_shortcode() {
		$this->assertInternalType( 'string', pods_profile_editor_shortcode() );
		$this->assertContains( '<form id="pods_profile_editor_form" class="pods_form" action="', pods_profile_editor_shortcode() );
	}
}
