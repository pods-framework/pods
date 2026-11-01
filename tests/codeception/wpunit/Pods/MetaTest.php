<?php

namespace Pods_Unit_Tests\Pods;

use Pods;
use Pods_Unit_Tests\Pods_UnitTestCase;
use PodsMeta;

/**
 * @group  pods-meta
 * @group  pods-config-required
 * @covers PodsMeta
 */
class MetaTest extends Pods_UnitTestCase {

	/**
	 * @var string
	 */
	protected $pod_name = 'test_meta';

	/**
	 * @var int
	 */
	protected $pod_id = 0;

	/**
	 * @var Pods
	 */
	protected $pod;

	/**
	 * @var string
	 */
	protected $pod_name2 = 'user';

	/**
	 * @var int
	 */
	protected $pod_id2 = 0;

	/**
	 * @var Pods
	 */
	protected $pod2;

	/**
	 * @var array
	 */
	public static $hooked = array();

	/**
	 *
	 */
	public function setUp(): void {
		parent::setUp();

		$api = pods_api();

		$this->pod_id = $api->save_pod( array(
			'type' => 'post_type',
			'name' => $this->pod_name,
		) );

		$params = array(
			'pod_id' => $this->pod_id,
			'name'   => 'number1',
			'type'   => 'number',
		);

		$api->save_field( $params );

		$params = array(
			'pod_id' => $this->pod_id,
			'name'   => 'number2',
			'type'   => 'number',
		);

		$api->save_field( $params );

		$params = array(
			'pod_id'           => $this->pod_id,
			'name'             => 'related_field',
			'type'             => 'pick',
			'pick_object'      => 'post_type',
			'pick_val'         => $this->pod_name,
			'pick_format_type' => 'multi',
		);

		$api->save_field( $params );

		try {
			$this->pod_id2 = $api->save_pod( [
				'type'          => 'user',
				'name'          => $this->pod_name2,
				'create_extend' => 'extend',
			] );
		} catch ( \Exception $exception ) {
			// Do nothing.
		}

		$this->pod  = pods( $this->pod_name );
		$this->pod2 = pods( $this->pod_name2 );

		wp_set_current_user( 1 );

		$this->_add_save_actions();

		// Reset all the hooks.
		pods_meta()->core();
	}

	/**
	 *
	 */
	public function tearDown(): void {
		$this->_reset_hooks();
		$this->_remove_save_actions();

		$this->pod_id  = null;
		$this->pod_id2 = null;

		$this->pod  = null;
		$this->pod2 = null;

		$GLOBALS['current_user'] = null;

		pods_no_conflict_off( 'all' );
		parent::tearDown();
	}

	/**
	 * @covers PodsMeta::save_post_detect_new
	 */
	public function test_save_post_detect_new() {
		pods_no_conflict_on( 'post' );

		$post_id = wp_insert_post( array(
			'post_title'  => 'Testing',
			'post_type'   => $this->pod_name,
			'post_status' => 'draft',
		) );

		pods_no_conflict_off( 'post' );

		$this->assertArrayNotHasKey( $this->pod_name, PodsMeta::$old_post_status );

		wp_update_post( array(
			'ID'          => $post_id,
			'post_status' => 'publish',
		) );

		$this->assertArrayHasKey( $this->pod_name, PodsMeta::$old_post_status );
		$this->assertEquals( 'draft', PodsMeta::$old_post_status[ $this->pod_name ] );
	}

