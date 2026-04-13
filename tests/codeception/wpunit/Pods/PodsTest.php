<?php

namespace Pods_Unit_Tests\Pods;

use Pods;
use Pods_Unit_Tests\Exceptions\Deprecated;
use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * @group  pods
 * @covers Pods
 */
class PodsTest extends Pods_UnitTestCase {

	/**
	 * @var string
	 */
	protected $pod_name = 'test_pods';

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
	protected $non_public_pod_name = 'test_pods_non_public';

	/**
	 * @var int
	 */
	protected $non_public_pod_id = 0;

	/**
	 * @var Pods
	 */
	protected $non_public_pod;

	/**
	 *
	 */
	public function setUp(): void {
		parent::setUp();

		$api = pods_api();

		$this->pod_id = $api->save_pod( array(
			'type'   => 'pod',
			'name'   => $this->pod_name,
		) );

		$params = array(
			'pod_id' => $this->pod_id,
			'name'   => 'number1',
			'type'   => 'number',
		);

		$api->save_field( $params );

		$this->pod = pods( $this->pod_name );

		$this->non_public_pod_id = $api->save_pod( array(
			'type'    => 'post_type',
			'storage' => 'meta',
			'name'    => $this->non_public_pod_name,
			'public'  => 0,
		) );

		$params = array(
			'pod_id' => $this->non_public_pod_id,
			'name'   => 'number2',
			'type'   => 'number',
		);

		$api->save_field( $params );

		$this->non_public_pod = pods( $this->non_public_pod_name );

		set_error_handler( static function ( int $errno, string $errstr ) {
			throw new Deprecated( $errstr, $errno );
		}, E_DEPRECATED | E_USER_DEPRECATED );
	}

	/**
	 *
	 */
	public function tearDown(): void {
		$this->pod_id            = null;
		$this->pod               = null;
		$this->non_public_pod_id = null;
		$this->non_public_pod    = null;

		pods_update_setting( 'session_auto_start', null );
		pods_update_setting( 'show_access_restricted_messages', null );
		pods_update_setting( 'show_access_admin_notices', null );
		remove_all_filters( 'pods_session_id' );

		restore_error_handler();

		parent::tearDown();
	}

	/**
	 * Test the add method when passing empty parameters
	 * @covers Pods::add
	 */
	public function test_method_add_empty() {
		$this->assertTrue( method_exists( $this->pod, 'add' ), 'Method add does not exist' );

		$return = $this->pod->add( null, null );

		$this->assertIsInt( $return );
		$this->assertEquals( 0, $return );
	}

	/**
	 * @covers Pods::exists
	 */
	public function test_method_exists_exists() {
		$this->assertTrue( method_exists( $this->pod, 'exists' ), 'Method exists does not exist' );
	}

	/**
	 * Test pod does not exist
	 * @covers Pods::exists
	 */
	public function test_method_exists_false() {
		$this->assertFalse( $this->pod->exists() );
	}

	/**
	 * @covers Pods::exists
	 */
	public function test_method_exists() {
		$this->setReflectionPropertyValue( $this->pod->data, 'row', 'foo' );
		$this->assertTrue( $this->pod->exists() );
	}

	/**
	 * @covers Pods::valid
	 */
	public function test_method_exists_valid() {
		$this->assertTrue( method_exists( $this->pod, 'valid' ), 'Method valid does not exist' );
		$this->assertTrue( method_exists( $this->pod, 'is_valid' ), 'Method valid does not exist' );
	}

	public function test_method_valid() {
		$this->assertTrue( $this->pod->valid() );
		$this->assertTrue( $this->pod->is_valid() );
	}

	public function test_method_valid_invalid() {
		$this->assertFalse( pods()->valid() );
		$this->assertFalse( pods()->is_valid() );
	}

	public function test_method_valid_invalid_with_non_existent_pod() {
		$pod = pods( 'truly_not_a_pod', null, false );

		$this->assertInstanceOf( Pods::class, $pod );
		$this->assertFalse( $pod->valid() );
		$this->assertFalse( $pod->is_valid() );
	}

