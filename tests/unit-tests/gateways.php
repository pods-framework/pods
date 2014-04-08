<?php
namespace Pods_Unit_Tests;

/**
 * @group pods_gateways
 */
class Test_Gateways extends Pods_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_payment_gateways() {
		$out = pods_get_payment_gateways();
		$this->assertArrayHasKey( 'paypal', $out );
		$this->assertArrayHasKey( 'manual', $out );

		$this->assertEquals( 'PayPal Standard', $out['paypal']['admin_label'] );
		$this->assertEquals( 'PayPal', $out['paypal']['checkout_label'] );

		$this->assertEquals( 'Test Payment', $out['manual']['admin_label'] );
		$this->assertEquals( 'Test Payment', $out['manual']['checkout_label'] );
	}

	public function test_enabled_gateways() {
		$this->assertEmpty( pods_get_enabled_payment_gateways() );
	}

	public function test_is_gateway_active() {
		$this->assertFalse( pods_is_gateway_active( 'paypal' ) );
	}

	public function test_default_gateway() {
		$this->assertEquals( 'paypal', pods_get_default_gateway() );
	}

	public function test_get_gateway_admin_label() {
		$this->assertEquals( 'paypal', pods_get_gateway_admin_label( 'paypal' ) );
		$this->assertEquals( 'manual', pods_get_gateway_admin_label( 'manual' ) );
	}

	public function test_get_gateway_checkout_label() {
		$this->assertEquals( 'paypal', pods_get_gateway_checkout_label( 'paypal' ) );
		$this->assertEquals( 'Free Purchase', pods_get_gateway_checkout_label( 'manual' ) );
	}

	public function test_show_gateways() {
		$this->assertFalse( pods_show_gateways() );
	}

	public function test_chosen_gateway() {
		$this->assertEquals( 'manual', pods_get_chosen_gateway() );
	}

	public function test_no_gateway_error() {
		pods_no_gateway_error();

		$errors = pods_get_errors();

		$this->assertArrayHasKey( 'no_gateways', $errors );
		$this->assertEquals( 'You must enable a payment gateway to use Pods Framework', $errors['no_gateways'] );
	}
}