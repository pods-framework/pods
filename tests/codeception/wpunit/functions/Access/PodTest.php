<?php

namespace Pods_Unit_Tests\functions\Access;

use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * @group pods
 * @group pods-functions
 * @group pods-functions-access
 */
class PodTest extends Pods_UnitTestCase {

	protected $public_pod;
	protected $public_pod_name = 'test_pub_pod';
	protected $non_public_pod;
	protected $non_public_pod_name = 'test_non_pub_pod';
	protected $user_ids = [];

	public function setUp(): void {
		parent::setUp();

		$api = pods_api();

		$api->save_pod( [
			'type'   => 'pod',
			'name'   => $this->public_pod_name,
			'public' => 1,
		] );

		$this->public_pod = pods( $this->public_pod_name );

		$api->save_pod( [
			'type'   => 'pod',
			'name'   => $this->non_public_pod_name,
			'public' => 0,
		] );

		$this->non_public_pod = pods( $this->non_public_pod_name );

		add_role( 'pods_contributor', 'Pods Contributor for test', [
			'read'                                      => true,
			'pods_add_' . $this->non_public_pod_name    => true,
			'pods_edit_' . $this->non_public_pod_name   => true,
			'pods_delete_' . $this->non_public_pod_name => true,
		] );

		add_filter( 'pods_view_cache_alt_get', '__return_true', 11 );
		add_filter( 'pods_view_cache_alt_get_value', '__return_null', 11 );
		add_filter( 'pods_view_cache_alt_set', '__return_true', 11 );
	}

	public function tearDown(): void {
		remove_filter( 'pods_view_cache_alt_get', '__return_true', 11 );
		remove_filter( 'pods_view_cache_alt_get_value', '__return_null', 11 );
		remove_filter( 'pods_view_cache_alt_set', '__return_true', 11 );

		$this->public_pod     = null;
		$this->non_public_pod = null;

		remove_role( 'pods_contributor' );

		parent::tearDown();
	}

	protected function create_test_user( array $user_data ): int {
		$user_id = wp_insert_user( $user_data );

		$this->assertIsInt( $user_id );

		$this->user_ids[] = $user_id;

		return $user_id;
	}