	public function test_method_valid_invalid_with_non_existent_pod_with_strict_mode() {
		$this->assertFalse( pods( 'truly_not_a_pod', null, true ) );
	}

	/**
	 * @covers Pods::is_iterator
	 */
	public function test_method_is_iterator() {
		$this->assertTrue( method_exists( $this->pod, 'is_iterator' ), 'Method is_iterator does not exist' );
		$this->setReflectionPropertyValue( $this->pod, 'iterator', true );
		$this->assertTrue( $this->pod->is_iterator() );
	}

	/**
	 * @covers Pods::stop_iterator
	 */
	public function test_method_stop_iterator() {
		$this->assertTrue( method_exists( $this->pod, 'stop_iterator' ), 'Method stop_iterator does not exist' );
		$this->setReflectionPropertyValue( $this->pod, 'iterator', true );
		$this->pod->stop_iterator();
		$this->assertFalse( $this->getReflectionPropertyValue( $this->pod, 'iterator' ) );
	}

	/**
	 * @covers Pods::rewind
	 */
	public function test_method_rewind_exists() {
		$this->assertTrue( method_exists( $this->pod, 'rewind' ), 'Method rewind does not exist' );
	}

	/**
	 * @covers  Pods::rewind
	 * @depends test_method_rewind_exists
	 */
	public function test_method_rewind() {
		$this->setReflectionPropertyValue( $this->pod, 'iterator', false );
		$this->assertNull( $this->pod->rewind(), 'Pods::rewind did not return null' );
		$this->assertEquals( 0, $this->getReflectionPropertyValue( $this->pod->data, 'row_number' ) );
	}

	/**
	 * @covers Pods::current
	 */
	public function test_method_current_exists() {
		$this->assertTrue( method_exists( $this->pod, 'current' ), 'Method current does not exist' );
	}

	/**
	 * Test current when iterator = false
	 * @covers  Pods::current
	 * @depends test_method_current_exists
	 */
	public function test_method_current_iterator_false() {
		$this->setReflectionPropertyValue( $this->pod, 'iterator', false );
		$this->assertFalse( $this->pod->current() );
	}

	/**
	 * Test current when iterator = true
	 * @covers  Pods::current
	 * @depends test_method_current_exists
	 */
	public function test_method_current_iterator_true() {
		$this->setReflectionPropertyValue( $this->pod, 'iterator', true );
		$this->assertFalse( $this->pod->current() );
	}

	/**
	 * @covers Pods::key
	 */
	public function test_method_key_exists() {
		$this->assertTrue( method_exists( $this->pod, 'key' ) );
	}

	/**
	 * Test key when iterator = false
	 * @covers  Pods::key
	 * @depends test_method_key_exists
	 */
	public function test_method_key_iterator_false() {
		$this->setReflectionPropertyValue( $this->pod, 'iterator', false );
		$this->setReflectionPropertyValue( $this->pod->data, 'row_number', 22 );
		$this->assertEquals( 22, $this->pod->key() );
	}

	/**
	 * Test current when iterator = true
	 * @covers  Pods::key
	 * @depends test_method_key_exists
	 */
	public function test_method_key() {
		$this->setReflectionPropertyValue( $this->pod, 'iterator', true );
		$this->setReflectionPropertyValue( $this->pod->data, 'row_number', 22 );
		$this->assertEquals( 22, $this->pod->key() );
	}

	/**
	 * @covers Pods::next
	 */
	public function test_method_next_exists() {
		$this->assertTrue( method_exists( $this->pod, 'next' ) );
	}

	/**
	 * Test next when iterator = false
	 * @covers  Pods::next
	 * @depends test_method_next_exists
	 */
	public function test_method_next_iterator_false() {
		$this->setReflectionPropertyValue( $this->pod, 'iterator', false );
		$this->setReflectionPropertyValue( $this->pod->data, 'row_number', 19 );
		$this->assertNull( $this->pod->next() );
		$this->assertEquals( 20, $this->getReflectionPropertyValue( $this->pod->data, 'row_number' ), 'The row number was not incremented correctly' );
	}

