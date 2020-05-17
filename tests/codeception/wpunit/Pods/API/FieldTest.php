<?php

namespace Pods_Unit_Tests\Pods\API;

use Pods\Whatsit\Field;
use Pods_Unit_Tests\Pods_UnitTestCase;
use PodsAPI;

/**
 * @group  pods
 * @covers PodsAPI
 */
class FieldTest extends Pods_UnitTestCase {

	/**
	 * @var PodsAPI
	 */
	protected $api;

	/**
	 * @var string
	 */
	protected $pod;

	/**
	 * @var int
	 */
	protected $pod_id;

	/**
	 * @var string
	 */
	protected $group;

	/**
	 * @var int
	 */
	protected $group_id;

	/**
	 * @var string
	 */
	protected $field;

	/**
	 * @var int
	 */
	protected $field_id;

	public function setUp() {
		parent::setUp();

		$this->api = pods_api();

		$this->populate();
	}

	/**
	 *
	 */
	public function tearDown() {
		$this->api = null;

		parent::tearDown();
	}

	public function populate() {
		$this->pod    = 'test_groups_pod';
		$this->pod_id = $this->api->save_pod( [
			'name'  => $this->pod,
			'type'  => 'post_type',
			'label' => 'Test pod for groups',
		] );

		$this->group    = 'test_group';
		$this->group_id = $this->api->save_group( [
			'pod_id' => $this->pod_id,
			'name'   => $this->group,
			'label'  => 'Test group',
		] );
	}

	public function populate_field() {
		$this->field    = 'test_field';
		$this->field_id = $this->api->save_field( [
			'pod_id'       => $this->pod_id,
			'name'         => $this->field,
			'label'        => 'Test field',
			'new_group_id' => $this->group_id,
		] );
	}

	/**
	 * @covers PodsAPI::save_field
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_save_field_with_empty_params() {
		$params = [];

		$this->expectExceptionMessage( 'Pod ID or name is required' );
		$this->expectException( \Exception::class );

		$this->api->save_field( $params );
	}

	/**
	 * @covers PodsAPI::save_field
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_save_field_with_no_field_name() {
		$params = [
			'pod_id' => $this->pod_id,
		];

		$this->expectExceptionMessage( 'Pod field name is required' );
		$this->expectException( \Exception::class );

		$this->api->save_field( $params );
	}

	/**
	 * @covers PodsAPI::save_field
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_save_field_with_pod_id() {
		$params = [
			'pod_id' => $this->pod_id,
			'name'   => 'test_group',
			'label'  => 'Test group',
		];

		$response = $this->api->save_field( $params );

		$this->assertInternalType( 'int', $response );

		$field = $this->api->load_field( $response );

		$this->assertEquals( $params['name'], $field['name'] );
		$this->assertEquals( 'text', $field['type'] );
		$this->assertEquals( $params['label'], $field['label'] );
	}

	/**
	 * @covers PodsAPI::save_field
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_save_field_with_pod_name() {
		$params = [
			'pod'   => $this->pod,
			'name'  => 'test_group',
			'label' => 'Test group',
		];

		$response = $this->api->save_field( $params );

		$this->assertInternalType( 'int', $response );

		$field = $this->api->load_field( $response );

		$this->assertEquals( $params['name'], $field['name'] );
		$this->assertEquals( 'text', $field['type'] );
		$this->assertEquals( $params['label'], $field['label'] );
	}

	/**
	 * @covers PodsAPI::save_field
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_save_field_with_pod_object() {
		$pod = $this->api->load_pod( [ 'id' => $this->pod_id ] );

		$params = [
			'pod'   => $pod,
			'name'  => 'test_group',
			'label' => 'Test group',
		];

		$response = $this->api->save_field( $params );

		$this->assertInternalType( 'int', $response );

		$field = $this->api->load_field( $response );

		$this->assertEquals( $params['name'], $field['name'] );
		$this->assertEquals( 'text', $field['type'] );
		$this->assertEquals( $params['label'], $field['label'] );
	}

	/**
	 * @covers PodsAPI::load_field
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_field_with_pod_id() {
		$this->populate_field();

		$field = $this->api->load_field( [ 'pod_id' => $this->pod_id, 'name' => $this->field ] );

		$this->assertInstanceOf( Field::class, $field );
	}

	/**
	 * @covers PodsAPI::load_field
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_field_with_pod_name() {
		$this->populate_field();

		$field = $this->api->load_field( [ 'pod' => $this->pod, 'name' => $this->field ] );

		$this->assertInstanceOf( Field::class, $field );
	}

	/**
	 * @covers PodsAPI::load_field
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_field_with_pod_object() {
		$pod = $this->api->load_pod( [ 'id' => $this->pod_id ] );

		$this->populate_field();

		$field = $this->api->load_field( [ 'pod' => $pod, 'name' => $this->field ] );

		$this->assertInstanceOf( Field::class, $field );
	}

	/**
	 * @covers PodsAPI::load_field
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_field_with_id() {
		$this->populate_field();

		$field = $this->api->load_field( [ 'id' => $this->field_id ] );

		$this->assertInstanceOf( Field::class, $field );
	}

	/**
	 * @covers PodsAPI::load_field
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_field_with_id_direct() {
		$this->populate_field();

		$field = $this->api->load_field( $this->field_id );

		$this->assertInstanceOf( Field::class, $field );
	}

	/**
	 * @covers PodsAPI::load_field
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_field_with_post_direct() {
		$this->populate_field();

		$field = $this->api->load_field( get_post( $this->field_id ) );

		$this->assertInstanceOf( Field::class, $field );
	}

	/**
	 * @covers PodsAPI::load_field
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_field_with_object_direct() {
		$this->populate_field();

		$field = $this->api->load_field( [ 'id' => $this->field_id ] );
		$field = $this->api->load_field( $field );

		$this->assertInstanceOf( Field::class, $field );
	}
}
