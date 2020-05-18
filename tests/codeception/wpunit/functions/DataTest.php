<?php

namespace Pods_Unit_Tests;

use stdClass;

/**
 * @group pods
 * @group pods-functions
 * @group pods-functions-data
 */
class DataTest extends Pods_UnitTestCase {

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

	public function test_pods_v() {
		$this->markTestSkipped( 'not yet implemented' );
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
		$this->markTestSkipped( 'not yet implemented' );
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
		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );

		// Should be `ID` but added lowercase for backwards compatibility.
		$this->assertEquals( get_current_user_id(), pods_evaluate_tag( '{@user.id}' ) );
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

}