	/**
	 * Test next when iterator = true
	 * @covers  Pods::next
	 * @depends test_method_next_exists
	 */
	public function test_method_next() {
		$this->setReflectionPropertyValue( $this->pod, 'iterator', true );
		$this->setReflectionPropertyValue( $this->pod->data, 'row_number', 19 );
		$this->assertNull( $this->pod->next() );
		$this->assertEquals( 20, $this->getReflectionPropertyValue( $this->pod->data, 'row_number' ), 'The row number was not incremented correctly' );
	}

	/**
	 * @covers Pods::input
	 */
	public function test_method_exists_input() {
		$this->assertTrue( method_exists( $this->pod, 'input' ), 'Method input does not exist' );
	}

	/**
	 * Test input when field parameter is string and does not exist
	 * @covers  Pods::input
	 * @depends test_method_exists_input
	 */
	public function test_method_input_field_string_missing_field() {
		$this->expectOutputString( '' );
		$this->pod->input( 'foo' );
	}

	/**
	 * Test input when field parameter is empty array
	 * @covers  Pods::input
	 * @depends test_method_exists_input
	 */
	public function test_method_input_field_empty_array() {
		$this->expectOutputString( '' );
		$this->pod->input( array() );
	}

	/**
	 * @covers Pods::row
	 */
	public function test_method_exists_row() {
		$this->assertTrue( method_exists( $this->pod, 'row' ), 'Method row does not exist' );
	}

	/**
	 * @covers  Pods::row
	 * @depends test_method_exists_row
	 */
	public function test_method_row_false() {
		$this->assertFalse( $this->pod->row() );
	}

	/**
	 * @covers  Pods::row
	 * @depends test_method_exists_row
	 */
	public function test_method_row() {
		$this->setReflectionPropertyValue( $this->pod->data, 'row', array() );
		$this->assertIsArray( $this->pod->row() );
	}

	/**
	 * @covers Pods::data
	 */
	public function test_method_exists_data() {
		$this->assertTrue( method_exists( $this->pod, 'data' ), 'Method data does not exist' );
	}

	/**
	 * @covers  Pods::data
	 * @depends test_method_exists_data
	 */
	public function test_method_data_empty_rows() {
		$this->setReflectionPropertyValue( $this->pod->data, 'rows', array() );
		$this->assertFalse( $this->pod->data() );
	}

	/**
	 * @covers  Pods::data
	 * @depends test_method_exists_data
	 */
	public function test_method_data() {
		$this->setReflectionPropertyValue( $this->pod->data, 'rows', array( 'foo' => 'bar' ) );
		$this->assertEquals( array( 'foo' => 'bar' ), $this->pod->data() );
	}

	/**
	 * @covers Pods::__get
	 */
	public function test_method_exists_get() {
		$this->assertTrue( method_exists( $this->pod, '__get' ), 'Method __get does not exist' );
	}

	/**
	 * Test the get method when the property does not exist in the deprecated class
	 * @covers  Pods::__get
	 * @depends test_method_exists_get
	 */
	public function test_method_get_deprecated_property_does_not_exist() {
		$this->assertNull( $this->pod->datatype );
		$this->assertNull( $this->pod->datatype_id );
	}

	/**
	 * @covers Pods::__call
	 */
	public function test_method_exists_call() {
		$this->assertTrue( method_exists( $this->pod, '__call' ), 'Method __call does not exist' );
	}

	/**
	 * Test the __call method when the called method does not exist in the deprecated class
	 * @covers  Pods::__call
	 * @depends test_method_exists_call
	 */
	public function test_method_call_method_does_not_exist() {
		$this->expectException( Deprecated::class );

		codecept_debug( $this->pod->__call( 'foo', array() ) );
		$this->assertNull( $this->pod->__call( 'foo', array() ), 'oh dat not null' );
	}

