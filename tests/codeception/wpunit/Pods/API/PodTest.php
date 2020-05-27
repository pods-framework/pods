<?php

namespace Pods_Unit_Tests\Pods\API;

use Codeception\Module\WPDb;
use Pods\Whatsit\Pod;
use Pods_Unit_Tests\Pods_UnitTestCase;
use PodsAPI;

/**
 * @group  pods
 * @covers PodsAPI
 */
class PodTest extends Pods_UnitTestCase {

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

	public function setUp(): void {
		parent::setUp();

		$this->api = pods_api();
	}

	/**
	 *
	 */
	public function tearDown(): void {
		$this->api = null;

		parent::tearDown();
	}

	public function populate_pod() {
		$this->pod    = 'test_pod';
		$this->pod_id = $this->api->save_pod( [
			'name'            => $this->pod,
			'type'            => 'post_type',
			'label'           => 'Test pod',
			'some_custom_key' => 'Some custom value',
			'another_key'     => 1,
		] );
	}

	/**
	 * @covers PodsAPI::save_pod
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_save_pod_with_empty_params() {
		$params = [];

		$this->expectExceptionMessage( 'Pod name is required' );
		$this->expectException( \Exception::class );

		$this->api->save_pod( $params );
	}

	public function get_save_pod_configs() {
		yield 'Post Type' => [
			[
				'name'  => 'test_1',
				'type'  => 'post_type',
				'label' => 'Test pod for Post Type',
			],
		];

		yield 'Taxonomy' => [
			[
				'name'  => 'test_2',
				'type'  => 'taxonomy',
				'label' => 'Test pod for Taxonomy',
			],
		];

		yield 'Advanced Content Type' => [
			[
				'name'  => 'test_3',
				'type'  => 'pod',
				'label' => 'Test pod for Advanced Content Type',
			],
		];
	}

	/**
	 * @covers       PodsAPI::save_pod
	 * @since        2.8
	 *
	 * @dataProvider get_save_pod_configs
	 * @throws \Exception
	 */
	public function test_save_pod_table( $config ) {
		/** @var WPDb $db */
		$db = $this->getModule( 'WPDb' );

		$params = $config;

		$params['storage'] = 'table';
		$params['name']    .= $params['storage'];

		$response = $this->api->save_pod( $params );

		$this->assertInternalType( 'int', $response );

		$pod = $this->api->load_pod( [ 'name' => $params['name'] ] );

		$this->assertEquals( $params['name'], $pod['name'] );
		$this->assertEquals( $params['type'], $pod['type'] );
		$this->assertEquals( $params['label'], $pod['label'] );
		$this->assertEquals( $params['storage'], $pod['storage'] );

		global $wpdb;

		$wpdb->get_var( 'SELECT id FROM `' . $db->grabTablePrefix() . 'pods_' . $params['name'] . '`' );

		$this->assertEmpty( $wpdb->last_error );
	}

	/**
	 * @covers       PodsAPI::save_pod
	 * @since        2.8
	 *
	 * @dataProvider get_save_pod_configs
	 * @throws \Exception
	 */
	public function test_save_pod_meta( $config ) {
		$db = $this->getModule( 'WPDb' );

		$params = $config;

		$params['storage'] = 'meta';
		$params['name']    .= $params['storage'];

		$response = $this->api->save_pod( $params );

		$this->assertInternalType( 'int', $response );

		$pod = $this->api->load_pod( [ 'name' => $params['name'] ] );

		$this->assertEquals( $params['name'], $pod['name'] );
		$this->assertEquals( $params['type'], $pod['type'] );
		$this->assertEquals( $params['label'], $pod['label'] );
		$this->assertEquals( $params['storage'], $pod['storage'] );

		global $wpdb;

		$wpdb->get_var( 'SELECT id FROM `' . $db->grabTablePrefix() . 'pods_' . $params['name'] . '`' );

		$this->assertContains( $db->grabTablePrefix() . 'pods_' . $params['name'] . '\' doesn\'t exist', $wpdb->last_error );
	}