	public function test_pods_current_user_can_access_object_returns_true_for_admin() {
		wp_set_current_user( 1 );

		$user_id = $this->create_test_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$item_id = $this->non_public_pod->add( [
			'name'   => 'Test item ' . __FUNCTION__,
			'author' => $user_id,
		] );

		$this->assertIsInt( $item_id );

		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'pod',
				'object_name' => $this->non_public_pod_name,
				'item_id'     => $item_id,
			],
			1,
			'edit'
		) );
	}

	public function test_pods_current_user_can_access_object_returns_false_for_contributor_user() {
		$user_id = $this->create_test_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$item_id = $this->non_public_pod->add( [
			'name'   => 'Test item ' . __FUNCTION__,
			'author' => $user_id,
		] );

		$this->assertIsInt( $item_id );

		wp_set_current_user( $user_id );

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'pod',
				'object_name' => $this->non_public_pod_name,
				'item_id'     => $item_id,
			],
			$user_id,
			'edit'
		) );
	}

	public function test_pods_current_user_can_access_object_returns_false_for_anon() {
		$user_id = $this->create_test_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$item_id = $this->non_public_pod->add( [
			'name'   => 'Test item ' . __FUNCTION__,
			'author' => $user_id,
		] );

		$this->assertIsInt( $item_id );

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'pod',
				'object_name' => $this->non_public_pod_name,
				'item_id'     => $item_id,
			],
			null,
			'edit'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_admin_on_read() {
		$user_id = $this->create_test_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$item_id = $this->non_public_pod->add( [
			'name'   => 'Test item ' . __FUNCTION__,
			'author' => $user_id,
		] );

		$this->assertIsInt( $item_id );

		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'pod',
				'object_name' => $this->non_public_pod_name,
				'item_id'     => $item_id,
			],
			1,
			'read'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_admin_on_add() {
		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'pod',
				'object_name' => $this->non_public_pod_name,
			],
			1,
			'add'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_admin_on_edit() {
		$user_id = $this->create_test_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$item_id = $this->non_public_pod->add( [
			'name'   => 'Test item ' . __FUNCTION__,
			'author' => $user_id,
		] );

		$this->assertIsInt( $item_id );

		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'pod',
				'object_name' => $this->non_public_pod_name,
				'item_id'     => $item_id,
			],
			1,
			'edit'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_admin_on_delete() {
		$user_id = $this->create_test_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$item_id = $this->non_public_pod->add( [
			'name'   => 'Test item ' . __FUNCTION__,
			'author' => $user_id,
		] );

		$this->assertIsInt( $item_id );

		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'pod',
				'object_name' => $this->non_public_pod_name,
				'item_id'     => $item_id,
			],
			1,
			'delete'
		) );
	}

	public function test_pods_user_can_access_object_returns_false_for_different_user_on_read() {
		$user_id = $this->create_test_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$item_id = $this->non_public_pod->add( [
			'name'   => 'Test item ' . __FUNCTION__,
			'author' => 1,
		] );

		$this->assertIsInt( $item_id );

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'pod',
				'object_name' => $this->non_public_pod_name,
				'item_id'     => $item_id,
			],
			$user_id,
			'read'
		) );
	}

	public function test_pods_user_can_access_object_returns_false_for_different_user_on_edit() {
		$user_id = $this->create_test_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$item_id = $this->non_public_pod->add( [
			'name'   => 'Test item ' . __FUNCTION__,
			'author' => 1,
		] );

		$this->assertIsInt( $item_id );

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'pod',
				'object_name' => $this->non_public_pod_name,
				'item_id'     => $item_id,
			],
			$user_id,
			'edit'
		) );
	}

	public function test_pods_user_can_access_object_returns_false_for_different_user_on_delete() {
		$user_id = $this->create_test_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$item_id = $this->non_public_pod->add( [
			'name'   => 'Test item ' . __FUNCTION__,
			'author' => 1,
		] );

		$this->assertIsInt( $item_id );

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'pod',
				'object_name' => $this->non_public_pod_name,
				'item_id'     => $item_id,
			],
			$user_id,
			'delete'
		) );
	}

	public function test_pods_user_can_access_object_returns_false_for_same_user_with_no_basic_caps_on_read() {
		$user_id = $this->create_test_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$item_id = $this->non_public_pod->add( [
			'name'   => 'Test item ' . __FUNCTION__,
			'author' => $user_id,
		] );

		$this->assertIsInt( $item_id );

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'pod',
				'object_name' => $this->non_public_pod_name,
				'item_id'     => $item_id,
			],
			$user_id,
			'read'
		) );
	}

	public function test_pods_user_can_access_object_returns_false_for_same_user_with_no_basic_caps_on_edit() {
		$user_id = $this->create_test_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$item_id = $this->non_public_pod->add( [
			'name'   => 'Test item ' . __FUNCTION__,
			'author' => $user_id,
		] );

		$this->assertIsInt( $item_id );

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'pod',
				'object_name' => $this->non_public_pod_name,
				'item_id'     => $item_id,
			],
			$user_id,
			'edit'
		) );
	}

	public function test_pods_user_can_access_object_returns_false_for_same_user_with_no_basic_caps_on_delete() {
		$user_id = $this->create_test_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$item_id = $this->non_public_pod->add( [
			'name'   => 'Test item ' . __FUNCTION__,
			'author' => $user_id,
		] );

		$this->assertIsInt( $item_id );

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'pod',
				'object_name' => $this->non_public_pod_name,
				'item_id'     => $item_id,
			],
			$user_id,
			'delete'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_same_user_with_basic_caps_on_read() {
		$user_id = $this->create_test_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'pods_contributor',
		] );

		$item_id = $this->non_public_pod->add( [
			'name'   => 'Test item ' . __FUNCTION__,
			'author' => $user_id,
		] );

		$this->assertIsInt( $item_id );

		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'pod',
				'object_name' => $this->non_public_pod_name,
				'item_id'     => $item_id,
			],
			$user_id,
			'read'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_same_user_with_basic_caps_on_edit() {
		$user_id = $this->create_test_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'pods_contributor',
		] );

		$item_id = $this->non_public_pod->add( [
			'name'   => 'Test item ' . __FUNCTION__,
			'author' => $user_id,
		] );

		$this->assertIsInt( $item_id );

		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'pod',
				'object_name' => $this->non_public_pod_name,
				'item_id'     => $item_id,
			],
			$user_id,
			'edit'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_same_user_with_basic_caps_on_delete() {
		$user_id = $this->create_test_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'pods_contributor',
		] );

		$item_id = $this->non_public_pod->add( [
			'name'   => 'Test item ' . __FUNCTION__,
			'author' => $user_id,
		] );

		$this->assertIsInt( $item_id );

		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'pod',
				'object_name' => $this->non_public_pod_name,
				'item_id'     => $item_id,
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
				'object_type' => 'pod',
				'object_name' => 'invalid',
			]
		) );
	}

	public function test_pods_access_map_capabilities_returns_capabilities() {
		$this->assertEquals( [
			'read'          => null,
			'add'           => 'pods_add_' . $this->public_pod_name,
			'edit'          => 'pods_edit_' . $this->public_pod_name,
			'delete'        => 'pods_delete_' . $this->public_pod_name,
			'edit_others'   => 'pods_edit_others_' . $this->public_pod_name,
			'delete_others' => 'pods_delete_others_' . $this->public_pod_name,
		], pods_access_map_capabilities( [
			'object_type' => 'pod',
			'object_name' => $this->public_pod_name,
		] ) );
	}

	public function test_pods_access_map_capabilities_returns_capabilities_for_private_pod() {
		$item_id = $this->non_public_pod->add( [
			'name'   => 'Test item ' . __FUNCTION__,
			'author' => 1,
		] );

		$this->assertIsInt( $item_id );

		$this->assertEquals( [
			'read'          => 'pods_read_' . $this->non_public_pod_name,
			'add'           => 'pods_add_' . $this->non_public_pod_name,
			'edit'          => 'pods_edit_others_' . $this->non_public_pod_name,
			'delete'        => 'pods_delete_others_' . $this->non_public_pod_name,
			'edit_others'   => 'pods_edit_others_' . $this->non_public_pod_name,
			'delete_others' => 'pods_delete_others_' . $this->non_public_pod_name,
		], pods_access_map_capabilities( [
			'object_type' => 'pod',
			'object_name' => $this->non_public_pod_name,
			'item_id'     => $item_id,
		] ) );
	}

	public function test_pods_access_map_capabilities_returns_capabilities_for_private_pod_for_author() {
		$item_id = $this->non_public_pod->add( [
			'name'   => 'Test item ' . __FUNCTION__,
			'author' => 1,
		] );

		$this->assertIsInt( $item_id );

		$this->assertEquals( [
			'read'          => [
				'pods_read_' . $this->non_public_pod_name,
				'pods_edit_' . $this->non_public_pod_name,
			],
			'add'           => 'pods_add_' . $this->non_public_pod_name,
			'edit'          => 'pods_edit_' . $this->non_public_pod_name,
			'delete'        => 'pods_delete_' . $this->non_public_pod_name,
			'edit_others'   => 'pods_edit_others_' . $this->non_public_pod_name,
			'delete_others' => 'pods_delete_others_' . $this->non_public_pod_name,
		], pods_access_map_capabilities( [
			'object_type' => 'pod',
			'object_name' => $this->non_public_pod_name,
			'item_id'     => $item_id,
		], 1 ) );
	}

	public function test_pods_access_map_capabilities_returns_capabilities_for_private_pod_for_non_existing_item() {
		$item_id = $this->non_public_pod->add( [
			'name'   => 'Test item ' . __FUNCTION__,
			'author' => 1,
		] );

		$this->assertIsInt( $item_id );

		// Item doesn't exist so they aren't technically the author.
		$this->assertEquals( [
			'read'          => 'pods_read_' . $this->non_public_pod_name,
			'add'           => 'pods_add_' . $this->non_public_pod_name,
			'edit'          => 'pods_edit_' . $this->non_public_pod_name,
			'delete'        => 'pods_delete_' . $this->non_public_pod_name,
			'edit_others'   => 'pods_edit_others_' . $this->non_public_pod_name,
			'delete_others' => 'pods_delete_others_' . $this->non_public_pod_name,
		], pods_access_map_capabilities( [
			'object_type' => 'pod',
			'object_name' => $this->non_public_pod_name,
			'item_id'     => $item_id + 1000,
		], 1 ) );
	}

	public function test_pods_access_map_capabilities_returns_capabilities_for_private_pod_for_non_author() {
		$item_id = $this->non_public_pod->add( [
			'name'   => 'Test item ' . __FUNCTION__,
			'author' => 1,
		] );

		$this->assertIsInt( $item_id );

		$user_id = $this->create_test_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$this->assertEquals( [
			'read'          => 'pods_read_' . $this->non_public_pod_name,
			'add'           => 'pods_add_' . $this->non_public_pod_name,
			'edit'          => 'pods_edit_others_' . $this->non_public_pod_name,
			'delete'        => 'pods_delete_others_' . $this->non_public_pod_name,
			'edit_others'   => 'pods_edit_others_' . $this->non_public_pod_name,
			'delete_others' => 'pods_delete_others_' . $this->non_public_pod_name,
		], pods_access_map_capabilities( [
			'object_type' => 'pod',
			'object_name' => $this->non_public_pod_name,
			'item_id'     => $item_id,
		], $user_id ) );
	}

	public function test_pods_is_type_public_returns_true_for_public_type() {
		$this->assertTrue( pods_is_type_public(
			[
				'object_type' => 'pod',
				'object_name' => $this->public_pod_name,
			]
		) );
	}

	public function test_pods_is_type_public_returns_false_for_non_public_type() {
		$this->assertFalse( pods_is_type_public(
			[
				'object_type' => 'pod',
				'object_name' => $this->non_public_pod_name,
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
			6,
			substr_count(
				pods_access_get_capabilities_preview( 'pod', $this->public_pod_name ),
				'<li>'
			)
		);

		$this->assertEquals(
			6,
			substr_count(
				pods_access_get_capabilities_preview( 'pod', $this->non_public_pod_name ),
				'<li>'
			)
		);
	}

}
