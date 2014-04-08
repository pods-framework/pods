<?php
namespace Pods_Unit_Tests;

/**
 * @group pods_checkout
 */
class Tests_Checkout extends Pods_UnitTestCase {
	public function setUp() {

		parent::setUp();

		global $wp_rewrite;
		$GLOBALS['wp_rewrite']->init();
		flush_rewrite_rules();

		pods_add_rewrite_endpoints( $wp_rewrite );

		$this->_rewrite = $wp_rewrite;

		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Download', 'post_type' => 'download', 'post_status' => 'publish' ) );

		$meta = array(
			'pods_price' => '10.50',
			'_pods_price_options_mode' => 'on',
			'_pods_product_type' => 'default',
		);
		foreach( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		$this->_post = get_post( $post_id );

		// Add our test product to the cart
		$options = array(
			'name' => 'Simple',
			'amount' => '10.50',
			'quantity' => 1
		);
		pods_add_to_cart( $this->_post->ID, $options );
	}

	/**
     * Test the can checkout function
     */
	public function test_can_checkout() {
		$this->assertTrue( pods_can_checkout() );
	}

	/**
     * Test to make sure the checkout form returns the expected HTML
     */
	public function test_checkout_form() {
		$this->markTestIncomplete('This test produces Travis killing output in https://travis-ci.org/pods-framework/pods/builds/11630800 on PHP 5.3 only');
		// $this->assertInternalType( 'string', pods_checkout_form() );
		// The checkout form should always have this
		// $this->assertContains( '<div id="pods_checkout_wrap">', pods_checkout_form() );
		// The checkout form will always have this if there are items in the cart
		// $this->assertContains( '<div id="pods_checkout_form_wrap" class="pods_clearfix">', pods_checkout_form() );
	}

	/**
     * Test to make sure the Next button is returned properly
     */
	public function test_checkout_button_next() {
		$this->assertInternalType( 'string', pods_checkout_button_next() );
		$this->assertContains( '<input type="hidden" name="pods_action" value="gateway_select" />', pods_checkout_button_next() );
	}

	/**
     * Test to make sure the purchase button is returned properly
     */
	public function test_checkout_button_purchase() {
		$this->assertInternalType( 'string', pods_checkout_button_purchase() );
		$this->assertContains( '<input type="submit" class="pods-submit blue button" id="pods-purchase-button" name="pods-purchase" value="Purchase"/>', pods_checkout_button_purchase() );
	}
}
