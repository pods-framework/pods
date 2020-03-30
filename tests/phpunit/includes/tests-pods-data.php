<?php

namespace Pods_Unit_Tests;

use stdClass;

require_once PODS_TEST_PLUGIN_DIR . '/classes/PodsData.php';

/**
 * Class Test_PodsData
 *
 * @package Pods_Unit_Tests
 * @group   pods
 * @group   pods-data
 */
class Test_PodsData extends Pods_UnitTestCase {

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
	 * @covers ::pods_sanitize
	 */
	public function test_pods_sanitize_sql() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

	public function test_pods_sanitize_like() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

	public function test_pods_slash() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

	public function test_pods_unsanitize() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

	public function test_pods_unslash() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

	public function test_pods_trim() {

		$string = ' test ';

		$this->assertEquals( 'test', pods_trim( $string ) );

		$this->assertEquals( ' test', pods_trim( $string, null, 'r' ) );

		$this->assertEquals( 'test ', pods_trim( $string, null, 'l' ) );

		$string = ' test & ';

		$this->assertEquals( 'test', pods_trim( $string, ' &' ) );

		$this->assertEquals( ' test', pods_trim( $string, ' &', 'r' ) );

		// Arrays.

		$array = array(
			' test ',
			' test2 ',
		);

		$result = array(
			'test',
			'test2'
		);

		$this->assertEquals( $result, pods_trim( $array ) );

		$result = array(
			' test',
			' test2'
		);

		$this->assertEquals( $result, pods_trim( $array, null, 'r' ) );

		$array = array(
			' test  & ',
			' test2 ',
		);

		$result = array(
			' test',
			' test2'
		);

		$this->assertEquals( $result, pods_trim( $array, ' &', 'r' ) );

		// Objects.

		$object = new stdClass();
		$object->test = ' test ';
		$object->test2 = ' test2 ';

		$result = new stdClass();
		$result->test = 'test';
		$result->test2 = 'test2';

		$this->assertEquals( $result, pods_trim( $object ) );

		$result->test = ' test';
		$result->test2 = ' test2';

		$this->assertEquals( $result, pods_trim( $object, null, 'r' ) );

		$object->test = ' test  & ';
		$object->test2 = ' test2 ';

		$result->test = ' test';
		$result->test2 = ' test2';

		$this->assertEquals( $result, pods_trim( $object, ' &', 'r' ) );

		//$this->markTestIncomplete( 'not yet implemented' );
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
			'two' => 2,
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
		$this->assertEquals( '2', pods_traverse( 'two', $value ) );
		$this->assertEquals( 1.5, pods_traverse( 'decimals.onehalf', $value ) );
		$this->assertEquals( null, pods_traverse( 'invalid', $value ) );
		$this->assertEquals( null, pods_traverse( 'decimals.invalid', $value ) );

		// Array traversal.
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
		$value->two               = 2;
		$value->decimals          = new stdClass();
		$value->decimals->half    = 0.5;
		$value->decimals->onehalf = 1.5;

		// No traversal.
		$this->assertEquals( $value, pods_traverse( null, $value ) );

		// String traversal.
		$this->assertEquals( '2', pods_traverse( 'two', $value ) );
		$this->assertEquals( 1.5, pods_traverse( 'decimals.onehalf', $value ) );
		$this->assertEquals( null, pods_traverse( 'invalid', $value ) );
		$this->assertEquals( null, pods_traverse( 'decimals.invalid', $value ) );

		// Array traversal.
		$this->assertEquals( '2', pods_traverse( array( 'two' ), $value ) );
		$this->assertEquals( 1.5, pods_traverse( array( 'decimals', 'onehalf' ), $value ) );
		$this->assertEquals( null, pods_traverse( array( 'invalid' ), $value ) );
		$this->assertEquals( null, pods_traverse( array( 'decimals', 'invalid' ), $value ) );

	}

	public function test_pods_v() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

	public function test_pods_v_sanitized() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

	public function test_pods_v_set() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

	public function test_pods_var() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

	public function test_pods_var_raw() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

	public function test_pods_var_set() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

	public function test_pods_query_arg() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

	public function test_pods_var_update() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

	public function test_pods_cast() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

	public function test_pods_create_slug() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

	public function test_pods_unique_slug() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

	public function test_pods_clean_name() {

		$this->markTestIncomplete( 'not yet implemented' );
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

		$this->markTestIncomplete( 'not yet implemented' );
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

	public function test_pods_evaluate_tags() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

	public function test_pods_evaluate_tag_sanitized() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

	public function test_pods_evaluate_tag() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

	public function test_pods_serial_comma() {

		$values = array(
			'test1',
			'test2',
			'test3',
		);

		$result = 'test1, test2, and test3';

		$this->assertEquals( $result, pods_serial_comma( $values ) );

		$args = array(
			'serial' => false,
		);

		$result = 'test1, test2 and test3';

		$this->assertEquals( $result, pods_serial_comma( $values, $args ) );

		$args = array(
			'separator' => ' | ',
			'serial'    => false,
		);

		$result = 'test1 | test2 and test3';

		$this->assertEquals( $result, pods_serial_comma( $values, $args ) );

		$args = array(
			'and'       => ' & ',
			'serial'    => false,
		);

		$result = 'test1, test2 & test3';

		$this->assertEquals( $result, pods_serial_comma( $values, $args ) );

		$args = array(
			'separator' => ' | ',
			'and'       => ' | ',
			'serial'    => false,
		);

		$result = 'test1 | test2 | test3';

		$this->assertEquals( $result, pods_serial_comma( $values, $args ) );

		//$this->markTestIncomplete( 'not yet implemented' );
	}

	public function test_pods_var_user() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

	public function test_pods_hierarchical_list() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

	public function test_pods_hierarchical_list_recurse() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

	public function test_pods_hierarchical_select() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

	public function test_pods_hierarchical_select_recurse() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

	public function test_pods_list_filter() {

		$this->markTestIncomplete( 'not yet implemented' );
	}

}
