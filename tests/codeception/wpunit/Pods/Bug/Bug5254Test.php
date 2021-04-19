<?php

namespace Pods_Unit_Tests\Bugs;

/**
 * @package Pods_Unit_Tests
 * @group   pods_acceptance_tests
 * @group   pods-issue-5254
 */
class Bug_5254Test extends \Pods_Unit_Tests\Pods_UnitTestCase {

	/*
	 * This should probably get rolled into extensive includes/data.php coverage
	 * but for now it is specific to at least Bug #5254
	 */
	public function test_pods_str_replace_false() {
		$params = false;
		$result = pods_str_replace( '@wp_', '{prefix}', $params );
		$this->assertFalse( $result );

		$params = array( 'bypass_helpers' => false );
		$result = pods_str_replace( '@wp_', '{prefix}', $params );
		$this->assertFalse( $result['bypass_helpers'] );

		$params = array( 'nested' => array( 'bypass_helpers' => false ) );
		$result = pods_str_replace( '@wp_', '{prefix}', $params );
		$this->assertFalse( $result['nested']['bypass_helpers'] );

	}
}
