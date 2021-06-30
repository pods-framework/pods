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

	public function setUp(): void {
		parent::setUp();

		$this->api = pods_api();

		$this->populate();
	}

	/**
	 *
	 */
	public function tearDown(): void {
		$this->api = null;

		parent::tearDown();
	}

	public function populate() {
		$this->pod    = 'test_groups_pod';
		$this->pod_id = $this->api->save_pod( [
			'name'            => $this->pod,
			'type'            => 'post_type',
			'label'           => 'Test pod for groups',
			'some_custom_key' => 'Some custom value',
			'another_key'     => - 1,
		] );

		$this->group    = 'test_group';
		$this->group_id = $this->api->save_group( [
			'pod_id'          => $this->pod_id,
			'name'            => $this->group,
			'label'           => 'Test group',
			'some_custom_key' => 'One custom value',
			'another_key'     => 0,
		] );
	}

	public function populate_field() {
		$this->field    = 'test_field';
		$this->field_id = $this->api->save_field( [
			'pod_id'          => $this->pod_id,
			'group_id'        => $this->group_id,
			'name'            => $this->field,
			'label'           => 'Test field',
			'some_custom_key' => 'Field custom value',
			'another_key'     => 1,
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

		$this->expectExceptionMessage( 'Pod field name or label is required' );
		$this->expectException( \Exception::class );

		$this->api->save_field( $params );
	}

	/**
	 * @covers PodsAPI::save_field
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_save_field_with_group_id() {
		$params = [
			'pod_id'   => $this->pod_id,
			'group_id' => $this->group_id,
			'name'     => 'test_field',
			'label'    => 'Test field',
		];

		$response = $this->api->save_field( $params );

		$this->assertInternalType( 'int', $response );

		$field = $this->api->load_field( $response );

		$this->assertEquals( $params['name'], $field['name'] );
		$this->assertEquals( 'text', $field['type'] );
		$this->assertEquals( $params['label'], $field['label'] );
		$this->assertEquals( $this->group_id, $field->get_group_id() );
		$this->assertEquals( $this->group, $field->get_group_name() );
	}

	/**
	 * @covers PodsAPI::add_field
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_add_field_with_group_id_using_existing_field_name() {
		$params = [
			'pod_id'   => $this->pod_id,
			'group_id' => $this->group_id,
			'name'     => 'test_field',
			'label'    => 'Test field',
		];

		$response = $this->api->save_field( $params );

		$this->assertInternalType( 'int', $response );

		$params = [
			'pod_id'   => $this->pod_id,
			'group_id' => $this->group_id,
			'name'     => 'test_field',
			'label'    => 'Test field 2',
		];

		$this->expectExceptionMessage( 'Field test_field already exists' );

		$this->api->add_field( $params );
	}

	/**
	 * @covers PodsAPI::save_field
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_save_field_with_group_name() {
		$params = [
			'pod'   => $this->pod,
			'group' => $this->group,
			'name'  => 'test_field',
			'label' => 'Test field',
		];

		$response = $this->api->save_field( $params );

		$this->assertInternalType( 'int', $response );

		$field = $this->api->load_field( $response );

		$this->assertEquals( $params['name'], $field['name'] );
		$this->assertEquals( 'text', $field['type'] );
		$this->assertEquals( $params['label'], $field['label'] );
		$this->assertEquals( $this->group_id, $field->get_group_id() );
		$this->assertEquals( $this->group, $field->get_group_name() );
	}

	/**
	 * @covers PodsAPI::save_field
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_save_field_with_group_object() {
		$pod   = $this->api->load_pod( [ 'id' => $this->pod_id ] );
		$group = $this->api->load_group( [ 'id' => $this->group_id ] );

		$params = [
			'pod'   => $pod,
			'group' => $group,
			'name'  => 'test_field',
			'label' => 'Test field',
		];

		$response = $this->api->save_field( $params );

		$this->assertInternalType( 'int', $response );

		$field = $this->api->load_field( $response );

		$this->assertEquals( $params['name'], $field['name'] );
		$this->assertEquals( 'text', $field['type'] );
		$this->assertEquals( $params['label'], $field['label'] );
		$this->assertEquals( $this->group_id, $field->get_group_id() );
		$this->assertEquals( $this->group, $field->get_group_name() );
	}

	/**
	 * @covers PodsAPI::save_field
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_save_field_with_new_group_id() {
		$this->populate_field();

		$group2    = 'test_another_group';
		$group_id2 = $this->api->save_group( [
			'pod_id'          => $this->pod_id,
			'name'            => $group2,
			'label'           => 'Test another group',
			'some_custom_key' => 'Another custom value',
			'another_key'     => 2,
		] );

		$params = [
			'pod_id'       => $this->pod_id,
			'group_id'     => $this->group_id,
			'id'           => $this->field_id,
			'new_group_id' => $group_id2,
		];

		$response = $this->api->save_field( $params );

		$this->assertInternalType( 'int', $response );

		$field = $this->api->load_field( $response );

		$this->assertEquals( $this->field, $field['name'] );
		$this->assertEquals( 'text', $field['type'] );
		$this->assertEquals( 'Test field', $field['label'] );
		$this->assertEquals( $group_id2, $field->get_group_id() );
		$this->assertEquals( $group2, $field->get_group_name() );
	}

	/**
	 * @covers PodsAPI::save_field
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_save_field_with_weight() {
		$this->populate_field();

		$params = [
			'pod_id'   => $this->pod_id,
			'group_id' => $this->group_id,
			'name'     => 'test_field2',
			'label'    => 'Test field2',
			'weight' => 10,
		];

		$response = $this->api->save_field( $params );

		$this->assertInternalType( 'int', $response );

		$field = $this->api->load_field( $response );

		$this->assertEquals( $params['name'], $field['name'] );
		$this->assertEquals( 'text', $field['type'] );
		$this->assertEquals( $params['label'], $field['label'] );
		$this->assertEquals( $params['weight'], $field['weight'] );

		$params = [
			'pod_id'   => $this->pod_id,
			'id'       => $this->field_id,
			'weight'   => 15,
		];

		$response = $this->api->save_field( $params );

		$this->assertInternalType( 'int', $response );

		$field = $this->api->load_field( $response );

		$this->assertEquals( $this->field, $field['name'] );
		$this->assertEquals( 'text', $field['type'] );
		$this->assertEquals( 'Test field', $field['label'] );
		$this->assertEquals( $params['weight'], $field['weight'] );
	}

	/**
	 * @covers PodsAPI::load_field
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_field_with_group_id() {
		$this->populate_field();

		$field = $this->api->load_field( [
			'pod_id'   => $this->pod_id,
			'group_id' => $this->group_id,
			'name'     => $this->field,
		] );

		$this->assertInstanceOf( Field::class, $field );
	}

	/**
	 * @covers PodsAPI::load_field
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_field_with_group_name() {
		$this->populate_field();

		$field = $this->api->load_field( [
			'pod'   => $this->pod,
			'group' => $this->group,
			'name'  => $this->field,
		] );

		$this->assertInstanceOf( Field::class, $field );
	}

	/**
	 * @covers PodsAPI::load_field
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_field_with_group_object() {
		$pod   = $this->api->load_pod( [ 'id' => $this->pod_id ] );
		$group = $this->api->load_group( [ 'id' => $this->group_id ] );

		$this->populate_field();

		$field = $this->api->load_field( [
			'pod'   => $pod,
			'group' => $group,
			'name'  => $this->field,
		] );

		$this->assertInstanceOf( Field::class, $field );
	}

	/**
	 * @covers PodsAPI::load_field
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_field_with_id() {
		$this->populate_field();

		$field = $this->api->load_field( [
			'id' => $this->field_id,
		] );

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

	/**
	 * @covers PodsAPI::load_fields
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_fields_has_fields() {
		$this->populate_field();

		$fields = $this->api->load_fields();

		$fields = wp_list_pluck( $fields, 'object_type' );
		$fields = array_values( array_unique( $fields ) );

		$this->assertCount( 1, $fields );
		$this->assertContains( 'field', $fields );

		$params = [
			'object_type' => 'pod',
		];

		$fields = $this->api->load_fields( $params );

		$fields = wp_list_pluck( $fields, 'object_type' );
		$fields = array_values( array_unique( $fields ) );

		$this->assertCount( 1, $fields );
		$this->assertContains( 'field', $fields );
	}

	/**
	 * @covers PodsAPI::load_fields
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_fields_by_type() {
		$this->populate_field();

		$field_id2 = $this->api->save_field( [
			'pod_id'   => $this->pod_id,
			'group_id' => $this->group_id,
			'name'     => 'test_another_field',
			'type'     => 'number',
			'label'    => 'Another test field',
		] );

		$params = [
			'type' => 'number',
		];

		$fields = $this->api->load_fields( $params );

		$fields = wp_list_pluck( $fields, 'type', 'id' );

		$this->assertArrayHasKey( $field_id2, $fields );

		$fields = array_values( array_unique( $fields ) );

		$this->assertCount( 1, $fields );
		$this->assertContains( 'number', $fields );

		$params = [
			'type' => [
				'number',
				'text',
			],
		];

		$fields = $this->api->load_fields( $params );

		$fields = wp_list_pluck( $fields, 'type', 'id' );

		$this->assertArrayHasKey( $this->field_id, $fields );
		$this->assertArrayHasKey( $field_id2, $fields );

		$fields = array_values( array_unique( $fields ) );

		$this->assertCount( 2, $fields );
		$this->assertContains( 'number', $fields );
		$this->assertContains( 'text', $fields );
	}

	/**
	 * @covers PodsAPI::load_fields
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_fields_by_id() {
		$this->populate_field();

		$field_id2 = $this->api->save_field( [
			'pod_id'   => $this->pod_id,
			'group_id' => $this->group_id,
			'name'     => 'test_another_field',
			'type'     => 'number',
			'label'    => 'Another test field',
		] );

		$params = [
			'id' => $field_id2,
		];

		$fields = $this->api->load_fields( $params );

		$fields = wp_list_pluck( $fields, 'id' );

		$this->assertCount( 1, $fields );
		$this->assertContains( $field_id2, $fields );

		$params = [
			'id' => [
				$field_id2,
				$this->field_id,
			],
		];

		$fields = $this->api->load_fields( $params );

		$fields = wp_list_pluck( $fields, 'id' );

		$this->assertCount( 2, $fields );
		$this->assertContains( $field_id2, $fields );
		$this->assertContains( $this->field_id, $fields );
	}

	/**
	 * @covers PodsAPI::load_fields
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_fields_by_args() {
		$this->populate_field();

		$params = [
			'args' => [
				'another_key' => 1,
			],
		];

		$fields = $this->api->load_fields( $params );

		$fields = wp_list_pluck( $fields, 'id' );

		$this->assertCount( 1, $fields );
		$this->assertContains( $this->field_id, $fields );

		$params = [
			'args' => [
				'some_custom_key' => 'Some',
			],
		];

		$fields = $this->api->load_fields( $params );

		$this->assertCount( 0, $fields );
	}

	/**
	 * @covers PodsAPI::load_fields
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_fields_by_options() {
		$this->populate_field();

		$params = [
			'options' => [
				'another_key' => 1,
			],
		];

		$fields = $this->api->load_fields( $params );

		$fields = wp_list_pluck( $fields, 'id' );

		$this->assertCount( 1, $fields );
		$this->assertContains( $this->field_id, $fields );

		$params = [
			'options' => [
				'some_custom_key' => 'Some',
			],
		];

		$fields = $this->api->load_fields( $params );

		$this->assertCount( 0, $fields );
	}

	/**
	 * @covers PodsAPI::load_fields
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_fields_by_where() {
		$this->populate_field();

		$params = [
			'where' => [
				[
					'key'   => 'another_key',
					'value' => 1,
				],
			],
		];

		$fields = $this->api->load_fields( $params );

		$fields = wp_list_pluck( $fields, 'id' );

		$this->assertCount( 1, $fields );
		$this->assertContains( $this->field_id, $fields );

		$params = [
			'where' => [
				[
					'key'   => 'some_custom_key',
					'value' => 'Some',
				],
			],
		];

		$fields = $this->api->load_fields( $params );

		$this->assertCount( 0, $fields );
	}

	/**
	 * @covers PodsAPI::load_fields
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_fields_with_names() {
		$this->populate_field();

		$params = [
			'id'    => $this->field_id,
			'names' => true,
		];

		$fields = $this->api->load_fields( $params );

		$this->assertCount( 1, $fields );
		$this->assertContains( $this->field, $fields );

		$params = [
			'id'          => $this->field_id,
			'return_type' => 'names',
		];

		$fields = $this->api->load_fields( $params );

		$this->assertCount( 1, $fields );
		$this->assertContains( $this->field, $fields );
	}

	/**
	 * @covers PodsAPI::load_fields
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_fields_with_names_ids() {
		$this->populate_field();

		$params = [
			'id'        => $this->field_id,
			'names_ids' => true,
		];

		$fields = $this->api->load_fields( $params );

		$this->assertCount( 1, $fields );
		$this->assertContains( $this->field, $fields );
		$this->assertArrayHasKey( $this->field_id, $fields );

		$params = [
			'id'          => $this->field_id,
			'return_type' => 'names_ids',
		];

		$fields = $this->api->load_fields( $params );

		$this->assertCount( 1, $fields );
		$this->assertContains( $this->field, $fields );
		$this->assertArrayHasKey( $this->field_id, $fields );
	}

	/**
	 * @covers PodsAPI::load_fields
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_fields_with_ids() {
		$this->populate_field();

		$params = [
			'id'          => $this->field_id,
			'return_type' => 'ids',
		];

		$fields = $this->api->load_fields( $params );

		$this->assertCount( 1, $fields );
		$this->assertContains( $this->field_id, $fields );
	}

	/**
	 * @covers PodsAPI::load_fields
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_fields_with_count() {
		$this->populate_field();

		$field_id2 = $this->api->save_field( [
			'pod_id'   => $this->pod_id,
			'group_id' => $this->group_id,
			'name'     => 'test_another_field',
			'type'     => 'number',
			'label'    => 'Another test field',
		] );

		$params = [
			'id'    => [
				$this->field_id,
				$field_id2,
			],
			'count' => true,
		];

		$fields = $this->api->load_fields( $params );

		$this->assertInternalType( 'int', $fields );
		$this->assertEquals( 2, $fields );

		$params = [
			'id'          => [
				$this->field_id,
				$field_id2,
			],
			'return_type' => 'count',
		];

		$fields = $this->api->load_fields( $params );

		$this->assertInternalType( 'int', $fields );
		$this->assertEquals( 2, $fields );
	}

	/**
	 * @covers PodsAPI::delete_field
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_delete_field() {
		$this->populate_field();

		$params = [
			'id' => $this->field_id,
		];

		$result = $this->api->delete_field( $params );

		$this->assertTrue( $result );
	}

	/**
	 * @covers PodsAPI::delete_field
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_delete_field_with_field_not_found() {
		$params = [
			'id' => 1234567,
		];

		$this->expectExceptionMessage( 'Field not found' );
		$this->expectException( \Exception::class );

		$this->api->delete_field( $params );
	}
}
