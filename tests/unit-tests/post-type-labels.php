<?php
namespace Pods_Unit_Tests;

/**
 * @group pods_cpt
 */
class Tests_Post_Type_Labels extends Pods_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_get_default_labels() {
		$out = pods_get_default_labels();
		$this->assertArrayHasKey( 'singular', $out );
		$this->assertArrayHasKey( 'plural', $out );

		$this->assertEquals( 'Download', $out['singular'] );
		$this->assertEquals( 'Downloads', $out['plural'] );
	}

	public function test_singular_label() {
		$this->assertEquals( 'Download', pods_get_label_singular() );
		$this->assertEquals( 'download', pods_get_label_singular( true ) );
	}

	public function test_plural_label() {
		$this->assertEquals( 'Downloads', pods_get_label_plural() );
		$this->assertEquals( 'downloads', pods_get_label_plural( true ) );
	}
}