<?php

namespace Pods_Unit_Tests\Pods;

use Pods;
use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * Class MappingTest
 *
 * @todo    Add image_fields and avatar tests.
 *
 * @package Pods_Unit_Tests
 *
 * @group   pods-mapping
 * @group   pods-field
 */
class MappingTest extends Pods_UnitTestCase {

	public static $db_reset_teardown = false;

	private $pod_id;
	private $pod_name = 'mappingtest';
	private $pod_label = 'Mapping Test';
	private $pod_type = 'post_type';
	private $pod_storage = 'meta';
	private $field_id;
	private $field_name = 'test_field';
	private $field_label = 'Test field';
	private $field_type = 'text';
	private $item_id;

	/**
	 * The pods system under test
	 *
	 * @var   \Pods
	 */
	private $pod;

	public function setUp() : void {
		parent::setUp();

		$params = [
			'name'    => $this->pod_name,
			'label'   => $this->pod_label,
			'type'    => $this->pod_type,
			'storage' => $this->pod_storage,
		];

		$api = pods_api();

		// Save Pod similar to PodsAdmin.
		$this->pod_id = $api->save_pod( $params );

		$field_params = [
			'pod_id' => $this->pod_id,
			'name'   => $this->field_name,
			'label'  => $this->field_label,
			'type'   => $this->field_type,
		];

		$this->field_id = $api->save_field( $field_params );

		$pod = pods( $this->pod_name );

		$this->item_id = $pod->add( [
			'post_title'   => 'Test title',
			'post_content' => 'Test content',
			'post_status'  => 'publish',
		] );
	}

	public function tearDown() : void {
		$api = pods_api();

		// Delete all posts.
		$api->reset_pod( [ 'name' => $this->pod_name ] );

		// Delete the pod config.
		$api->delete_pod( [ 'name' => $this->pod_name ] );

		parent::tearDown();
	}

	/**
	 * @covers \Pods\Data\Map_Field_Values::map_value
	 */
	public function test_default() {
		$pod = pods( $this->pod_name, $this->item_id );

		$this->assertEquals( [], $pod->field( 'any_map' ) );
	}

	/**
	 * @covers \Pods\Data\Map_Field_Values::custom
	 */
	public function test_custom() {
		add_filter( 'pods_data_map_field_values_custom', '__return_zero' );

		$pod = pods( $this->pod_name, $this->item_id );

		$this->assertEquals( 0, $pod->field( 'any_map' ) );

		remove_filter( 'pods_data_map_field_values_custom', '__return_zero' );
	}

	/**
	 * @covers \Pods\Data\Map_Field_Values::pod_info
	 */
	public function test_pod_info() {
		$pod = pods( $this->pod_name, $this->item_id );

		$this->assertEquals( $this->pod_name, $pod->field( '_pod' ) );

		$this->assertEquals( $this->pod_id, $pod->field( '_pod.id' ) );
		$this->assertEquals( $this->pod_id, $pod->field( '_pod.ID' ) );
		$this->assertEquals( $this->pod_name, $pod->field( '_pod.name' ) );
		$this->assertEquals( $this->pod_label, $pod->field( '_pod.label' ) );
		$this->assertEquals( $this->pod_type, $pod->field( '_pod.type' ) );
		$this->assertEquals( $this->pod_storage, $pod->field( '_pod.storage' ) );

		$this->assertEquals( '', $pod->field( '_pod.any_non_option' ) );
	}

	/**
	 * @covers \Pods\Data\Map_Field_Values::field_info
	 */
	public function test_field_info() {
		$pod = pods( $this->pod_name, $this->item_id );

		$this->assertEquals( $this->field_label, $pod->field( '_field.' . $this->field_name ) );

		$this->assertEquals( $this->field_id, $pod->field( '_field.' . $this->field_name . '.id' ) );
		$this->assertEquals( $this->field_id, $pod->field( '_field.' . $this->field_name . '.ID' ) );
		$this->assertEquals( $this->field_name, $pod->field( '_field.' . $this->field_name . '.name' ) );
		$this->assertEquals( $this->field_label, $pod->field( '_field.' . $this->field_name . '.label' ) );
		$this->assertEquals( $this->field_type, $pod->field( '_field.' . $this->field_name . '.type' ) );

		$this->assertEquals( '', $pod->field( '_field.' . $this->field_name . '.any_non_option' ) );
	}

	/**
	 * @covers \Pods\Data\Map_Field_Values::context_info
	 */
	public function test_context_info() {
		$pod = pods( $this->pod_name, $this->item_id );

		$_GET['some-value'] = '123';

		$this->assertEquals( '123', $pod->field( '_context.get.some-value' ) );

		$_POST['some-value2'] = '456';

		$this->assertEquals( '456', $pod->field( '_context.post.some-value2' ) );

		global $wpdb;

		$this->assertEquals( $wpdb->prefix, $pod->field( '_context.prefix' ) );
	}

	/**
	 * @covers \Pods\Data\Map_Field_Values::calculation
	 */
	public function test_calculation() {
		$pod = pods( $this->pod_name );

		// Add 4 more tests so we have 5 total.
		$pod->add( [
			'post_title'   => 'Test title 2',
			'post_name'    => 'test-title-2',
			'post_content' => 'Test content',
			'post_status'  => 'publish',
		] );

		$pod->add( [
			'post_title'   => 'Test title 3',
			'post_name'    => 'test-title-3',
			'post_content' => 'Test content',
			'post_status'  => 'publish',
		] );

		$pod->add( [
			'post_title'   => 'Test title 4',
			'post_name'    => 'test-title-4',
			'post_content' => 'Test content',
			'post_status'  => 'publish',
		] );

		$pod->add( [
			'post_title'   => 'Test title 5',
			'post_name'    => 'test-title-5',
			'post_content' => 'Test content',
			'post_status'  => 'publish',
		] );

		$pod = pods( $this->pod_name );

		$pod->find( [
			// Limit to 2 and expect 3 pages.
			'limit' => 2,
		] );

		// Defaults prior to loop.
		$zebra    = true;
		$position = 0;

		$this->assertEquals( (int) $zebra, $pod->zebra() );
		$this->assertEquals( $position, $pod->position() );
		$this->assertEquals( 2, $pod->total() );
		$this->assertEquals( 5, $pod->total_found() );
		$this->assertEquals( 3, $pod->total_pages() );

		while ( $pod->fetch() ) {
			$position ++;
			$zebra = ! $zebra;

			$this->assertEquals( (int) $zebra, $pod->field( '_zebra' ) );
			$this->assertEquals( $position, $pod->field( '_position' ) );
			$this->assertEquals( 2, $pod->field( '_total' ) );
			$this->assertEquals( 5, $pod->field( '_total_found' ) );
			$this->assertEquals( 3, $pod->field( '_total_pages' ) );
		}
	}

}
