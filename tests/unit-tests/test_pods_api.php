<?php
namespace Pods_Unit_Tests;

/**
 * @group pods
 */
class Test_Pods_Api extends Pods_UnitTestCase {

	/**
	 * @covers Pods_API::init
	 * @since  3.0
	 */
	public function test_method_exists_init()
	{
		$this->assertTrue( method_exists( 'Pods_API', 'init' ), 'Method init does not exist' );
	}

	/**
	 * Test the init method with no pod defined
	 * @covers  Pods_API::init
	 * @depends test_method_exists_init
	 * @since   3.0
	 */
	public function test_method_init_no_pod()
	{
		$this->assertInstanceOf( 'Pods_API', \Pods_API::init(), 'Object returned is not of type Pods_API' );
	}
}
