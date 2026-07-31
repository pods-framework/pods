<?php

namespace Pods_Unit_Tests\Pods;

use Pods\Whatsit\Field;
use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * Bi-directional (sister_id) references through package export/import.
 *
 * Packages store sister_id as a source-site wp_posts.ID; export emits a
 * portable sister_field name and import remaps references after all pods
 * are saved.
 *
 * @group  pods
 * @group  pods-migrate-packages
 * @covers Pods_Migrate_Packages
 */
class MigratePackagesSisterIdTest extends Pods_UnitTestCase {

	/**
	 * @var \PodsAPI
	 */
	protected $api;

	/**
	 * @var string
	 */
	protected $pod_name = 'test_sister_a';

	/**
	 * @var string
	 */
	protected $pod_name2 = 'test_sister_b';

	/**
	 * @var int
	 */
	protected $pod_id = 0;

	/**
	 * @var int
	 */
	protected $pod_id2 = 0;

	/**
	 * @var int
	 */
	protected $field_id = 0;

	/**
	 * @var int
	 */
	protected $field_id2 = 0;

	public function setUp(): void {
		parent::setUp();

		$this->api = pods_api();

		$components = \PodsInit::$components;

		$components->load();
		$components->activate_component( 'migrate-packages' );
		$components->load();

		$this->populate();
	}

	public function tearDown(): void {
		$this->api = null;

		parent::tearDown();
	}

	public function populate() {
		$this->pod_id = $this->api->save_pod( [
			'storage' => 'meta',
			'type'    => 'post_type',
			'name'    => $this->pod_name,
		] );

		$this->pod_id2 = $this->api->save_pod( [
			'storage' => 'meta',
			'type'    => 'post_type',
			'name'    => $this->pod_name2,
		] );

		$this->field_id = $this->api->save_field( [
			'pod_id'           => $this->pod_id,
			'name'             => 'rel_b',
			'type'             => 'pick',
			'pick_object'      => 'post_type',
			'pick_val'         => $this->pod_name2,
			'pick_format_type' => 'multi',
		] );

		$this->field_id2 = $this->api->save_field( [
			'pod_id'           => $this->pod_id2,
			'name'             => 'rel_a',
			'type'             => 'pick',
			'pick_object'      => 'post_type',
			'pick_val'         => $this->pod_name,
			'pick_format_type' => 'multi',
			'sister_id'        => $this->field_id,
		] );

		// Complete the reciprocal reference.
		$this->api->save_field( [
			'pod_id'    => $this->pod_id,
			'id'        => $this->field_id,
			'name'      => 'rel_b',
			'sister_id' => $this->field_id2,
		] );

		$this->api->cache_flush_pods();
	}

	/**
	 * @return string The package JSON.
	 */
	protected function export_package() {
		$json = \Pods_Migrate_Packages::export( [
			'pods' => [ $this->pod_id, $this->pod_id2 ],
		] );

		$this->assertNotFalse( $json );

		return $json;
	}

	/**
	 * @param array $package The decoded package data.
	 *
	 * @return array Map of pod name => field name => field args.
	 */
	protected function package_fields( array $package ) {
		$fields = [];

		foreach ( $package['pods'] as $pod ) {
			foreach ( $pod['groups'] as $group ) {
				foreach ( $group['fields'] as $field ) {
					$fields[ $pod['name'] ][ $field['name'] ] = $field;
				}
			}
		}

		return $fields;
	}

	protected function assert_reciprocal() {
		$field_a = $this->api->load_field( [ 'name' => 'rel_b', 'pod' => $this->pod_name ] );
		$field_b = $this->api->load_field( [ 'name' => 'rel_a', 'pod' => $this->pod_name2 ] );

		$this->assertInstanceOf( Field::class, $field_a );
		$this->assertInstanceOf( Field::class, $field_b );

		$this->assertSame( (int) $field_b->get_id(), (int) $field_a->get_arg( 'sister_id' ) );
		$this->assertSame( (int) $field_a->get_id(), (int) $field_b->get_arg( 'sister_id' ) );

		$bidirectional = $field_a->get_bidirectional_field();

		$this->assertInstanceOf( Field::class, $bidirectional );
		$this->assertSame( 'rel_a', $bidirectional->get_name() );
	}