	/**
	 * @covers PodsMeta::save_post
	 */
	public function test_save_post_create() {
		$_POST['pods_meta'] = wp_create_nonce( 'pods_meta_post' );

		pods_no_conflict_on( 'post' );

		wp_insert_post( array(
			'post_title'  => 'Testing 1',
			'post_type'   => $this->pod_name,
			'post_status' => 'draft',
		) );

		pods_no_conflict_off( 'post' );

		$this->assertArrayNotHasKey( 'pods_api_post_save_pod_item', self::$hooked );
		$this->assertArrayNotHasKey( 'pods_api_post_create_pod_item', self::$hooked );
		$this->assertArrayNotHasKey( 'pods_api_post_edit_pod_item', self::$hooked );

		$this->_reset_hooks();

		$_POST['number1'] = 123;
		$_POST['number2'] = 456;

		wp_insert_post( array(
			'post_title'  => 'Testing 2',
			'post_type'   => $this->pod_name,
			'post_status' => 'draft',
		) );

		$this->assertArrayHasKey( 'pods_api_post_save_pod_item', self::$hooked );
		$this->assertArrayHasKey( 'pods_api_post_create_pod_item', self::$hooked );
		$this->assertArrayNotHasKey( 'pods_api_post_edit_pod_item', self::$hooked );
	}

	/**
	 * @covers PodsMeta::save_post
	 */
	public function test_save_post_edit() {
		$_POST['pods_meta'] = wp_create_nonce( 'pods_meta_post' );

		pods_no_conflict_on( 'post' );

		$post_id = wp_insert_post( array(
			'post_title'  => 'Testing 1',
			'post_type'   => $this->pod_name,
			'post_status' => 'draft',
		) );

		pods_no_conflict_off( 'post' );

		$this->assertArrayNotHasKey( 'pods_api_post_save_pod_item', self::$hooked );
		$this->assertArrayNotHasKey( 'pods_api_post_create_pod_item', self::$hooked );
		$this->assertArrayNotHasKey( 'pods_api_post_edit_pod_item', self::$hooked );

		$this->_reset_hooks();

		$_POST['number1'] = 123;
		$_POST['number2'] = 456;

		wp_update_post( array(
			'ID'          => $post_id,
			'post_status' => 'publish',
		) );

		$this->assertArrayHasKey( 'pods_api_post_save_pod_item', self::$hooked );
		$this->assertArrayNotHasKey( 'pods_api_post_create_pod_item', self::$hooked );
		$this->assertArrayHasKey( 'pods_api_post_edit_pod_item', self::$hooked );
	}

	/**
	 * @covers PodsMeta::save_user
	 */
	public function test_save_user_create() {
		$_POST['pods_meta'] = wp_create_nonce( 'pods_meta_user' );

		pods_no_conflict_on( 'user' );

		wp_insert_user( array(
			'user_login' => '1' . wp_generate_password( 10, false ),
			'user_email' => '1' . wp_generate_password( 10, false ) . '@example.com',
			'user_pass'  => wp_generate_password(),
		) );

		pods_no_conflict_off( 'user' );

		$this->assertArrayNotHasKey( 'pods_api_post_save_pod_item', self::$hooked );
		$this->assertArrayNotHasKey( 'pods_api_post_create_pod_item', self::$hooked );
		$this->assertArrayNotHasKey( 'pods_api_post_edit_pod_item', self::$hooked );

		$this->_reset_hooks();

		wp_insert_user( array(
			'user_login' => '2' . wp_generate_password( 10, false ),
			'user_email' => '2' . wp_generate_password( 10, false ) . '@example.com',
			'user_pass'  => wp_generate_password(),
		) );

		$this->assertArrayHasKey( 'pods_api_post_save_pod_item', self::$hooked );
		$this->assertArrayHasKey( 'pods_api_post_create_pod_item', self::$hooked );
		$this->assertArrayNotHasKey( 'pods_api_post_edit_pod_item', self::$hooked );
	}

