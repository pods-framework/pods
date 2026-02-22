<?php

namespace Pods_Unit_Tests\Functions;

use Pods_Unit_Tests\Pods_UnitTestCase;
use Exception;
use stdClass;

/**
 * @group pods
 * @group pods-functions
 * @group pods-functions-data
 */
class DataTest extends Pods_UnitTestCase {

	public static $db_reset_teardown = false;

	public function tearDown(): void {
		global $wpdb;

		$wpdb->show_errors( true );

		// Clean up superglobals from pods_v tests
		unset(
			$_GET['test_get'],
			$_GET['empty_string'],
			$_GET['zero'],
			$_GET['false'],
			$_GET['test_cast'],
			$_GET['test_allowed'],
			$_GET['test_allowed_single'],
			$_GET['test_bool'],
			$_POST['test_post'],
			$_REQUEST['test_request'],
			$_COOKIE['test_cookie'],
			$_SERVER['TEST_SERVER_VAR']
		);

		// Clean up session variables
		if ( isset( $_SESSION['test_pods_v_session'] ) ) {
			unset( $_SESSION['test_pods_v_session'] );
		}

		// Clean up global variables
		if ( isset( $GLOBALS['test_pods_v_global'] ) ) {
			unset( $GLOBALS['test_pods_v_global'] );
		}

		// Clean up options
		delete_option( 'test_pods_v_option' );
		delete_site_option( 'test_pods_v_site_option' );

		// Clean up transients
		delete_transient( 'test_pods_v_transient' );
		delete_site_transient( 'test_pods_v_site_transient' );

		// Clean up cache
		wp_cache_delete( 'test_pods_v_cache', 'test_group' );

		// Reset current user
		wp_set_current_user( 0 );

		parent::tearDown();
	}

	/**
	 * @covers ::pods_sanitize
	 */
	public function test_pods_sanitize_returns_empty_string() {
		$this->assertEquals( '', pods_sanitize( '' ) );
	}

	/**
	 * @covers ::pods_sanitize
	 */
	public function test_pods_sanitize_returns_int() {
		$this->assertEquals( 1, pods_sanitize( 1 ) );
	}

	/**
	 * @covers ::pods_sanitize
	 */
	public function test_pods_sanitize_returns_float() {
		$this->assertEquals( 12.348329, pods_sanitize( 12.348329 ) );
	}

	/**
	 * @covers ::pods_sanitize
	 */
	public function test_pods_sanitize_null() {
		$this->assertEquals( null, pods_sanitize( null ) );
	}

	/**
	 * @covers ::pods_sanitize
	 */
	public function test_pods_sanitize_returns_object() {
		$object      = new stdClass();
		$object->foo = 1;
		$object->bar = 'a test string';

		$this->assertEquals( $object, pods_sanitize( $object ) );
	}

	/**
	 * @covers ::pods_sanitize
	 */
	public function test_pods_sanitize_returns_array() {
		$array = [
			'foo' => 1,
			'bar' => 'a test string',
		];
		$this->assertEquals( $array, pods_sanitize( $array ) );
	}

	/**
	 * @covers ::pods_sanitize
	 */
	public function test_pods_sanitize_string() {
		$original = "'\\`";

		$this->assertEquals( "\'\\\`", pods_sanitize( $original ) );
	}

	/**
	 * @covers ::pods_sanitize
	 */
	public function test_pods_sanitize_sql() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	/**
	 * @covers ::pods_sanitize_like
	 */
	public function test_pods_sanitize_like() {
		$original = 'test%test%';

		$this->assertEquals( 'test\\\\%test\\\\%', pods_sanitize_like( $original ) );
	}

	/**
	 * @covers ::pods_slash
	 */
	public function test_pods_slash() {
		$original = "slash'this";

		$this->assertEquals( "slash\\'this", pods_slash( $original ) );
	}

	/**
	 * @covers ::pods_unsanitize
	 */
	public function test_pods_unsanitize() {
		$original = "unsanitize\\'this";

		$this->assertEquals( "unsanitize'this", pods_unsanitize( $original ) );
	}

	/**
	 * @covers ::pods_unslash
	 */
	public function test_pods_unslash() {
		$original = "unslash\\'this";

		$this->assertEquals( "unslash'this", pods_unslash( $original ) );
	}

	/**
	 * @covers ::pods_trim
	 */
	public function test_pods_trim() {
		$string = ' test ';

		$this->assertEquals( 'test', pods_trim( $string ) );

		$this->assertEquals( ' test', pods_trim( $string, null, 'r' ) );

		$this->assertEquals( 'test ', pods_trim( $string, null, 'l' ) );

		$string = ' test & ';

		$this->assertEquals( 'test', pods_trim( $string, ' &' ) );

		$this->assertEquals( ' test', pods_trim( $string, ' &', 'r' ) );

		// Arrays.

		$array = [
			' test ',
			' test2 ',
		];

		$result = [
			'test',
			'test2',
		];

		$this->assertEquals( $result, pods_trim( $array ) );

		$result = [
			' test',
			' test2',
		];

		$this->assertEquals( $result, pods_trim( $array, null, 'r' ) );

		$array = [
			' test  & ',
			' test2 ',
		];

		$result = [
			' test',
			' test2',
		];

		$this->assertEquals( $result, pods_trim( $array, ' &', 'r' ) );

		// Objects.

		$object        = new stdClass();
		$object->test  = ' test ';
		$object->test2 = ' test2 ';

		$result        = new stdClass();
		$result->test  = 'test';
		$result->test2 = 'test2';

		$this->assertEquals( $result, pods_trim( $object ) );

		$result->test  = ' test';
		$result->test2 = ' test2';

		$this->assertEquals( $result, pods_trim( $object, null, 'r' ) );

		$object->test  = ' test  & ';
		$object->test2 = ' test2 ';

		$result->test  = ' test';
		$result->test2 = ' test2';

		$this->assertEquals( $result, pods_trim( $object, ' &', 'r' ) );
	}