	/**
	 * @covers PodsAPI::load_pod
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_pod_with_id() {
		$this->populate_pod();

		$pod = $this->api->load_pod( [ 'id' => $this->pod_id ] );

		$this->assertInstanceOf( Pod::class, $pod );
	}

	/**
	 * @covers PodsAPI::load_pod
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_pod_with_internal() {
		$pod = $this->api->load_pod( [ 'name' => '_pods_pod' ] );

		$this->assertInstanceOf( Pod::class, $pod );
	}

	/**
	 * @covers PodsAPI::load_pod
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_pod_with_name() {
		$this->populate_pod();

		$pod = $this->api->load_pod( [ 'name' => $this->pod ] );

		$this->assertInstanceOf( Pod::class, $pod );
	}

	/**
	 * @covers PodsAPI::load_pod
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_pod_with_object_direct() {
		$this->populate_pod();

		$pod = $this->api->load_pod( [ 'id' => $this->pod_id ] );
		$pod = $this->api->load_pod( $pod );

		$this->assertInstanceOf( Pod::class, $pod );
	}

	/**
	 * @covers PodsAPI::load_pod
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_pod_with_id_direct() {
		$this->populate_pod();

		$pod = $this->api->load_pod( $this->pod_id );

		$this->assertInstanceOf( Pod::class, $pod );
	}

	/**
	 * @covers PodsAPI::load_pod
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_pod_with_post_direct() {
		$this->populate_pod();

		$pod = $this->api->load_pod( get_post( $this->pod_id ) );

		$this->assertInstanceOf( Pod::class, $pod );
	}

	/**
	 * @covers PodsAPI::load_pods
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_pods_has_pods() {
		$this->populate_pod();

		$pods = $this->api->load_pods();

		$pods = wp_list_pluck( $pods, 'object_type' );
		$pods = array_values( array_unique( $pods ) );

		$this->assertCount( 1, $pods );
		$this->assertContains( 'pod', $pods );

		$params = [
			'object_type' => 'field',
		];

		$pods = $this->api->load_pods( $params );

		$pods = wp_list_pluck( $pods, 'object_type' );
		$pods = array_values( array_unique( $pods ) );

		$this->assertCount( 1, $pods );
		$this->assertContains( 'pod', $pods );
	}

	/**
	 * @covers PodsAPI::load_pods
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_pods_by_type() {
		$this->populate_pod();

		$pod_id2 = $this->api->save_pod( [
			'name'  => 'test_another_pod',
			'type'  => 'taxonomy',
			'label' => 'Another test pod',
		] );

		$params = [
			'type' => 'taxonomy',
		];

		$pods = $this->api->load_pods( $params );

		$pods = wp_list_pluck( $pods, 'type', 'id' );

		$this->assertArrayHasKey( $pod_id2, $pods );

		$pods = array_values( array_unique( $pods ) );

		$this->assertCount( 1, $pods );
		$this->assertContains( 'taxonomy', $pods );

		$params = [
			'type' => [
				'taxonomy',
				'post_type',
			],
		];

		$pods = $this->api->load_pods( $params );

		$pods = wp_list_pluck( $pods, 'type', 'id' );

		$this->assertArrayHasKey( $this->pod_id, $pods );
		$this->assertArrayHasKey( $pod_id2, $pods );

		$pods = array_values( array_unique( $pods ) );

		$this->assertCount( 2, $pods );
		$this->assertContains( 'taxonomy', $pods );
		$this->assertContains( 'post_type', $pods );
	}

	/**
	 * @covers PodsAPI::load_pods
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_pods_by_id() {
		$this->populate_pod();

		$pod_id2 = $this->api->save_pod( [
			'name'  => 'test_another_pod',
			'type'  => 'taxonomy',
			'label' => 'Another test pod',
		] );

		$params = [
			'id' => $pod_id2,
		];

		$pods = $this->api->load_pods( $params );

		$pods = wp_list_pluck( $pods, 'id' );

		$this->assertCount( 1, $pods );
		$this->assertContains( $pod_id2, $pods );

		$params = [
			'id' => [
				$pod_id2,
				$this->pod_id,
			],
		];

		$pods = $this->api->load_pods( $params );

		$pods = wp_list_pluck( $pods, 'id' );

		$this->assertCount( 2, $pods );
		$this->assertContains( $pod_id2, $pods );
		$this->assertContains( $this->pod_id, $pods );

		$params = [
			'ids' => $pod_id2,
		];

		$pods = $this->api->load_pods( $params );

		$pods = wp_list_pluck( $pods, 'id' );

		$this->assertCount( 1, $pods );
		$this->assertContains( $pod_id2, $pods );
	}

	/**
	 * @covers PodsAPI::load_pods
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_pods_by_args() {
		$this->populate_pod();

		$params = [
			'args' => [
				'another_key' => 1,
			],
		];

		$pods = $this->api->load_pods( $params );

		$pods = wp_list_pluck( $pods, 'id' );

		$this->assertCount( 1, $pods );
		$this->assertContains( $this->pod_id, $pods );

		$params = [
			'args' => [
				'some_custom_key' => 'Some',
			],
		];

		$pods = $this->api->load_pods( $params );

		$this->assertCount( 0, $pods );
	}

	/**
	 * @covers PodsAPI::load_pods
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_pods_by_options() {
		$this->populate_pod();

		$params = [
			'options' => [
				'another_key' => 1,
			],
		];

		$pods = $this->api->load_pods( $params );

		$pods = wp_list_pluck( $pods, 'id' );

		$this->assertCount( 1, $pods );
		$this->assertContains( $this->pod_id, $pods );

		$params = [
			'options' => [
				'some_custom_key' => 'Some',
			],
		];

		$pods = $this->api->load_pods( $params );

		$this->assertCount( 0, $pods );
	}

	/**
	 * @covers PodsAPI::load_pods
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_pods_by_where() {
		$this->populate_pod();

		$params = [
			'where' => [
				[
					'key'   => 'another_key',
					'value' => 1,
				],
			],
		];

		$pods = $this->api->load_pods( $params );

		$pods = wp_list_pluck( $pods, 'id' );

		$this->assertCount( 1, $pods );
		$this->assertContains( $this->pod_id, $pods );

		$params = [
			'where' => [
				[
					'key'   => 'some_custom_key',
					'value' => 'Some',
				],
			],
		];

		$pods = $this->api->load_pods( $params );

		$this->assertCount( 0, $pods );
	}

	/**
	 * @covers PodsAPI::load_pods
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_pods_include_internal() {
		$this->populate_pod();

		$params = [
			'include_internal' => true,
			'name'             => '_pods_pod',
		];

		$pods = $this->api->load_pods( $params );

		$this->assertCount( 1, $pods );
		$this->assertArrayHasKey( '_pods_pod', $pods );

		$params = [
			'name' => '_pods_pod',
		];

		$pods = $this->api->load_pods( $params );

		$this->assertCount( 0, $pods );

		$params = [
			'include_internal' => true,
		];

		$pods = $this->api->load_pods( $params );

		$this->assertArrayHasKey( '_pods_pod', $pods );

		$params = [];

		$pods = $this->api->load_pods( $params );

		$this->assertArrayNotHasKey( '_pods_pod', $pods );
	}

	/**
	 * @covers PodsAPI::load_pods
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_pods_with_names() {
		$this->populate_pod();

		$params = [
			'id'    => $this->pod_id,
			'names' => true,
		];

		$pods = $this->api->load_pods( $params );

		$this->assertCount( 1, $pods );
		$this->assertContains( $this->pod, $pods );

		$params = [
			'id'          => $this->pod_id,
			'return_type' => 'names',
		];

		$pods = $this->api->load_pods( $params );

		$this->assertCount( 1, $pods );
		$this->assertContains( $this->pod, $pods );
	}

	/**
	 * @covers PodsAPI::load_pods
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_pods_with_names_ids() {
		$this->populate_pod();

		$params = [
			'id'        => $this->pod_id,
			'names_ids' => true,
		];

		$pods = $this->api->load_pods( $params );

		$this->assertCount( 1, $pods );
		$this->assertContains( $this->pod, $pods );
		$this->assertArrayHasKey( $this->pod_id, $pods );

		$params = [
			'id'          => $this->pod_id,
			'return_type' => 'names_ids',
		];

		$pods = $this->api->load_pods( $params );

		$this->assertCount( 1, $pods );
		$this->assertContains( $this->pod, $pods );
		$this->assertArrayHasKey( $this->pod_id, $pods );
	}

	/**
	 * @covers PodsAPI::load_pods
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_pods_with_ids() {
		$this->populate_pod();

		$params = [
			'id'          => $this->pod_id,
			'return_type' => 'ids',
		];

		$pods = $this->api->load_pods( $params );

		$this->assertCount( 1, $pods );
		$this->assertContains( $this->pod_id, $pods );
	}

	/**
	 * @covers PodsAPI::load_pods
	 * @since  2.8
	 * @throws \Exception
	 */
	public function test_load_pods_with_count() {
		$this->populate_pod();

		$pod_id2 = $this->api->save_pod( [
			'name'  => 'test_another_pod',
			'type'  => 'taxonomy',
			'label' => 'Another test pod',
		] );

		$params = [
			'id'    => [
				$this->pod_id,
				$pod_id2,
			],
			'count' => true,
		];

		$pods = $this->api->load_pods( $params );

		$this->assertInternalType( 'int', $pods );
		$this->assertEquals( 2, $pods );

		$params = [
			'id'          => [
				$this->pod_id,
				$pod_id2,
			],
			'return_type' => 'count',
		];

		$pods = $this->api->load_pods( $params );

		$this->assertInternalType( 'int', $pods );
		$this->assertEquals( 2, $pods );
	}
}
