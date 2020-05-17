<?php

namespace Pods_Unit_Tests\Pods;

use Codeception\Module\WPDb;
use Pods\Whatsit\Field;
use Pods_Unit_Tests\Pods_UnitTestCase;
use PodsAPI;

/**
 * @group  pods
 * @covers PodsAPI
 */
class APITest extends Pods_UnitTestCase {

	/**
	 * @var PodsAPI
	 */
	protected $api;

	/**
	 * @var string
	 */
	protected $pod_name = 'test_api';

	/**
	 * @var string
	 */
	protected $pod_name2 = 'test_api2';

	/**
	 * @var int
	 */
	protected $pod_id = 0;

	/**
	 * @var int
	 */
	protected $pod_id2 = 0;

	public function setUp() {
		parent::setUp();

		$this->api = pods_api();
	}

	/**
	 *
	 */
	public function tearDown() {
		$this->pod_id  = null;
		$this->pod_id2 = null;
		$this->api     = null;

		parent::tearDown();
	}

	/**
	 *
	 */
	public function populate() {
		$this->pod_id = $this->api->save_pod( array(
			'storage' => 'meta',
			'type'    => 'post_type',
			'name'    => $this->pod_name,
		) );

		$this->pod_id2 = $this->api->save_pod( array(
			'storage' => 'meta',
			'type'    => 'post_type',
			'name'    => $this->pod_name2,
		) );

		$params = array(
			'pod_id' => $this->pod_id,
			'name'   => 'number1',
			'type'   => 'number',
		);

		$this->api->save_field( $params );

		$params = array(
			'pod_id' => $this->pod_id2,
			'name'   => 'number2',
			'type'   => 'number',
		);

		$this->api->save_field( $params );

		$params = array(
			'pod_id'           => $this->pod_id,
			'name'             => 'related_field',
			'type'             => 'pick',
			'pick_object'      => 'post_type',
			'pick_val'         => $this->pod_name2,
			'pick_format_type' => 'multi',
		);

		$this->api->save_field( $params );

		$params = array(
			'pod_id'           => $this->pod_id2,
			'name'             => 'related_field2',
			'type'             => 'pick',
			'pick_object'      => 'post_type',
			'pick_val'         => $this->pod_name,
			'pick_format_type' => 'multi',
		);

		$this->api->save_field( $params );
	}

	/**
	 * @covers PodsAPI::init
	 * @since  2.8
	 */
	public function test_method_exists_init() {
		$this->assertTrue( method_exists( 'PodsAPI', 'init' ), 'Method init does not exist' );
	}

	/**
	 * Test the init method with no pod defined
	 * @covers  PodsAPI::init
	 * @depends test_method_exists_init
	 * @since   2.8
	 */
	public function test_method_init_no_pod() {
		$this->assertInstanceOf( 'PodsAPI', PodsAPI::init(), 'Object returned is not of type Pods_API' );
	}

	/**
	 * @covers PodsAPI::__construct
	 * @since  2.8
	 */
	public function test_method_construct_no_pod() {
		$this->assertTrue( $this->api->display_errors, 'Property display_errors not true' );
		$this->assertNull( $this->api->pod_data, 'Property pod_data not null' );
		$this->assertNull( $this->api->pod, 'Property pod not null' );
		$this->assertNull( $this->api->pod_id, 'Property pod_id not null' );
		$this->assertEmpty( $this->api->fields, 'Property fields not empty' );
		$this->assertNull( $this->api->format, 'Property format not null' );
	}

	/**
	 * @covers PodsAPI::traverse_fields
	 * @throws \Exception
	 * @since  2.8
	 */
	public function test_method_traverse_fields() {
		$this->assertTrue( method_exists( $this->api, 'traverse_fields' ), 'Method traverse_fields does not exist' );

		$this->populate();

		// Test getting just one related field.
		$params = array(
			'expand' => array(
				'related_field',
			),
			'pod'    => $this->pod_name,
		);

		$traversed = $this->api->traverse_fields( $params );

		$this->assertCount( 1, $traversed );
		$this->assertInstanceOf( Field::class, $traversed[0] );

		// Test getting just one related field with another non-related field.
		$params = array(
			'expand' => array(
				'related_field',
				'number1',
			),
			'pod'    => $this->pod_name,
		);

		$traversed = $this->api->traverse_fields( $params );

		$this->assertCount( 1, $traversed );
		$this->assertInstanceOf( Field::class, $traversed[0] );

		// Test getting just multiple related fields.
		$params = array(
			'expand' => array(
				'related_field',
				'related_field2',
				'related_field',
				'related_field2',
			),
			'pod'    => $this->pod_name,
		);

		$traversed = $this->api->traverse_fields( $params );

		$this->assertCount( 4, $traversed );
		$this->assertInstanceOf( Field::class, $traversed[0] );
		$this->assertEquals( $this->pod_name, $traversed[0]['pod'] );
		$this->assertInstanceOf( Field::class, $traversed[1] );
		$this->assertEquals( $this->pod_name2, $traversed[1]['pod'] );
		$this->assertInstanceOf( Field::class, $traversed[2] );
		$this->assertEquals( $this->pod_name, $traversed[2]['pod'] );
		$this->assertInstanceOf( Field::class, $traversed[3] );
		$this->assertEquals( $this->pod_name2, $traversed[3]['pod'] );

		// Test getting just multiple related fields with another non-related field.
		$params = array(
			'expand' => array(
				'related_field',
				'related_field2',
				'related_field',
				'related_field2',
				'number2',
			),
			'pod'    => $this->pod_name,
		);

		$traversed = $this->api->traverse_fields( $params );

		$this->assertCount( 4, $traversed );
		$this->assertInstanceOf( Field::class, $traversed[0] );
		$this->assertEquals( $this->pod_name, $traversed[0]['pod'] );
		$this->assertInstanceOf( Field::class, $traversed[1] );
		$this->assertEquals( $this->pod_name2, $traversed[1]['pod'] );
		$this->assertInstanceOf( Field::class, $traversed[2] );
		$this->assertEquals( $this->pod_name, $traversed[2]['pod'] );
		$this->assertInstanceOf( Field::class, $traversed[3] );
		$this->assertEquals( $this->pod_name2, $traversed[3]['pod'] );
	}
}
