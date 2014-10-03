<?php
namespace Pods_Unit_Tests;

/**
 * @group pods_tax
 */
class Tests_Taxes extends Pods_UnitTestCase {

	protected $_payment_id = null;

	protected $_post = null;

	public function setUp() {
		parent::setUp();

		global $pods_options;

		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'publish' ) );

		$meta = array(
			'pods_price' => '10.00',
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

		$total += $item_price;

		$cart_details = array();
		$cart_details[] = array(
			'name' => 'Test Download',
			'id' => $this->_post->ID,
			'item_number' => array(
				'id' => $this->_post->ID,
				'options' => array()
			),
			'subtotal' => '10',
			'discount' => '0',
			'tax'      => '0.36',
			'price'    => '10.36',
			'quantity' => 1
		);

		$purchase_data = array(
			'price' => '10.36',
			'date' => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
			'purchase_key' => strtolower( md5( uniqid() ) ),
			'user_email' => $user_info['email'],
			'user_info' => $user_info,
			'currency' => 'USD',
			'downloads' => $download_details,
			'cart_details' => $cart_details,
			'status' => 'publish'
		);

		$_SERVER['REMOTE_ADDR'] = '10.0.0.0';
		$_SERVER['SERVER_NAME'] = 'pods_virtual';

		$payment_id = pods_insert_payment( $purchase_data );

		$this->_payment_id = $payment_id;

		// Setup global tax rate
		$pods_options['enable_taxes'] = '1';
		$pods_options['tax_rate']     = '3.6';

		// Setup country / state tax rates
		$tax_rates = array();
		$tax_rates[] = array( 'country' => 'US', 'state' => 'AL', 'rate' => 15 );

		update_option( 'pods_options', $pods_options );
		update_option( 'pods_tax_rates', $tax_rates );
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_use_taxes() {
		global $pods_options;
		$this->assertTrue( pods_use_taxes() );
	}

	public function test_taxes_after_discounts() {
		$this->assertFalse( pods_taxes_after_discounts() );
	}

	public function test_get_tax_rates() {
		$this->assertInternalType( 'array', pods_get_tax_rates() );
	}

	public function test_get_tax_rate() {
		$this->assertInternalType( 'float', pods_get_tax_rate( 'US', 'AL' ) );
		// Test the one state that has its own rate
		$this->assertEquals( '0.15', pods_get_tax_rate( 'US', 'AL' ) );

		// Test some other arbitrary states to ensure they fall back to default
		$this->assertEquals( '0.036', pods_get_tax_rate( 'US', 'KS' ) );
		$this->assertEquals( '0.036', pods_get_tax_rate( 'US', 'AK' ) );
		$this->assertEquals( '0.036', pods_get_tax_rate( 'US', 'CA' ) );

		// Test some other countries to ensure they fall back to default
		$this->assertEquals( '0.036', pods_get_tax_rate( 'JP' ) );
		$this->assertEquals( '0.036', pods_get_tax_rate( 'BR' ) );
		$this->assertEquals( '0.036', pods_get_tax_rate( 'CN' ) );
		$this->assertEquals( '0.036', pods_get_tax_rate( 'HK' ) );
	}

	public function test_get_global_tax_rate() {
		$this->assertInternalType( 'float', pods_get_tax_rate( 'CA', 'AB' ) );
		$this->assertEquals( '0.036', pods_get_tax_rate( 'CA', 'AB' ) );

		$this->assertInternalType( 'float', pods_get_tax_rate() );
		$this->assertEquals( '0.036', pods_get_tax_rate() );
	}

	public function test_get_formatted_tax_rate() {
		$this->assertEquals( '3.6%', pods_get_formatted_tax_rate() );
	}

	public function test_calculate_tax() {
		$this->assertEquals( '1.944', pods_calculate_tax( 54 ) );
		$this->assertEquals( '1.9692', pods_calculate_tax( 54.7 ) );
		$this->assertEquals( '5.5386', pods_calculate_tax( 153.85 ) );
		$this->assertEquals( '9.29916', pods_calculate_tax( 258.31 ) );
		$this->assertEquals( '37.41552', pods_calculate_tax( 1039.32 ) );
		$this->assertEquals( '361.58724', pods_calculate_tax( 10044.09 ) );
	}

	public function test_get_sales_tax_for_year() {
		$this->assertEquals( '0.36', pods_get_sales_tax_for_year( date( 'Y' ) ) );
		$this->assertEquals( '0', pods_get_sales_tax_for_year( date( 'Y' ) - 1 ) );
	}

	public function test_sales_tax_for_year() {
		ob_start();
		pods_sales_tax_for_year( date( 'Y' ) );
		$this_year = ob_get_clean();

		ob_start();
		pods_sales_tax_for_year( date( 'Y' ) - 1 );
		$last_year = ob_get_clean();

		$this->assertEquals( '&#36;0.36', $this_year );
		$this->assertEquals( '&#36;0.00', $last_year );
	}

	public function test_prices_show_tax_on_checkout() {
		$this->assertFalse( pods_prices_show_tax_on_checkout() );
	}

	public function test_prices_include_tax() {
		$this->assertFalse( pods_prices_include_tax() );
	}

	public function test_is_cart_taxed() {
		$this->assertTrue( pods_is_cart_taxed() );

	}

	public function test_display_tax_rates() {
		$this->assertFalse( pods_display_tax_rate() );
	}

	public function test_download_is_exclusive_of_tax() {
		$this->assertFalse( pods_download_is_tax_exclusive( $this->_post->ID ) );
	}

	public function test_get_payment_tax() {
		$this->assertEquals( '0.36', pods_get_payment_tax( $this->_payment_id ) );
	}

}