	/**
	 * @covers pods_traverse
	 */
	public function test_pods_traverse() {

		/**
		 * Array values.
		 */
		$value = array(
			'foobar',
			'one' => 1,
			'two' => '2',
			'decimals' => array(
				'no_key',
				'second_no_key',
				'third_no_key',
				'half'    => 0.5,
				'onehalf' => 1.5,
			),
		);

		// No traversal.
		$this->assertEquals( $value, pods_traverse( null, $value ) );

		// String traversal.
		$this->assertEquals( 1, pods_traverse( 'one', $value ) );
		$this->assertEquals( '2', pods_traverse( 'two', $value ) );
		$this->assertEquals( 1.5, pods_traverse( 'decimals.onehalf', $value ) );
		$this->assertEquals( null, pods_traverse( 'invalid', $value ) );
		$this->assertEquals( null, pods_traverse( 'decimals.invalid', $value ) );

		// Array traversal.
		$this->assertEquals( 1, pods_traverse( array( 'one' ), $value ) );
		$this->assertEquals( '2', pods_traverse( array( 'two' ), $value ) );
		$this->assertEquals( 1.5, pods_traverse( array( 'decimals', 'onehalf' ), $value ) );
		$this->assertEquals( null, pods_traverse( array( 'invalid' ), $value ) );
		$this->assertEquals( null, pods_traverse( array( 'decimals', 'invalid' ), $value ) );

		// Numeric array keys.
		$this->assertEquals( 'foobar', pods_traverse( 0, $value ) );
		$this->assertEquals( 'third_no_key', pods_traverse( 'decimals.2', $value ) );
		$this->assertEquals( 'foobar', pods_traverse( array( 0 ), $value ) );
		$this->assertEquals( 'third_no_key', pods_traverse( array( 'decimals', 2 ), $value ) );

		/**
		 * Object values.
		 * Numeric keys not available in objects.
		 */
		$value                    = new stdClass();
		$value->one               = 1;
		$value->two               = '2';
		$value->decimals          = new stdClass();
		$value->decimals->half    = 0.5;
		$value->decimals->onehalf = 1.5;

		// No traversal.
		$this->assertEquals( $value, pods_traverse( null, $value ) );

		// String traversal.
		$this->assertEquals( 1, pods_traverse( 'one', $value ) );
		$this->assertEquals( '2', pods_traverse( 'two', $value ) );
		$this->assertEquals( 1.5, pods_traverse( 'decimals.onehalf', $value ) );
		$this->assertEquals( null, pods_traverse( 'invalid', $value ) );
		$this->assertEquals( null, pods_traverse( 'decimals.invalid', $value ) );

		// Array traversal.
		$this->assertEquals( 1, pods_traverse( array( 'one' ), $value ) );
		$this->assertEquals( '2', pods_traverse( array( 'two' ), $value ) );
		$this->assertEquals( 1.5, pods_traverse( array( 'decimals', 'onehalf' ), $value ) );
		$this->assertEquals( null, pods_traverse( array( 'invalid' ), $value ) );
		$this->assertEquals( null, pods_traverse( array( 'decimals', 'invalid' ), $value ) );

	}

	public function test_pods_v_get() {
		$_GET['test_get'] = 'test value';

		$this->assertEquals( 'test value', pods_v( 'test_get' ) );
		$this->assertEquals( 'test value', pods_v( 'test_get', 'get' ) );
	}

	public function test_pods_v_post() {
		$_POST['test_post'] = 'test value';

		$this->assertEquals( 'test value', pods_v( 'test_post', 'post' ) );
	}

