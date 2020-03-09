<?php

namespace Pods_Unit_Tests;

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
		$object      = new \stdClass();
		$object->foo = 1;
		$object->bar = 'a test string';

		$this->assertEquals( $object, pods_sanitize( $object ) );
	}

	/**
	 * @covers ::pods_sanitize
	 */
	public function test_pods_sanitize_returns_array() {
		$array = array(
			'foo' => 1,
			'bar' => 'a test string',
		);
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
		$original  = ' test ';
		$original2 = array(
			' test ',
		);

		$this->assertEquals( 'test', pods_trim( $original ) );
		$this->assertEquals( array( 'test' ), pods_trim( $original2 ) );
	}

	public function _test_pods_v() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function _test_pods_v_sanitized() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function _test_pods_v_set() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function _test_pods_var() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function _test_pods_var_raw() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function _test_pods_var_set() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function _test_pods_query_arg() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function _test_pods_var_update() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function _test_pods_cast() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function _test_pods_create_slug() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function _test_pods_unique_slug() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function _test_pods_clean_name() {
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

	public function _test_pods_str_replace() {
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

	public function _test_pods_evaluate_tags() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function _test_pods_evaluate_tag_sanitized() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function _test_pods_evaluate_tag() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function _test_pods_serial_comma() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function _test_pods_var_user() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function _test_pods_hierarchical_list() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function _test_pods_hierarchical_list_recurse() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function _test_pods_hierarchical_select() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function _test_pods_hierarchical_select_recurse() {
		$this->markTestSkipped( 'not yet implemented' );
	}

	public function _test_pods_list_filter() {
		$this->markTestSkipped( 'not yet implemented' );
	}

}
