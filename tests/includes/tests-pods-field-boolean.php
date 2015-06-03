<?php
/**
 * @package Pods
 * @category Tests
 */
namespace Pods_Unit_Tests;
use PodsField_Boolean;

require PODS_TEST_PLUGIN_DIR . '/classes/Pods/Field/Boolean.php';

/**
 * Class Test_PodsField_Boolean
 *
 * @package            Pods_Unit_Tests
 * @group              pods-field
 * @coversDefaultClass PodsField_Boolean
 */
class Test_PodsField_Boolean extends Pods_UnitTestCase {
    public function setUp() {
        $this->field = new PodsField_Boolean;
    }

    public function tearDown() {
        unset( $this->field );
    }

    /**
     * @covers ::options
     */
    public function test_method_options_exists() {
        $this->assertTrue( method_exists( $this->field, 'options' ), 'Method ::options does not exist.' );
    }

    /**
     * @covers  ::options
     * @depends test_method_options_exists
     */
    public function test_method_options_returns_array() {
        $this->assertInternalType( 'array', $this->field->options() );
    }

    /**
     * @covers  ::options
     * @depends test_method_options_exists
     * @depends test_method_options_returns_array
     */
    public function test_method_options_key_boolean_format_type_present() {
        $this->assertArrayHasKey( 'boolean_format_type', $this->field->options() );
    }

    /**
     * @covers  ::options
     * @depends test_method_options_exists
     * @depends test_method_options_returns_array
     */
    public function test_method_options_key_boolean_yes_label_present() {
        $this->assertArrayHasKey( 'boolean_yes_label', $this->field->options() );
    }

    /**
     * @covers  ::options
     * @depends test_method_options_exists
     * @depends test_method_options_returns_array
     */
    public function test_method_options_key_boolean_no_label_present() {
        $this->assertArrayHasKey( 'boolean_no_label', $this->field->options() );
    }

    /**
     * @covers ::schema
     */
    public function test_method_schema_exists() {
        $this->assertTrue( method_exists( $this->field, 'schema' ), 'Method ::schema does not exist.' );
    }

    /**
     * @covers  ::schema
     * @depends test_method_schema_exists
     */
    public function test_method_schema() {
        $this->assertEquals( 'BOOL DEFAULT 0', $this->field->schema() );
    }

    /**
     * @covers ::display
     */
    public function test_method_display_exists() {
        $this->assertTrue( method_exists( $this->field, 'display' ), 'Method ::display does not exist.' );
    }

    /**
     * @covers  ::display
     * @depends test_method_display_exists
     * @uses    ::pods_v
     */
    public function test_method_display_defaults() {
        $this->assertNUll( $this->field->display() );
    }

    /**
     * @covers  ::display
     * @depends test_method_display_exists
     * @uses    ::pods_v
     */
    public function test_method_display_true() {
        $this->assertEquals( 1, $this->field->display( 1 ) );
    }

    /**
     * @covers  ::display
     * @depends test_method_display_exists
     * @uses    ::pods_v
     */
    public function test_method_display_false() {
        $this->assertEquals( 0, $this->field->display( 0 ) );
    }

    /**
     * @covers ::input
     */
    public function test_method_input_exists() {
        $this->assertTrue( method_exists( $this->field, 'input' ), 'Method ::input does not exist.' );
    }

    /**
     * @covers  ::input
     * @depends test_method_input_exists
     * @uses    Pods_Form::permission
     * @uses    ::pods_v
     * @uses    ::pods_has_permissions
     */
    public function test_method_input_defaults() {
        $this->markTestIncomplete();
    }

    /**
     * @covers ::data
     */
    public function test_method_data_exists() {
        $this->assertTrue( method_exists( $this->field, 'data' ), 'Method ::data does not exist.' );
    }

    /**
     * @covers  ::data
     * @depends test_method_data_exists
     * @uses    ::pods_v
     */
    public function test_method_data_defaults() {
        $this->assertEquals( array( 1 => null, 0 => null ), $this->field->data( 'foo' ) );
    }

    /**
     * @covers  ::data
     * @depends test_method_data_exists
     * @uses    ::pods_v
     */
    public function test_method_data_format_type_radio() {
        $this->assertEquals( array( 1 => 'bar', 0 => 'baz' ), $this->field->data( 'foo', null, array( 'boolean_format_type' => 'radio', 'boolean_yes_label' => 'bar', 'boolean_no_label' => 'baz' ) ) );
    }

    /**
     * @covers  ::data
     * @depends test_method_data_exists
     * @uses    ::pods_v
     */
    public function test_method_data_format_type_checkbox() {
        $this->assertEquals( array( 1 => 'bar' ), $this->field->data( 'foo', null, array( 'boolean_format_type' => 'checkbox', 'boolean_yes_label' => 'bar' ) ) );
    }

    /**
     * @covers ::regex
     */
    public function test_method_regex_exists() {
        $this->assertTrue( method_exists( $this->field, 'regex' ), 'Method ::regex does not exist.' );
    }

    /**
     * @covers  ::regex
     * @depends test_method_regex_exists
     */
    public function test_method_regex() {
        $this->assertFalse( $this->field->regex() );
    }

    /**
     * @covers ::validate
     */
    public function test_method_validate_exists() {
        $this->assertTrue( method_exists( $this->field, 'validate' ), 'Method ::validate does not exist.' );
    }

    /**
     * @covers  ::validate
     * @depends test_method_validate_exists
     */
    public function test_method_validate() {
        $this->assertTrue( $this->field->validate( 'foo' ) );
    }

    /**
     * @covers ::pre_save
     */
    public function test_method_pre_save_exists() {
        $this->assertTrue( method_exists( $this->field, 'pre_save' ), 'Method ::pre_save does not exist.' );
    }

