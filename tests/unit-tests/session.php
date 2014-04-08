<?php
namespace Pods_Unit_Tests;

/**
 * @group pods_session
 */
class Tests_Session extends Pods_UnitTestCase {
	public function setUp() {
		parent::setUp();
		new \Pods_Session;
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_set() {
		$this->assertEquals( 'bar', Pods()->session->set( 'foo', 'bar' ) );
	}

	public function test_get() {
		$this->assertEquals( 'bar', Pods()->session->get( 'foo' ) );
	}
}