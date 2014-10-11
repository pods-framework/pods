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

	/**
	 * @covers Pods_API::__construct
	 * @since  3.0
	 */
	public function test_method_construct_no_pod() {
		$pods_api = new \Pods_API();

		$this->assertEmpty( $pods_api->display_errors, 'Property display_errors not empty' );
		$this->assertEmpty( $pods_api->pod_data,       'Property pod_data not empty' );
		$this->assertEmpty( $pods_api->pod,            'Property pod not empty' );
		$this->assertEmpty( $pods_api->pod_id,         'Property pod_id not empty' );
		$this->assertEmpty( $pods_api->fields,         'Property fields not empty' );
		$this->assertEmpty( $pods_api->format,         'Property format not empty' );
	}

	/**
	 * @covers Pods_API::__construct
	 * @since  3.0
	 */
	public function test_method_construct() {
		$pods_api = new \Pods_API( 'foo' );

		$this->assertEmpty( $pods_api->display_errors, 'Property display_errors not empty' );
		$this->assertEmpty( $pods_api->pod_data,       'Property pod_data not empty' );
		$this->assertEmpty( $pods_api->pod,            'Property pod not empty' );
		$this->assertEmpty( $pods_api->pod_id,         'Property pod_id not empty' );
		$this->assertEmpty( $pods_api->fields,         'Property fields not empty' );
		$this->assertEmpty( $pods_api->format,         'Property format not empty' );
	}
}
