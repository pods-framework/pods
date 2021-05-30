<?php

namespace Pods_Unit_Tests\Pods;

use WP_User;
use Codeception\TestCase\WPTestCase;
use Pods\Permissions;

/**
 * @group  pods-utils
 * @covers Permissions
 */
class PermissionsTest extends WPTestCase {

	public function tearDown() : void {
		// Reset current user.
		global $current_user;

		$current_user = null;

		wp_set_current_user( 0 );

		parent::tearDown();
	}

	/**
	 * Test get_user() with current user.
	 */
	public function test_get_user_with_current_user() {
		$sut = $this->sut();

		$user_id = $this->factory()->user->create();

		wp_set_current_user( $user_id );

		$this->assertInstanceOf( WP_User::class, $sut->get_user() );
	}

	/**
	 * Test get_user() with current user while not logged in.
	 */
	public function test_get_user_with_current_user_not_logged_in() {
		$sut = $this->sut();

		$this->assertFalse( $sut->get_user() );
	}

	/**
	 * Test get_user() with specific user object.
	 */
	public function test_get_user_with_specific_user_object() {
		$sut = $this->sut();

		$user_id = $this->factory()->user->create();

		$user = get_userdata( $user_id );

		$this->assertInstanceOf( WP_User::class, $sut->get_user( $user ) );
	}

	/**
	 * Test get_user() with specific user ID.
	 */
	public function test_get_user_with_specific_user_id() {
		$sut = $this->sut();

		$user_id = $this->factory()->user->create();

		$this->assertInstanceOf( WP_User::class, $sut->get_user( $user_id ) );
	}

	/**
	 * Test user_has_permission() with admin role.
	 */
	public function test_user_has_permission_with_admin_role() {
		$sut = $this->sut();

		$user_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );

		$object = [
			'admin_only' => 1,
		];

