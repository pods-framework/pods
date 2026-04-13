<?php

// Add to PostTypeTest >> pods_access_get_capabilities_preview( string $pod_type, string $pod_name ): string {


namespace Pods_Unit_Tests\functions\Access;

use Pods;
use Pods\Whatsit\Pod;
use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * @group pods
 * @group pods-functions
 * @group pods-functions-access
 */
class AccessTest extends Pods_UnitTestCase {

	protected $public_pod;
	protected $public_pod_name = 'test_pub_cpt';
	protected $info_defaults = [
		'object_type' => null,
		'object_name' => null,
		'item_id'     => null,
		'pods'        => null,
		'pod'         => null,
	];

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
	}

	public function tearDown(): void {
		$this->public_pod = null;

		pods_update_setting( 'dynamic_features_allow', null );
		pods_update_setting( 'dynamic_features_enabled', null );
		pods_update_setting( 'show_access_restricted_messages', null );
		pods_update_setting( 'show_access_admin_notices', null );

		parent::tearDown();
	}

	public function test_pods_can_use_dynamic_feature_can_be_disabled() {
		pods_update_setting( 'dynamic_features_allow', '0' );
		pods_update_setting( 'dynamic_features_enabled', [
			'display',
			'form',
			'view',
		] );

		$this->assertFalse( pods_can_use_dynamic_feature( 'display' ) );
		$this->assertFalse( pods_can_use_dynamic_feature( 'form' ) );
		$this->assertFalse( pods_can_use_dynamic_feature( 'view' ) );
	}

	public function test_pods_can_use_dynamic_feature_display_enabled_by_default() {
		$this->assertTrue( pods_can_use_dynamic_feature( 'display' ) );
	}

	public function test_pods_can_use_dynamic_feature_display_can_be_disabled() {
		pods_update_setting( 'dynamic_features_enabled', [] );

		$this->assertFalse( pods_can_use_dynamic_feature( 'display' ) );
	}

	public function test_pods_can_use_dynamic_feature_form_enabled_by_default() {
		$this->assertTrue( pods_can_use_dynamic_feature( 'form' ) );
	}

	public function test_pods_can_use_dynamic_feature_form_can_be_disabled() {
		pods_update_setting( 'dynamic_features_enabled', [] );

		$this->assertFalse( pods_can_use_dynamic_feature( 'form' ) );
	}

	public function test_pods_can_use_dynamic_feature_view_disabled_by_default() {
		$this->assertFalse( pods_can_use_dynamic_feature( 'view' ) );
	}

	public function test_pods_can_use_dynamic_feature_view_can_be_enabled() {
		pods_update_setting( 'dynamic_features_enabled', [
			'view',
		] );

		$this->assertTrue( pods_can_use_dynamic_feature( 'view' ) );
	}

	public function test_pods_info_from_args() {
		$info = pods_info_from_args( [
			'object_type' => 'post_type',
			'object_name' => $this->public_pod_name,
		] );

		$this->assertEquals( array_merge( $this->info_defaults, [
			'object_type' => 'post_type',
			'object_name' => $this->public_pod_name,
		] ), $info );
	}

	public function test_pods_info_from_args_with_invalid_info_and_no_build_just_returns_as_is() {
		$this->assertEquals( $this->info_defaults, pods_info_from_args( [] ) );

		$this->assertEquals( array_merge( $this->info_defaults, [
			'object_type' => 'post_type',
			'object_name' => 'invalid',
		] ), pods_info_from_args( [
			'object_type' => 'post_type',
			'object_name' => 'invalid',
		] ) );

		$this->assertEquals( array_merge( $this->info_defaults, [
			'object_type' => 'invalid',
			'object_name' => 'post',
		] ), pods_info_from_args( [
			'object_type' => 'invalid',
			'object_name' => 'post',
		] ) );

		$this->assertEquals( array_merge( $this->info_defaults, [
			'object_type' => 'invalid',
			'object_name' => 'invalid',
		] ), pods_info_from_args( [
			'object_type' => 'invalid',
			'object_name' => 'invalid',
		] ) );

		$this->assertEquals( array_merge( $this->info_defaults, [
			'object_type' => 'invalid',
		] ), pods_info_from_args( [
			'object_type' => 'invalid',
		] ) );

		$this->assertEquals( array_merge( $this->info_defaults, [
			'object_name' => 'invalid',
		] ), pods_info_from_args( [
			'object_name' => 'invalid',
		] ) );
	}

	public function test_pods_info_from_args_with_build_pods() {
		$info = pods_info_from_args( [
			'object_type' => 'post_type',
			'object_name' => $this->public_pod_name,
			'build_pods'  => true,
		] );

		$this->assertEquals( 'post_type', $info['object_type'] );
		$this->assertEquals( $this->public_pod_name, $info['object_name'] );
		$this->assertNull( $info['item_id'] );
		$this->assertInstanceOf( Pods::class, $info['pods'] );
		$this->assertInstanceOf( Pod::class, $info['pod'] );

		$info = pods_info_from_args( [
			'object_name' => $this->public_pod_name,
			'build_pods'  => true,
		] );

		$this->assertEquals( 'post_type', $info['object_type'] );
		$this->assertEquals( $this->public_pod_name, $info['object_name'] );
		$this->assertNull( $info['item_id'] );
		$this->assertInstanceOf( Pods::class, $info['pods'] );
		$this->assertInstanceOf( Pod::class, $info['pod'] );
	}

	public function test_pods_info_from_args_with_build_pods_with_invalid_info_returns_null_for_pods() {
		$this->assertEquals( $this->info_defaults, pods_info_from_args( [] ) );

		$this->assertEquals( array_merge( $this->info_defaults, [
			'object_type' => 'post_type',
			'object_name' => 'invalid',
		] ), pods_info_from_args( [
			'object_type' => 'post_type',
			'object_name' => 'invalid',
			'build_pods'  => true,
		] ) );

		$this->assertEquals( array_merge( $this->info_defaults, [
			'object_type' => 'invalid',
			'object_name' => 'post',
		] ), pods_info_from_args( [
			'object_type' => 'invalid',
			'object_name' => 'post',
			'build_pods'  => true,
		] ) );

		$this->assertEquals( array_merge( $this->info_defaults, [
			'object_type' => 'invalid',
			'object_name' => 'invalid',
		] ), pods_info_from_args( [
			'object_type' => 'invalid',
			'object_name' => 'invalid',
			'build_pods'  => true,
		] ) );

		$this->assertEquals( array_merge( $this->info_defaults, [
			'object_type' => 'invalid',
		] ), pods_info_from_args( [
			'object_type' => 'invalid',
			'build_pods'  => true,
		] ) );

		$this->assertEquals( array_merge( $this->info_defaults, [
			'object_name' => 'invalid',
		] ), pods_info_from_args( [
			'object_name' => 'invalid',
			'build_pods'  => true,
		] ) );
	}

	public function test_pods_info_from_args_with_build_pod() {
		$info = pods_info_from_args( [
			'object_type' => 'post_type',
			'object_name' => $this->public_pod_name,
			'build_pod'   => true,
		] );

		$this->assertEquals( 'post_type', $info['object_type'] );
		$this->assertEquals( $this->public_pod_name, $info['object_name'] );
		$this->assertNull( $info['item_id'] );
		$this->assertNull( $info['pods'] );
		$this->assertInstanceOf( Pod::class, $info['pod'] );

		$info = pods_info_from_args( [
			'object_name' => $this->public_pod_name,
			'build_pod'   => true,
		] );

		$this->assertEquals( 'post_type', $info['object_type'] );
		$this->assertEquals( $this->public_pod_name, $info['object_name'] );
		$this->assertNull( $info['item_id'] );
		$this->assertNull( $info['pods'] );
		$this->assertInstanceOf( Pod::class, $info['pod'] );
	}

	public function test_pods_info_from_args_with_build_pod_with_pods_passed_uses_same_object() {
		$pods = pods( $this->public_pod_name );

		$unique_key = md5( 'unique_key' );

		$pods->pod_data->set_arg( 'test_unique_key', $unique_key );

		$info = pods_info_from_args( [
			'pods'      => $pods,
			'build_pod' => true,
		] );

		$this->assertEquals( 'post_type', $info['object_type'] );
		$this->assertEquals( $this->public_pod_name, $info['object_name'] );
		$this->assertNull( $info['item_id'] );
		$this->assertInstanceOf( Pods::class, $info['pods'] );
		$this->assertInstanceOf( Pod::class, $info['pod'] );
		$this->assertEquals( $unique_key, $info['pod']->get_arg( 'test_unique_key' ) );
	}

	public function test_pods_info_from_args_with_build_pod_with_invalid_info_returns_null_for_pod() {
		$this->assertEquals( $this->info_defaults, pods_info_from_args( [] ) );

		$this->assertEquals( array_merge( $this->info_defaults, [
			'object_type' => 'post_type',
			'object_name' => 'invalid',
		] ), pods_info_from_args( [
			'object_type' => 'post_type',
			'object_name' => 'invalid',
			'build_pod'   => true,
		] ) );

		$this->assertEquals( array_merge( $this->info_defaults, [
			'object_type' => 'invalid',
			'object_name' => 'post',
		] ), pods_info_from_args( [
			'object_type' => 'invalid',
			'object_name' => 'post',
			'build_pod'   => true,
		] ) );

		$this->assertEquals( array_merge( $this->info_defaults, [
			'object_type' => 'invalid',
			'object_name' => 'invalid',
		] ), pods_info_from_args( [
			'object_type' => 'invalid',
			'object_name' => 'invalid',
			'build_pod'   => true,
		] ) );

		$this->assertEquals( array_merge( $this->info_defaults, [
			'object_type' => 'invalid',
		] ), pods_info_from_args( [
			'object_type' => 'invalid',
			'build_pod'   => true,
		] ) );

		$this->assertEquals( array_merge( $this->info_defaults, [
			'object_name' => 'invalid',
		] ), pods_info_from_args( [
			'object_name' => 'invalid',
			'build_pod'   => true,
		] ) );
	}

	public function test_pods_can_use_dynamic_feature_unrestricted_by_default() {
		$pod = $this->public_pod->pod_data;

		$pod->set_arg( 'restrict_dynamic_features', null );

		$this->assertTrue( pods_can_use_dynamic_feature_unrestricted( [
			'pod' => $pod,
		], 'display' ) );

		$this->assertFalse( pods_can_use_dynamic_feature_unrestricted( [
			'pod' => $pod,
		], 'form' ) );

		$this->assertTrue( pods_can_use_dynamic_feature_unrestricted( [
			'pod' => $pod,
		], 'form', 'add' ) );

		$this->assertFalse( pods_can_use_dynamic_feature_unrestricted( [
			'pod' => $pod,
		], 'form', 'edit' ) );
	}

	public function test_pods_can_use_dynamic_feature_unrestricted_can_be_enabled() {
		$pod = $this->public_pod->pod_data;

		$pod->set_arg( 'restrict_dynamic_features', '0' );

		$this->assertTrue( pods_can_use_dynamic_feature_unrestricted( [
			'pod' => $pod,
		], 'display' ) );

		$this->assertTrue( pods_can_use_dynamic_feature_unrestricted( [
			'pod' => $pod,
		], 'form' ) );
	}

	public function test_pods_can_use_dynamic_feature_unrestricted_can_be_enabled_by_feature() {
		$pod = $this->public_pod->pod_data;

		$pod->set_arg( 'restrict_dynamic_features', null );
		$pod->set_arg( 'restricted_dynamic_features', [] );

		$this->assertTrue( pods_can_use_dynamic_feature_unrestricted( [
			'pod' => $pod,
		], 'display' ) );

		$this->assertTrue( pods_can_use_dynamic_feature_unrestricted( [
			'pod' => $pod,
		], 'form' ) );

		$this->assertTrue( pods_can_use_dynamic_feature_unrestricted( [
			'pod' => $pod,
		], 'form', 'add' ) );

		$this->assertTrue( pods_can_use_dynamic_feature_unrestricted( [
			'pod' => $pod,
		], 'form', 'edit' ) );
	}

	public function test_pods_can_use_dynamic_feature_unrestricted_can_be_enabled_by_feature_form() {
		$pod = $this->public_pod->pod_data;

		$pod->set_arg( 'restrict_dynamic_features', null );
		$pod->set_arg( 'restricted_dynamic_features_forms', [
			'edit',
		] );

		$this->assertTrue( pods_can_use_dynamic_feature_unrestricted( [
			'pod' => $pod,
		], 'display' ) );

		$this->assertFalse( pods_can_use_dynamic_feature_unrestricted( [
			'pod' => $pod,
		], 'form' ) );

		$this->assertTrue( pods_can_use_dynamic_feature_unrestricted( [
			'pod' => $pod,
		], 'form', 'add' ) );

		$this->assertFalse( pods_can_use_dynamic_feature_unrestricted( [
			'pod' => $pod,
		], 'form', 'edit' ) );
	}

	public function test_pods_get_access_admin_notice_is_shown() {
		$this->assertStringContainsString( '<!-- pods:access-notices/admin/message ', pods_get_access_admin_notice( [
			'pods' => $this->public_pod,
		] ) );
	}

	public function test_pods_get_access_admin_notice_is_hidden_by_setting() {
		pods_update_setting( 'show_access_admin_notices', '0' );

		$this->assertStringContainsString( '<!-- pods:access-notices/admin/hidden-by-setting ', pods_get_access_admin_notice( [
			'pods' => $this->public_pod,
		] ) );
	}

	public function test_pods_get_access_admin_notice_is_hidden_by_pod() {
		$this->public_pod->pod_data->set_arg( 'show_access_admin_notices', '0' );

		$this->assertStringContainsString( '<!-- pods:access-notices/admin/hidden-by-pod ', pods_get_access_admin_notice( [
			'pods' => $this->public_pod,
		] ) );
	}

	public function test_pods_get_access_user_notice_is_shown() {
		pods_update_setting( 'show_access_restricted_messages', '1' );
		$this->public_pod->pod_data->set_arg( 'show_access_restricted_messages', '1' );

		$this->assertStringContainsString( '<!-- pods:access-notices/user/message ', pods_get_access_user_notice( [
			'pods' => $this->public_pod,
		] ) );
	}

	public function test_pods_get_access_user_notice_is_hidden_by_setting() {
		$this->assertStringContainsString( '<!-- pods:access-notices/user/hidden-by-setting ', pods_get_access_user_notice( [
			'pods' => $this->public_pod,
		] ) );
	}

	public function test_pods_get_access_user_notice_is_hidden_by_pod() {
		pods_update_setting( 'show_access_restricted_messages', '1' );
		$this->public_pod->pod_data->set_arg( 'show_access_restricted_messages', '0' );

		$this->assertStringContainsString( '<!-- pods:access-notices/user/hidden-by-pod ', pods_get_access_user_notice( [
			'pods' => $this->public_pod,
		] ) );
	}

	public function test_pods_access_pod_options() {
		$this->assertEquals( [
			'security_access_rights_info',
			'dynamic_features_allow',
			'restrict_dynamic_features',
			'restricted_dynamic_features',
			'restricted_dynamic_features_forms',
			'show_access_restricted_messages',
			'show_access_admin_notices',
			'security_access_rights_preview',
		], array_keys( pods_access_pod_options( 'post_type', $this->public_pod_name ) ) );
	}

	public function test_pods_access_settings_config() {
		$this->assertEquals( [
			'dynamic_features_allow',
			'security_access_rights_info',
			'dynamic_features_enabled',
			'show_access_restricted_messages',
			'show_access_admin_notices',
			'dynamic_features_allow_sql_clauses',
			'display_callbacks',
			'display_callbacks_allowed',
		], array_keys( pods_access_settings_config() ) );
	}

}
