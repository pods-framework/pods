<?php

namespace Pods_Unit_Tests\Bugs;

/**
 * @package Pods_Unit_Tests
 * @group   pods_acceptance_tests
 * @group   pods-issue-5476
 */
class Bug_5476Test extends \Pods_Unit_Tests\Pods_UnitTestCase {

	public function test_import_page() {
		$import = file_get_contents( codecept_data_dir( 'packages/test-import.json' ) );

		$components = \PodsInit::$components;
		$components->load();
		$active = $components->activate_component('migrate-packages');
		$this->assertTrue( $active );

		$components->load();

		// The WordPress test framework rolls back during tearDown, so we can safely delete the data without affecting other tests
		pods_api()->delete_pod( 'page' );

		$migrate = $components->components['migrate-packages']['object'];
		$result = $migrate->import( $import );
		$this->assertNotFalse( $result );

		$pod = pods( 'page' );
		$id = $pod->add( array( 'banana' => 'This failure is brought to you by migrate-packages' ) );
		$pod->fetch( $id );
		$value = $pod->display('banana');
		$this->assertEquals( 'This failure is brought to you by migrate-packages', $value );

	}
}