		$this->assertTrue( $sut->user_has_permission( $object, $user_id ) );
	}

	/**
	 * Test are_permissions_restricted() with admin only.
	 */
	public function test_are_permissions_restricted_with_admin_only() {
		$sut = $this->sut();

		$object = [
			'admin_only' => 1,
		];

		$this->assertTrue( $sut->are_permissions_restricted( $object ) );
	}

	/**
	 * Test are_roles_restricted_for_user() for user with access.
	 */
	public function test_are_roles_restricted_for_user_with_access() {
		$sut = $this->sut();

		$user_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );

		$object = [
			'restrict_role' => 1,
			'roles_allowed' => 'administrator',
		];

		$this->assertFalse( $sut->are_roles_restricted_for_user( $object, $user_id ) );
	}

	/**
	 * Test are_roles_restricted_for_user() for user without access.
	 */
	public function test_are_roles_restricted_for_user_without_access() {
		$sut = $this->sut();

		$user_id = $this->factory()->user->create();

		$object = [
			'restrict_role' => 1,
			'roles_allowed' => 'administrator',
		];

		$this->assertTrue( $sut->are_roles_restricted_for_user( $object, $user_id ) );
	}

	/**
	 * Test get_restricted_roles().
	 */
	public function test_get_restricted_roles() {
		$sut = $this->sut();

		$object = [
			'restrict_role' => 1,
			'roles_allowed' => 'administrator',
		];

		$this->assertEquals( [ 'administrator' ], $sut->get_restricted_roles( $object ) );
	}

	/**
	 * Test get_restricted_roles() with restrict off.
	 */
	public function test_get_restricted_roles_with_restrict_off() {
		$sut = $this->sut();

		$object = [
			'restrict_role' => 0,
			'roles_allowed' => 'administrator',
		];

		$this->assertFalse( $sut->get_restricted_roles( $object ) );
	}

	/**
	 * Test get_restricted_roles() default.
	 */
	public function test_get_restricted_roles_default() {
		$sut = $this->sut();

		$object = [];

		$this->assertFalse( $sut->get_restricted_roles( $object ) );
	}

	/**
	 * Test are_capabilities_restricted_for_user() with access.
	 */
	public function test_are_capabilities_restricted_for_user_with_access() {
		$sut = $this->sut();

		$user_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );

		$object = [
			'restrict_capability' => 1,
			'capability_allowed'  => 'delete_users',
		];

		$this->assertFalse( $sut->are_capabilities_restricted_for_user( $object, $user_id ) );
	}

	/**
	 * Test are_capabilities_restricted_for_user() without access.
	 */
	public function test_are_capabilities_restricted_for_user_without_access() {
		$sut = $this->sut();

		$user_id = $this->factory()->user->create();

		$object = [
			'restrict_capability' => 1,
			'capability_allowed'  => 'delete_users',
		];

		$this->assertTrue( $sut->are_capabilities_restricted_for_user( $object, $user_id ) );
	}

	/**
	 * Test are_capabilities_restricted_for_user() with access using matrix.
	 */
	public function test_are_capabilities_restricted_for_user_with_access_using_matrix() {
		$sut = $this->sut();

		$user_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );

		$object = [
			'restrict_capability' => 1,
			'capability_allowed'  => 'non_existent_cap,delete_users&&edit_users,another_non_existent_cap',
		];

		$this->assertFalse( $sut->are_capabilities_restricted_for_user( $object, $user_id ) );
	}

	/**
	 * Test are_capabilities_restricted_for_user() with access using matrix single.
	 */
	public function test_are_capabilities_restricted_for_user_with_access_using_matrix_single() {
		$sut = $this->sut();

		$user_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );

		$object = [
			'restrict_capability' => 1,
			'capability_allowed'  => 'non_existent_cap,another_non_existent_cap&&edit_users,and_another_non_existent_cap',
		];

		$this->assertTrue( $sut->are_capabilities_restricted_for_user( $object, $user_id ) );
	}

	/**
	 * Test are_capabilities_restricted_for_user() without access using matrix.
	 */
	public function test_are_capabilities_restricted_for_user_without_access_using_matrix() {
		$sut = $this->sut();

		$user_id = $this->factory()->user->create();

		$object = [
			'restrict_capability' => 1,
			'capability_allowed'  => 'non_existent_cap,delete_users&&edit_users,another_non_existent_cap',
		];

		$this->assertTrue( $sut->are_capabilities_restricted_for_user( $object, $user_id ) );
	}

	/**
	 * Test get_restricted_capabilities().
	 */
	public function test_get_restricted_capabilities() {
		$sut = $this->sut();

		$object = [
			'restrict_capability' => 1,
			'capability_allowed'  => 'delete_users',
		];

		// This is returned as an array of arrays.
		$this->assertEquals( [ [ 'delete_users' ] ], $sut->get_restricted_capabilities( $object ) );
	}

	/**
	 * Test get_restricted_capabilities() with restrict off.
	 */
	public function test_get_restricted_capabilities_with_restrict_off() {
		$sut = $this->sut();

		$object = [
			'restrict_capability' => 0,
			'capability_allowed'  => 'delete_users',
		];

		$this->assertFalse( $sut->get_restricted_capabilities( $object ) );
	}

	/**
	 * Test get_restricted_capabilities() default.
	 */
	public function test_get_restricted_capabilities_default() {
		$sut = $this->sut();

		$object = [];

		$this->assertFalse( $sut->get_restricted_capabilities( $object ) );
	}

	/**
	 * Test is_admin_only().
	 */
	public function test_is_admin_only() {
		$sut = $this->sut();

		$object = [
			'admin_only' => 1,
		];

		$this->assertTrue( $sut->is_admin_only( $object ) );
	}

	/**
	 * Test is_admin_only() off.
	 */
	public function test_is_admin_only_off() {
		$sut = $this->sut();

		$object = [
			'admin_only' => 0,
		];

		$this->assertFalse( $sut->is_admin_only( $object ) );
	}

	/**
	 * Test is_admin_only() default.
	 */
	public function test_is_admin_only_default() {
		$sut = $this->sut();

		$object = [];

		$this->assertFalse( $sut->is_admin_only( $object ) );
	}

	/**
	 * Test is_input_allowed() is not for internal.
	 */
	public function test_is_input_allowed_is_not_for_internal() {
		$sut = $this->sut();

		$object = [
			'type' => 'internal',
		];

		$this->assertFalse( $sut->is_input_allowed( $object ) );
	}

	/**
	 * Test is_input_allowed() is for hidden.
	 */
	public function test_is_input_allowed_is_for_hidden() {
		$sut = $this->sut();

		$object = [
			'type' => 'hidden',
		];

		$this->assertTrue( $sut->is_input_allowed( $object ) );
	}

	/**
	 * Test is_input_allowed() is for text.
	 */
	public function test_is_input_allowed_is_for_text() {
		$sut = $this->sut();

		$object = [
			'type' => 'text',
		];

		$this->assertTrue( $sut->is_input_allowed( $object ) );
	}

	/**
	 * Test is_user_an_admin() for admin.
	 */
	public function test_is_user_an_admin_for_admin() {
		$sut = $this->sut();

		$user_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );

		$this->assertTrue( $sut->is_user_an_admin( null, $user_id ) );
	}

	/**
	 * Test is_user_an_admin() for non-admin.
	 */
	public function test_is_user_an_admin_for_non_admin() {
		$sut = $this->sut();

		$user_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );

		$this->assertTrue( $sut->is_user_an_admin( null, $user_id ) );
	}

	/**
	 * Test is_user_an_admin() with additional capabilities for non-admin with capabilities.
	 */
	public function test_is_user_an_admin_with_additional_capabilities_for_non_admin_with_capabilities() {
		$sut = $this->sut();

		$user_id = $this->factory()->user->create( [ 'role' => 'editor' ] );

		$this->assertTrue( $sut->is_user_an_admin( 'edit_posts', $user_id ) );
	}

	/**
	 * Test is_user_an_admin() with additional capabilities for non-admin without capabilities.
	 */
	public function test_is_user_an_admin_with_additional_capabilities_for_non_admin_without_capabilities() {
		$sut = $this->sut();

		$user_id = $this->factory()->user->create();

		$this->assertFalse( $sut->is_user_an_admin( 'edit_posts', $user_id ) );
	}

	/**
	 * @return Permissions
	 */
	private function sut() {
		return new Permissions();
	}

}
