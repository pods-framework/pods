<?php

namespace Pods_Unit_Tests\functions\Access;

use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * @group pods
 * @group pods-functions
 * @group pods-functions-access
 */
class TaxonomyTest extends Pods_UnitTestCase {

	protected $public_pod;
	protected $public_pod_name = 'test_pub_ct';
	protected $non_public_pod;
	protected $non_public_pod_name = 'test_non_pub_ct';

	public function setUp(): void {
		parent::setUp();

		$api = pods_api();

		$api->save_pod( [
			'type'    => 'taxonomy',
			'storage' => 'meta',
			'name'    => $this->public_pod_name,
			'public'  => 1,
		] );

		$this->public_pod = pods( $this->public_pod_name );

		$api->save_pod( [
			'type'    => 'taxonomy',
			'storage' => 'meta',
			'name'    => $this->non_public_pod_name,
			'public'  => 0,
		] );

		$this->non_public_pod = pods( $this->non_public_pod_name );
	}

	public function tearDown(): void {
		$this->public_pod     = null;
		$this->non_public_pod = null;

		parent::tearDown();
	}

	public function test_pods_current_user_can_access_object_returns_true_for_admin() {
		wp_set_current_user( 1 );

		$term_data = wp_insert_term( 'Test term', 'category' );

		$this->assertIsArray( $term_data );

		$term_id = $term_data['term_id'];

		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'taxonomy',
				'object_name' => 'category',
				'item_id'     => $term_id,
			],
			1,
			'edit'
		) );
	}

	public function test_pods_current_user_can_access_object_returns_false_for_contributor_user() {
		$user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$this->assertIsInt( $user_id );

		wp_set_current_user( $user_id );

		$term_data = wp_insert_term( 'Test term', 'category' );

		$this->assertIsArray( $term_data );

		$term_id = $term_data['term_id'];

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'taxonomy',
				'object_name' => 'category',
				'item_id'     => $term_id,
			],
			$user_id,
			'edit'
		) );
	}

	public function test_pods_current_user_can_access_object_returns_false_for_anon() {
		$term_data = wp_insert_term( 'Test term', 'category' );

		$this->assertIsArray( $term_data );

		$term_id = $term_data['term_id'];

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'taxonomy',
				'object_name' => 'category',
				'item_id'     => $term_id,
			],
			null,
			'edit'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_admin_on_read() {
		$term_data = wp_insert_term( 'Test term', 'category' );

		$this->assertIsArray( $term_data );

		$term_id = $term_data['term_id'];

		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'taxonomy',
				'object_name' => 'category',
				'item_id'     => $term_id,
			],
			1,
			'read'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_admin_on_add() {
		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'taxonomy',
				'object_name' => 'category',
			],
			1,
			'add'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_admin_on_edit() {
		$term_data = wp_insert_term( 'Test term', 'category' );

		$this->assertIsArray( $term_data );

		$term_id = $term_data['term_id'];

		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'taxonomy',
				'object_name' => 'category',
				'item_id'     => $term_id,
			],
			1,
			'edit'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_admin_on_delete() {
		$term_data = wp_insert_term( 'Test term', 'category' );

		$this->assertIsArray( $term_data );

		$term_id = $term_data['term_id'];

		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'taxonomy',
				'object_name' => 'category',
				'item_id'     => $term_id,
			],
			1,
			'delete'
		) );
	}

	public function test_pods_user_can_access_object_returns_false_for_contributor_user_on_read() {
		$user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$this->assertIsInt( $user_id );

		$term_data = wp_insert_term( 'Test term', 'category' );

		$this->assertIsArray( $term_data );

		$term_id = $term_data['term_id'];

		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'taxonomy',
				'object_name' => 'category',
				'item_id'     => $term_id,
			],
			$user_id,
			'read'
		) );
	}

	public function test_pods_user_can_access_object_returns_false_for_contributor_user_on_edit() {
		$user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$this->assertIsInt( $user_id );

		$term_data = wp_insert_term( 'Test term', 'category' );

		$this->assertIsArray( $term_data );

		$term_id = $term_data['term_id'];

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'taxonomy',
				'object_name' => 'category',
				'item_id'     => $term_id,
			],
			$user_id,
			'edit'
		) );
	}

	public function test_pods_user_can_access_object_returns_false_for_contributor_user_on_delete() {
		$user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$this->assertIsInt( $user_id );

		$term_data = wp_insert_term( 'Test term', 'category' );

		$this->assertIsArray( $term_data );

		$term_id = $term_data['term_id'];

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'taxonomy',
				'object_name' => 'category',
				'item_id'     => $term_id,
			],
			$user_id,
			'delete'
		) );
	}

	public function test_pods_access_map_capabilities_returns_null_for_invalid_object_type_name() {
		$this->assertNull( pods_access_map_capabilities(
			[
				'object_type' => 'invalid',
				'object_name' => 'invalid',
			]
		) );
		$this->assertNull( pods_access_map_capabilities(
			[
				'object_type' => 'taxonomy',
				'object_name' => 'invalid',
			]
		) );
	}

	public function test_pods_access_map_capabilities_returns_capabilities() {
		$this->assertEquals( [
			'read'   => null,
			'add'    => 'manage_categories',
			'edit'   => 'edit_categories',
			'delete' => 'delete_categories',
		], pods_access_map_capabilities( [
			'object_type' => 'taxonomy',
			'object_name' => 'category',
		] ) );
	}

	public function test_pods_is_type_public_returns_true_for_public_type() {
		$this->assertTrue( pods_is_type_public(
			[
				'object_type' => 'taxonomy',
				'object_name' => 'category',
			]
		) );
	}

	public function test_pods_is_type_public_returns_false_for_non_public_type() {
		$this->assertFalse( pods_is_type_public(
			[
				'object_type' => 'taxonomy',
				'object_name' => 'nav_menu',
			]
		) );
	}

	public function test_pods_is_type_public_returns_true_for_public_type_with_pod_object() {
		$this->assertTrue( pods_is_type_public( [
			'pod' => $this->public_pod->pod_data,
		] ) );
	}

	public function test_pods_is_type_public_returns_false_for_non_public_type_with_pod_object() {
		$this->assertFalse( pods_is_type_public( [
			'pod' => $this->non_public_pod->pod_data,
		] ) );
	}

	public function test_pods_access_get_capabilities_preview_returns_expected_number_of_capabilities() {
		$this->assertEquals(
			4,
			substr_count(
				pods_access_get_capabilities_preview( 'taxonomy', 'category' ),
				'<li>'
			)
		);

		$this->assertEquals(
			4,
			substr_count(
				pods_access_get_capabilities_preview( 'taxonomy', $this->public_pod_name ),
				'<li>'
			)
		);

		$this->assertEquals(
			4,
			substr_count(
				pods_access_get_capabilities_preview( 'taxonomy', $this->non_public_pod_name ),
				'<li>'
			)
		);
	}

}