    /**
     * @covers  ::pre_save
     * @depends test_method_pre_save_exists
     */
    public function test_method_pre_save_value_1() {
        $this->assertEquals( 1, $this->field->pre_save( 1 ) );
    }

    /**
     * @covers  ::pre_save
     * @depends test_method_pre_save_exists
     */
    public function test_method_pre_save_value_yes() {
        $this->assertEquals( 1, $this->field->pre_save( 'yes' ) );
    }

    /**
     * @covers  ::pre_save
     * @depends test_method_pre_save_exists
     */
    public function test_method_pre_save_value_capitalized_yes() {
        $this->assertEquals( 1, $this->field->pre_save( 'Yes' ) );
    }

    /**
     * @covers  ::pre_save
     * @depends test_method_pre_save_exists
     */
    public function test_method_pre_save_value_0() {
        $this->assertEquals( 0, $this->field->pre_save( 0 ) );
    }

    /**
     * @covers  ::pre_save
     * @depends test_method_pre_save_exists
     */
    public function test_method_pre_save_value_no() {
        $this->assertEquals( 0, $this->field->pre_save( 'no' ) );
    }

    /**
     * @covers  ::pre_save
     * @depends test_method_pre_save_exists
     */
    public function test_method_pre_save_value_captialized_no() {
        $this->assertEquals( 0, $this->field->pre_save( 'No' ) );
    }

    /**
     * @covers  ::pre_save
     * @depends test_method_pre_save_exists
     */
    public function test_method_pre_save_value_foo_no_label_match() {
        $this->assertEquals( 0, $this->field->pre_save( 'foo' ) );
    }

    /**
     * @covers  ::pre_save
     * @depends test_method_pre_save_exists
     */
    public function test_method_pre_save_value_foo_matches_yes_label() {
        $this->assertEquals( 1, $this->field->pre_save( 'foo', null, null, array( 'boolean_yes_label' => 'foo' ) ) );
    }

    /**
     * @covers  ::pre_save
     * @depends test_method_pre_save_exists
     */
    public function test_method_pre_save_value_capitalized_foo_matches_yes_label() {
        $this->assertEquals( 1, $this->field->pre_save( 'Foo', null, null, array( 'boolean_yes_label' => 'foo' ) ) );
    }

    /**
     * @covers  ::pre_save
     * @depends test_method_pre_save_exists
     */
    public function test_method_pre_save_value_foo_matches_capitalized_yes_label() {
        $this->assertEquals( 1, $this->field->pre_save( 'foo', null, null, array( 'boolean_yes_label' => 'Foo' ) ) );
    }

    /**
     * @covers  ::pre_save
     * @depends test_method_pre_save_exists
     */
    public function test_method_pre_save_value_capitalized_foo_matches_capitalized_yes_label() {
        $this->assertEquals( 1, $this->field->pre_save( 'Foo', null, null, array( 'boolean_yes_label' => 'Foo' ) ) );
    }

    /**
     * @covers  ::pre_save
     * @depends test_method_pre_save_exists
     */
    public function test_method_pre_save_value_foo_matches_no_label() {
        $this->assertEquals( 0, $this->field->pre_save( 'foo', null, null, array( 'boolean_no_label' => 'foo' ) ) );
    }

    /**
     * @covers  ::pre_save
     * @depends test_method_pre_save_exists
     */
    public function test_method_pre_save_value_capitalized_foo_matches_no_label() {
        $this->assertEquals( 0, $this->field->pre_save( 'Foo', null, null, array( 'boolean_no_label' => 'foo' ) ) );
    }

    /**
     * @covers  ::pre_save
     * @depends test_method_pre_save_exists
     */
    public function test_method_pre_save_value_foo_matches_capitalized_no_label() {
        $this->assertEquals( 0, $this->field->pre_save( 'foo', null, null, array( 'boolean_no_label' => 'Foo' ) ) );
    }

    /**
     * @covers  ::pre_save
     * @depends test_method_pre_save_exists
     */
    public function test_method_pre_save_value_capitalized_foo_matches_capitalized_no_label() {
        $this->assertEquals( 0, $this->field->pre_save( 'Foo', null, null, array( 'boolean_no_label' => 'Foo' ) ) );
    }

    /**
     * @covers ::ui
     */
    public function test_method_ui_exists() {
        $this->assertTrue( method_exists( $this->field, 'ui' ), 'Method ::ui does not exist.' );
    }

    /**
     * @covers  ::ui
     * @depends test_method_ui_exists
     */
    public function test_method_ui_defaults_to_no() {
        $this->assertEquals( 'No', $this->field->ui( 'foo', 'bar' ) );
    }

    /**
     * @covers  ::ui
     * @depends test_method_ui_exists
     */
    public function test_method_ui_default_no_label() {
        $this->assertEquals( 'No', $this->field->ui( 'foo', 0 ) );
    }

    /**
     * @covers  ::ui
     * @depends test_method_ui_exists
     */
    public function test_method_ui_default_yes_label() {
        $this->assertEquals( 'Yes', $this->field->ui( 'foo', 1 ) );
    }

    /**
     * @covers  ::ui
     * @depends test_method_ui_exists
     */
    public function test_method_ui_custom_yes_label() {
        $this->assertEquals( 'Bar', $this->field->ui( 'foo', 1, null, array( 'boolean_yes_label' => 'Bar' ) ) );
    }

    /**
     * @covers  ::ui
     * @depends test_method_ui_exists
     */
    public function test_method_ui_custom_no_label() {
        $this->assertEquals( 'Bar', $this->field->ui( 'foo', 0, null, array( 'boolean_no_label' => 'Bar' ) ) );
    }
}
