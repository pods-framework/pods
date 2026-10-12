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

		$_POST = [];

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
	public function test_save_post_clears_visible_fields_that_save_value_when_hidden() {
		list( $post_id ) = $this->create_conditional_post();

		$this->submit_meta_form( $post_id, [
			'show_extra'       => 1,
			'note_unless_flag' => 'kept',
		] );

		$this->assertSame( '0', $this->get_table_value( $post_id, 'flag_on' ) );
		$this->assertSame( [], get_post_meta( $post_id, 'related_on' ) );
	}

	/**
	 * @covers PodsMeta::save_post
	 */
	public function test_save_post_keeps_hidden_fields_that_save_value_when_hidden() {
		list( $post_id, $related_id ) = $this->create_conditional_post();

		$this->submit_meta_form( $post_id, [] );

		$this->assertSame( '0', $this->get_table_value( $post_id, 'show_extra' ) );
		$this->assertSame( '1', $this->get_table_value( $post_id, 'flag_on' ) );
		$this->assertSame( [ (string) $related_id ], get_post_meta( $post_id, 'related_on' ) );
		$this->assertSame( 'kept', $this->get_table_value( $post_id, 'note_unless_flag' ) );
	}

	/**
	 * @covers PodsMeta::save_post
	 */
	public function test_save_post_clears_field_without_conditional_logic_that_saves_value_when_hidden() {
		list( $post_id ) = $this->create_conditional_post();

		$this->submit_meta_form( $post_id, [
			'show_extra'       => 1,
			'note_unless_flag' => 'kept',
		] );

		$this->assertSame( '0', $this->get_table_value( $post_id, 'flag_no_logic' ) );
	}

	/**
	 * Create a post whose conditional fields all have values, with "Save Value When Hidden" on for each of them.
	 *
	 * note_unless_flag depends on flag_on, which depends on show_extra.
	 *
	 * @return int[] The post ID and the related post ID.
	 */
	private function create_conditional_post() {
		$conditional_logic = [
			'action' => 'show',
			'logic'  => 'any',
			'rules'  => [
				[
					'field'   => 'show_extra',
					'compare' => '=',
					'value'   => '1',
				],
			],
		];

		$fields = [
			[
				'name' => 'show_extra',
				'type' => 'boolean',
			],
			[
				'name'                         => 'flag_on',
				'type'                         => 'boolean',
				'enable_conditional_logic'     => 1,
				'conditional_logic'            => $conditional_logic,
				'conditional_logic_save_value' => 1,
			],
			[
				'name'                         => 'related_on',
				'type'                         => 'pick',
				'pick_object'                  => 'post_type',
				'pick_val'                     => $this->pod_name,
				'pick_format_type'             => 'multi',
				'enable_conditional_logic'     => 1,
				'conditional_logic'            => $conditional_logic,
				'conditional_logic_save_value' => 1,
			],
			[
				'name'                         => 'flag_no_logic',
				'type'                         => 'boolean',
				'enable_conditional_logic'     => 0,
				'conditional_logic_save_value' => 1,
			],
			[
				'name'                         => 'note_unless_flag',
				'type'                         => 'text',
				'enable_conditional_logic'     => 1,
				'conditional_logic'            => [
					'action' => 'show',
					'logic'  => 'any',
					'rules'  => [
						[
							'field'   => 'flag_on',
							'compare' => '!=',
							'value'   => '1',
						],
					],
				],
				'conditional_logic_save_value' => 1,
			],
		];

		$api = pods_api();

		foreach ( $fields as $field ) {
			$field['pod_id'] = $this->pod_id;

			$api->save_field( $field );
		}

		pods_no_conflict_on( 'post' );

		$post_id = wp_insert_post( [
			'post_title'  => 'Conditional',
			'post_type'   => $this->pod_name,
			'post_status' => 'publish',
		] );

		$related_id = wp_insert_post( [
			'post_title'  => 'Related',
			'post_type'   => $this->pod_name,
			'post_status' => 'publish',
		] );

		pods_no_conflict_off( 'post' );

		pods( $this->pod_name, $post_id )->save( [
			'show_extra'       => 1,
			'flag_on'          => 1,
			'related_on'       => [ $related_id ],
			'flag_no_logic'    => 1,
			'note_unless_flag' => 'kept',
		] );

		$this->assertSame( '1', $this->get_table_value( $post_id, 'flag_on' ) );
		$this->assertSame( [ (string) $related_id ], get_post_meta( $post_id, 'related_on' ) );
		$this->assertSame( '1', $this->get_table_value( $post_id, 'flag_no_logic' ) );
		$this->assertSame( 'kept', $this->get_table_value( $post_id, 'note_unless_flag' ) );

		return [ $post_id, $related_id ];
	}

	/**
	 * Get a field value as stored in the pod's table.
	 *
	 * @param int    $post_id    The post ID.
	 * @param string $field_name The field name.
	 *
	 * @return string|null The stored value.
	 */
	private function get_table_value( $post_id, $field_name ) {
		global $wpdb;

		return $wpdb->get_var( $wpdb->prepare( "SELECT `{$field_name}` FROM `{$wpdb->prefix}pods_{$this->pod_name}` WHERE `id` = %d", $post_id ) );
	}

	/**
	 * Update a post through the edit screen's meta form, submitting only the given field values.
	 *
	 * @param int   $post_id The post ID.
	 * @param array $values  The field values the form submits.
	 */
	private function submit_meta_form( $post_id, array $values ) {
		$_POST = [];

		$uri_hash = pods_access_form_uri_hash();
		$groups   = pods_meta()->groups_get( 'post_type', $this->pod_name, null, false, $post_id );

		foreach ( $groups as $group ) {
			$field_names = [];

			foreach ( $group['fields'] as $field ) {
				$field_names[] = $field['name'];
			}

			$nonce_field_names = pods_access_form_field_names( 'meta', $group['name'] );

			$_POST[ $nonce_field_names['nonce'] ] = pods_access_create_form_nonce( $this->pod_name, $post_id, $field_names, $uri_hash );
			$_POST[ $nonce_field_names['pod'] ]   = $this->pod_name;
			$_POST[ $nonce_field_names['id'] ]    = (string) $post_id;
			$_POST[ $nonce_field_names['uri'] ]   = $uri_hash;
			$_POST[ $nonce_field_names['form'] ]  = implode( ',', $field_names );
		}

		foreach ( $values as $field_name => $value ) {
			$_POST[ 'pods_meta_' . $field_name ] = $value;
		}

		wp_update_post( [
			'ID'         => $post_id,
			'post_title' => 'Conditional updated',
		] );
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
