<?php

namespace Pods_Unit_Tests;

/**
 * @group pods
 */
class Test_Pods_Api extends Pods_UnitTestCase {

	public function setUp() {

		pods_api();
	}

	/**
	 * @covers PodsAPI::init
	 * @since 3.0.0
	 */
	public function test_method_exists_init() {

		$this->assertTrue( method_exists( 'PodsAPI', 'init' ), 'Method init does not exist' );
	}

	/**
	 * Test the init method with no pod defined
	 *
	 * @covers  PodsAPI::init
	 * @depends test_method_exists_init
	 * @since 3.0.0
	 */
	public function test_method_init_no_pod() {

		$this->assertInstanceOf( 'PodsAPI', \PodsAPI::init(), 'Object returned is not of type Pods_API' );
	}

	/**
	 * @covers PodsAPI::__construct
	 * @since 3.0.0
	 */
	public function test_method_construct_no_pod() {

		$pods_api = pods_api();

		$this->assertTrue( $pods_api->display_errors, 'Property display_errors not true' );
		$this->assertNull( $pods_api->pod_data, 'Property pod_data not null' );
		$this->assertNull( $pods_api->pod, 'Property pod not null' );
		$this->assertNull( $pods_api->pod_id, 'Property pod_id not null' );
		$this->assertEmpty( $pods_api->fields, 'Property fields not empty' );
		$this->assertNull( $pods_api->format, 'Property format not null' );
	}
}
