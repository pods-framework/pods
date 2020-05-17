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

	public function setUp() {
		parent::setUp();

		$this->api = pods_api();
	}

	/**
	 *
	 */
	public function tearDown() {
		$this->api = null;

		parent::tearDown();
	}

	public function populate_pod() {
		$this->pod    = 'test_pod';
		$this->pod_id = $this->api->save_pod( [
			'name'  => $this->pod,
			'type'  => 'post_type',
			'label' => 'Test pod',
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
}
