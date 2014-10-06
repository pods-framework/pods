<?php
namespace Pods_Unit_Tests;

/**
 * @group pods
 */
class Test_Pods_Field_Text extends Pods_UnitTestCase {

	public function setUp() {
		$this->field = new \Pods_Field_Text;
	}

	public function tearDown() {
		unset( $this->field );
	}

	/**
	 * @covers Pods_Field_Text::options
	 */
	public function test_method_exists_options() {
		$this->assertTrue( method_exists ( 'Pods_Field_Text', 'options' ) );
	}

	/**
	 * @covers  Pods_Field_Text::options
	 * @depends test_method_exists_options
	 */
	public function test_method_options_returns_array() {
		$this->assertInternalType( 'array', $this->field->options() );
	}

	/**
	 * @covers  Pods_Field_Text::options
	 * @depends test_method_exists_options
	 */
	public function test_method_options_key_exists_text_repeatable() {
		$this->assertArrayHasKey( 'text_repeatable', $this->field->options() );
	}

	/**
	 * @covers  Pods_Field_Text::options
	 * @depends test_method_exists_options
	 */
	public function test_method_options_key_exists_output_options() {
		$this->assertArrayHasKey( 'output_options', $this->field->options() );
	}

	/**
	 * @covers  Pods_Field_Text::options
	 * @depends test_method_exists_options
	 */
	public function test_method_options_key_exists_text_allowed_html_tags() {
		$this->assertArrayHasKey( 'text_allowed_html_tags', $this->field->options() );
	}

	/**
	 * @covers  Pods_Field_Text::options
	 * @depends test_method_exists_options
	 */
	public function test_method_options_key_exists_text_max_length() {
		$this->assertArrayHasKey( 'text_max_length', $this->field->options() );
	}

	/**
	 * @covers Pods_Field_Text::schema
	 */
	public function test_method_exists_schema() {
		$this->assertTrue( method_exists( 'Pods_Field_Text', 'schema' ) );
	}

	/**
	 * @covers  Pods_Field_Text::schema
	 * @depends test_method_exists_schema
	 */
	public function test_method_schema_returns_string() {
		$this->assertInternalType( 'string', $this->field->schema() );
	}

	/**
	 * @covers  Pods_Field_Text::schema
	 * @depends test_method_exists_schema
	 */
	public function test_method_schema_returns_varchar_default() {
		$this->assertEquals( 'VARCHAR(255)', $this->field->schema() );
	}

	/**
	 * @covers  Pods_Field_Text::schema
	 * @depends test_method_exists_schema
	 */
	public function test_method_schema_returns_mediumtext() {
		$this->markTestIncomplete();
		$this->assertEquals( 'MEDIUMTEXT', $this->field->schema( $options ) );
	}

	/**
	 * @covers  Pods_Field_Text::schema
	 * @depends test_method_exists_schema
	 */
	public function test_method_schema_returns_longtext() {
		$this->markTestIncomplete();
		$this->assertEquals( 'LONGTEXT', $this->field->schema( $options ) );
	}

	/**
	 * @covers  Pods_Field_Text::display
	 */
	public function test_method_exists_display() {
		$this->assertTrue( method_exists( 'Pods_Field_Text', 'display' ) );
	}

	/**
	 * @covers  Pods_Field_Text::display
	 * @depends test_method_exists_display
	 */
	public function test_method_display_defaults() {
		$this->assertEquals( '', $this->field->display() );
	}

	/**
	 * @covers  Pods_Field_Text::display
	 * @depends test_method_exists_display
	 */
	public function test_method_display_value() {
		$this->assertEquals( 'foo', $this->field->display( 'foo' ) );
	}

	/**
	 * @covers  Pods_Field_Text::display
	 * @depends test_method_exists_display
	 */
	public function test_method_display_value_allow_shortcode() {
		$this->markTestIncomplete();
	}

	/**
	 * @covers Pods_Field_Text::ui
	 */
	public function test_method_exists_ui() {
		$this->assertTrue( method_exists( 'Pods_Field_Text', 'ui' ), 'Pods_Field_Text::ui does not exist.' );
	}

	/**
	 * @covers  Pods_Field_Text::ui
	 * @depends test_method_exists_ui
	 */
	public function test_method_ui() {
		$this->assertEquals( 'foo', $this->field->ui( 1, 'foo' ) );
	}
}
