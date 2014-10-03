<?php
namespace Pods_Unit_Tests;

/**
 * @group pods_cpt
 */
class Tests_Downloads extends Pods_UnitTestCase {
	protected $_post = null;

	protected $_variable_pricing = null;

	protected $_download_files = null;

	public function setUp() {
		parent::setUp();

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

		$this->_post = get_post( $post_id );
	}

	public function test_get_download() {
		$out = pods_get_download( $this->_post->ID );

		$this->assertObjectHasAttribute( 'ID', $out );
		$this->assertObjectHasAttribute( 'post_title', $out );
		$this->assertObjectHasAttribute( 'post_type', $out );

		$this->assertEquals( $out->post_type, $this->_post->post_type );
	}

	public function test_download_price() {
		// This is correct and should equal 0.00 because this download uses variable pricing
		$this->assertEquals( 0.00, pods_get_download_price( $this->_post->ID ) );
	}

	public function test_variable_pricing() {
		$out = pods_get_variable_prices( $this->_post->ID );
		$this->assertNotEmpty( $out );
		foreach ( $out as $var ) {
			$this->assertArrayHasKey( 'name', $var );
			$this->assertArrayHasKey( 'amount', $var );

			if ( $var['name'] == 'Simple' ) {
				$this->assertEquals( 20, $var['amount'] );
			}

			if ( $var['name'] == 'Advanced' ) {
				$this->assertEquals( 100, $var['amount'] );
			}
		}
	}

	public function test_has_variable_prices() {
		$this->assertTrue( pods_has_variable_prices( $this->_post->ID ) );
	}

	public function test_get_price_option_name() {
		$this->assertEquals( 'Simple', pods_get_price_option_name( $this->_post->ID, 0 ) );
		$this->assertEquals( 'Advanced', pods_get_price_option_name( $this->_post->ID, 1 ) );
	}

	public function test_get_lowest_price_option() {
		$this->assertEquals( 20, pods_get_lowest_price_option( $this->_post->ID ) );
	}

	public function test_get_highest_price_option() {
		$this->assertEquals( 100, pods_get_highest_price_option( $this->_post->ID ) );
	}

	public function test_price_range() {
		$range = pods_price_range( $this->_post->ID );
		$expected = '<span class="pods_price_range_low">&#36;20.00</span><span class="pods_price_range_sep">&nbsp;&ndash;&nbsp;</span><span class="pods_price_range_high">&#36;100.00</span>';
		$this->assertInternalType( 'string', $range );
		$this->assertEquals( $expected, $range );
	}

	public function test_single_price_option_mode() {
		$this->assertTrue( pods_single_price_option_mode( $this->_post->ID ) );
	}

	public function test_download_type() {
		$this->assertEquals( 'default', pods_get_download_type( $this->_post->ID ) );
	}

	public function test_download_earnings() {
		$this->assertEquals( 129.43, pods_get_download_earnings_stats( $this->_post->ID ) );
	}

	public function test_download_sales() {
		$this->assertEquals( 59, pods_get_download_sales_stats( $this->_post->ID ) );
	}

	public function test_increase_purchase_count() {
		$this->assertEquals( 60, pods_increase_purchase_count( $this->_post->ID ) );
	}

	public function test_decrease_purchase_count() {
		$this->assertEquals( 58, pods_decrease_purchase_count( $this->_post->ID ) );
	}

	public function test_earnings_increase() {
		$this->assertEquals( 149.43, pods_increase_earnings( $this->_post->ID, 20 ) );
	}

	public function test_decrease_earnings() {
		$this->assertEquals( 109.43, pods_decrease_earnings( $this->_post->ID, 20 ) );
	}

	public function test_get_download_files() {
		$out = pods_get_download_files( $this->_post->ID );

		foreach ( $out as $file ) {
			$this->assertArrayHasKey( 'name', $file );
			$this->assertArrayHasKey( 'file', $file );
			$this->assertArrayHasKey( 'condition', $file );

			if ( $file['name'] == 'File 1' ) {
				$this->assertEquals( 'http://localhost/file1.jpg', $file['file'] );
				$this->assertEquals( 0, $file['condition'] );
			}

			if ( $file['name'] == 'File 2' ) {
				$this->assertEquals( 'http://localhost/file2.jpg', $file['file'] );
				$this->assertEquals( 'all', $file['condition'] );
			}
		}
	}

	public function test_get_file_download_limit() {
		$this->assertEquals( 20, pods_get_file_download_limit( $this->_post->ID ) );
	}

	public function test_get_file_download_limit_override() {
		$this->assertEquals( 1, pods_get_file_download_limit_override( $this->_post->ID, 1 ) );
	}

	public function test_is_file_at_download_limit() {
		$this->assertFalse( pods_is_file_at_download_limit( $this->_post->ID, 1, 1 ) );
	}

	public function test_get_file_price_condition() {
		$this->assertEquals( 0, pods_get_file_price_condition( $this->_post->ID, 0 ) );
		$this->assertEquals( 'all', pods_get_file_price_condition( $this->_post->ID, 1 ) );
	}

	public function test_get_product_notes() {
		$this->assertEquals( 'Purchase Notes', pods_get_product_notes( $this->_post->ID ) );
	}

	public function test_get_download_type() {
		$this->assertEquals( 'default', pods_get_download_type( $this->_post->ID ) );
	}

	public function test_get_download_is_bundle() {
		$this->assertFalse( pods_is_bundled_product( $this->_post->ID ) );
	}
}
