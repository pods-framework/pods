<?php

namespace Pods_Unit_Tests\Pods\Field;

use Pods_Unit_Tests\Pods_UnitTestCase;
use PodsField_Paragraph;

/**
 * @group              pods-field
 * @coversDefaultClass PodsField_Paragraph
 */
class PodsField_ParagraphTest extends Pods_UnitTestCase {

	/**
	 * @var PodsField_Paragraph
	 */
	private $field;

	public function setUp(): void {
		$this->field = new PodsField_Paragraph();
	}

	public function tearDown(): void {
		unset( $this->field );
	}

	/**
	 * @covers PodsField_Paragraph::options
	 */
	public function test_method_exists_options() {
		$this->assertTrue( method_exists( 'PodsField_Paragraph', 'options' ) );
	}

	/**
	 * @covers  PodsField_Paragraph::options
	 * @depends test_method_exists_options
	 */
	public function test_method_options_key_exists_paragraph_min_length() {
		$this->assertArrayHasKey( 'paragraph_min_length', $this->field->options() );
	}

	/**
	 * @covers PodsField_Paragraph::validate
	 */
	public function test_method_exists_validate() {
		$this->assertTrue( method_exists( 'PodsField_Paragraph', 'validate' ), 'PodsField_Paragraph::validate does not exist.' );
	}

	/**
	 * @covers  PodsField_Paragraph::validate
	 * @depends test_method_exists_validate
	 * @uses    PodsField_Paragraph::strip_html
	 * @uses    ::pods_v
	 * @uses    ::pods_mb_strlen
	 * @uses    ::pods_mb_substr
	 */
	public function test_method_validate() {
		$this->assertTrue( $this->field->validate( 'foobar' ) );
	}

	/**
	 * @covers  PodsField_Paragraph::validate
	 * @depends test_method_exists_validate
	 * @uses    PodsField_Paragraph::strip_html
	 * @uses    ::pods_v
	 * @uses    ::pods_mb_strlen
	 * @uses    ::pods_mb_substr
	 */
	public function test_method_validate_empty_value() {
		$this->assertTrue( $this->field->validate( '' ) );
	}

	/**
	 * @covers  PodsField_Paragraph::validate
	 * @depends test_method_exists_validate
	 * @uses    PodsField_Paragraph::strip_html
	 * @uses    PodsField_Paragraph::get_min_length_error
	 * @uses    ::pods_v
	 * @uses    ::pods_mb_strlen
	 * @uses    ::pods_mb_substr
	 */
	public function test_method_validate_min_length_below() {
		$errors = $this->field->validate( 'ab', 'my_field', array( 'paragraph_min_length' => 3 ) );

		$this->assertIsArray( $errors );
		$this->assertSame( array( 'My Field must be at least 3 characters long.' ), $errors );
	}

	/**
	 * @covers  PodsField_Paragraph::validate
	 * @depends test_method_exists_validate
	 * @uses    PodsField_Paragraph::strip_html
	 * @uses    PodsField_Paragraph::get_min_length_error
	 * @uses    ::pods_v
	 * @uses    ::pods_mb_strlen
	 * @uses    ::pods_mb_substr
	 */
	public function test_method_validate_min_length_met() {
		$this->assertTrue( $this->field->validate( 'abc', 'my_field', array( 'paragraph_min_length' => 3 ) ) );
	}

	/**
	 * @covers  PodsField_Paragraph::validate
	 * @depends test_method_exists_validate
	 * @uses    PodsField_Paragraph::strip_html
	 * @uses    PodsField_Paragraph::get_min_length_error
	 * @uses    ::pods_v
	 * @uses    ::pods_mb_strlen
	 * @uses    ::pods_mb_substr
	 */
	public function test_method_validate_min_length_not_enforced_on_empty_value() {
		$this->assertTrue( $this->field->validate( '', 'my_field', array( 'paragraph_min_length' => 3 ) ) );
	}

	/**
	 * @covers  PodsField_Paragraph::validate
	 * @depends test_method_exists_validate
	 * @uses    PodsField_Paragraph::strip_html
	 * @uses    PodsField_Paragraph::get_min_length_error
	 * @uses    ::pods_v
	 * @uses    ::pods_mb_strlen
	 * @uses    ::pods_mb_substr
	 */
	public function test_method_validate_min_length_default_disabled() {
		$this->assertTrue( $this->field->validate( 'a' ) );
	}
}
