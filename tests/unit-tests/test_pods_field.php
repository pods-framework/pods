<?php
namespace Pods_Unit_Tests;

/**
 * @group pods
 */
class Test_Pods_Field extends Pods_UnitTestCase {

	public function setUp() {
		$this->field = new \Pods_Field;
	}

	public function tearDown() {
		unset( $this->field );
	}

	/**
	 * @covers Pods_Field::options
	 */
	public function test_method_options_exists() {
		$this->assertTrue( method_exists( 'Pods_Field', 'options' ), 'Pods_Field::options does not exist.' );
	}

	/**
	 * @covers  Pods_Field::options
	 * @depends test_method_options_exists
	 */
	public function test_method_options_returns_array() {
		$this->assertInternalType( 'array', $this->field->options() );
	}

	/**
	 * @covers  Pods_Field::options
	 * @depends test_method_options_exists
	 */
	public function test_method_options() {
		$this->assertEquals( array(), $this->field->options() );
	}

	/**
	 * @covers Pods_Field::ui_options
	 */
	public function test_method_ui_options_exists() {
		$this->assertTrue( method_exists( 'Pods_Field', 'options' ), 'Pods_Field::options does not exist.' );
	}

	/**
	 * @covers  Pods_Field::options
	 * @depends test_method_options_exists
	 */
	public function test_method_ui_options() {
		$this->assertEquals( array(), $this->field->ui_options() );
	}

	/**
	 * @covers  Pods_Field::ui_options
	 * @depends test_method_ui_options_exists
	 */
	public function test_method_ui_options_returns_array() {
		$this->assertInternalType( 'array', $this->field->ui_options() );
	}

	/**
	 * @covers Pods_Field::schema
	 */
	public function test_method_schema_exists() {
		$this->assertTrue( method_exists( 'Pods_Field', 'schema' ), 'Pods_Field::schema does not exist.' );
	}

	/**
	 * @covers  Pods_Field::schema
	 * @depends test_method_schema_exists
	 */
	public function test_method_schema() {
		$this->assertEquals( 'VARCHAR(255)', $this->field->schema() );
	}

	/**
	 * @covers Pods_Field::prepare
	 */
	public function test_method_prepare_exists() {
		$this->assertTrue( method_exists( 'Pods_Field', 'prepare' ), 'Pods_Field::prepare does not exist.' );
	}

	/**
	 * @covers  Pods_Field::prepare
	 * @depends test_method_prepare_exists
	 */
	public function test_method_prepare() {
		$this->assertEquals( '%s', $this->field->prepare() );
	}

	/**
	 * @covers Pods_Field::value
	 */
	public function test_method_value_exists() {
		$this->assertTrue( method_exists( 'Pods_Field', 'value' ), 'Pods_Field::value does not exist.' );
	}

	/**
	 * @covers  Pods_Field::value
	 * @depends test_method_value_exists
	 */
	public function test_method_value_defaults_to_null() {
		$this->assertNull( $this->field->value() );
	}

	/**
	 * @covers  Pods_Field::value
	 * @depends test_method_value_exists
	 */
	public function test_method_value() {
		$this->assertEquals( 'foo', $this->field->value( 'foo' ) );
	}

	/**
	 * @covers Pods_Field::display
	 */
	public function test_method_display_exists() {
		$this->assertTrue( method_exists( 'Pods_Field', 'display' ), 'Pods_Field::display does not exist.' );
	}

	/**
	 * @covers  Pods_Field::display
	 * @depends test_method_display_exists
	 */
	public function test_method_display_defaults_to_null() {
		$this->assertNull( $this->field->display() );
	}

	/**
	 * @covers  Pods_Field::display
	 * @depends test_method_display_exists
	 */
	public function test_method_display() {
		$this->assertEquals( 'foo', $this->field->display( 'foo' ) );
	}

	/**
	 * @covers Pods_Field::input
	 */
	public function test_method_input_exists() {
		$this->assertTrue( method_exists( 'Pods_Field', 'input' ), 'Pods_Field::input does not exist.' );
	}

	/**
	 * @covers Pods_Field::data
	 */
	public function test_method_data_exists() {
		$this->assertTrue( method_exists( 'Pods_Field', 'data' ), 'Pods_Field::data does not exist.' );
	}

	/**
	 * @covers  Pods_Field::data
	 * @depends test_method_data_exists
	 */
	public function test_method_data_returns_array() {
		$this->assertInternalType( 'array', $this->field->data( 'foo' ) ); 
	}

	/**
	 * @covers  Pods_Field::data
	 * @depends test_method_data_exists
	 */
	public function test_method_data_defaults() {
		$this->assertEquals( array( 'bar' ), $this->field->data( 'foo', array( 'bar' ) ) ); 
	}

	/**
	 * @covers  Pods_Field::data
	 * @depends test_method_data_exists
	 */
	public function test_method_data_string_value() {
		$this->assertEquals( array( 'bar' ), $this->field->data( 'foo', 'bar' ) ); 
	}

	/**
	 * @covers Pods_Field::regex
	 */
	public function test_method_regex_exists() {
		$this->assertTrue( method_exists( 'Pods_Field', 'regex' ), 'Pods_Field::regex does not exist.' );
	}

	/**
	 * @covers  Pods_Field::regex
	 * @depends test_method_regex_exists
	 */
	public function test_method_regex() {
		$this->assertFalse( $this->field->regex( 'foo' ) );
	}

	/**
	 * @covers Pods_Field::validate
	 */
	public function test_method_validate_exists() {
		$this->assertTrue( method_exists( 'Pods_Field', 'validate' ), 'Pods_Field::validate does not exist.' );
	}

	/**
	 * @covers  Pods_Field::validate
	 * @depends test_method_validate_exists
	 */
	public function test_method_validate() {
		$this->assertTrue( $this->field->validate( 'foo' ) );
	}

	/**
	 * @covers Pods_Field::ui
	 */
	public function test_method_ui_exists() {
		$this->assertTrue( method_exists( 'Pods_Field', 'ui' ), 'Pods_Field::ui does not exist.' );
	}

	/**
	 * @covers  Pods_Field::ui
	 * @depends test_method_ui_exists
	 */
	public function test_method_ui() {
		$this->assertEquals( 'bar', $this->field->ui( 'foo', 'bar' ) );
	}
}