	/**
	 * Test we can call a non-Pod post type.
	 */
	public function test_we_can_call_a_non_pod_post_type() {
		register_post_type( 'my_test_cpt' );

		$pod = pods( 'my_test_cpt' );

		$this->assertInstanceOf( Pods::class, $pod );
		$this->assertTrue( $pod->valid() );
		$this->assertEquals( 'post_type', $pod->pod_data['type'] );
	}

	/**
	 * Test we can call a non-Pod post type.
	 */
	public function test_we_can_call_a_non_pod_taxonomy() {
		register_taxonomy( 'my_test_tax', 'post' );

		$pod = pods( 'my_test_tax' );

		$this->assertInstanceOf( Pods::class, $pod );
		$this->assertTrue( $pod->valid() );
		$this->assertEquals( 'taxonomy', $pod->pod_data['type'] );
	}

	public function test_pods_form() {
		// test shortcode
		$output = $this->pod->form( [ 'check_access' => false ] );

		$this->assertStringContainsString( 'Anonymous form submissions are not enabled for this site', $output );
	}

	public function test_pods_form_with_anon_enabled() {
		pods_update_setting( 'session_auto_start', '1' );

		// test shortcode
		$output = $this->pod->form( [ 'check_access' => false ] );

		$this->assertStringContainsString( 'Anonymous form submissions are not compatible with sessions on this site', $output );
	}

	public function test_pods_form_with_anon_enabled_and_compatible() {
		pods_update_setting( 'session_auto_start', '1' );

		add_filter( 'pods_session_id', static function() { return 'testsession'; } );

		// test shortcode
		$output = $this->pod->form( [ 'check_access' => false ] );

		$this->assertStringContainsString( '<form', $output );
	}

	public function test_pods_form_logged_in() {
		wp_set_current_user( 1 );

		// test shortcode
		$output = $this->pod->form( [ 'check_access' => false ] );

		$this->assertStringContainsString( '<form', $output );
	}

	public function test_pods_form_with_non_public_cpt_with_error_hidden_by_setting() {
		$new_user_id = wp_insert_user( [
			'user_login' => 'testsubscriber',
			'user_email' => 'testsubscriber@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'subscriber',
		] );

		wp_set_current_user( $new_user_id );

		// test shortcode
		$output = $this->non_public_pod->form();

		$this->assertStringContainsString( '<!-- pods:access-notices/user/hidden-by-setting ', $output );
		$this->assertStringNotContainsString( '<form', $output );
	}

	public function test_pods_form_with_non_public_cpt_with_error_inherited() {
		$new_user_id = wp_insert_user( [
			'user_login' => 'testsubscriber',
			'user_email' => 'testsubscriber@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'subscriber',
		] );

		wp_set_current_user( $new_user_id );

		pods_update_setting( 'show_access_restricted_messages', '0' );

		// test shortcode
		$output = $this->non_public_pod->form();

		$this->assertStringContainsString( '<!-- pods:access-notices/user/hidden-by-setting ', $output );
		$this->assertStringNotContainsString( '<form', $output );
	}

	public function test_pods_form_with_non_public_cpt_with_error_shown() {
		$new_user_id = wp_insert_user( [
			'user_login' => 'testsubscriber',
			'user_email' => 'testsubscriber@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'subscriber',
		] );

		wp_set_current_user( $new_user_id );

		pods_update_setting( 'show_access_restricted_messages', '1' );
		pods_api()->save_pod( [ 'id' => $this->non_public_pod_id, 'show_access_restricted_messages' => '1' ] );

		$this->non_public_pod = pods( $this->non_public_pod_name );

		// test shortcode
		$output = $this->non_public_pod->form();

		$this->assertStringContainsString( '<!-- pods:access-notices/user/message ', $output );
		$this->assertStringNotContainsString( '<form', $output );
	}