	public function test_export_emits_sister_field_names() {
		$fields = $this->package_fields( json_decode( $this->export_package(), true ) );

		$this->assertSame( 'rel_a', $fields[ $this->pod_name ]['rel_b']['sister_field'] );
		$this->assertSame( 'rel_b', $fields[ $this->pod_name2 ]['rel_a']['sister_field'] );
	}

	public function test_import_remaps_sister_id_when_fields_get_new_ids() {
		$json = $this->export_package();

		$old_field_id  = $this->field_id;
		$old_field_id2 = $this->field_id2;

		$this->api->delete_pod( [ 'id' => $this->pod_id ] );
		$this->api->delete_pod( [ 'id' => $this->pod_id2 ] );
		$this->api->cache_flush_pods();

		$this->assertNotFalse( \Pods_Migrate_Packages::import( $json ) );

		$this->assert_reciprocal();

		// The re-imported fields are new posts; the remap must land on the new IDs.
		$field_a = $this->api->load_field( [ 'name' => 'rel_b', 'pod' => $this->pod_name ] );

		$this->assertNotEquals( $old_field_id, (int) $field_a->get_id() );
		$this->assertNotEquals( $old_field_id2, (int) $field_a->get_arg( 'sister_id' ) );
	}

	public function test_import_remaps_via_package_ids_when_sister_field_absent() {
		$package = json_decode( $this->export_package(), true );

		// Simulate a package from before sister_field existed.
		foreach ( $package['pods'] as &$pod ) {
			foreach ( $pod['groups'] as &$group ) {
				foreach ( $group['fields'] as &$field ) {
					unset( $field['sister_field'] );
				}
			}
		}

		unset( $pod, $group, $field );

		$this->api->delete_pod( [ 'id' => $this->pod_id ] );
		$this->api->delete_pod( [ 'id' => $this->pod_id2 ] );
		$this->api->cache_flush_pods();

		$this->assertNotFalse( \Pods_Migrate_Packages::import( wp_json_encode( $package ) ) );

		$this->assert_reciprocal();
	}

	public function test_import_clears_unresolvable_sister_id() {
		$package = json_decode( $this->export_package(), true );

		// Keep only the first pod; its sister field's pod never gets imported.
		$package['pods'] = array_values( array_filter( $package['pods'], function ( $pod ) {
			return $this->pod_name === $pod['name'];
		} ) );

		$this->api->delete_pod( [ 'id' => $this->pod_id ] );
		$this->api->delete_pod( [ 'id' => $this->pod_id2 ] );
		$this->api->cache_flush_pods();

		$this->assertNotFalse( \Pods_Migrate_Packages::import( wp_json_encode( $package ) ) );

		$field_a = $this->api->load_field( [ 'name' => 'rel_b', 'pod' => $this->pod_name ] );

		$this->assertInstanceOf( Field::class, $field_a );
		$this->assertSame( 0, (int) $field_a->get_arg( 'sister_id' ) );
	}

	public function test_import_leaves_fields_without_package_reference_untouched() {
		$package = json_decode( $this->export_package(), true );

		// The package carries no sister reference for rel_b.
		foreach ( $package['pods'] as &$pod ) {
			foreach ( $pod['groups'] as &$group ) {
				foreach ( $group['fields'] as &$field ) {
					if ( 'rel_b' === $field['name'] ) {
						unset( $field['sister_field'], $field['sister_id'] );
					}
				}
			}
		}

		unset( $pod, $group, $field );

		// Import over the existing pods; rel_b's live sister_id must survive.
		$this->assertNotFalse( \Pods_Migrate_Packages::import( wp_json_encode( $package ) ) );

		$field_a = $this->api->load_field( [ 'name' => 'rel_b', 'pod' => $this->pod_name ] );

		$this->assertSame( $this->field_id2, (int) $field_a->get_arg( 'sister_id' ) );
	}

	public function test_save_field_skips_sister_meta_on_non_field_posts() {
		$post_id = wp_insert_post( [
			'post_title'  => 'Not a Pods field',
			'post_type'   => 'post',
			'post_status' => 'publish',
		] );

		$this->api->save_field( [
			'pod_id'    => $this->pod_id,
			'id'        => $this->field_id,
			'name'      => 'rel_b',
			'sister_id' => $post_id,
		] );

		$this->assertSame( '', get_post_meta( $post_id, 'sister_id', true ) );
	}
}
