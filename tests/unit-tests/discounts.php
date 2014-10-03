<?php
namespace Pods_Unit_Tests;

/**
 * @group pods_discounts
 */
class Tests_Discounts extends Pods_UnitTestCase {
	protected $_post = null;

	public function setUp() {
		parent::setUp();

		$meta = array(
			'name' => '20 Percent Off',
			'type' => 'percent',
			'amount' => '20',
			'code' => '20OFF',
			'product_condition' => 'all',
			'max' => 10,
			'uses' => 54,
			'min_price' => 128
		);

		pods_store_discount( $meta );

		$this->_post->ID = pods_get_discount_id_by_code( '20OFF' );
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_addition_of_discount() {
		$post = array(
			'name' => 'Test Discount',
			'type' => 'percent',
			'amount' => '20',
			'code' => '20OFF',
			'product_condition' => 'all',
			'start' => '12/12/2050 00:00:00',
			'expiration' => '12/31/2050 00:00:00'
		);

		$this->assertInternalType( 'int', pods_store_discount( $post ) );
	}

	public function test_discount_status_update() {
		$this->assertTrue( pods_update_discount_status( $this->_post->ID ) );
	}

	public function test_discounts_exists() {
		pods_update_discount_status( $this->_post->ID );
		$this->assertTrue( pods_has_active_discounts() );
	}

	public function test_discount_exists() {
		$this->assertTrue( pods_discount_exists( $this->_post->ID ) );
	}

	public function test_discount_retrieved_from_database() {
		$this->assertObjectHasAttribute( 'ID', pods_get_discount( $this->_post->ID ) );
		$this->assertObjectHasAttribute( 'post_title', pods_get_discount( $this->_post->ID ) );
		$this->assertObjectHasAttribute( 'post_status', pods_get_discount( $this->_post->ID ) );
		$this->assertObjectHasAttribute( 'post_type', pods_get_discount( $this->_post->ID ) );
	}

	public function test_get_discount_code() {
		$this->assertSame( '20OFF', pods_get_discount_code( $this->_post->ID ) );
	}

	public function test_discount_start_date() {
		$this->assertSame( '', pods_get_discount_start_date( $this->_post->ID ) );
	}

	public function test_discount_expiration_date() {
		$this->assertSame( '', pods_get_discount_expiration( $this->_post->ID ) );
	}

	public function test_discount_max_uses() {
		$this->assertSame( 10, pods_get_discount_max_uses( $this->_post->ID ) );
	}

	public function test_discount_uses() {
		$this->assertSame( 54, pods_get_discount_uses( $this->_post->ID ) );
	}

	public function testDiscountMinPrice() {
		$this->assertSame(128.0, pods_get_discount_min_price($this->_post->ID));
	}

	public function test_discount_amount() {
		$this->assertSame( 20.0, pods_get_discount_amount( $this->_post->ID ) );
	}

	public function test_discount_type() {
		$this->assertSame( 'percent', pods_get_discount_type( $this->_post->ID ) );
	}

	public function test_discount_product_condition() {
		$this->assertSame( 'all', pods_get_discount_product_condition( $this->_post->ID ) );
	}

	public function test_discount_is_not_global() {
		$this->assertFalse( pods_is_discount_not_global( $this->_post->ID ) );
	}

	public function test_discount_is_single_use() {
		$this->assertFalse( pods_discount_is_single_use( $this->_post->ID ) );
	}

	public function test_discount_is_started() {
		$this->assertTrue( pods_is_discount_started( $this->_post->ID ) );
	}

	public function test_discount_is_expired() {
		$this->assertFalse( pods_is_discount_expired( $this->_post->ID ) );
	}

	public function test_discount_is_maxed_out() {
		$this->assertTrue( pods_is_discount_maxed_out( $this->_post->ID ) );
	}

	public function test_discount_is_min_met() {
		$this->assertFalse( pods_discount_is_min_met( $this->_post->ID ) );
	}

	public function test_discount_is_used() {
		$this->assertFalse( pods_is_discount_used( $this->_post->ID ) );
	}

	public function test_discount_is_valid_when_purchasing() {
		$this->assertFalse( pods_is_discount_valid( $this->_post->ID ) );
	}

	public function test_discount_id_by_code() {
		$this->assertSame( $this->_post->ID, pods_get_discount_id_by_code( '20OFF' ) );
	}


	public function test_get_discounted_amount() {
		$this->assertEquals( 432.0, pods_get_discounted_amount( '20OFF', '540' ) );
	}

	public function test_increase_discount_usage() {
		$uses = pods_increase_discount_usage( '20OFF' );
		$this->assertSame( 55, $uses );
	}

	public function test_formatted_discount_amount() {
		$this->assertSame( '20%', pods_format_discount_rate( 'percent', get_post_meta( $this->_post->ID, '_pods_discount_amount', true ) ) );
	}

	public function test_deletion_of_discount() {
		pods_remove_discount( $this->_post->ID );
		$this->assertFalse( wp_cache_get( $this->_post->ID, 'posts' ) );
	}
}