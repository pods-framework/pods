<?php

namespace Pods_Unit_Tests\functions\Access;

use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * @group pods
 * @group pods-functions
 * @group pods-functions-access
 */
class UserTest extends Pods_UnitTestCase {

	public function test_pods_current_user_can_access_object_returns_true_for_admin() {
		wp_set_current_user( 1 );

		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'user',
				'object_name' => null,
				'item_id'     => 1,
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

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'user',
				'object_name' => null,
				'item_id'     => 1,
			],
			$user_id,
			'edit'
		) );
	}

	public function test_pods_current_user_can_access_object_returns_true_for_contributor_as_same_user() {
		$user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$this->assertIsInt( $user_id );

		wp_set_current_user( $user_id );

		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'user',
				'object_name' => null,
				'item_id'     => $user_id,
			],
			$user_id,
			'edit'
		) );
	}

	public function test_pods_current_user_can_access_object_returns_false_for_anon() {
		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'user',
				'object_name' => null,
				'item_id'     => 1,
			],
			null,
			'edit'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_admin_on_read() {
		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'user',
				'object_name' => null,
				'item_id'     => 1,
			],
			1,
			'read'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_admin_on_add() {
		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'user',
				'object_name' => null,
				'item_id'     => 1,
			],
			1,
			'add'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_admin_on_edit() {
		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'user',
				'object_name' => null,
				'item_id'     => 1,
			],
			1,
			'edit'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_admin_on_delete() {
		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'user',
				'object_name' => null,
				'item_id'     => 1,
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

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'user',
				'object_name' => null,
				'item_id'     => 1,
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

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'user',
				'object_name' => null,
				'item_id'     => 1,
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

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'user',
				'object_name' => null,
				'item_id'     => 1,
			],
			$user_id,
			'delete'
		) );
	}

	public function test_pods_user_can_access_object_returns_false_for_contributor_as_same_user_on_read() {
		$user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$this->assertIsInt( $user_id );

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'user',
				'object_name' => null,
				'item_id'     => $user_id,
			],
			$user_id,
			'read'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_contributor_as_same_user_on_edit() {
		$user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$this->assertIsInt( $user_id );

		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'user',
				'object_name' => null,
				'item_id'     => $user_id,
			],
			$user_id,
			'edit'
		) );
	}

	public function test_pods_user_can_access_object_returns_false_for_contributor_as_same_user_on_delete() {
		$user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$this->assertIsInt( $user_id );

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'user',
				'object_name' => null,
				'item_id'     => $user_id,
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
	}

	public function test_pods_access_map_capabilities_returns_capabilities() {
		$this->assertEquals( [
			'read'   => 'list_users',
			'add'    => 'create_users',
			'edit'   => 'edit_users',
			'delete' => 'delete_users',
		], pods_access_map_capabilities( [
			'object_type' => 'user',
		] ) );

		$this->assertEquals( [
			'read'   => 'list_users',
			'add'    => 'create_users',
			'edit'   => 'edit_users',
			'delete' => 'delete_users',
		], pods_access_map_capabilities( [
			'object_type' => 'user',
			'object_name' => 'user',
		] ) );
	}

	public function test_pods_is_type_public_returns_true_for_non_shortcode_context() {
		$this->assertTrue( pods_is_type_public(
			[
				'object_type' => 'user',
			],
			'something-else'
		) );
	}

	public function test_pods_is_type_public_returns_false_for_shortcode_context() {
		$this->assertFalse( pods_is_type_public(
			[
				'object_type' => 'user',
				'object_name' => null,
			]
		) );
	}

	public function test_pods_access_get_capabilities_preview_returns_expected_number_of_capabilities() {
		$this->assertEquals(
			4,
			substr_count(
				pods_access_get_capabilities_preview( 'user', 'user' ),
				'<li>'
			)
		);

		$this->assertEquals(
			4,
			substr_count(
				pods_access_get_capabilities_preview( 'user', 'user' ),
				'<li>'
			)
		);

		$this->assertEquals(
			4,
			substr_count(
				pods_access_get_capabilities_preview( 'user', 'user' ),
				'<li>'
			)
		);
	}

}
