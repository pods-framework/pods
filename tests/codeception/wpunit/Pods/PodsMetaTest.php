<?php

namespace Pods_Unit_Tests;

use PodsMeta;

/**
 * @group  pods-meta
 * @covers PodsMeta
 */
class PodsMetaTest extends Pods_UnitTestCase {

	/**
	 * @var PodsMeta
	 */
	public static $meta;

	/**
	 * @var array
	 */
	public static $hooked = array();

	/**
	 * Set up PodsMeta for our use
	 */
	public function setUp() {
		self::$meta = pods_meta()->core();
	}

	/**
	 * @covers PodsMeta::save_post_detect_new
	 */
	public function test_save_post_detect_new() {
		pods_no_conflict_on( 'post' );

		$post_id = wp_insert_post( array(
			'post_title'  => 'Testing',
			'post_type'   => 'post',
			'post_status' => 'draft',
		) );

		pods_no_conflict_off( 'post' );

		$this->assertArrayNotHasKey( 'post', PodsMeta::$old_post_status );

		wp_update_post( array(
			'ID'          => $post_id,
			'post_status' => 'publish',
		) );

		$this->assertArrayHasKey( 'post', PodsMeta::$old_post_status );
		$this->assertEquals( 'draft', PodsMeta::$old_post_status['post'] );
	}

	/**
	 * @covers PodsMeta::save_post
	 */
	public function test_save_post_create() {
		$this->_add_save_actions();

		$_POST['pods_meta'] = wp_create_nonce( 'pods_meta_post' );

		pods_no_conflict_on( 'post' );

		wp_insert_post( array(
			'post_title'  => 'Testing 1',
			'post_type'   => 'post',
			'post_status' => 'draft',
		) );

		pods_no_conflict_off( 'post' );

		$this->assertArrayNotHasKey( 'pods_api_post_save_pod_item', self::$hooked );
		$this->assertArrayNotHasKey( 'pods_api_post_create_pod_item', self::$hooked );
		$this->assertArrayNotHasKey( 'pods_api_post_edit_pod_item', self::$hooked );

		$this->_reset_hooks();

		wp_insert_post( array(
			'post_title'  => 'Testing 2',
			'post_type'   => 'post',
			'post_status' => 'draft',
		) );

		$this->assertArrayHasKey( 'pods_api_post_save_pod_item', self::$hooked );
		$this->assertArrayHasKey( 'pods_api_post_create_pod_item', self::$hooked );
		$this->assertArrayNotHasKey( 'pods_api_post_edit_pod_item', self::$hooked );

		$this->_reset_hooks();
		$this->_remove_save_actions();
	}

	/**
	 * @covers PodsMeta::save_post
	 */
	public function test_save_post_edit() {
		$this->_add_save_actions();

		$_POST['pods_meta'] = wp_create_nonce( 'pods_meta_post' );

		pods_no_conflict_on( 'post' );

		$post_id = wp_insert_post( array(
			'post_title'  => 'Testing 1',
			'post_type'   => 'post',
			'post_status' => 'draft',
		) );

		pods_no_conflict_off( 'post' );

		$this->assertArrayNotHasKey( 'pods_api_post_save_pod_item', self::$hooked );
		$this->assertArrayNotHasKey( 'pods_api_post_create_pod_item', self::$hooked );
		$this->assertArrayNotHasKey( 'pods_api_post_edit_pod_item', self::$hooked );

		$this->_reset_hooks();

		wp_update_post( array(
			'ID'          => $post_id,
			'post_status' => 'publish',
		) );

		$this->assertArrayHasKey( 'pods_api_post_save_pod_item', self::$hooked );
		$this->assertArrayNotHasKey( 'pods_api_post_create_pod_item', self::$hooked );
		$this->assertArrayHasKey( 'pods_api_post_edit_pod_item', self::$hooked );

		$this->_reset_hooks();
		$this->_remove_save_actions();
	}

	/**
	 * @covers PodsMeta::save_user
	 */
	public function test_save_user_create() {
		$this->_add_save_actions();

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

		$this->_reset_hooks();
		$this->_remove_save_actions();
	}

	/**
	 * @covers PodsMeta::save_user
	 */
	public function test_save_user_edit() {
		$this->_add_save_actions();

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

		$this->_reset_hooks();
		$this->_remove_save_actions();
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
