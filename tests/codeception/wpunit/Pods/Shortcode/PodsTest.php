<?php

namespace Pods_Unit_Tests\Pods\Shortcode;

use Pods_Unit_Tests\Pods_UnitTestCase;
use Pods;

/**
 * Class PodsTest
 *
 * @group pods-shortcode
 * @group pods-shortcode-pods
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
		pods_update_setting( 'dynamic_features_allow_sql_clauses', null );
		remove_all_filters( 'pods_session_id' );

		parent::tearDown();
	}

	/**
	 *
	 */
	public function test_shortcode_pods() {
		pods_update_setting( 'dynamic_features_allow_sql_clauses', 'all' );

		$pod_name = $this->pod_name;

		// add an item
		$this->pod->add( array(
			'name'    => 'Tatooine',
			'number1' => 5,
		) );

		// test shortcode
		$this->assertEquals( '5', do_shortcode( '[pods name="' . $pod_name . '" where="t.number1=5" orderby="t.id"]{@number1}[/pods]' ) );

		// add another item
		$this->pod->add( array(
			'name'    => 'Alderaan',
			'number1' => 7,
		) );

		// test shortcode
		$this->assertEquals( '5', do_shortcode( '[pods name="' . $pod_name . '" where="t.number1=5" orderby="t.id"]{@number1}[/pods]' ) );

		// add third item
		$this->pod->add( array(
			'name'    => 'Hoth',
			'number1' => 5,
		) );

		// test shortcode
		$this->assertEquals( '55', do_shortcode( '[pods name="' . $pod_name . '" where="t.number1=5" orderby="t.id"]{@number1}[/pods]' ) );

		// Test the pagination parameter
		/** @see http://php.net/manual/en/filter.filters.validate.php FILTER_VALIDATE_BOOLEAN */
		$this->assertContains( '<a', do_shortcode( '[pods name="' . $pod_name . '" pagination="1" limit="2"]~[/pods]' ) );
		$this->assertContains( '<a', do_shortcode( '[pods name="' . $pod_name . '" pagination="true" limit="2"]~[/pods]' ) );
		$this->assertContains( '<a', do_shortcode( '[pods name="' . $pod_name . '" pagination="on" limit="2"]~[/pods]' ) );
		$this->assertContains( '<a', do_shortcode( '[pods name="' . $pod_name . '" pagination="yes" limit="2"]~[/pods]' ) );
		$this->assertContains( '<a', do_shortcode( '[pods name="' . $pod_name . '" pagination="1" limit="2"]~[/pods]' ) );
		$this->assertContains( '<a', do_shortcode( '[pods name="' . $pod_name . '" pagination="true" limit="2"]~[/pods]' ) );

		$this->assertEquals( '~~', do_shortcode( '[pods name="' . $pod_name . '" pagination="0" limit="2"]~[/pods]' ) );
		$this->assertEquals( '~~', do_shortcode( '[pods name="' . $pod_name . '" pagination="false" limit="2"]~[/pods]' ) );
		$this->assertEquals( '~~', do_shortcode( '[pods name="' . $pod_name . '" pagination="off" limit="2"]~[/pods]' ) );
		$this->assertEquals( '~~', do_shortcode( '[pods name="' . $pod_name . '" pagination="no" limit="2"]~[/pods]' ) );
		$this->assertEquals( '~~', do_shortcode( '[pods name="' . $pod_name . '" pagination="0" limit="2"]~[/pods]' ) );
		$this->assertEquals( '~~', do_shortcode( '[pods name="' . $pod_name . '" pagination="false" limit="2"]~[/pods]' ) );
		$this->assertEquals( '~~', do_shortcode( '[pods name="' . $pod_name . '" pagination="-1" limit="2"]~[/pods]' ) );
		$this->assertEquals( '~~', do_shortcode( '[pods name="' . $pod_name . '" pagination="xyzzy" limit="2"]~[/pods]' ) );

		// Not enough records to trigger pagination even if on
		$this->assertNotContains( '<a', do_shortcode( '[pods name="' . $pod_name . '" pagination="1" limit="100"]~[/pods]' ) );

		/** @link https://github.com/pods-framework/pods/pull/2807 */
		$this->assertEquals( '57', do_shortcode( '[pods name="' . $pod_name . '" page="1" limit="2" orderby="t.id"]{@number1}[/pods]' ) );
		$this->assertEquals( '5', do_shortcode( '[pods name="' . $pod_name . '" page="2" limit="2" orderby="t.id"]{@number1}[/pods]' ) );
	}

	/**
	 * PR 2339
	 *
	 * @link  https://github.com/pods-framework/pods/pull/2339
	 * @since 2.8.0
	 */
	public function test_shortcode_pods_field_in_shortcode() {
		pods_update_setting( 'dynamic_features_allow_sql_clauses', 'all' );

		$pod_name = $this->pod_name;

		// add an item
		$this->pod->add( array(
			'name'    => 'Dagobah',
			'number1' => 5,
		) );

		// test shortcode
		$this->assertEquals( '5', do_shortcode( '[pods name="' . $pod_name . '" where="t.number1=5" field="number1"]' ) );
	}

	public function test_shortcode_pods_with_non_public_cpt() {
		pods_update_setting( 'dynamic_features_allow_sql_clauses', 'all' );

		$pod_name = $this->non_public_pod_name;

		// add an item
		$this->non_public_pod->add( [
			'name'        => 'Dagobah',
			'number2'     => 555,
			'post_status' => 'publish',
		] );

		// test shortcode
		$output = do_shortcode( '[pods name="' . $pod_name . '" where="number2.meta_value=555"]{@number2}[/pods]' );

		$this->assertContains( '<!-- pods:access-notices/user/hidden-by-setting ', $output );
		$this->assertNotContains( '555', $output );
	}

	public function test_shortcode_pods_with_non_public_cpt_using_field_returns_empty() {
		pods_update_setting( 'dynamic_features_allow_sql_clauses', 'all' );

		$pod_name = $this->non_public_pod_name;

		// add an item
		$this->non_public_pod->add( [
			'name'        => 'Dagobah',
			'number2'     => 555,
			'post_status' => 'publish',
		] );

		// test shortcode
		$output = do_shortcode( '[pods name="' . $pod_name . '" where="number2.meta_value=555" field="number2"]' );

		$this->assertEquals( '', $output );
	}

	public function test_shortcode_pods_with_non_public_cpt_and_user_without_access() {
		pods_update_setting( 'dynamic_features_allow_sql_clauses', 'all' );

		$new_user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		wp_set_current_user( $new_user_id );

		$pod_name = $this->non_public_pod_name;

		// add an item
		$this->non_public_pod->add( [
			'name'        => 'Dagobah',
			'number2'     => 555,
			'post_status' => 'publish',
		] );

		// test shortcode
		$output = do_shortcode( '[pods name="' . $pod_name . '" where="number2.meta_value=555"]{@number2}[/pods]' );

		$this->assertContains( '<!-- pods:access-notices/user/hidden-by-setting ', $output );
		$this->assertNotContains( '555', $output );
	}

	public function test_shortcode_pods_with_non_public_cpt_and_user_without_access_and_access_error_disabled() {
		pods_update_setting( 'dynamic_features_allow_sql_clauses', 'all' );

		$new_user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		wp_set_current_user( $new_user_id );

		$pod_name = $this->non_public_pod_name;

		// add an item
		$this->non_public_pod->add( [
			'name'        => 'Dagobah',
			'number2'     => 555,
			'post_status' => 'publish',
		] );

		pods_update_setting( 'show_access_restricted_messages', '0' );

		// test shortcode
		$output = do_shortcode( '[pods name="' . $pod_name . '" where="number2.meta_value=555"]{@number2}[/pods]' );

		$this->assertContains( '<!-- pods:access-notices/user/hidden-by-setting ', $output );
		$this->assertNotContains( '555', $output );
	}

	public function test_shortcode_pods_with_non_public_cpt_and_admin_user_with_access_and_notice_hidden_by_setting() {
		pods_update_setting( 'dynamic_features_allow_sql_clauses', 'all' );

		pods_update_setting( 'show_access_admin_notices', '0' );

		wp_set_current_user( 1 );

		$pod_name = $this->non_public_pod_name;

		// add an item
		$this->non_public_pod->add( [
			'name'        => 'Dagobah',
			'number2'     => 555,
			'post_status' => 'publish',
		] );

		// test shortcode
		$output = do_shortcode( '[pods name="' . $pod_name . '" where="number2.meta_value=555"]{@number2}[/pods]' );

		$this->assertContains( '<!-- pods:access-notices/admin/hidden-by-setting ', $output );
		$this->assertContains( '555', $output );
	}

	public function test_shortcode_pods_with_non_public_cpt_and_admin_user_with_access_and_notice_hidden_by_pod() {
		pods_update_setting( 'dynamic_features_allow_sql_clauses', 'all' );

		pods_update_setting( 'show_access_admin_notices', '1' );
		pods_api()->save_pod( [ 'id' => $this->non_public_pod_id, 'show_access_admin_notices' => '0' ] );

		wp_set_current_user( 1 );

		$pod_name = $this->non_public_pod_name;

		// add an item
		$this->non_public_pod->add( [
			'name'        => 'Dagobah',
			'number2'     => 555,
			'post_status' => 'publish',
		] );

		// test shortcode
		$output = do_shortcode( '[pods name="' . $pod_name . '" where="number2.meta_value=555"]{@number2}[/pods]' );

		$this->assertContains( '<!-- pods:access-notices/admin/hidden-by-pod ', $output );
		$this->assertContains( '555', $output );
	}

	public function test_shortcode_pods_with_non_public_cpt_and_admin_user_with_access_and_notice_shown() {
		pods_update_setting( 'dynamic_features_allow_sql_clauses', 'all' );

		pods_update_setting( 'show_access_admin_notices', '1' );
		pods_api()->save_pod( [ 'id' => $this->non_public_pod_id, 'show_access_admin_notices' => '1' ] );

		wp_set_current_user( 1 );

		$pod_name = $this->non_public_pod_name;

		// add an item
		$this->non_public_pod->add( [
			'name'        => 'Dagobah',
			'number2'     => 555,
			'post_status' => 'publish',
		] );

		// test shortcode
		$output = do_shortcode( '[pods name="' . $pod_name . '" where="number2.meta_value=555"]{@number2}[/pods]' );

		$this->assertContains( '<!-- pods:access-notices/admin/message ', $output );
		$this->assertContains( '555', $output );
	}

	public function test_shortcode_pods_form() {
		$pod_name = $this->pod_name;

		// test shortcode
		$output = do_shortcode( '[pods name="' . $pod_name . '" form="1"]' );

		$this->assertContains( 'Anonymous form submissions are not enabled for this site', $output );
	}

	public function test_shortcode_pods_form_with_anon_enabled() {
		pods_update_setting( 'session_auto_start', '1' );

		$pod_name = $this->pod_name;

		// test shortcode
		$output = do_shortcode( '[pods name="' . $pod_name . '" form="1"]' );

		$this->assertContains( 'Anonymous form submissions are not compatible with sessions on this site', $output );
	}

	public function test_shortcode_pods_form_with_anon_enabled_and_compatible() {
		pods_update_setting( 'session_auto_start', '1' );

		add_filter( 'pods_session_id', static function() { return 'testsession'; } );

		$pod_name = $this->pod_name;

		// test shortcode
		$output = do_shortcode( '[pods name="' . $pod_name . '" form="1"]' );

		$this->assertContains( '<form', $output );
	}

	public function test_shortcode_pods_form_logged_in() {
		wp_set_current_user( 1 );

		$pod_name = $this->pod_name;

		// test shortcode
		$output = do_shortcode( '[pods name="' . $pod_name . '" form="1"]' );

		$this->assertContains( '<form', $output );
	}

	public function test_shortcode_pods_form_with_non_public_cpt_with_error_hidden_by_setting() {
		$pod_name = $this->non_public_pod_name;

		// test shortcode
		$output = do_shortcode( '[pods name="' . $pod_name . '" form="1"]' );

		$this->assertContains( '<!-- pods:access-notices/user/hidden-by-setting ', $output );
		$this->assertNotContains( '<form', $output );
	}

	public function test_shortcode_pods_form_with_non_public_cpt_with_error_shown_by_global() {
		pods_update_setting( 'show_access_restricted_messages', '1' );

		$pod_name = $this->non_public_pod_name;

		// test shortcode
		$output = do_shortcode( '[pods name="' . $pod_name . '" form="1"]' );

		$this->assertContains( '<!-- pods:access-notices/user/message ', $output );
		$this->assertNotContains( '<form', $output );
	}

	public function test_shortcode_pods_form_with_non_public_cpt_with_error_hidden_by_pod() {
		pods_update_setting( 'show_access_restricted_messages', '1' );
		pods_api()->save_pod( [ 'id' => $this->non_public_pod_id, 'show_access_restricted_messages' => '0' ] );

		$pod_name = $this->non_public_pod_name;

		// test shortcode
		$output = do_shortcode( '[pods name="' . $pod_name . '" form="1"]' );

		$this->assertContains( '<!-- pods:access-notices/user/hidden-by-pod ', $output );
		$this->assertNotContains( '<form', $output );
	}

	public function test_shortcode_pods_form_with_non_public_cpt_with_error_shown_by_pod() {
		pods_update_setting( 'show_access_restricted_messages', '1' );
		pods_api()->save_pod( [ 'id' => $this->non_public_pod_id, 'show_access_restricted_messages' => '1' ] );

		$pod_name = $this->non_public_pod_name;

		// test shortcode
		$output = do_shortcode( '[pods name="' . $pod_name . '" form="1"]' );

		$this->assertContains( '<!-- pods:access-notices/user/message ', $output );
		$this->assertNotContains( '<form', $output );
	}

	public function test_shortcode_pods_form_with_non_public_cpt_and_user_without_access_with_error_hidden() {
		$new_user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		wp_set_current_user( $new_user_id );

		$pod_name = $this->non_public_pod_name;

		// test shortcode
		$output = do_shortcode( '[pods name="' . $pod_name . '" form="1"]' );

		$this->assertContains( '<!-- pods:access-notices/user/hidden-by-setting ', $output );
		$this->assertNotContains( '<form', $output );
	}

	public function test_shortcode_pods_form_with_non_public_cpt_and_user_without_access_with_error_shown() {
		pods_update_setting( 'show_access_restricted_messages', '1' );
		pods_api()->save_pod( [ 'id' => $this->non_public_pod_id, 'show_access_restricted_messages' => '1' ] );

		$new_user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		wp_set_current_user( $new_user_id );

		$pod_name = $this->non_public_pod_name;

		// test shortcode
		$output = do_shortcode( '[pods name="' . $pod_name . '" form="1"]' );

		$this->assertContains( '<!-- pods:access-notices/user/message ', $output );
		$this->assertNotContains( '<form', $output );
	}

	public function test_shortcode_pods_form_with_non_public_cpt_and_user_without_access_and_access_error_hidden() {
		$new_user_id = wp_insert_user( [
			'user_login' => 'testcontributor',
			'user_email' => 'testcontributor@test.local',
			'user_pass'  => 'hayyyyyy',
			'role'       => 'contributor',
		] );

		wp_set_current_user( $new_user_id );

		$pod_name = $this->non_public_pod_name;

		// test shortcode
		$output = do_shortcode( '[pods name="' . $pod_name . '" form="1"]' );

		$this->assertContains( '<!-- pods:access-notices/user/hidden-by-setting ', $output );
		$this->assertNotContains( '<form', $output );
	}

	public function test_shortcode_pods_form_with_non_public_cpt_and_admin_user_with_access_and_notice_hidden_by_setting() {
		pods_update_setting( 'show_access_admin_notices', '0' );

		wp_set_current_user( 1 );

		$pod_name = $this->non_public_pod_name;

		// test shortcode
		$output = do_shortcode( '[pods name="' . $pod_name . '" form="1"]' );

		$this->assertContains( '<!-- pods:access-notices/admin/hidden-by-setting ', $output );
		$this->assertContains( '<form', $output );
	}

	public function test_shortcode_pods_form_with_non_public_cpt_and_admin_user_with_access_and_notice_hidden_by_pod() {
		pods_update_setting( 'show_access_admin_notices', '1' );
		pods_api()->save_pod( [ 'id' => $this->non_public_pod_id, 'show_access_admin_notices' => '0' ] );

		wp_set_current_user( 1 );

		$pod_name = $this->non_public_pod_name;

		// test shortcode
		$output = do_shortcode( '[pods name="' . $pod_name . '" form="1"]' );

		$this->assertContains( '<!-- pods:access-notices/admin/hidden-by-pod ', $output );
		$this->assertContains( '<form', $output );
	}

	public function test_shortcode_pods_form_with_non_public_cpt_and_admin_user_with_access_and_notice_shown() {
		pods_update_setting( 'show_access_admin_notices', '1' );
		pods_api()->save_pod( [ 'id' => $this->non_public_pod_id, 'show_access_admin_notices' => '1' ] );

		wp_set_current_user( 1 );

		$pod_name = $this->non_public_pod_name;

		// test shortcode
		$output = do_shortcode( '[pods name="' . $pod_name . '" form="1"]' );

		$this->assertContains( '<!-- pods:access-notices/admin/message ', $output );
		$this->assertContains( '<form', $output );
	}

}
