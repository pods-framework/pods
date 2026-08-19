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
		pods_update_setting( 'dynamic_features_allow_sql_clauses', null );
		pods_update_setting( 'display_callbacks', null );
		pods_update_setting( 'display_callbacks_allowed', null );

		pods_transient_clear( 'pods_dynamic_features_allow_sql_clauses' );

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

	public function test_pods_get_access_admin_notice_uses_custom_message() {
		$output = pods_get_access_admin_notice(
			[ 'pods' => $this->public_pod ],
			true,
			'This is a custom admin access message.'
		);

		$this->assertStringContainsString( '<!-- pods:access-notices/admin/message ', $output );
		$this->assertStringContainsString( 'This is a custom admin access message.', $output );
	}

	public function test_pods_get_access_user_notice_uses_custom_message() {
		$output = pods_get_access_user_notice(
			[ 'pods' => $this->public_pod ],
			true,
			'This is a custom user access message.'
		);

		$this->assertStringContainsString( '<!-- pods:access-notices/user/message ', $output );
		$this->assertStringContainsString( 'This is a custom user access message.', $output );
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

	public function test_pods_access_map_capabilities_for_settings_uses_pods_edit_capability() {
		$capabilities = pods_access_map_capabilities( [
			'object_type' => 'settings',
			'object_name' => 'my_custom_settings',
		] );

		$this->assertIsArray( $capabilities );
		// The "edit" capability is object-specific so it can be granted on the
		// edit pod screen without requiring full manage_options.
		$this->assertSame( 'manage_options', $capabilities['read'] );
		$this->assertSame( 'pods_edit_my_custom_settings', $capabilities['edit'] );
		$this->assertSame( 'manage_options', $capabilities['delete'] );
	}

	public function test_pods_access_map_capabilities_returns_null_without_object() {
		$this->assertNull( pods_access_map_capabilities( [] ) );
	}

	/*
	 * pods_can_use_dynamic_features() (the plural "are dynamic features usable
	 * at all" gate, distinct from the already-covered singular variant).
	 */

	public function test_pods_can_use_dynamic_features_enabled_by_default() {
		$this->assertTrue( pods_can_use_dynamic_features() );
	}

	public function test_pods_can_use_dynamic_features_can_be_disabled_by_setting() {
		pods_update_setting( 'dynamic_features_allow', '0' );

		$this->assertFalse( pods_can_use_dynamic_features() );
	}

	/*
	 * pods_can_use_dynamic_feature_sql_clauses().
	 *
	 * The result is cached in a transient, so each case updates the setting and
	 * clears the transient to force a fresh calculation.
	 */

	public function test_pods_can_use_dynamic_feature_sql_clauses_disabled() {
		pods_update_setting( 'dynamic_features_allow_sql_clauses', '0' );
		pods_transient_clear( 'pods_dynamic_features_allow_sql_clauses' );

		$this->assertFalse( pods_can_use_dynamic_feature_sql_clauses() );
		$this->assertFalse( pods_can_use_dynamic_feature_sql_clauses( 'simple' ) );
		$this->assertFalse( pods_can_use_dynamic_feature_sql_clauses( 'all' ) );
	}

	public function test_pods_can_use_dynamic_feature_sql_clauses_simple_allows_simple_only() {
		pods_update_setting( 'dynamic_features_allow_sql_clauses', 'simple' );
		pods_transient_clear( 'pods_dynamic_features_allow_sql_clauses' );

		// Defaults to the "simple" clause type when none is provided.
		$this->assertTrue( pods_can_use_dynamic_feature_sql_clauses() );
		$this->assertTrue( pods_can_use_dynamic_feature_sql_clauses( 'simple' ) );
		$this->assertFalse( pods_can_use_dynamic_feature_sql_clauses( 'all' ) );
	}

	public function test_pods_can_use_dynamic_feature_sql_clauses_all_includes_simple() {
		pods_update_setting( 'dynamic_features_allow_sql_clauses', 'all' );
		pods_transient_clear( 'pods_dynamic_features_allow_sql_clauses' );

		$this->assertTrue( pods_can_use_dynamic_feature_sql_clauses( 'simple' ) );
		$this->assertTrue( pods_can_use_dynamic_feature_sql_clauses( 'all' ) );
	}

	/*
	 * pods_access_callback_allowed().
	 */

	public function test_pods_access_callback_allowed_blocks_non_string_callables() {
		pods_update_setting( 'display_callbacks', 'restricted' );

		// Closures are not permitted.
		$this->assertFalse( pods_access_callback_allowed( static function () {
		} ) );

		// Array callables (instance and static method references) are rejected.
		$this->assertFalse( pods_access_callback_allowed( [ $this, 'setUp' ] ) );
		$this->assertFalse( pods_access_callback_allowed( [ __CLASS__, 'setUp' ] ) );

		// Invokable objects (implementing __invoke) are rejected.
		$invokable = new class() {
			public function __invoke() {
			}
		};

		$this->assertFalse( pods_access_callback_allowed( $invokable ) );
	}

	public function test_pods_access_callback_allowed_blocks_class_method_strings() {
		pods_update_setting( 'display_callbacks', 'restricted' );

		// String class method references must never be allowed, even benign ones.
		$this->assertFalse( pods_access_callback_allowed( 'PodsForm::field_method' ) );
		$this->assertFalse( pods_access_callback_allowed( 'WP_Query::get_posts' ) );
		$this->assertFalse( pods_access_callback_allowed( '\\Pods\\Whatsit::get_arg' ) );
		$this->assertFalse( pods_access_callback_allowed( 'Foo :: bar' ) );
		$this->assertFalse( pods_access_callback_allowed( 'parent::method' ) );
		$this->assertFalse( pods_access_callback_allowed( 'self::method' ) );
		$this->assertFalse( pods_access_callback_allowed( 'static::method' ) );
	}

	public function test_pods_access_callback_allowed_permits_class_callbacks_when_enabled() {
		pods_update_setting( 'display_callbacks', 'restricted' );

		// The PODS_ALLOW_CLASS_CALLBACKS constant defaults to off; the filter it
		// feeds into lets us exercise the "enabled" path without defining a
		// constant that would leak into other tests.
		add_filter( 'pods_access_allow_class_callbacks', '__return_true' );

		// Non-string callables are now permitted.
		$this->assertTrue( pods_access_callback_allowed( static function () {
		} ) );
		$this->assertTrue( pods_access_callback_allowed( [ $this, 'setUp' ] ) );

		$invokable = new class() {
			public function __invoke() {
			}
		};

		$this->assertTrue( pods_access_callback_allowed( $invokable ) );

		// "Class::method" strings still flow through the denylist under "restricted".
		$this->assertTrue( pods_access_callback_allowed( 'PodsForm::field_method' ) );

		remove_filter( 'pods_access_allow_class_callbacks', '__return_true' );
	}

	public function test_pods_access_callback_allowed_blocks_disallowed_functions() {
		// "restricted" leaves the allow list empty, isolating the denylist behavior.
		pods_update_setting( 'display_callbacks', 'restricted' );

		$blocked = [
			// Shell / eval / dynamic invocation.
			'system',
			'exec',
			'shell_exec',
			'eval',
			'assert',
			'create_function',
			'call_user_func',
			'call_user_func_array',
			'array_map',
			'array_filter',
			'array_walk',
			'usort',
			'ob_start',
			'register_shutdown_function',
			'set_error_handler',
			// Deserialization / variable handling.
			'unserialize',
			'maybe_unserialize',
			'extract',
			'compact',
			'parse_str',
			// File read / write / delete / manipulation.
			'file_get_contents',
			'file_put_contents',
			'readfile',
			'fopen',
			'fwrite',
			'unlink',
			'copy',
			'rename',
			'rmdir',
			'mkdir',
			'chmod',
			'symlink',
			'scandir',
			'glob',
			'highlight_file',
			'show_source',
			// Network / HTTP.
			'fsockopen',
			'curl_exec',
			'curl_init',
			'wp_remote_get',
			'wp_remote_post',
			// Template / include.
			'include',
			'require',
			'get_template_part',
			'load_template',
			'locate_template',
			'get_header',
			// PHP environment.
			'phpinfo',
			'getenv',
			'putenv',
			'ini_set',
			'debug_backtrace',
			'error_log',
			// WordPress data access / modification.
			'get_option',
			'update_option',
			'delete_option',
			'get_user_meta',
			'update_user_meta',
			'wp_insert_user',
			'wp_set_password',
			'get_post',
			'get_userdata',
			// WordPress hooks / shortcodes / HTTP.
			'do_action',
			'add_filter',
			'apply_filters',
			'do_shortcode',
			'wp_mail',
		];

		foreach ( $blocked as $callback ) {
			$this->assertFalse(
				pods_access_callback_allowed( $callback ),
				sprintf( 'Expected callback "%s" to be disallowed.', $callback )
			);
		}

		// A benign formatting function is still allowed under "restricted".
		$this->assertTrue( pods_access_callback_allowed( 'strtoupper' ) );
		$this->assertTrue( pods_access_callback_allowed( 'wpautop' ) );
	}

	public function test_pods_access_callback_allowed_is_case_and_namespace_insensitive() {
		pods_update_setting( 'display_callbacks', 'restricted' );

		// Callback matching is case- and namespace-insensitive; these variations are all handled.
		$this->assertFalse( pods_access_callback_allowed( 'SYSTEM' ) );
		$this->assertFalse( pods_access_callback_allowed( 'System' ) );
		$this->assertFalse( pods_access_callback_allowed( 'sYsTeM' ) );
		$this->assertFalse( pods_access_callback_allowed( '\\system' ) );
		$this->assertFalse( pods_access_callback_allowed( '\\Unserialize' ) );
		$this->assertFalse( pods_access_callback_allowed( 'EXEC' ) );

		// A benign function is still allowed regardless of case.
		$this->assertTrue( pods_access_callback_allowed( 'STRTOUPPER' ) );
	}

	public function test_pods_access_callback_allowed_respects_allowed_list_when_customized() {
		pods_update_setting( 'display_callbacks', 'customized' );
		pods_update_setting( 'display_callbacks_allowed', 'esc_attr,esc_html' );

		$this->assertTrue( pods_access_callback_allowed( 'esc_html' ) );
		$this->assertTrue( pods_access_callback_allowed( 'esc_attr' ) );
		// Not part of the explicit allow list.
		$this->assertFalse( pods_access_callback_allowed( 'strtoupper' ) );
	}

	public function test_pods_access_callback_allowed_disabled_blocks_all_string_callbacks() {
		pods_update_setting( 'display_callbacks', '0' );

		$this->assertFalse( pods_access_callback_allowed( 'esc_html' ) );
		// Non-string callables are rejected regardless of the display setting.
		$this->assertFalse( pods_access_callback_allowed( static function () {
		} ) );
	}

	/*
	 * pods_access_bleep_placeholder() / pods_access_bleep_text() /
	 * pods_access_bleep_data() / pods_access_bleep_items().
	 */

	public function test_pods_access_bleep_placeholder() {
		$this->assertEquals( '****************', pods_access_bleep_placeholder() );
	}

	public function test_pods_access_bleep_text() {
		$placeholder = pods_access_bleep_placeholder();

		$this->assertEquals( $placeholder, pods_access_bleep_text( 'secret' ) );
		// A single "0" character still has a length, so it is bleeped.
		$this->assertEquals( $placeholder, pods_access_bleep_text( '0' ) );
		// Empty values are returned unchanged.
		$this->assertEquals( '', pods_access_bleep_text( '' ) );
		$this->assertNull( pods_access_bleep_text( null ) );
	}

	public function test_pods_access_bleep_data_object() {
		$placeholder = pods_access_bleep_placeholder();

		$data = (object) [
			'user_pass'           => 'hashed-password',
			'user_activation_key' => 'activation-key',
			'post_password'       => '',
			'display_name'        => 'Public Name',
		];

		$bleeped = pods_access_bleep_data( $data );

		$this->assertEquals( $placeholder, $bleeped->user_pass );
		$this->assertEquals( $placeholder, $bleeped->user_activation_key );
		// Empty sensitive values stay empty rather than becoming the placeholder.
		$this->assertEquals( '', $bleeped->post_password );
		// Non-sensitive values are left untouched.
		$this->assertEquals( 'Public Name', $bleeped->display_name );
	}

	public function test_pods_access_bleep_data_array_with_additional_properties() {
		$placeholder = pods_access_bleep_placeholder();

		$data = [
			'user_pass'   => 'hashed-password',
			'secret_key'  => 'my-secret',
			'public_data' => 'ok',
		];

		$bleeped = pods_access_bleep_data( $data, [ 'secret_key' ] );

		$this->assertEquals( $placeholder, $bleeped['user_pass'] );
		$this->assertEquals( $placeholder, $bleeped['secret_key'] );
		$this->assertEquals( 'ok', $bleeped['public_data'] );
	}

	public function test_pods_access_bleep_items() {
		$placeholder = pods_access_bleep_placeholder();

		$items = [
			(object) [ 'user_pass' => 'a' ],
			[ 'post_password' => 'b' ],
		];

		$bleeped = pods_access_bleep_items( $items );

		$this->assertEquals( $placeholder, $bleeped[0]->user_pass );
		$this->assertEquals( $placeholder, $bleeped[1]['post_password'] );
	}

	/*
	 * pods_maybe_safely_unserialize().
	 */

	public function test_pods_maybe_safely_unserialize_returns_non_serialized_as_is() {
		$this->assertEquals( 'just a string', pods_maybe_safely_unserialize( 'just a string' ) );
		$this->assertEquals( 12345, pods_maybe_safely_unserialize( 12345 ) );
	}

	public function test_pods_maybe_safely_unserialize_unserializes_arrays() {
		$data = [ 'a' => 1, 'b' => [ 'c' => 2 ] ];

		$this->assertEquals( $data, pods_maybe_safely_unserialize( serialize( $data ) ) );
	}

	public function test_pods_maybe_safely_unserialize_excludes_objects() {
		$serialized = serialize( (object) [ 'foo' => 'bar' ] );

		$result = pods_maybe_safely_unserialize( $serialized );

		// Objects are unserialized without their class.
		$this->assertInstanceOf( \__PHP_Incomplete_Class::class, $result );
	}

	/*
	 * pods_access_get_dynamic_features_allow_options() /
	 * pods_access_get_restricted_dynamic_features_options().
	 */

	public function test_pods_access_get_dynamic_features_allow_options() {
		$this->assertEquals(
			[ 'inherit', '1', '0' ],
			array_keys( pods_access_get_dynamic_features_allow_options() )
		);
	}

	public function test_pods_access_get_restricted_dynamic_features_options() {
		$this->assertEquals(
			[ 'display', 'form' ],
			array_keys( pods_access_get_restricted_dynamic_features_options() )
		);
	}

	/*
	 * SQL fragment validation.
	 *
	 * Each disallow_* filter is tested directly (they are pure functions of
	 * their string input), plus the aggregate pods_access_sql_fragment_is_allowed()
	 * to confirm every filter is registered and wired together.
	 */

	public function test_pods_access_sql_fragment_disallow_unsafe_functions_blocks_dangerous_functions() {
		$blocked = [
			'SLEEP(5)',
			'BENCHMARK(1000000, MD5(1))',
			'GET_LOCK("x", 10)',
			'RELEASE_LOCK("x")',
			'LOAD_FILE("/etc/passwd")',
			'EXTRACTVALUE(1, CONCAT(0x7e, VERSION()))',
			'UPDATEXML(1, 1, 1)',
			'FROM_BASE64("x")',
			'TO_BASE64("x")',
			'UNHEX("41")',
			'AES_DECRYPT(a, b)',
			'VERSION()',
			'DATABASE()',
			'CURRENT_USER()',
			'SESSION_USER()',
			'CONNECTION_ID()',
			'SYS_EXEC("id")',
			'SYS_EVAL("id")',
			// Case-insensitive.
			'sleep(5)',
			// Whitespace before the parenthesis is still caught.
			'SLEEP (5)',
		];

		foreach ( $blocked as $sql ) {
			$this->assertFalse(
				pods_access_sql_fragment_disallow_unsafe_functions( true, $sql ),
				sprintf( 'Expected "%s" to be disallowed.', $sql )
			);
		}
	}

	public function test_pods_access_sql_fragment_disallow_unsafe_functions_allows_safe_clauses() {
		$allowed = [
			'menu_order ASC',
			'post_title',
			'CONCAT(first_name, " ", last_name)',
			'YEAR(post_date) = 2024',
			'COALESCE(meta_value, 0)',
			// Contains "sleep"/"user" but not as a function call.
			'sleep_minutes > 5',
			'user_id = 1',
		];

		foreach ( $allowed as $sql ) {
			$this->assertTrue(
				pods_access_sql_fragment_disallow_unsafe_functions( true, $sql ),
				sprintf( 'Expected "%s" to be allowed.', $sql )
			);
		}
	}

	public function test_pods_access_sql_fragment_disallow_unsafe_functions_respects_prior_disallow() {
		// When already disallowed, it stays disallowed regardless of content.
		$this->assertFalse( pods_access_sql_fragment_disallow_unsafe_functions( false, 'post_title' ) );
	}

	public function test_pods_access_sql_fragment_disallow_unsafe_tables_blocks_system_tables() {
		$blocked = [
			'information_schema.tables',
			'mysql.user',
			'performance_schema.events_statements_current',
			'sys.host_summary',
			// Backtick-quoted identifiers must not evade the check.
			'`information_schema`.`tables`',
			// Whitespace around the separator must not evade the check.
			'information_schema . tables',
		];

		foreach ( $blocked as $sql ) {
			$this->assertFalse(
				pods_access_sql_fragment_disallow_unsafe_tables( true, $sql ),
				sprintf( 'Expected "%s" to be disallowed.', $sql )
			);
		}
	}

	public function test_pods_access_sql_fragment_disallow_unsafe_tables_allows_normal_tables() {
		$this->assertTrue( pods_access_sql_fragment_disallow_unsafe_tables( true, 'wp_posts.ID' ) );
		$this->assertTrue( pods_access_sql_fragment_disallow_unsafe_tables( true, 'post_title' ) );
	}

	public function test_pods_access_sql_fragment_disallow_comments_blocks_comment_markers() {
		$this->assertFalse( pods_access_sql_fragment_disallow_comments( true, 'a = 1 -- comment' ) );
		$this->assertFalse( pods_access_sql_fragment_disallow_comments( true, 'SL/*x*/EEP(5)' ) );
		$this->assertFalse( pods_access_sql_fragment_disallow_comments( true, 'a */ b' ) );
	}

	public function test_pods_access_sql_fragment_disallow_comments_allows_hash_in_values() {
		// "#" is intentionally allowed since it appears in legitimate values.
		$this->assertTrue( pods_access_sql_fragment_disallow_comments( true, 'meta_value = "#ff0000"' ) );
		$this->assertTrue( pods_access_sql_fragment_disallow_comments( true, 'post_title LIKE "%#tag%"' ) );
		$this->assertTrue( pods_access_sql_fragment_disallow_comments( true, 'a = 1 AND b = 2' ) );
	}

	public function test_pods_access_sql_fragment_disallow_unsafe_keywords_blocks() {
		$blocked = [
			'@@version',
			'@@datadir',
			'1 UNION SELECT user, pass FROM wp_users',
			'id INTO OUTFILE "/tmp/x"',
			'id INTO DUMPFILE "/tmp/x"',
			'LOAD DATA INFILE "/tmp/x"',
			'id; DROP TABLE wp_users',
		];

		foreach ( $blocked as $sql ) {
			$this->assertFalse(
				pods_access_sql_fragment_disallow_unsafe_keywords( true, $sql ),
				sprintf( 'Expected "%s" to be disallowed.', $sql )
			);
		}
	}

	public function test_pods_access_sql_fragment_disallow_unsafe_keywords_allows_safe_clauses() {
		// Word-boundary aware: identifiers that merely contain "union" are fine.
		$this->assertTrue( pods_access_sql_fragment_disallow_unsafe_keywords( true, 'union_id = 1' ) );
		$this->assertTrue( pods_access_sql_fragment_disallow_unsafe_keywords( true, 'reunion_count > 0' ) );
		$this->assertTrue( pods_access_sql_fragment_disallow_unsafe_keywords( true, 'menu_order ASC' ) );
	}

	public function test_pods_access_sql_fragment_disallow_mismatch_parenthesis() {
		$this->assertTrue( pods_access_sql_fragment_disallow_mismatch_parenthesis( true, 'CONCAT(a, b)' ) );
		$this->assertTrue( pods_access_sql_fragment_disallow_mismatch_parenthesis( true, 'a = 1' ) );
		$this->assertFalse( pods_access_sql_fragment_disallow_mismatch_parenthesis( true, 'CONCAT(a, b' ) );
		$this->assertFalse( pods_access_sql_fragment_disallow_mismatch_parenthesis( true, '((a)' ) );
	}

	public function test_pods_access_sql_fragment_disallow_double_hyphens() {
		$this->assertTrue( pods_access_sql_fragment_disallow_double_hyphens( true, 'a - b' ) );
		$this->assertFalse( pods_access_sql_fragment_disallow_double_hyphens( true, 'a -- comment' ) );
	}

	public function test_pods_access_sql_fragment_disallow_subqueries() {
		$this->assertTrue( pods_access_sql_fragment_disallow_subqueries( true, 'post_title' ) );
		// "select" as part of a larger identifier is not a subquery.
		$this->assertTrue( pods_access_sql_fragment_disallow_subqueries( true, 'selected_flag = 1' ) );
		$this->assertFalse( pods_access_sql_fragment_disallow_subqueries( true, 'SELECT id FROM wp_users' ) );
		$this->assertFalse( pods_access_sql_fragment_disallow_subqueries( true, '(SELECT id FROM wp_users)' ) );
		$this->assertFalse( pods_access_sql_fragment_disallow_subqueries( true, 'select id' ) );
	}

	public function test_pods_access_sql_fragment_disallow_post_status_ignores_non_where_having() {
		$this->assertTrue( pods_access_sql_fragment_disallow_post_status( true, 'post_status = "draft"', 'SELECT', [] ) );
		$this->assertTrue( pods_access_sql_fragment_disallow_post_status( true, 'post_status = "draft"', 'ORDER BY', [] ) );
	}

	public function test_pods_access_sql_fragment_disallow_post_status_allows_where_without_post_status() {
		$this->assertTrue( pods_access_sql_fragment_disallow_post_status( true, 'post_title = "x"', 'WHERE', [] ) );
	}

	public function test_pods_access_sql_fragment_disallow_post_status_blocks_post_status_in_where_for_non_admin() {
		wp_set_current_user( 0 );

		$this->assertFalse( pods_access_sql_fragment_disallow_post_status( true, 'post_status = "draft"', 'WHERE', [] ) );
		$this->assertFalse( pods_access_sql_fragment_disallow_post_status( true, 'post_status = "draft"', 'HAVING', [] ) );
	}

	public function test_pods_access_sql_fragment_is_allowed_allows_simple_clauses() {
		$this->assertTrue( pods_access_sql_fragment_is_allowed( 'post_title', 'SELECT' ) );
		$this->assertTrue( pods_access_sql_fragment_is_allowed( 'menu_order ASC', 'ORDER BY' ) );
	}

	public function test_pods_access_sql_fragment_is_allowed_blocks_dangerous_clauses() {
		// Confirms each disallow_* filter is registered on the aggregate filter.
		$this->assertFalse( pods_access_sql_fragment_is_allowed( 'SLEEP(5)', 'SELECT' ) );
		$this->assertFalse( pods_access_sql_fragment_is_allowed( 'SL/*x*/EEP(5)', 'SELECT' ) );
		$this->assertFalse( pods_access_sql_fragment_is_allowed( '@@datadir', 'ORDER BY' ) );
		$this->assertFalse( pods_access_sql_fragment_is_allowed( '`information_schema`.`tables`', 'SELECT' ) );
		$this->assertFalse( pods_access_sql_fragment_is_allowed( 'id; DROP TABLE wp_users', 'SELECT' ) );
		$this->assertFalse( pods_access_sql_fragment_is_allowed( 'a -- x', 'SELECT' ) );
		$this->assertFalse( pods_access_sql_fragment_is_allowed( '1 UNION SELECT 1', 'SELECT' ) );
		$this->assertFalse( pods_access_sql_fragment_is_allowed( 'CONCAT(a, b', 'SELECT' ) );
	}

}
