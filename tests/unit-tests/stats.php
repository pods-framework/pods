<?php
namespace Pods_Unit_Tests;
use \Pods_Stats;
use \Pods_Payment_Stats;
use \WP_Error;
/**
 * @group pods_stats
 */
class Tests_Stats extends Pods_UnitTestCase {

	protected $_post;
	protected $_stats;
	protected $_payment_stats;

	public function setUp() {
		parent::setUp();

		$this->_stats = new Pods_Stats();
		$this->_payment_stats = new Pods_Payment_Stats;

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

		$meta = array(
			'pods_price' => '0.00',
			'_variable_pricing' => 1,
			'_pods_price_options_mode' => 'on',
			'pods_variable_prices' => array_values( $_variable_pricing ),
			'_pods_download_earnings' => 129.43,
			'_pods_download_sales' => 59
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

		$price      = '100.00';
		$total      = 0;
		$prices     = pods_get_variable_prices( $this->_post->ID );
		$item_price = $prices[1]['amount'];

		$total += $item_price;

		$cart_details = array(
			array(
				'name'        => 'Test Download',
				'id'          => $this->_post->ID,
				'price'       =>  100,
				'quantity'    => 1,
				'item_number' => array(
					'id'      => $this->_post->ID,
					'options' => array(
						'price_id' => 1
					)
				),
			)
		);

		$purchase_data     = array(
			'price'        => number_format( (float) $total, 2 ),
			'date'         => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
			'purchase_key' => strtolower( md5( uniqid() ) ),
			'user_email'   => $user_info['email'],
			'user_info'    => $user_info,
			'currency'     => 'USD',
			'downloads'    => $download_details,
			'cart_details' => $cart_details,
			'status'       => 'pending'
		);

		$_SERVER['REMOTE_ADDR'] = '10.0.0.0';
		$_SERVER['SERVER_NAME'] = 'pods_virtual';

		$payment_id = pods_insert_payment( $purchase_data );
		pods_update_payment_status( $payment_id );

	}

	/*
	 *
	 * Pods_Stats tests
	 *
	 */

	public function test_predefined_date_rages() {

		$out = $this->_stats->get_predefined_dates();

		$expected = array(
			'today'        => 'Today',
			'yesterday'    => 'Yesterday',
			'this_week'    => 'This Week',
			'last_week'    => 'Last Week',
			'this_month'   => 'This Month',
			'last_month'   => 'Last Month',
			'this_quarter' => 'This Quarter',
			'last_quarter' => 'Last Quater',
			'this_year'    => 'This Year',
			'last_year'    => 'Last Year'
		);

		$this->assertEquals( $expected, $out );

	}

	public function test_setup_dates() {

		// Set start date only
		$this->_stats->setup_dates( 'yesterday' );
		$this->assertInternalType( 'numeric', $this->_stats->start_date );
		$this->assertEquals( $this->_stats->start_date, $this->_stats->end_date );

		// Set some valid predefined date ranges
		$this->_stats->setup_dates( 'yesterday', 'today' );
		$this->assertInternalType( 'numeric', $this->_stats->start_date );
		$this->assertInternalType( 'numeric', $this->_stats->end_date );
		$this->assertGreaterThan( $this->_stats->start_date, $this->_stats->end_date );

		// Set some valid dates
		$this->_stats->setup_dates( '2012-01-12', '2012-04-15' );
		$this->assertInternalType( 'numeric', $this->_stats->start_date );
		$this->assertInternalType( 'numeric', $this->_stats->end_date );
		$this->assertGreaterThan( $this->_stats->start_date, $this->_stats->end_date );

		// Set some valid date strings
		$this->_stats->setup_dates( 'January 15, 2013', 'February 24, 2013' );
		$this->assertInternalType( 'numeric', $this->_stats->start_date );
		$this->assertInternalType( 'numeric', $this->_stats->end_date );
		$this->assertGreaterThan( $this->_stats->start_date, $this->_stats->end_date );


		// Set some valid timestamps
		$this->_stats->setup_dates( '1379635200', '1379645200' );
		$this->assertInternalType( 'numeric', $this->_stats->start_date );
		$this->assertInternalType( 'numeric', $this->_stats->end_date );
		$this->assertGreaterThan( $this->_stats->start_date, $this->_stats->end_date );

		// Set some invalid dates
		$this->_stats->setup_dates( 'nonvaliddatestring', 'nonvaliddatestring' );
		$this->assertInstanceOf( 'WP_Error', $this->_stats->start_date );
		$this->assertInstanceOf( 'WP_Error', $this->_stats->end_date );

	}


	/*
	 *
	 * Pods_Payment_Stats tests
	 *
	 */

	public function test_get_earnings_by_date() {

		$stats = new Pods_Payment_Stats;
		$earnings = $stats->get_earnings( 0, 'this_month' );

		$this->assertEquals( 100, $earnings );
	}

	public function test_get_sales_by_date() {

		$stats = new Pods_Payment_Stats;
		$sales = $stats->get_sales( 0, 'this_month' );

		$this->assertEquals( 1, $sales );
	}

	public function test_get_earnings_by_date_of_download() {

		$stats = new Pods_Payment_Stats;
		$earnings = $stats->get_earnings( $this->_post->ID, 'this_month' );

		$this->assertEquals( 100, $earnings );
	}

	public function test_get_sales_by_date_of_download() {

		$stats = new Pods_Payment_Stats;
		$sales = $stats->get_sales( $this->_post->ID, 'this_month' );

		$this->assertEquals( 1, $sales );
	}

}
