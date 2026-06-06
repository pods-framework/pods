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
	 * @covers PodsMeta::save_post
	 */
	public function test_save_post_preserves_hidden_field_value() {
		$api = pods_api();

		// Add a hidden pick field to the existing test pod.
		$hidden_field_id = $api->save_field( array(
			'pod_id'           => $this->pod_id,
			'name'             => 'hidden_pick_field',
			'label'            => 'Hidden Pick',
			'type'             => 'pick',
			'pick_object'      => 'post_type',
			'pick_val'         => $this->pod_name,
			'pick_format_type' => 'multi',
			'hidden'           => '1',
		) );

		$this->assertNotEmpty( $hidden_field_id, 'Hidden field should be created.' );

		$_POST['pods_meta'] = wp_create_nonce( 'pods_meta_post' );

		pods_no_conflict_on( 'post' );

		$post_id_a = wp_insert_post( array(
			'post_title'  => 'Hidden Field Test A',
			'post_type'   => $this->pod_name,
			'post_status' => 'draft',
		) );

		$post_id_b = wp_insert_post( array(
			'post_title'  => 'Hidden Field Test B',
			'post_type'   => $this->pod_name,
			'post_status' => 'draft',
		) );

		pods_no_conflict_off( 'post' );

		// Seed the hidden field with a value via the API.
		$pod = pods( $this->pod_name, $post_id_a );
		$pod->save( 'hidden_pick_field', array( $post_id_b ) );

		$saved_value = get_post_meta( $post_id_a, 'hidden_pick_field', true );
		$this->assertContains( (int) $post_id_b, array_map( 'intval', (array) $saved_value ), 'Hidden field should have seeded value.' );

		// Now simulate a save where the hidden field is NOT in $_POST.
		// Only a visible field is submitted.
		$_POST['pods_meta_number1'] = 999;
		unset( $_POST['pods_meta_hidden_pick_field'] );

		wp_update_post( array(
			'ID'          => $post_id_a,
			'post_status' => 'publish',
		) );

		// The hidden field value must be preserved.
		$preserved_value = get_post_meta( $post_id_a, 'hidden_pick_field', true );
		$this->assertNotEmpty( $preserved_value, 'Hidden field value must not be cleared when not in POST.' );
		$this->assertContains( (int) $post_id_b, array_map( 'intval', (array) $preserved_value ), 'Hidden pick field should still contain seeded post id after save.' );

		// The visible field should still be saved normally.
		$number1 = get_post_meta( $post_id_a, 'number1', true );
		$this->assertEquals( '999', $number1, 'Visible field should be saved normally.' );
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
