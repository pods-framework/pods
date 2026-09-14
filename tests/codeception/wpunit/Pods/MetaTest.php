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
	 * A meta group that only contains layout fields (heading, html) has no submittable fields.
	 * The rendered nonce and the save-side check both hash that empty list, so the save must
	 * not die with "The form nonce is invalid".
	 *
	 * @covers PodsMeta::get_submittable_meta_field_names
	 * @covers PodsMeta::verify_meta_form_nonce_or_die
	 */
	public function test_layout_only_group_nonce_round_trip() {
		$api = pods_api();

		$group_id = $api->save_group( [
			'pod_id' => $this->pod_id,
			'name'   => 'add_to_google_calendar',
			'label'  => 'Add to Google Calendar',
		] );

		$api->save_field( [
			'pod_id'   => $this->pod_id,
			'group_id' => $group_id,
			'name'     => 'shortcode',
			'label'    => 'Shortcode',
			'type'     => 'html',
		] );

		// Ensure groups_get() reads the freshly saved group.
		pods_static_cache_clear();

		$meta = pods_meta();

		$groups = $meta->groups_get( 'post_type', $this->pod_name );

		$group = null;

		foreach ( $groups as $candidate ) {
			if ( isset( $candidate['name'] ) && 'add_to_google_calendar' === $candidate['name'] ) {
				$group = $candidate;
				break;
			}
		}

		$this->assertNotNull( $group, 'Expected to find the layout-only group.' );

		$submittable = $this->reflectionMethodInvokeArgs(
			$meta,
			'get_submittable_meta_field_names',
			[ [ $group ] ]
		);

		$this->assertSame( [], $submittable, 'Layout-only groups must have no submittable fields.' );

		$group_key         = $this->reflectionMethodInvokeArgs( $meta, 'get_meta_nonce_group_key', [ $group ] );
		$nonce_field_names = pods_access_form_field_names( 'meta', $group_key );

		$uri_hash = pods_access_form_uri_hash( '/wp-admin/post.php' );
		$nonce    = pods_access_create_form_nonce( $this->pod_name, 123, [], $uri_hash );

		$_POST[ $nonce_field_names['nonce'] ] = $nonce;
		$_POST[ $nonce_field_names['pod'] ]   = $this->pod_name;
		$_POST[ $nonce_field_names['id'] ]    = '123';
		$_POST[ $nonce_field_names['uri'] ]   = $uri_hash;
		$_POST[ $nonce_field_names['form'] ]  = pods_access_form_normalize_fields( [] );

		$this->assertTrue(
			$this->reflectionMethodInvokeArgs(
				$meta,
				'verify_meta_form_nonce_or_die',
				[ 'meta', $group_key ]
			),
			'The layout-only group nonce must verify without dying.'
		);
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
