<?php

namespace Pods_Unit_Tests\functions\Access;

use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * @group pods
 * @group pods-functions
 * @group pods-functions-access
 */
class PostTypeTest extends Pods_UnitTestCase {

	protected $public_pod;
	protected $public_pod_name = 'test_pub_cpt';
	protected $non_public_pod;
	protected $non_public_pod_name = 'test_non_pub_cpt';

	public function setUp(): void {
		parent::setUp();

		$api = pods_api();

		$api->save_pod( [
			'type'    => 'post_type',
			'storage' => 'meta',
			'name'    => $this->public_pod_name,
			'public'  => 1,
		] );

		$this->public_pod = pods( $this->public_pod_name );

		$api->save_pod( [
			'type'    => 'post_type',
			'storage' => 'meta',
			'name'    => $this->non_public_pod_name,
			'public'  => 0,
		] );

		$this->non_public_pod = pods( $this->non_public_pod_name );
	}

	public function tearDown(): void {
		$this->public_pod     = null;
		$this->non_public_pod = null;

		unset( $_COOKIE[ 'wp-postpass_' . COOKIEHASH ] );

		parent::tearDown();
	}

	private function set_post_password_cookie( string $password ) {
		require_once ABSPATH . WPINC . '/class-phpass.php';
		$hasher = new \PasswordHash( 8, true );

		$_COOKIE['wp-postpass_' . COOKIEHASH] = $hasher->HashPassword( wp_unslash( $password ) );
	}

