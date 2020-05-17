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
	}

	public function populate_group() {
		$this->group    = 'test_group';
		$this->group_id = $this->api->save_group( [
			'pod_id' => $this->pod_id,
			'name'   => $this->group,
			'label'  => 'Test group',
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

		$group = $this->api->load_group( [ 'pod_id' => $this->pod_id, 'name' => $this->group ] );

		$this->assertInstanceOf( Group::class, $group );
	}

	/**
	 * @covers PodsAPI::load_group
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_group_with_pod_name() {
		$this->populate_group();

		$group = $this->api->load_group( [ 'pod' => $this->pod, 'name' => $this->group ] );

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

		$group = $this->api->load_group( [ 'pod' => $pod, 'name' => $this->group ] );

		$this->assertInstanceOf( Group::class, $group );
	}

	/**
	 * @covers PodsAPI::load_group
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_group_with_id() {
		$this->populate_group();

		$group = $this->api->load_group( [ 'id' => $this->group_id ] );

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
}