	/**
	 * @covers PodsMeta::save_user
	 */
	public function test_save_user_edit() {
		$_POST['pods_meta'] = wp_create_nonce( 'pods_meta_user' );

		pods_no_conflict_on( 'user' );

		$user_id = wp_insert_user( array(
			'user_login' => '3' . wp_generate_password( 10, false ),
			'user_email' => '3' . wp_generate_password( 10, false ) . '@example.com',
			'user_pass'  => wp_generate_password(),
		) );

		pods_no_conflict_off( 'user' );

		$this->assertArrayNotHasKey( 'pods_api_post_save_pod_item', self::$hooked );
		$this->assertArrayNotHasKey( 'pods_api_post_create_pod_item', self::$hooked );
		$this->assertArrayNotHasKey( 'pods_api_post_edit_pod_item', self::$hooked );

		$this->_reset_hooks();

		wp_update_user( array(
			'ID'         => $user_id,
			'user_email' => '4' . wp_generate_password( 10, false ) . '@example.com',
		) );

		$this->assertArrayHasKey( 'pods_api_post_save_pod_item', self::$hooked );
		$this->assertArrayNotHasKey( 'pods_api_post_create_pod_item', self::$hooked );
		$this->assertArrayHasKey( 'pods_api_post_edit_pod_item', self::$hooked );
	}

	/**
	 * @covers PodsMeta::get_keys_not_covered
	 */
	public function test_get_keys_not_covered_caches_per_type() {
		PodsMeta::$keys_not_covered_cache       = [];
		PodsMeta::$keys_not_covered_regex_cache = [];
		remove_all_filters( 'pods_meta_keys_not_covered' );

		$meta = pods_meta();

		$uncached_post     = $meta->get_keys_not_covered( 'post' );
		$uncached_user     = $meta->get_keys_not_covered( 'user' );
		$uncached_settings = $meta->get_keys_not_covered( 'settings' );

		$this->assertNotEmpty( $uncached_post );
		$this->assertArrayHasKey( '_thumbnail_id', $uncached_post );
		$this->assertArrayHasKey( '_elementor_.*', $uncached_post );

		$this->assertNotEmpty( $uncached_user );
		$this->assertArrayHasKey( 'rich_editing', $uncached_user );
		$this->assertArrayHasKey( 'wp_rich_editing', $uncached_user );

		$this->assertNotEmpty( $uncached_settings );

		// The follow up calls must come back identical from the cache.
		$this->assertSame( $uncached_post, $meta->get_keys_not_covered( 'post' ) );
		$this->assertSame( $uncached_user, $meta->get_keys_not_covered( 'user' ) );
		$this->assertSame( $uncached_settings, $meta->get_keys_not_covered( 'settings' ) );

		$this->assertNotEmpty( PodsMeta::$keys_not_covered_cache );
		$this->assertNotEmpty( PodsMeta::$keys_not_covered_regex_cache );

		// Prove the follow up calls read from the cache.
		PodsMeta::$keys_not_covered_cache['settings'] = [ 'sentinel_key' => true ];

		$this->assertSame( [ 'sentinel_key' => true ], $meta->get_keys_not_covered( 'settings' ) );
	}

	/**
	 * @covers PodsMeta::get_keys_not_covered_regex
	 */
	public function test_get_keys_not_covered_regex_builds_wildcard_alternation() {
		PodsMeta::$keys_not_covered_cache       = [];
		PodsMeta::$keys_not_covered_regex_cache = [];
		remove_all_filters( 'pods_meta_keys_not_covered' );

		$meta = pods_meta();

		$regex = $meta->get_keys_not_covered_regex( 'post' );

		$this->assertNotEmpty( $regex );
		$this->assertStringContainsString( '_elementor_.*', $regex );
		$this->assertStringContainsString( '_wp_.*', $regex );
		$this->assertStringNotContainsString( '_thumbnail_id', $regex );

		// The follow up calls must come back identical from the cache.
		$this->assertSame( $regex, $meta->get_keys_not_covered_regex( 'post' ) );

		PodsMeta::$keys_not_covered_regex_cache = [];

		// A fresh build must match the cached pattern.
		$this->assertSame( $regex, $meta->get_keys_not_covered_regex( 'post' ) );

		// Prove the follow up calls read from the cache.
		PodsMeta::$keys_not_covered_regex_cache['post_type'] = 'sentinel_.*';

		$this->assertSame( 'sentinel_.*', $meta->get_keys_not_covered_regex( 'post' ) );
	}