	public function test_pods_v_request() {
		$_REQUEST['test_request'] = 'test value';

		$this->assertEquals( 'test value', pods_v( 'test_request', 'request' ) );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_array() {
		$array = [
			'key1' => 'value1',
			'key2' => 'value2',
		];

		$this->assertEquals( 'value1', pods_v( 'key1', $array ) );
		$this->assertEquals( 'value2', pods_v( 'key2', $array ) );
		$this->assertNull( pods_v( 'key3', $array ) );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_object() {
		$object = new stdClass();
		$object->key1 = 'value1';
		$object->key2 = 'value2';

		$this->assertEquals( 'value1', pods_v( 'key1', $object ) );
		$this->assertEquals( 'value2', pods_v( 'key2', $object ) );
		$this->assertNull( pods_v( 'key3', $object ) );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_default() {
		$this->assertEquals( 'default_value', pods_v( 'nonexistent', 'get', 'default_value' ) );
		$this->assertNull( pods_v( 'nonexistent', 'get' ) );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_strict() {
		$_GET['empty_string'] = '';
		$_GET['zero'] = 0;
		$_GET['false'] = false;

		$this->assertEquals( '', pods_v( 'empty_string', 'get', 'default' ) );
		$this->assertEquals( 'default', pods_v( 'empty_string', 'get', 'default', true ) );

		$this->assertEquals( 0, pods_v( 'zero', 'get', 'default' ) );
		$this->assertEquals( 'default', pods_v( 'zero', 'get', 'default', true ) );

		$this->assertEquals( false, pods_v( 'false', 'get', 'default' ) );
		$this->assertEquals( 'default', pods_v( 'false', 'get', 'default', true ) );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_casting() {
		$_GET['test_cast'] = '123';

		$result = pods_v( 'test_cast', 'get', 456, false, [ 'casting' => true ] );
		$this->assertSame( 456, $result );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_allowed() {
		$_GET['test_allowed'] = 'valid';

		$result = pods_v( 'test_allowed', 'get', 'default', false, [ 'allowed' => [ 'valid', 'also_valid' ] ] );
		$this->assertEquals( 'valid', $result );

		$_GET['test_allowed'] = 'invalid';
		$result = pods_v( 'test_allowed', 'get', 'default', false, [ 'allowed' => [ 'valid', 'also_valid' ] ] );
		$this->assertEquals( 'default', $result );

		$_GET['test_allowed_single'] = 'exact_match';
		$result = pods_v( 'test_allowed_single', 'get', 'default', false, [ 'allowed' => 'exact_match' ] );
		$this->assertEquals( 'exact_match', $result );

		$_GET['test_allowed_single'] = 'no_match';
		$result = pods_v( 'test_allowed_single', 'get', 'default', false, [ 'allowed' => 'exact_match' ] );
		$this->assertEquals( 'default', $result );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_prefix() {
		global $wpdb;

		$result = pods_v( null, 'prefix' );
		$this->assertEquals( $wpdb->prefix, $result );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_cookie() {
		$_COOKIE['test_cookie'] = 'cookie_value';

		$this->assertEquals( 'cookie_value', pods_v( 'test_cookie', 'cookie' ) );
		$this->assertNull( pods_v( 'nonexistent_cookie', 'cookie' ) );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_constant() {
		if ( ! defined( 'TEST_PODS_V_CONSTANT' ) ) {
			define( 'TEST_PODS_V_CONSTANT', 'constant_value' );
		}

		$this->assertEquals( 'constant_value', pods_v( 'TEST_PODS_V_CONSTANT', 'constant' ) );
		$this->assertNull( pods_v( 'NONEXISTENT_CONSTANT', 'constant' ) );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_option() {
		add_option( 'test_pods_v_option', 'option_value' );

		$this->assertEquals( 'option_value', pods_v( 'test_pods_v_option', 'option' ) );
		$this->assertEquals( 'default_option', pods_v( 'nonexistent_option', 'option', 'default_option' ) );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_site_option() {
		add_site_option( 'test_pods_v_site_option', 'site_option_value' );

		$this->assertEquals( 'site_option_value', pods_v( 'test_pods_v_site_option', 'site-option' ) );
		$this->assertEquals( 'default_site_option', pods_v( 'nonexistent_site_option', 'site-option', 'default_site_option' ) );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_transient() {
		set_transient( 'test_pods_v_transient', 'transient_value', 3600 );

		$this->assertEquals( 'transient_value', pods_v( 'test_pods_v_transient', 'transient' ) );
		$this->assertFalse( pods_v( 'nonexistent_transient', 'transient' ) );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_site_transient() {
		set_site_transient( 'test_pods_v_site_transient', 'site_transient_value', 3600 );

		$this->assertEquals( 'site_transient_value', pods_v( 'test_pods_v_site_transient', 'site-transient' ) );
		$this->assertFalse( pods_v( 'nonexistent_site_transient', 'site-transient' ) );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_cache() {
		wp_cache_set( 'test_pods_v_cache', 'cache_value', 'test_group' );

		$this->assertEquals( 'cache_value', pods_v( 'test_pods_v_cache|test_group', 'cache' ) );
		$this->assertEquals( 'cache_value', pods_v( [ 'test_pods_v_cache', 'test_group' ], 'cache' ) );
		$this->assertFalse( pods_v( 'nonexistent_cache|test_group', 'cache' ) );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_template_url() {
		$result = pods_v( null, 'template-url' );
		$this->assertEquals( get_template_directory_uri(), $result );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_stylesheet_url() {
		$result = pods_v( null, 'stylesheet-url' );
		$this->assertEquals( get_stylesheet_directory_uri(), $result );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_site_url() {
		$result = pods_v( null, 'site-url' );
		$this->assertEquals( get_site_url(), $result );

		$result = pods_v( [ null, '/test-path' ], 'site-url' );
		$this->assertEquals( get_site_url( null, '/test-path' ), $result );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_home_url() {
		$result = pods_v( null, 'home-url' );
		$this->assertEquals( get_home_url(), $result );

		$result = pods_v( [ null, '/test-path' ], 'home-url' );
		$this->assertEquals( get_home_url( null, '/test-path' ), $result );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_admin_url() {
		$result = pods_v( null, 'admin-url' );
		$this->assertEquals( get_admin_url(), $result );

		$result = pods_v( [ null, 'admin.php' ], 'admin-url' );
		$this->assertEquals( get_admin_url( null, 'admin.php' ), $result );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_includes_url() {
		$result = pods_v( '', 'includes-url' );
		$this->assertEquals( includes_url(), $result );

		$result = pods_v( 'js/jquery/jquery.js', 'includes-url' );
		$this->assertEquals( includes_url( 'js/jquery/jquery.js' ), $result );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_content_url() {
		$result = pods_v( '', 'content-url' );
		$this->assertEquals( content_url(), $result );

		$result = pods_v( '/uploads', 'content-url' );
		$this->assertEquals( content_url( '/uploads' ), $result );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_plugins_url() {
		$result = pods_v( '', 'plugins-url' );
		$this->assertEquals( plugins_url(), $result );

		$result = pods_v( 'test-plugin/asset.js', 'plugins-url' );
		$this->assertEquals( plugins_url( 'test-plugin/asset.js' ), $result );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_network_site_url() {
		$result = pods_v( '', 'network-site-url' );
		$this->assertEquals( network_site_url(), $result );

		$result = pods_v( [ '/path' ], 'network-site-url' );
		$this->assertEquals( network_site_url( '/path' ), $result );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_network_home_url() {
		$result = pods_v( '', 'network-home-url' );
		$this->assertEquals( network_home_url(), $result );

		$result = pods_v( [ '/path' ], 'network-home-url' );
		$this->assertEquals( network_home_url( '/path' ), $result );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_network_admin_url() {
		$result = pods_v( '', 'network-admin-url' );
		$this->assertEquals( network_admin_url(), $result );

		$result = pods_v( [ 'settings.php' ], 'network-admin-url' );
		$this->assertEquals( network_admin_url( 'settings.php' ), $result );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_user_admin_url() {
		$result = pods_v( '', 'user-admin-url' );
		$this->assertEquals( user_admin_url(), $result );

		$result = pods_v( [ 'profile.php' ], 'user-admin-url' );
		$this->assertEquals( user_admin_url( 'profile.php' ), $result );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_date() {
		$result = pods_v( 'Y-m-d', 'date' );
		$this->assertEquals( date_i18n( 'Y-m-d' ), $result );

		$result = pods_v( 'Y-m-d|2024-01-01', 'date' );
		$this->assertEquals( date_i18n( 'Y-m-d', strtotime( '2024-01-01' ) ), $result );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_post_id() {
		$post_id = $this->factory->post->create();

		$result = pods_v( $post_id, 'post_id' );
		$this->assertEquals( $post_id, $result );

		// Test with empty var should use current post
		global $post;
		$old_post = $post;
		$post = get_post( $post_id );

		$result = pods_v( '', 'post_id' );
		$this->assertEquals( $post_id, $result );

		$post = $old_post;
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_user() {
		$user_id = $this->factory->user->create( [
			'user_login' => 'testuser',
			'user_email' => 'testuser@example.com',
			'first_name' => 'Test',
			'last_name' => 'User',
		] );

		wp_set_current_user( $user_id );

		$this->assertEquals( $user_id, pods_v( 'ID', 'user' ) );
		$this->assertEquals( $user_id, pods_v( 'id', 'user' ) ); // Test lowercase conversion
		$this->assertEquals( 'testuser', pods_v( 'user_login', 'user' ) );
		$this->assertEquals( 'testuser@example.com', pods_v( 'user_email', 'user' ) );

		wp_set_current_user( 0 );

		// When logged out, ID should return 0
		$this->assertEquals( 0, pods_v( 'ID', 'user' ) );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_global() {
		$GLOBALS['test_pods_v_global'] = 'global_value';

		$this->assertEquals( 'global_value', pods_v( 'test_pods_v_global', 'global' ) );
		$this->assertEquals( 'global_value', pods_v( 'test_pods_v_global', 'globals' ) );
		$this->assertNull( pods_v( 'nonexistent_global', 'global' ) );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_session() {
		if ( ! isset( $_SESSION ) ) {
			$_SESSION = [];
		}

		$_SESSION['test_pods_v_session'] = 'session_value';

		$this->assertEquals( 'session_value', pods_v( 'test_pods_v_session', 'session' ) );
		$this->assertNull( pods_v( 'nonexistent_session', 'session' ) );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_disallowed_types_for_magic_tag() {
		$_SERVER['TEST_SERVER_VAR'] = 'server_value';

		// Without source restriction, should work
		$this->assertEquals( 'server_value', pods_v( 'TEST_SERVER_VAR', 'server' ) );

		// With magic-tag source, server type should be disallowed
		$result = pods_v( 'TEST_SERVER_VAR', 'server', 'default', false, [ 'source' => 'magic-tag' ] );
		$this->assertEquals( 'default', $result );
	}

	/**
	 * @covers ::pods_v
	 */
	public function test_pods_v_invalid_type() {
		$result = pods_v( 'test', null );
		$this->assertNull( $result );

		$result = pods_v( 'test', '' );
		$this->assertNull( $result );
	}

	/**
	 * @covers ::pods_v_bool
	 */
	public function test_pods_v_bool_basic() {
		// Test with truthy value
		$_GET['test_bool'] = '1';
		$this->assertTrue( pods_v_bool( 'test_bool' ) );

		// Test with falsy value
		$_GET['test_bool'] = '0';
		$this->assertFalse( pods_v_bool( 'test_bool' ) );

		// Test with default true
		$this->assertTrue( pods_v_bool( 'nonexistent_bool', 'get', true ) );

		// Test with default false
		$this->assertFalse( pods_v_bool( 'nonexistent_bool', 'get', false ) );
	}

	/**
	 * @covers ::pods_v_bool
	 */
	public function test_pods_v_bool_return_type() {
		// Ensure return type is always boolean
		$_GET['test_bool'] = '1';
		$result = pods_v_bool( 'test_bool' );
		$this->assertIsBool( $result );

		$_GET['test_bool'] = '0';
		$result = pods_v_bool( 'test_bool' );
		$this->assertIsBool( $result );

		// Test with non-existent value
		$result = pods_v_bool( 'nonexistent' );
		$this->assertIsBool( $result );
	}

	/**
	 * @covers ::pods_is_truthy
	 */
	public function test_pods_is_truthy_with_null() {
		$this->assertFalse( pods_is_truthy( null ) );
	}

	/**
	 * @covers ::pods_is_truthy
	 */
	public function test_pods_is_truthy_with_boolean() {
		$this->assertTrue( pods_is_truthy( true ) );
		$this->assertFalse( pods_is_truthy( false ) );
	}

	/**
	 * @covers ::pods_is_truthy
	 */
	public function test_pods_is_truthy_with_integer() {
		// Only integer 1 is truthy
		$this->assertTrue( pods_is_truthy( 1 ) );

		// Other integers are not truthy
		$this->assertFalse( pods_is_truthy( 0 ) );
		$this->assertFalse( pods_is_truthy( 2 ) );
		$this->assertFalse( pods_is_truthy( -1 ) );
		$this->assertFalse( pods_is_truthy( 100 ) );
	}

	/**
	 * @covers ::pods_is_truthy
	 */
	public function test_pods_is_truthy_with_float() {
		// Only float 1.0 is truthy
		$this->assertTrue( pods_is_truthy( 1.0 ) );

		// Other floats are not truthy
		$this->assertFalse( pods_is_truthy( 0.0 ) );
		$this->assertFalse( pods_is_truthy( 1.5 ) );
		$this->assertFalse( pods_is_truthy( 2.0 ) );
		$this->assertFalse( pods_is_truthy( -1.0 ) );
	}

	/**
	 * @covers ::pods_is_truthy
	 */
	public function test_pods_is_truthy_with_truthy_strings() {
		// Test all supported truthy strings
		$this->assertTrue( pods_is_truthy( '1' ) );
		$this->assertTrue( pods_is_truthy( 'true' ) );
		$this->assertTrue( pods_is_truthy( 'on' ) );
		$this->assertTrue( pods_is_truthy( 'yes' ) );
		$this->assertTrue( pods_is_truthy( 'y' ) );
		$this->assertTrue( pods_is_truthy( 'enabled' ) );
	}

	/**
	 * @covers ::pods_is_truthy
	 */
	public function test_pods_is_truthy_with_case_insensitive_strings() {
		// Test uppercase
		$this->assertTrue( pods_is_truthy( 'TRUE' ) );
		$this->assertTrue( pods_is_truthy( 'ON' ) );
		$this->assertTrue( pods_is_truthy( 'YES' ) );
		$this->assertTrue( pods_is_truthy( 'Y' ) );
		$this->assertTrue( pods_is_truthy( 'ENABLED' ) );

		// Test mixed case
		$this->assertTrue( pods_is_truthy( 'True' ) );
		$this->assertTrue( pods_is_truthy( 'On' ) );
		$this->assertTrue( pods_is_truthy( 'Yes' ) );
		$this->assertTrue( pods_is_truthy( 'Enabled' ) );
	}

	/**
	 * @covers ::pods_is_truthy
	 */
	public function test_pods_is_truthy_with_whitespace() {
		// Test strings with leading/trailing whitespace
		$this->assertFalse( pods_is_truthy( ' 1 ' ) );
		$this->assertFalse( pods_is_truthy( '  true  ' ) );
		$this->assertFalse( pods_is_truthy( "\ton\t" ) );
		$this->assertFalse( pods_is_truthy( "\nyes\n" ) );
	}

	/**
	 * @covers ::pods_is_truthy
	 */
	public function test_pods_is_truthy_with_falsy_strings() {
		// Test strings that are not truthy
		$this->assertFalse( pods_is_truthy( '0' ) );
		$this->assertFalse( pods_is_truthy( 'false' ) );
		$this->assertFalse( pods_is_truthy( 'off' ) );
		$this->assertFalse( pods_is_truthy( 'no' ) );
		$this->assertFalse( pods_is_truthy( 'n' ) );
		$this->assertFalse( pods_is_truthy( '' ) );
		$this->assertFalse( pods_is_truthy( 'disabled' ) );
		$this->assertFalse( pods_is_truthy( 'random' ) );
	}

	/**
	 * @covers ::pods_is_truthy
	 */
	public function test_pods_is_truthy_with_unsupported_types() {
		// Arrays are not supported
		$this->assertFalse( pods_is_truthy( [] ) );
		$this->assertFalse( pods_is_truthy( [ 1 ] ) );

		// Objects are not supported
		$obj = new stdClass();
		$this->assertFalse( pods_is_truthy( $obj ) );
	}

	/**
	 * @covers ::pods_is_falsey
	 */
	public function test_pods_is_falsey_with_null() {
		$this->assertTrue( pods_is_falsey( null ) );
	}

	/**
	 * @covers ::pods_is_falsey
	 */
	public function test_pods_is_falsey_with_boolean() {
		$this->assertTrue( pods_is_falsey( false ) );
		$this->assertFalse( pods_is_falsey( true ) );
	}

	/**
	 * @covers ::pods_is_falsey
	 */
	public function test_pods_is_falsey_with_integer() {
		// Only integer 0 is falsey
		$this->assertTrue( pods_is_falsey( 0 ) );

		// Other integers are not falsey
		$this->assertFalse( pods_is_falsey( 1 ) );
		$this->assertFalse( pods_is_falsey( 2 ) );
		$this->assertFalse( pods_is_falsey( -1 ) );
		$this->assertFalse( pods_is_falsey( 100 ) );
	}

	/**
	 * @covers ::pods_is_falsey
	 */
	public function test_pods_is_falsey_with_float() {
		// Only float 0.0 is falsey
		$this->assertTrue( pods_is_falsey( 0.0 ) );

		// Other floats are not falsey
		$this->assertFalse( pods_is_falsey( 1.0 ) );
		$this->assertFalse( pods_is_falsey( 1.5 ) );
		$this->assertFalse( pods_is_falsey( 2.0 ) );
		$this->assertFalse( pods_is_falsey( -1.0 ) );
	}

	/**
	 * @covers ::pods_is_falsey
	 */
	public function test_pods_is_falsey_with_falsy_strings() {
		// Test all supported falsy strings
		$this->assertTrue( pods_is_falsey( '0' ) );
		$this->assertTrue( pods_is_falsey( 'false' ) );
		$this->assertTrue( pods_is_falsey( 'off' ) );
		$this->assertTrue( pods_is_falsey( 'no' ) );
		$this->assertTrue( pods_is_falsey( 'n' ) );
	}

	/**
	 * @covers ::pods_is_falsey
	 */
	public function test_pods_is_falsey_with_case_insensitive_strings() {
		// Test uppercase
		$this->assertTrue( pods_is_falsey( 'FALSE' ) );
		$this->assertTrue( pods_is_falsey( 'OFF' ) );
		$this->assertTrue( pods_is_falsey( 'NO' ) );
		$this->assertTrue( pods_is_falsey( 'N' ) );

		// Test mixed case
		$this->assertTrue( pods_is_falsey( 'False' ) );
		$this->assertTrue( pods_is_falsey( 'Off' ) );
		$this->assertTrue( pods_is_falsey( 'No' ) );
	}

	/**
	 * @covers ::pods_is_falsey
	 */
	public function test_pods_is_falsey_with_whitespace() {
		// Test strings with leading/trailing whitespace
		$this->assertFalse( pods_is_falsey( ' 0 ' ) );
		$this->assertFalse( pods_is_falsey( '  false  ' ) );
		$this->assertFalse( pods_is_falsey( "\toff\t" ) );
		$this->assertFalse( pods_is_falsey( "\nno\n" ) );
	}

	/**
	 * @covers ::pods_is_falsey
	 */
	public function test_pods_is_falsey_with_truthy_strings() {
		// Test strings that are not falsey
		$this->assertFalse( pods_is_falsey( '1' ) );
		$this->assertFalse( pods_is_falsey( 'true' ) );
		$this->assertFalse( pods_is_falsey( 'on' ) );
		$this->assertFalse( pods_is_falsey( 'yes' ) );
		$this->assertFalse( pods_is_falsey( 'y' ) );
		$this->assertFalse( pods_is_falsey( 'enabled' ) );
		$this->assertFalse( pods_is_falsey( 'random' ) );
	}

	/**
	 * @covers ::pods_is_falsey
	 */
	public function test_pods_is_falsey_with_empty_string() {
		// Empty string is NOT considered falsey (cannot be validated)
		$this->assertFalse( pods_is_falsey( '' ) );
	}

	/**
	 * @covers ::pods_is_falsey
	 */
	public function test_pods_is_falsey_with_unsupported_types() {
		// Arrays are not supported (cannot be validated as falsey)
		$this->assertFalse( pods_is_falsey( [] ) );
		$this->assertFalse( pods_is_falsey( [ 0 ] ) );

		// Objects are not supported (cannot be validated as falsey)
		$obj = new stdClass();
		$this->assertFalse( pods_is_falsey( $obj ) );
	}

	public function test_pods_v_sanitized() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function test_pods_v_set() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function test_pods_var() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function test_pods_var_raw() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function test_pods_var_set() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function test_pods_query_arg() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function test_pods_query() {
		$sql = "
			SELECT ID
			FROM @wp_posts
		    LIMIT 1
	    ";

		$result = pods_query( $sql );

		$this->assertTrue( is_array( $result ) );
		$this->assertCount( 1, $result );
		$this->assertArrayHasKey( 0, $result );
		$this->assertTrue( is_object( $result[0] ) );
		$this->assertObjectHasAttribute( 'ID', $result[0] );
		$this->assertTrue( is_numeric( $result[0]->ID ) );
	}

	public function test_pods_query_with_error() {
		global $wpdb;

		$wpdb->hide_errors();
		$wpdb->suppress_errors( true );

		$sql = "
			SELECT ID
			FROM @wp_postssssss
		    WHERE ID = 123456
		    LIMIT 1
	    ";

		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Database Error' );

		$result = pods_query( $sql );

		$this->assertFalse( $result );
	}

	public function test_pods_query_with_custom_error_message() {
		global $wpdb;

		$wpdb->hide_errors();
		$wpdb->suppress_errors( true );

		$sql = "
			SELECT ID
			FROM @wp_postssssss
		    WHERE ID = 123456
		    LIMIT 1
	    ";

		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Custom database error message' );

		$result = pods_query( $sql, 'Custom database error message' );

		$this->assertFalse( $result );
	}

	public function test_pods_query_with_results_error() {
		$sql = "
			SELECT ID
			FROM @wp_posts
		    WHERE ID != 123456
		    LIMIT 1
	    ";

		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'I have results but I should not' );

		$result = pods_query( $sql, 'Database Error', 'I have results but I should not' );

		$this->assertFalse( $result );
	}

	public function test_pods_query_with_no_results_error() {
		$sql = "
			SELECT ID
			FROM @wp_posts
		    WHERE ID = 123456
		    LIMIT 1
	    ";

		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'I have no results but I should' );

		$result = pods_query( $sql, 'Database Error', null, 'I have no results but I should' );

		$this->assertFalse( $result );
	}

	public function test_pods_query_prepare() {
		$sql = "
			SELECT ID
			FROM @wp_posts
		    WHERE
		        ID != %d
		        AND post_title != %s
		    LIMIT 1
	    ";

		$result = pods_query_prepare( $sql, [ 1234, 'Not this title' ] );

		$this->assertTrue( is_array( $result ) );
		$this->assertCount( 1, $result );
		$this->assertArrayHasKey( 0, $result );
		$this->assertTrue( is_object( $result[0] ) );
		$this->assertObjectHasAttribute( 'ID', $result[0] );
		$this->assertTrue( is_numeric( $result[0]->ID ) );
	}

	public function test_pods_query_prepare_with_error() {
		global $wpdb;

		$wpdb->hide_errors();
		$wpdb->suppress_errors( true );

		$sql = "
			SELECT ID
			FROM @wp_postssssss
		    WHERE
		        ID != %d
		        AND post_title != %s
		    LIMIT 1
	    ";

		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Database Error' );

		$result = pods_query_prepare( $sql, [ 1234, 'Not this title' ] );

		$this->assertFalse( $result );
	}

	public function test_pods_query_prepare_with_custom_error_message() {
		global $wpdb;

		$wpdb->hide_errors();
		$wpdb->suppress_errors( true );

		$sql = "
			SELECT ID
			FROM @wp_postssssss
		    WHERE
		        ID != %d
		        AND post_title != %s
		    LIMIT 1
	    ";

		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Custom database error message' );

		$result = pods_query_prepare( $sql, [ 1234, 'Not this title' ], 'Custom database error message' );

		$this->assertFalse( $result );
	}

	public function test_pods_query_prepare_with_results_error() {
		$sql = "
			SELECT ID
			FROM @wp_posts
		    WHERE
		        ID != %d
		        AND post_title != %s
		    LIMIT 1
	    ";

		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'I have results but I should not' );

		$result = pods_query_prepare( $sql, [ 1234, 'Not this title' ], 'Database Error', 'I have results but I should not' );

		$this->assertFalse( $result );
	}

	public function test_pods_query_prepare_with_no_results_error() {
		$sql = "
			SELECT ID
			FROM @wp_posts
		    WHERE
		        ID = %d
		        AND post_title = %s
		    LIMIT 1
	    ";

		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'I have no results but I should' );

		$result = pods_query_prepare( $sql, [ 1234, 'Not this title' ], 'Database Error', null, 'I have no results but I should' );

		$this->assertFalse( $result );
	}

	public function test_pods_var_update() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function test_pods_cast() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function test_pods_create_slug() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function test_pods_unique_slug() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function test_pods_clean_name() {
		$this->assertEquals( '_test_field_name_', pods_clean_name( ' _Test field! name_ ' ) );
		$this->assertEquals( '_test_field_name_', pods_clean_name( ' _Test field!  name_ ' ) );
		$this->assertEquals( '_test_field__name_', pods_clean_name( ' _Test field! __name_ ' ) );
		$this->assertEquals( '_test_field__name_', pods_clean_name( ' _Test_field!__name_ ' ) );
		$this->assertEquals( 'test_field-name', pods_clean_name( ' Test field-name ' ) );
		$this->assertEquals( 'test_field-name', pods_clean_name( ' Test field--name ' ) );

		$this->assertEquals( 'Test_field_name', pods_clean_name( ' Test field! name ', false ) );
		$this->assertEquals( 'Test_field_name', pods_clean_name( ' Test field!  name ', false ) );
		$this->assertEquals( 'Test_field__name', pods_clean_name( ' Test field! __name ', false ) );
		$this->assertEquals( 'Test_field__name', pods_clean_name( ' Test_field!__name',  false ) );

		$this->assertEquals( 'test_field_name', pods_clean_name( ' _Test field! name_ ', true, true ) );
		$this->assertEquals( 'test_field_name', pods_clean_name( ' _Test field!  name_ ', true, true ) );
		$this->assertEquals( 'test_field__name', pods_clean_name( ' _Test field! __name_ ', true, true ) );
		$this->assertEquals( 'test_field__name', pods_clean_name( ' _Test_field!__name_ ', true, true ) );
	}

	/**
	 * @covers ::pods_absint
	 */
	public function test_pods_absint() {
		$this->assertEquals( 1, pods_absint( 1.234 ) );
	}

	/**
	 * @covers ::pods_absint
	 */
	public function test_pods_absint_no_negative() {
		$this->assertEquals( 1, pods_absint( - 1.234 ) );
	}

	/**
	 * @covers ::pods_absint
	 */
	public function test_pods_absint_allows_negative() {
		$this->assertEquals( - 1, pods_absint( - 1.234, true, true ) );
	}

	/**
	 * @covers ::pods_absint
	 */
	public function test_pods_absint_returns_zero_for_string() {
		$this->assertEquals( 0, pods_absint( 'asdf' ) );
	}

	public function test_pods_str_replace() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	/**
	 * @covers ::pods_mb_strlen
	 */
	public function test_pods_mb_strlen() {
		$this->assertEquals( 4, pods_mb_strlen( 'asdf' ) );
	}

	/**
	 * @covers ::pods_mb_substr
	 */
	public function test_pods_mb_substr() {
		$this->assertEquals( 'sd', pods_mb_substr( 'asdf', 1, 2 ) );
	}

	/**
	 * @covers ::pods_evaluate_tags
	 * @covers ::pods_evaluate_tag
	 */
	public function test_pods_evaluate_tags() {
		global $wpdb;

		/**
		 * Special magic tags.
		 *
		 * @link https://docs.pods.io/displaying-pods/magic-tags/special-magic-tags/
		 */
		$this->assertEquals( $wpdb->prefix, pods_evaluate_tag( '{@prefix}' ) );
		$this->assertEquals( get_template_directory_uri(), pods_evaluate_tag( '{@template-url}' ) );
		$this->assertEquals( get_stylesheet_directory_uri(), pods_evaluate_tag( '{@stylesheet-url}' ) );
		$this->assertEquals( site_url(), pods_evaluate_tag( '{@site-url}' ) );
		$this->assertEquals( home_url(), pods_evaluate_tag( '{@home-url}' ) );
		$this->assertEquals( admin_url(), pods_evaluate_tag( '{@admin-url}' ) );
		$this->assertEquals( includes_url(), pods_evaluate_tag( '{@includes-url}' ) );
		$this->assertEquals( plugins_url(), pods_evaluate_tag( '{@plugins-url}' ) );
		$this->assertEquals( network_site_url(), pods_evaluate_tag( '{@network-site-url}' ) );
		$this->assertEquals( network_home_url(), pods_evaluate_tag( '{@network-home-url}' ) );
		$this->assertEquals( network_admin_url(), pods_evaluate_tag( '{@network-admin-url}' ) );
		$this->assertEquals( user_admin_url(), pods_evaluate_tag( '{@user-admin-url}' ) );
		$this->assertEquals( date_i18n( 'Y-m-d' ), pods_evaluate_tag( '{@date.Y-m-d}' ) );
		$this->assertEquals( date_i18n( 'Y-m-d', strtotime( 'tomorrow' ) ), pods_evaluate_tag( '{@date.Y-m-d|tomorrow}' ) );

		// First log in the user.
		$user_id = $this->factory()->user->create();
		wp_set_current_user( $user_id );

		// Should be `ID` but added lowercase for backwards compatibility.
		$this->assertEquals( get_current_user_id(), pods_evaluate_tag( '{@user.id}' ) );
	}

	/**
	 * @covers ::pods_evaluate_tag_sql
	 */
	public function test_pods_evaluate_tag_sql() {

		$params = array(
			'sanitize' => true,
			'fallback' => '""',
		);

		// EQUALS

		$sql     = "value = {@get.test_sql_tag}";
		$compare = 'value = ""';

		$this->assertEquals( pods_evaluate_tags_sql( $sql, $params ), $compare );

		$_GET['test_sql_tag'] = '5797';
		$compare              = 'value = 5797';

		$this->assertEquals( pods_evaluate_tags_sql( $sql, $params ), $compare );

		// LIKE

		$sql     = "value LIKE '{@get.test_sql_tag_like}%'";
		$compare = "value LIKE '%'";

		$this->assertEquals( pods_evaluate_tags_sql( $sql, $params ), $compare );

		$_GET['test_sql_tag_like'] = '5797';
		$compare                   = "value LIKE '5797%'";

		$this->assertEquals( pods_evaluate_tags_sql( $sql, $params ), $compare );
	}

	public function test_pods_evaluate_tag_sanitized() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function test_pods_evaluate_tag() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function test_pods_serial_comma() {
		$values = [
			'test1',
			'test2',
			'test3',
		];

		$result = 'test1, test2, and test3';

		$this->assertEquals( $result, pods_serial_comma( $values ) );

		$args = [
			'serial' => false,
		];

		$result = 'test1, test2 and test3';

		$this->assertEquals( $result, pods_serial_comma( $values, $args ) );

		$args = [
			'separator' => ' | ',
			'serial'    => false,
		];

		$result = 'test1 | test2 and test3';

		$this->assertEquals( $result, pods_serial_comma( $values, $args ) );

		$args = [
			'and'    => ' & ',
			'serial' => false,
		];

		$result = 'test1, test2 & test3';

		$this->assertEquals( $result, pods_serial_comma( $values, $args ) );

		$args = [
			'separator' => ' | ',
			'and'       => ' | ',
			'serial'    => false,
		];

		$result = 'test1 | test2 | test3';

		$this->assertEquals( $result, pods_serial_comma( $values, $args ) );
	}

	public function test_pods_var_user() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function test_pods_hierarchical_list() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function test_pods_hierarchical_list_recurse() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function test_pods_hierarchical_select() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function test_pods_hierarchical_select_recurse() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function test_pods_list_filter() {
		// Test objects since that is not supported by wp_list_filter.

		$obj       = new stdClass();
		$obj->obj1 = new stdClass();
		$obj->obj2 = new stdClass();
		$obj->obj3 = new stdClass();
		$obj->obj4 = new stdClass();

		$obj->obj1->status = 'published';
		$obj->obj2->status = 'published';
		$obj->obj3->status = 'draft';
		$obj->obj4->status = 'published';

		$obj->obj1->param = 'valid';
		$obj->obj2->param = 'invalid';
		$obj->obj3->param = 'valid';
		$obj->obj4->param = 'valid';

		$args = [
			'status' => 'published',
			'param'  => 'valid',
		];

		$result = $obj;
		unset( $result->obj2 );
		unset( $result->obj3 );

		$this->assertEquals( $result, pods_list_filter( $obj, $args ) );

		// NOT operator.

		$args = [
			'status' => 'published',
		];

		$result = $obj;
		unset( $result->obj1 );
		unset( $result->obj2 );
		unset( $result->obj4 );

		$this->assertEquals( $result, pods_list_filter( $obj, $args, 'NOT' ) );
	}

	public function test_pods_clean_linebreaks() {
		$input = "
			My text here.
\t
			My second text here.
\t

			My extended text here.

\t

		";

		$expected = "
			My text here.

			My second text here.

			My extended text here.

		";

		$this->assertEquals( $expected, pods_clean_linebreaks( $input ) );
	}

	public function pods_replace_gt_et_placeholders() {
		$cases = [
			'This is a test string with &gt;, &ge;, &lt;, and &le; placeholders.',
			'This is a test string with __GREATER_THAN__, __GREATER_THAN_OR_EQUAL__, __LESS_THAN__, and __LESS_THAN_OR_EQUAL__ placeholders.',
		];

		$expected = 'This is a test string with >, >=, <, and <= placeholders.';

		foreach ( $cases as $case ) {
			$this->assertEquals( $expected, pods_replace_gt_et_placeholders( $case ) );
		}
	}

	public function pods_replace_gt_et_placeholders_not_equal() {
		$this->assertEquals(
			'This is a test string with != placeholder.',
			pods_replace_gt_et_placeholders( 'This is a test string with &ne; placeholder.' )
		);
	}

}
