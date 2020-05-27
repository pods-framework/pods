<?php

namespace Pods_Unit_Tests\Pods\API;

use Pods\Whatsit\Group;
use Pods_Unit_Tests\Pods_UnitTestCase;
use PodsAPI;

/**
 * @group  pods
 * @covers PodsAPI
 */
class GroupTest extends Pods_UnitTestCase {

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
			'another_key'     => 0,
		] );
	}

	public function populate_group() {
		$this->group    = 'test_group';
		$this->group_id = $this->api->save_group( [
			'pod_id'          => $this->pod_id,
			'name'            => $this->group,
			'label'           => 'Test group',
			'some_custom_key' => 'One custom value',
			'another_key'     => 1,
		] );
	}

	/**
	 * @covers PodsAPI::save_group
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_save_group_with_empty_params() {
		$params = [];

		$this->expectExceptionMessage( 'Pod ID or name is required' );
		$this->expectException( \Exception::class );

		$this->api->save_group( $params );
	}

	/**
	 * @covers PodsAPI::save_group
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_save_group_with_no_group_name() {
		$params = [
			'pod_id' => $this->pod_id,
		];

		$this->expectExceptionMessage( 'Pod group name is required' );
		$this->expectException( \Exception::class );

		$this->api->save_group( $params );
	}

	/**
	 * @covers PodsAPI::save_group
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_save_group_with_pod_id() {
		$params = [
			'pod_id' => $this->pod_id,
			'name'   => 'test_group',
			'label'  => 'Test group',
		];

		$response = $this->api->save_group( $params );

		$this->assertInternalType( 'int', $response );

		$group = $this->api->load_group( $response );

		$this->assertEquals( $params['name'], $group['name'] );
		$this->assertEquals( '', $group['type'] );
		$this->assertEquals( $params['label'], $group['label'] );
	}

	/**
	 * @covers PodsAPI::save_group
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_save_group_with_pod_name() {
		$params = [
			'pod'   => $this->pod,
			'name'  => 'test_group',
			'label' => 'Test group',
		];

		$response = $this->api->save_group( $params );

		$this->assertInternalType( 'int', $response );

		$group = $this->api->load_group( $response );

		$this->assertEquals( $params['name'], $group['name'] );
		$this->assertEquals( '', $group['type'] );
		$this->assertEquals( $params['label'], $group['label'] );
	}

	/**
	 * @covers PodsAPI::save_group
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_save_group_with_pod_object() {
		$pod = $this->api->load_pod( [ 'id' => $this->pod_id ] );

		$params = [
			'pod'   => $pod,
			'name'  => 'test_group',
			'label' => 'Test group',
		];

		$response = $this->api->save_group( $params );

		$this->assertInternalType( 'int', $response );

		$group = $this->api->load_group( $response );

		$this->assertEquals( $params['name'], $group['name'] );
		$this->assertEquals( '', $group['type'] );
		$this->assertEquals( $params['label'], $group['label'] );
	}

	/**
	 * @covers PodsAPI::load_group
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_group_with_pod_id() {
		$this->populate_group();

		$group = $this->api->load_group( [
			'pod_id' => $this->pod_id,
			'name'   => $this->group,
		] );

		$this->assertInstanceOf( Group::class, $group );
	}

	/**
	 * @covers PodsAPI::load_group
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_group_with_pod_name() {
		$this->populate_group();

		$group = $this->api->load_group( [
			'pod'  => $this->pod,
			'name' => $this->group,
		] );

		$this->assertInstanceOf( Group::class, $group );
	}

	/**
	 * @covers PodsAPI::load_group
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_group_with_pod_object() {
		$pod = $this->api->load_pod( [ 'id' => $this->pod_id ] );

		$this->populate_group();

		$group = $this->api->load_group( [
			'pod'  => $pod,
			'name' => $this->group,
		] );

		$this->assertInstanceOf( Group::class, $group );
	}

	/**
	 * @covers PodsAPI::load_group
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_group_with_id() {
		$this->populate_group();

		$group = $this->api->load_group( [
			'id' => $this->group_id,
		] );

		$this->assertInstanceOf( Group::class, $group );
	}

	/**
	 * @covers PodsAPI::load_group
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_group_with_id_direct() {
		$this->populate_group();

		$group = $this->api->load_group( $this->group_id );

		$this->assertInstanceOf( Group::class, $group );
	}

	/**
	 * @covers PodsAPI::load_group
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_group_with_post_direct() {
		$this->populate_group();

		$group = $this->api->load_group( get_post( $this->group_id ) );

		$this->assertInstanceOf( Group::class, $group );
	}

	/**
	 * @covers PodsAPI::load_group
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_group_with_object_direct() {
		$this->populate_group();

		$group = $this->api->load_group( [ 'id' => $this->group_id ] );
		$group = $this->api->load_group( $group );

		$this->assertInstanceOf( Group::class, $group );
	}

	/**
	 * @covers PodsAPI::load_groups
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_groups_has_groups() {
		$this->populate_group();

		$groups = $this->api->load_groups();

		$groups = wp_list_pluck( $groups, 'object_type' );
		$groups = array_values( array_unique( $groups ) );

		$this->assertCount( 1, $groups );
		$this->assertContains( 'group', $groups );

		$params = [
			'object_type' => 'field',
		];

		$groups = $this->api->load_groups( $params );

		$groups = wp_list_pluck( $groups, 'object_type' );
		$groups = array_values( array_unique( $groups ) );

		$this->assertCount( 1, $groups );
		$this->assertContains( 'group', $groups );
	}

	/**
	 * @covers PodsAPI::load_groups
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_groups_by_id() {
		$this->populate_group();

		$group_id2 = $this->api->save_group( [
			'pod_id' => $this->pod_id,
			'name'   => 'test_another_group',
			'label'  => 'Another test group',
		] );

		$params = [
			'id' => $group_id2,
		];

		$groups = $this->api->load_groups( $params );

		$groups = wp_list_pluck( $groups, 'id' );

		$this->assertCount( 1, $groups );
		$this->assertContains( $group_id2, $groups );

		$params = [
			'id' => [
				$group_id2,
				$this->group_id,
			],
		];

		$groups = $this->api->load_groups( $params );

		$groups = wp_list_pluck( $groups, 'id' );

		$this->assertCount( 2, $groups );
		$this->assertContains( $group_id2, $groups );
		$this->assertContains( $this->group_id, $groups );
	}

	/**
	 * @covers PodsAPI::load_groups
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_groups_by_args() {
		$this->populate_group();

		$params = [
			'args' => [
				'another_key' => 1,
			],
		];

		$groups = $this->api->load_groups( $params );

		$groups = wp_list_pluck( $groups, 'id' );

		$this->assertCount( 1, $groups );
		$this->assertContains( $this->group_id, $groups );

		$params = [
			'args' => [
				'some_custom_key' => 'Some',
			],
		];

		$groups = $this->api->load_groups( $params );

		$this->assertCount( 0, $groups );
	}

	/**
	 * @covers PodsAPI::load_groups
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_groups_by_options() {
		$this->populate_group();

		$params = [
			'options' => [
				'another_key' => 1,
			],
		];

		$groups = $this->api->load_groups( $params );

		$groups = wp_list_pluck( $groups, 'id' );

		$this->assertCount( 1, $groups );
		$this->assertContains( $this->group_id, $groups );

		$params = [
			'options' => [
				'some_custom_key' => 'Some',
			],
		];

		$groups = $this->api->load_groups( $params );

		$this->assertCount( 0, $groups );
	}

	/**
	 * @covers PodsAPI::load_groups
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_groups_by_where() {
		$this->populate_group();

		$params = [
			'where' => [
				[
					'key'   => 'another_key',
					'value' => 1,
				],
			],
		];

		$groups = $this->api->load_groups( $params );

		$groups = wp_list_pluck( $groups, 'id' );

		$this->assertCount( 1, $groups );
		$this->assertContains( $this->group_id, $groups );

		$params = [
			'where' => [
				[
					'key'   => 'some_custom_key',
					'value' => 'Some',
				],
			],
		];

		$groups = $this->api->load_groups( $params );

		$this->assertCount( 0, $groups );
	}

	/**
	 * @covers PodsAPI::load_groups
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_groups_with_names() {
		$this->populate_group();

		$params = [
			'id'    => $this->group_id,
			'names' => true,
		];

		$groups = $this->api->load_groups( $params );

		$this->assertCount( 1, $groups );
		$this->assertContains( $this->group, $groups );

		$params = [
			'id'          => $this->group_id,
			'return_type' => 'names',
		];

		$groups = $this->api->load_groups( $params );

		$this->assertCount( 1, $groups );
		$this->assertContains( $this->group, $groups );
	}

	/**
	 * @covers PodsAPI::load_groups
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_groups_with_names_ids() {
		$this->populate_group();

		$params = [
			'id'        => $this->group_id,
			'names_ids' => true,
		];

		$groups = $this->api->load_groups( $params );

		$this->assertCount( 1, $groups );
		$this->assertContains( $this->group, $groups );
		$this->assertArrayHasKey( $this->group_id, $groups );

		$params = [
			'id'          => $this->group_id,
			'return_type' => 'names_ids',
		];

		$groups = $this->api->load_groups( $params );

		$this->assertCount( 1, $groups );
		$this->assertContains( $this->group, $groups );
		$this->assertArrayHasKey( $this->group_id, $groups );
	}

	/**
	 * @covers PodsAPI::load_groups
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_groups_with_ids() {
		$this->populate_group();

		$params = [
			'id'          => $this->group_id,
			'return_type' => 'ids',
		];

		$groups = $this->api->load_groups( $params );

		$this->assertCount( 1, $groups );
		$this->assertContains( $this->group_id, $groups );
	}

	/**
	 * @covers PodsAPI::load_groups
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_groups_with_count() {
		$this->populate_group();

		$group_id2 = $this->api->save_group( [
			'pod_id' => $this->pod_id,
			'name'   => 'test_another_group',
			'label'  => 'Another test group',
		] );

		$params = [
			'id'    => [
				$this->group_id,
				$group_id2,
			],
			'count' => true,
		];

		$groups = $this->api->load_groups( $params );

		$this->assertInternalType( 'int', $groups );
		$this->assertEquals( 2, $groups );

		$params = [
			'id'          => [
				$this->group_id,
				$group_id2,
			],
			'return_type' => 'count',
		];

		$groups = $this->api->load_groups( $params );

		$this->assertInternalType( 'int', $groups );
		$this->assertEquals( 2, $groups );
	}
}