	public function test_pods_current_user_can_access_object_returns_true_for_admin() {
		wp_set_current_user( 1 );

		$post_id = wp_insert_post( [
			'post_title'   => 'Test post',
			'post_content' => 'Test content',
			'post_type'    => 'post',
			'post_status'  => 'private',
			'post_author'  => 1,
		] );

		$this->assertIsInt( $post_id );

		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'post_type',
				'object_name' => 'post',
				'item_id'     => $post_id,
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

		$post_id = wp_insert_post( [
			'post_title'   => 'Test post',
			'post_content' => 'Test content',
			'post_type'    => 'post',
			'post_status'  => 'private',
			'post_author'  => 1,
		] );

		$this->assertIsInt( $post_id );

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'post_type',
				'object_name' => 'post',
				'item_id'     => $post_id,
			],
			$user_id,
			'edit'
		) );
	}

	public function test_pods_current_user_can_access_object_returns_false_for_anon() {
		$post_id = wp_insert_post( [
			'post_title'   => 'Test post',
			'post_content' => 'Test content',
			'post_type'    => 'post',
			'post_status'  => 'private',
			'post_author'  => 1,
		] );

		$this->assertIsInt( $post_id );

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'post_type',
				'object_name' => 'post',
				'item_id'     => $post_id,
			],
			null,
			'edit'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_admin_on_read() {
		$post_id = wp_insert_post( [
			'post_title'   => 'Test post',
			'post_content' => 'Test content',
			'post_type'    => 'post',
			'post_status'  => 'private',
			'post_author'  => 2,
		] );

		$this->assertIsInt( $post_id );

		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'post_type',
				'object_name' => 'post',
				'item_id'     => $post_id,
			],
			1,
			'read'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_admin_on_add() {
		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'post_type',
				'object_name' => 'post',
			],
			1,
			'add'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_admin_on_edit() {
		$post_id = wp_insert_post( [
			'post_title'   => 'Test post',
			'post_content' => 'Test content',
			'post_type'    => 'post',
			'post_status'  => 'private',
			'post_author'  => 2,
		] );

		$this->assertIsInt( $post_id );

		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'post_type',
				'object_name' => 'post',
				'item_id'     => $post_id,
			],
			1,
			'edit'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_admin_on_delete() {
		$post_id = wp_insert_post( [
			'post_title'   => 'Test post',
			'post_content' => 'Test content',
			'post_type'    => 'post',
			'post_status'  => 'private',
			'post_author'  => 2,
		] );

		$this->assertIsInt( $post_id );

		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'post_type',
				'object_name' => 'post',
				'item_id'     => $post_id,
			],
			1,
			'delete'
		) );
	}

	public function test_pods_user_can_access_object_returns_false_for_different_user_on_read() {
		$user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$this->assertIsInt( $user_id );

		$post_id = wp_insert_post( [
			'post_title'   => 'Test post',
			'post_content' => 'Test content',
			'post_type'    => 'post',
			'post_status'  => 'private',
			'post_author'  => 1,
		] );

		$this->assertIsInt( $post_id );

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'post_type',
				'object_name' => 'post',
				'item_id'     => $post_id,
			],
			$user_id,
			'read'
		) );
	}

	public function test_pods_user_can_access_object_returns_false_for_different_user_on_edit() {
		$user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$this->assertIsInt( $user_id );

		$post_id = wp_insert_post( [
			'post_title'   => 'Test post',
			'post_content' => 'Test content',
			'post_type'    => 'post',
			'post_status'  => 'private',
			'post_author'  => 1,
		] );

		$this->assertIsInt( $post_id );

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'post_type',
				'object_name' => 'post',
				'item_id'     => $post_id,
			],
			$user_id,
			'edit'
		) );
	}

	public function test_pods_user_can_access_object_returns_false_for_different_user_on_delete() {
		$user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$this->assertIsInt( $user_id );

		$post_id = wp_insert_post( [
			'post_title'   => 'Test post',
			'post_content' => 'Test content',
			'post_type'    => 'post',
			'post_status'  => 'private',
			'post_author'  => 1,
		] );

		$this->assertIsInt( $post_id );

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'post_type',
				'object_name' => 'post',
				'item_id'     => $post_id,
			],
			$user_id,
			'delete'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_same_user_on_read() {
		$user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$this->assertIsInt( $user_id );

		$post_id = wp_insert_post( [
			'post_title'   => 'Test post',
			'post_content' => 'Test content',
			'post_type'    => 'post',
			'post_status'  => 'private',
			'post_author'  => $user_id,
		] );

		$this->assertIsInt( $post_id );

		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'post_type',
				'object_name' => 'post',
				'item_id'     => $post_id,
			],
			$user_id,
			'read'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_same_user_on_edit() {
		$user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$this->assertIsInt( $user_id );

		$post_id = wp_insert_post( [
			'post_title'   => 'Test post',
			'post_content' => 'Test content',
			'post_type'    => 'post',
			'post_status'  => 'private',
			'post_author'  => $user_id,
		] );

		$this->assertIsInt( $post_id );

		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'post_type',
				'object_name' => 'post',
				'item_id'     => $post_id,
			],
			$user_id,
			'edit'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_same_user_on_delete() {
		$user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$this->assertIsInt( $user_id );

		$post_id = wp_insert_post( [
			'post_title'   => 'Test post',
			'post_content' => 'Test content',
			'post_type'    => 'post',
			'post_status'  => 'private',
			'post_author'  => $user_id,
		] );

		$this->assertIsInt( $post_id );

		$this->assertTrue( pods_user_can_access_object(
			[
				'object_type' => 'post_type',
				'object_name' => 'post',
				'item_id'     => $post_id,
			],
			$user_id,
			'delete'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_anon_on_read() {
		$user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$this->assertIsInt( $user_id );

		$post_id = wp_insert_post( [
			'post_title'   => 'Test post',
			'post_content' => 'Test content',
			'post_type'    => 'post',
			'post_status'  => 'private',
			'post_author'  => $user_id,
		] );

		$this->assertIsInt( $post_id );

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'post_type',
				'object_name' => 'post',
				'item_id'     => $post_id,
			],
			0,
			'read'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_anon_on_add() {
		$user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$this->assertIsInt( $user_id );

		$post_id = wp_insert_post( [
			'post_title'   => 'Test post',
			'post_content' => 'Test content',
			'post_type'    => 'post',
			'post_status'  => 'private',
			'post_author'  => $user_id,
		] );

		$this->assertIsInt( $post_id );

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'post_type',
				'object_name' => 'post',
				'item_id'     => $post_id,
			],
			0,
			'add'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_anon_on_edit() {
		$user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$this->assertIsInt( $user_id );

		$post_id = wp_insert_post( [
			'post_title'   => 'Test post',
			'post_content' => 'Test content',
			'post_type'    => 'post',
			'post_status'  => 'private',
			'post_author'  => $user_id,
		] );

		$this->assertIsInt( $post_id );

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'post_type',
				'object_name' => 'post',
				'item_id'     => $post_id,
			],
			0,
			'edit'
		) );
	}

	public function test_pods_user_can_access_object_returns_true_for_anon_on_delete() {
		$user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		$this->assertIsInt( $user_id );

		$post_id = wp_insert_post( [
			'post_title'   => 'Test post',
			'post_content' => 'Test content',
			'post_type'    => 'post',
			'post_status'  => 'private',
			'post_author'  => $user_id,
		] );

		$this->assertIsInt( $post_id );

		$this->assertFalse( pods_user_can_access_object(
			[
				'object_type' => 'post_type',
				'object_name' => 'post',
				'item_id'     => $post_id,
			],
			0,
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
				'object_type' => 'post_type',
				'object_name' => 'invalid',
			]
		) );
	}

	public function test_pods_access_map_capabilities_returns_capabilities() {
		$this->assertEquals( [
			'read'             => null,
			'add'              => 'edit_posts',
			'edit'             => 'edit_posts',
			'delete'           => 'delete_posts',
			'read_private'     => 'read_private_posts',
			'edit_others'      => 'edit_others_posts',
			'delete_others'    => 'delete_others_posts',
			'delete_published' => 'delete_published_posts',
			'delete_private'   => 'delete_private_posts',
		], pods_access_map_capabilities( [
			'object_type' => 'post_type',
			'object_name' => 'post',
		] ) );
	}

	public function test_pods_access_map_capabilities_returns_capabilities_for_private_post() {
		$post_id = wp_insert_post( [
			'post_title'   => 'Test post',
			'post_content' => 'Test content',
			'post_type'    => 'post',
			'post_status'  => 'private',
			'post_author'  => 1,
		] );

		$this->assertIsInt( $post_id );

		$this->assertEquals( [
			'read'             => 'read_private_posts',
			'add'              => 'edit_posts',
			'edit'             => 'edit_post',
			'delete'           => 'delete_post',
			'read_private'     => 'read_private_posts',
			'edit_others'      => 'edit_others_posts',
			'delete_others'    => 'delete_others_posts',
			'delete_published' => 'delete_published_posts',
			'delete_private'   => 'delete_private_posts',
		], pods_access_map_capabilities( [
			'object_type' => 'post_type',
			'object_name' => 'post',
			'item_id'     => $post_id,
		] ) );
	}

	public function test_pods_access_map_capabilities_returns_capabilities_for_private_post_for_author() {
		$post_id = wp_insert_post( [
			'post_title'   => 'Test post',
			'post_content' => 'Test content',
			'post_type'    => 'post',
			'post_status'  => 'private',
			'post_author'  => 1,
		] );

		$this->assertIsInt( $post_id );

		$this->assertEquals( [
			'read'             => 'read',
			'add'              => 'edit_posts',
			'edit'             => 'edit_post',
			'delete'           => 'delete_post',
			'read_private'     => 'read_private_posts',
			'edit_others'      => 'edit_others_posts',
			'delete_others'    => 'delete_others_posts',
			'delete_published' => 'delete_published_posts',
			'delete_private'   => 'delete_private_posts',
		], pods_access_map_capabilities( [
			'object_type' => 'post_type',
			'object_name' => 'post',
			'item_id'     => $post_id,
		], 1 ) );
	}

	public function test_pods_is_type_public_returns_true_for_public_type() {
		$this->assertTrue( pods_is_type_public(
			[
				'object_type' => 'post_type',
				'object_name' => 'post',
			]
		) );
	}

	public function test_pods_is_type_public_returns_false_for_non_public_type() {
		$this->assertFalse( pods_is_type_public(
			[
				'object_type' => 'post_type',
				'object_name' => 'revision',
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

	public function test_pods_access_bypass_post_with_password_returns_false_for_public_post() {
		$post_id = wp_insert_post( [
			'post_title'    => 'Test post with post password',
			'post_content'  => 'Test post content',
			'post_type'     => 'post',
			'post_status'   => 'publish',
		] );

		$this->assertFalse( pods_access_bypass_post_with_password(
			[
				'object_type' => 'post_type',
				'object_name' => 'post',
				'item_id'     => $post_id,
			]
		) );
	}

	public function test_pods_access_bypass_post_with_password_returns_true_without_password_set() {
		$post_id = wp_insert_post( [
			'post_title'    => 'Test post with post password',
			'post_content'  => 'Test post content',
			'post_password' => 'test',
			'post_type'     => 'post',
			'post_status'   => 'publish',
		] );

		$this->assertTrue( pods_access_bypass_post_with_password(
			[
				'object_type' => 'post_type',
				'object_name' => 'post',
				'item_id'     => $post_id,
			]
		) );
	}

	public function test_pods_access_bypass_post_with_password_returns_false_with_password_set() {
		$post_id = wp_insert_post( [
			'post_title'    => 'Test post with post password',
			'post_content'  => 'Test post content',
			'post_password' => 'test',
			'post_type'     => 'post',
			'post_status'   => 'publish',
		] );

		$this->set_post_password_cookie( 'test' );

		$this->assertFalse( pods_access_bypass_post_with_password(
			[
				'object_type' => 'post_type',
				'object_name' => 'post',
				'item_id'     => $post_id,
			]
		) );
	}

	public function test_pods_access_bypass_post_with_password_returns_false_for_non_post_type() {
		$this->assertFalse( pods_access_bypass_post_with_password(
			[
				'object_type' => 'taxonomy',
				'object_name' => 'category',
			]
		) );
	}

	public function test_pods_access_bypass_private_post_returns_false_for_public_post() {
		$post_id = wp_insert_post( [
			'post_title'    => 'Test post with post password',
			'post_content'  => 'Test post content',
			'post_type'     => 'post',
			'post_status'   => 'publish',
		] );

		$this->assertFalse( pods_access_bypass_private_post(
			[
				'object_type' => 'post_type',
				'object_name' => 'post',
				'item_id'     => $post_id,
			]
		) );
	}

	public function test_pods_access_bypass_private_post_returns_true_with_private_post() {
		$post_id = wp_insert_post( [
			'post_title'    => 'Test post with post password',
			'post_content'  => 'Test post content',
			'post_type'     => 'post',
			'post_status'   => 'private',
		] );

		$this->set_post_password_cookie( 'some-password' );

		$this->assertTrue( pods_access_bypass_private_post(
			[
				'object_type' => 'post_type',
				'object_name' => 'post',
				'item_id'     => $post_id,
			]
		) );
	}

	public function test_pods_access_bypass_private_post_returns_false_for_non_post_type() {
		$this->assertFalse( pods_access_bypass_private_post(
			[
				'object_type' => 'taxonomy',
				'object_name' => 'category',
			]
		) );
	}

	public function test_pods_access_get_capabilities_preview_returns_expected_number_of_capabilities() {
		$this->assertEquals(
			9,
			substr_count(
				pods_access_get_capabilities_preview( 'post_type', 'post' ),
				'<li>'
			)
		);

		$this->assertEquals(
			4,
			substr_count(
				pods_access_get_capabilities_preview( 'post_type', $this->public_pod_name ),
				'<li>'
			)
		);

		$this->assertEquals(
			4,
			substr_count(
				pods_access_get_capabilities_preview( 'post_type', $this->non_public_pod_name ),
				'<li>'
			)
		);
	}

}