	/**
	 * @covers PodsMeta::is_key_covered
	 */
	public function test_is_key_covered() {
		PodsMeta::$keys_not_covered_cache       = [];
		PodsMeta::$keys_not_covered_regex_cache = [];
		remove_all_filters( 'pods_meta_keys_not_covered' );

		$meta = pods_meta();

		// Literal keys not covered.
		$this->assertFalse( $meta->is_key_covered( 'post', '_edit_lock' ) );
		$this->assertFalse( $meta->is_key_covered( 'post', '_thumbnail_id' ) );

		// Wildcard keys not covered.
		$this->assertFalse( $meta->is_key_covered( 'post', '_elementor_data' ) );
		$this->assertFalse( $meta->is_key_covered( 'post', '_wp_page_template' ) );

		// Regular keys are covered.
		$this->assertTrue( $meta->is_key_covered( 'post', 'my_custom_key' ) );
		$this->assertTrue( $meta->is_key_covered( 'post', 'number1' ) );

		// User keys, both literal and prefix-prefixed.
		$this->assertFalse( $meta->is_key_covered( 'user', 'rich_editing' ) );
		$this->assertFalse( $meta->is_key_covered( 'user', 'wp_capabilities' ) );
		$this->assertTrue( $meta->is_key_covered( 'user', 'my_user_key' ) );
	}

	/**
	 * @covers PodsMeta::is_key_covered
	 */
	public function test_is_key_covered_with_filter() {
		PodsMeta::$keys_not_covered_cache       = [];
		PodsMeta::$keys_not_covered_regex_cache = [];
		remove_all_filters( 'pods_meta_keys_not_covered' );

		$meta = pods_meta();

		// Prime the cache before the filter is added.
		$this->assertTrue( $meta->is_key_covered( 'post', 'my_plugin_key' ) );
		$this->assertTrue( $meta->is_key_covered( 'post', 'my_plugin_wild_x' ) );

		$filter_calls = 0;

		add_filter( 'pods_meta_keys_not_covered', static function ( $keys_not_covered, $type ) use ( &$filter_calls ) {
			$filter_calls ++;

			$keys_not_covered[ $type ]['my_plugin_key']     = true;
			$keys_not_covered[ $type ]['my_plugin_wild_.*'] = true;

			return $keys_not_covered;
		}, 10, 2 );

		try {
			// While the filter is listening, the cache is bypassed for literal and wildcard keys.
			$this->assertFalse( $meta->is_key_covered( 'post', 'my_plugin_key' ) );
			$this->assertFalse( $meta->is_key_covered( 'post', 'my_plugin_wild_x' ) );
			$this->assertTrue( $meta->is_key_covered( 'post', 'number1' ) );

			// The filter runs once per check.
			$this->assertSame( 3, $filter_calls );
		} finally {
			remove_all_filters( 'pods_meta_keys_not_covered' );
		}

		// The filtered keys are covered again once the filter is removed.
		$this->assertTrue( $meta->is_key_covered( 'post', 'my_plugin_key' ) );
		$this->assertTrue( $meta->is_key_covered( 'post', 'my_plugin_wild_x' ) );
	}

	/**
	 * Track current hook info
	 */
	public function _track_hook() {
		self::$hooked[ \current_filter() ] = func_get_args();
	}

	/**
	 * Reset hook info
	 */
	public function _reset_hooks() {
		self::$hooked = array();
	}

	/**
	 * Add save hook actions
	 */
	public function _add_save_actions() {
		add_action( 'pods_api_post_save_pod_item', array( $this, '_track_hook' ), 10, 3 );
		add_action( 'pods_api_post_create_pod_item', array( $this, '_track_hook' ), 10, 3 );
		add_action( 'pods_api_post_edit_pod_item', array( $this, '_track_hook' ), 10, 3 );
	}

	/**
	 * Remove save hook actions
	 */
	public function _remove_save_actions() {
		remove_action( 'pods_api_post_save_pod_item', array( $this, '_track_hook' ) );
		remove_action( 'pods_api_post_create_pod_item', array( $this, '_track_hook' ) );
		remove_action( 'pods_api_post_edit_pod_item', array( $this, '_track_hook' ) );
	}

}