	public function test_pods_form_with_non_public_cpt_with_error_hidden_by_pod() {
		$new_user_id = wp_insert_user( [
			'user_login' => 'testsubscriber',
			'user_email' => 'testsubscriber@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'subscriber',
		] );

		wp_set_current_user( $new_user_id );

		pods_update_setting( 'show_access_restricted_messages', '1' );
		pods_api()->save_pod( [ 'id' => $this->non_public_pod_id, 'show_access_restricted_messages' => '0' ] );

		$this->non_public_pod = pods( $this->non_public_pod_name );

		// test shortcode
		$output = $this->non_public_pod->form();

		$this->assertStringContainsString( '<!-- pods:access-notices/user/hidden-by-pod ', $output );
		$this->assertStringNotContainsString( '<form', $output );
	}

	public function test_pods_form_with_non_public_cpt_and_user_without_access_with_error_hidden() {
		$new_user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		wp_set_current_user( $new_user_id );

		// test shortcode
		$output = $this->non_public_pod->form();

		$this->assertStringContainsString( '<!-- pods:access-notices/user/hidden-by-setting ', $output );
		$this->assertStringNotContainsString( '<form', $output );
	}

	public function test_pods_form_with_non_public_cpt_and_user_without_access_with_error_shown() {
		$new_user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		wp_set_current_user( $new_user_id );

		pods_update_setting( 'show_access_restricted_messages', '1' );
		pods_api()->save_pod( [ 'id' => $this->non_public_pod_id, 'show_access_restricted_messages' => '1' ] );

		$this->non_public_pod = pods( $this->non_public_pod_name );
		// test shortcode
		$output = $this->non_public_pod->form();

		$this->assertStringContainsString( '<!-- pods:access-notices/user/message ', $output );
		$this->assertStringNotContainsString( '<form', $output );
	}

	public function test_pods_form_with_non_public_cpt_and_user_without_access_and_access_error_hidden() {
		$new_user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		wp_set_current_user( $new_user_id );

		// test shortcode
		$output = $this->non_public_pod->form();

		$this->assertStringContainsString( '<!-- pods:access-notices/user/hidden-by-setting ', $output );
		$this->assertStringNotContainsString( '<form', $output );
	}

	public function test_pods_form_with_non_public_cpt_and_admin_user_with_access_and_notice_hidden_by_setting() {
		wp_set_current_user( 1 );

		pods_update_setting( 'show_access_admin_notices', '0' );

		// test shortcode
		$output = $this->non_public_pod->form();

		$this->assertStringContainsString( '<!-- pods:access-notices/admin/hidden-by-setting ', $output );
		$this->assertStringContainsString( '<form', $output );
	}

	public function test_pods_form_with_non_public_cpt_and_admin_user_with_access_and_notice_hidden_by_pod() {
		wp_set_current_user( 1 );

		pods_update_setting( 'show_access_admin_notices', '1' );
		pods_api()->save_pod( [ 'id' => $this->non_public_pod_id, 'show_access_admin_notices' => '0' ] );

		$this->non_public_pod = pods( $this->non_public_pod_name );

		// test shortcode
		$output = $this->non_public_pod->form();

		$this->assertStringContainsString( '<!-- pods:access-notices/admin/hidden-by-pod ', $output );
		$this->assertStringContainsString( '<form', $output );
	}

	public function test_pods_form_with_non_public_cpt_and_admin_user_with_access_and_notice_shown() {
		wp_set_current_user( 1 );

		pods_update_setting( 'show_access_admin_notices', '1' );
		pods_api()->save_pod( [ 'id' => $this->non_public_pod_id, 'show_access_admin_notices' => '1' ] );

		$this->non_public_pod = pods( $this->non_public_pod_name );

		// test shortcode
		$output = $this->non_public_pod->form();

		$this->assertStringContainsString( '<!-- pods:access-notices/admin/message ', $output );
		$this->assertStringContainsString( '<form', $output );
	}
}
