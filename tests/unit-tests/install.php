<?php
namespace Pods_Unit_Tests;

/**
 * @group pods_activation
 */
class Tests_Activation extends Pods_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function test_settings() {
		global $pods_options;
		$this->assertArrayHasKey( 'purchase_page', $pods_options );
		$this->assertArrayHasKey( 'success_page', $pods_options );
		$this->assertArrayHasKey( 'failure_page', $pods_options );
	}
}
