<?php

namespace Pods_Unit_Tests\Pods\Field;

use Pods_Unit_Tests\Pods_UnitTestCase;
use PodsField_WYSIWYG;

/**
 * @group              pods-field
 * @coversDefaultClass PodsField_WYSIWYG
 */
class PodsField_WYSIWYGTest extends Pods_UnitTestCase {

	/**
	 * @var PodsField_WYSIWYG
	 */
	private $field;

	public function setUp(): void {
		$this->field = new PodsField_WYSIWYG();
	}

	public function tearDown(): void {
		unset( $this->field );
	}

	/**
	 * @covers PodsField_WYSIWYG::options
	 */
	public function test_method_exists_options() {
		$this->assertTrue( method_exists( 'PodsField_WYSIWYG', 'options' ) );
	}

	/**
	 * @covers  PodsField_WYSIWYG::options
	 * @depends test_method_exists_options
	 */
	public function test_method_options_key_exists_wysiwyg_min_length() {
		$this->assertArrayHasKey( 'wysiwyg_min_length', $this->field->options() );
	}

	/**
	 * @covers PodsField_WYSIWYG::validate
	 */
	public function test_method_exists_validate() {
		$this->assertTrue( method_exists( 'PodsField_WYSIWYG', 'validate' ), 'PodsField_WYSIWYG::validate does not exist.' );
	}

	/**
	 * @covers  PodsField_WYSIWYG::validate
	 * @depends test_method_exists_validate
	 * @uses    PodsField_WYSIWYG::strip_html
	 * @uses    ::pods_v
	 * @uses    ::pods_mb_strlen
	 * @uses    ::pods_mb_substr
	 */
	public function test_method_validate() {
		$this->assertTrue( $this->field->validate( 'foobar' ) );
	}

	/**
	 * @covers  PodsField_WYSIWYG::validate
	 * @depends test_method_exists_validate
	 * @uses    PodsField_WYSIWYG::strip_html
	 * @uses    ::pods_v
	 * @uses    ::pods_mb_strlen
	 * @uses    ::pods_mb_substr
	 */
	public function test_method_validate_empty_value() {
		$this->assertTrue( $this->field->validate( '' ) );
	}

	/**
	 * @covers  PodsField_WYSIWYG::validate
	 * @depends test_method_exists_validate
	 * @uses    PodsField_WYSIWYG::strip_html
	 * @uses    PodsField_WYSIWYG::get_min_length_error
	 * @uses    ::pods_v
	 * @uses    ::pods_mb_strlen
	 * @uses    ::pods_mb_substr
	 */
	public function test_method_validate_min_length_below() {
		$errors = $this->field->validate( 'ab', 'my_field', array( 'wysiwyg_min_length' => 3 ) );

		$this->assertIsArray( $errors );
		$this->assertSame( array( 'My Field must be at least 3 characters long.' ), $errors );
	}

	/**
	 * @covers  PodsField_WYSIWYG::validate
	 * @depends test_method_exists_validate
	 * @uses    PodsField_WYSIWYG::strip_html
	 * @uses    PodsField_WYSIWYG::get_min_length_error
	 * @uses    ::pods_v
	 * @uses    ::pods_mb_strlen
	 * @uses    ::pods_mb_substr
	 */
	public function test_method_validate_min_length_met() {
		$this->assertTrue( $this->field->validate( 'abc', 'my_field', array( 'wysiwyg_min_length' => 3 ) ) );
	}

	/**
	 * @covers  PodsField_WYSIWYG::validate
	 * @depends test_method_exists_validate
	 * @uses    PodsField_WYSIWYG::strip_html
	 * @uses    PodsField_WYSIWYG::get_min_length_error
	 * @uses    ::pods_v
	 * @uses    ::pods_mb_strlen
	 * @uses    ::pods_mb_substr
	 */
	public function test_method_validate_min_length_not_enforced_on_empty_value() {
		$this->assertTrue( $this->field->validate( '', 'my_field', array( 'wysiwyg_min_length' => 3 ) ) );
	}

	/**
	 * @covers  PodsField_WYSIWYG::validate
	 * @depends test_method_exists_validate
	 * @uses    PodsField_WYSIWYG::strip_html
	 * @uses    PodsField_WYSIWYG::get_min_length_error
	 * @uses    ::pods_v
	 * @uses    ::pods_mb_strlen
	 * @uses    ::pods_mb_substr
	 */
	public function test_method_validate_min_length_default_disabled() {
		$this->assertTrue( $this->field->validate( 'a' ) );
	}
}
