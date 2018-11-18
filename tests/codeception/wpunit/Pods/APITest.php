<?php

namespace Pods_Unit_Tests;

use PodsAPI;

/**
 * @group  pods
 * @covers PodsAPI
 */
class APITest extends Pods_UnitTestCase {

	/**
	 * @var PodsAPI
	 */
	private $api;

	public function setUp() {
		$this->api = pods_api();
	}

	/**
	 * @covers PodsAPI::init
	 * @since  3.0.0
	 */
	public function test_method_exists_init() {
		$this->assertTrue( method_exists( 'PodsAPI', 'init' ), 'Method init does not exist' );
	}

	/**
	 * Test the init method with no pod defined
	 * @covers  PodsAPI::init
	 * @depends test_method_exists_init
	 * @since   3.0.0
	 */
	public function test_method_init_no_pod() {
		$this->assertInstanceOf( 'PodsAPI', \PodsAPI::init(), 'Object returned is not of type Pods_API' );
	}

	/**
	 * @covers PodsAPI::__construct
	 * @since  3.0.0
	 */
	public function test_method_construct_no_pod() {
		$this->assertTrue( $this->api->display_errors, 'Property display_errors not true' );
		$this->assertNull( $this->api->pod_data, 'Property pod_data not null' );
		$this->assertNull( $this->api->pod, 'Property pod not null' );
		$this->assertNull( $this->api->pod_id, 'Property pod_id not null' );
		$this->assertEmpty( $this->api->fields, 'Property fields not empty' );
		$this->assertNull( $this->api->format, 'Property format not null' );
	}
}
