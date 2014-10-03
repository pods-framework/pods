<?php
namespace Pods_Unit_Tests;
use \Pods_Email_Template_Tags;

/**
 * @group pods_emails
 */
class Tests_Emails extends Pods_UnitTestCase {

	protected $_tags;

	protected $payment_id;

	public function setUp() {
		parent::setUp();
		$this->_tags = new Pods_Email_Template_Tags;

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
			'first_name' => 'Network',
			'last_name' => 'Administrator',
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

		$prices = get_post_meta( $download_details[0]['id'], 'pods_variable_prices', true );
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
			'key' => strtolower( md5( uniqid() ) ),
			'user_email' => $user_info['email'],
			'user_info' => $user_info,
			'currency' => 'USD',
			'downloads' => $download_details,
			'cart_details' => $cart_details,
			'status' => 'pending',
			'gateway' => 'manual',
			'email' => 'admin@example.org',
			'amount' => number_format( (float) $total, 2 ),
		);

		$_SERVER['REMOTE_ADDR'] = '10.0.0.0';
		$_SERVER['SERVER_NAME'] = 'pods_virtual';

		$this->_payment_id = pods_insert_payment( $purchase_data );

	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
     * Test that each of the actions are added and each hooked in with the right priority
     */
	public function test_email_actions() {
		global $wp_filter;
		$this->assertarrayHasKey( 'pods_admin_email_notice',       $wp_filter['pods_admin_sale_notice'][10]  );
		$this->assertarrayHasKey( 'pods_trigger_purchase_receipt', $wp_filter['pods_complete_purchase'][999] );
		$this->assertarrayHasKey( 'pods_resend_purchase_receipt',  $wp_filter['pods_email_links'][10]        );
		$this->assertarrayHasKey( 'pods_send_test_email',          $wp_filter['pods_send_test_email'][10]    );
	}

	public function test_admin_notice_emails() {
		$expected = array( 'admin@example.org' );
		$this->assertEquals( $expected, pods_get_admin_notice_emails() );
	}

	public function test_admin_notice_disabled() {
		$this->assertFalse( pods_admin_notices_disabled() );
	}

	public function test_email_templates() {
		$expected = array(
			'default' => 'Default Template',
			'none' => 'No template, plain text only'
		);

		$this->assertEquals( $expected, pods_get_email_templates() );
	}

	public function test_pods_get_default_sale_notification_email() {
		$this->assertContains( 'Hello', pods_get_default_sale_notification_email() );
		$this->assertContains( 'A Downloads purchase has been made', pods_get_default_sale_notification_email() );
		$this->assertContains( 'Downloads sold:', pods_get_default_sale_notification_email() );
		$this->assertContains( '{download_list}', pods_get_default_sale_notification_email() );
		$this->assertContains( 'Amount:  {price}', pods_get_default_sale_notification_email() );
	}

	public function test_email_tags_get_tags() {
		$this->assertInternalType( 'array', pods_get_email_tags() );
		$this->assertarrayHasKey( 'download_list', pods_get_email_tags() );
		$this->assertarrayHasKey( 'file_urls', pods_get_email_tags() );
		$this->assertarrayHasKey( 'name', pods_get_email_tags() );
		$this->assertarrayHasKey( 'fullname', pods_get_email_tags() );
		$this->assertarrayHasKey( 'username', pods_get_email_tags() );
		$this->assertarrayHasKey( 'user_email', pods_get_email_tags() );
		$this->assertarrayHasKey( 'date', pods_get_email_tags() );
		$this->assertarrayHasKey( 'subtotal', pods_get_email_tags() );
		$this->assertarrayHasKey( 'tax', pods_get_email_tags() );
		$this->assertarrayHasKey( 'price', pods_get_email_tags() );
		$this->assertarrayHasKey( 'payment_id', pods_get_email_tags() );
		$this->assertarrayHasKey( 'payment_method', pods_get_email_tags() );
		$this->assertarrayHasKey( 'sitename', pods_get_email_tags() );
		$this->assertarrayHasKey( 'receipt_link', pods_get_email_tags() );
	}

	public function test_email_tags_add() {
		pods_add_email_tag( 'sample_tag', 'A sample tag for the unit test', '__return_empty_array' );
		$this->assertTrue( pods_email_tag_exists( 'sample_tag' ) );
	}

	public function test_email_tags_remove() {
		pods_remove_email_tag( 'sample_tag' );
		$this->assertFalse( pods_email_tag_exists( 'sample_tag' ) );
	}

	public function test_email_tags_first_name() {
		$this->assertEquals( 'Network', pods_email_tag_first_name( $this->_payment_id ) );
	}

	public function test_email_tags_fullname() {
		$this->assertEquals( 'Network Administrator', pods_email_tag_fullname( $this->_payment_id ) );
	}

	public function test_email_tags_username() {
		$this->assertEquals( 'admin', pods_email_tag_username( $this->_payment_id ) );
	}

	public function test_email_tags_email() {
		$this->assertEquals( 'admin@example.org', pods_email_tag_user_email( $this->_payment_id ) );
	}

	public function test_email_tags_date() {
		$this->assertEquals( date( 'F j, Y', strtotime( 'today' ) ), pods_email_tag_date( $this->_payment_id ) );
	}

	public function test_email_tags_subtotal() {
		$this->assertEquals( '&#36;100.00', pods_email_tag_subtotal( $this->_payment_id ) );
	}

	public function test_email_tags_tax() {
		$this->assertEquals( '&#36;0.00', pods_email_tag_tax( $this->_payment_id ) );
	}

	public function test_email_tags_price() {
		$this->assertEquals( '&#36;100.00', pods_email_tag_price( $this->_payment_id ) );
	}

	public function test_email_tags_payment_id() {
		$this->assertEquals( $this->_payment_id, pods_email_tag_payment_id( $this->_payment_id ) );
	}

	public function test_email_tags_receipt_id() {
		$this->assertEquals( pods_get_payment_key( $this->_payment_id ), pods_email_tag_receipt_id( $this->_payment_id ) );
	}

	public function test_email_tags_payment_method() {
		$this->assertEquals( 'Free Purchase', pods_email_tag_payment_method( $this->_payment_id ) );
	}

	public function test_email_tags_site_name() {
		$this->assertEquals( get_bloginfo( 'name' ), pods_email_tag_sitename( $this->_payment_id ) );
	}

	public function test_email_tags_receipt_link() {
		$this->assertContains( 'View it in your browser.', pods_email_tag_receipt_link( $this->_payment_id ) );
	}

}
