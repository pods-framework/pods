<?php
/**
 * @package Pods
 * @category Tests
 */
namespace Pods_Unit_Tests;
    use PodsField_Text;

require_once PODS_TEST_PLUGIN_DIR . '/classes/Pods/Field/Text.php';

/**
 * @group pods_field
 */
class Test_PodsField_Text extends Pods_UnitTestCase
{
    /**
     * @var PodsField_Text
     */
    private $field;

    public function setUp() {
        $this->field = new PodsField_Text();
    }

    public function tearDown() {
        if ( shortcode_exists( 'fooshortcode') ) {
            remove_shortcode( 'fooshortcode' );
        }

        unset( $this->field );
    }

    /**
     * @covers PodsField_Text::options
     */
    public function test_method_exists_options() {
        $this->assertTrue( method_exists ( 'PodsField_Text', 'options' ) );
    }

    /**
     * @covers  PodsField_Text::options
     * @depends test_method_exists_options
     */
    public function test_method_options_returns_array() {
        $this->assertInternalType( 'array', $this->field->options() );
    }

    /**
     * @covers  PodsField_Text::options
     * @depends test_method_exists_options
     */
    public function test_method_options_key_exists_text_repeatable() {
        $this->assertArrayHasKey( 'text_repeatable', $this->field->options() );
    }

    /**
     * @covers  PodsField_Text::options
     * @depends test_method_exists_options
     */
    public function test_method_options_key_exists_output_options() {
        $this->assertArrayHasKey( 'output_options', $this->field->options() );
    }

    /**
     * @covers  PodsField_Text::options
     * @depends test_method_exists_options
     */
    public function test_method_options_key_exists_text_allowed_html_tags() {
        $this->assertArrayHasKey( 'text_allowed_html_tags', $this->field->options() );
    }

    /**
     * @covers  PodsField_Text::options
     * @depends test_method_exists_options
     */
    public function test_method_options_key_exists_text_max_length() {
        $this->assertArrayHasKey( 'text_max_length', $this->field->options() );
    }

    /**
     * @covers PodsField_Text::schema
     */
    public function test_method_exists_schema() {
        $this->assertTrue( method_exists( 'PodsField_Text', 'schema' ) );
    }

    /**
     * @covers  PodsField_Text::schema
     * @depends test_method_exists_schema
     */
    public function test_method_schema_returns_string() {
        $this->assertInternalType( 'string', $this->field->schema() );
    }

    /**
     * @covers  PodsField_Text::schema
     * @depends test_method_exists_schema
     * @uses    ::pods_v
     */
    public function test_method_schema_returns_varchar_default() {
        $this->assertEquals( 'VARCHAR(255)', $this->field->schema() );
    }

    /**
     * @covers  PodsField_Text::schema
     * @depends test_method_exists_schema
     * @uses    ::pods_v
     */
    public function test_method_schema_returns_longtext() {
        $this->assertEquals( 'LONGTEXT', $this->field->schema( array( 'text_max_length' => 16777216 ) ) );
    }

    /**
     * @covers  PodsField_Text::display
     */
    public function test_method_exists_display() {
        $this->assertTrue( method_exists( 'PodsField_Text', 'display' ) );
    }

    /**
     * @covers  PodsField_Text::display
     * @depends test_method_exists_display
     * @uses    PodsField_Text::strip_html
     * @uses    ::pods_v
     */
    public function test_method_display_defaults() {
        $this->assertEquals( '', $this->field->display() );
    }

    /**
     * @covers  PodsField_Text::display
     * @depends test_method_exists_display
     * @uses    PodsField_Text::strip_html
     * @uses    ::pods_v
     */
    public function test_method_display_value() {
        $this->assertEquals( 'foo', $this->field->display( 'foo' ) );
    }

    /**
     * @covers  PodsField_Text::display
     * @depends test_method_exists_display
     * @uses    PodsField_Text::strip_html
     * @uses    ::pods_v
     */
    public function test_method_display_value_allow_shortcode() {
        add_shortcode( 'fooshortcode', function() { return 'foobar'; } );

        $this->assertEquals( 'foobar', $this->field->display( '[fooshortcode]foo[/fooshortcode]', 'bar', array( 'text_allow_shortcode' => 1 ) ) );
    }

    /**
     * @covers PodsField_Text::pre_save
     */
    public function test_method_exists_pre_save() {
        $this->assertTrue( method_exists( 'PodsField_Text', 'pre_save' ), 'PodsField_Text::pre_save does not exist.' );
    }

    /**
     * @covers  PodsField_Text::pre_save
     * @depends test_method_exists_pre_save
     * @uses    PodsField_Text::strip_html
     * @uses    ::pods_v
     * @uses    ::pods_mb_strlen
     * @uses    ::pods_mb_substr
     */
    public function test_method_pre_save_defaults() {
        $this->assertEquals( 'foo', $this->field->pre_save( 'foo' ) );
    }

    /**
     * @covers  PodsField_Text::pre_save
     * @depends test_method_exists_pre_save
     * @uses    PodsField_Text::strip_html
     * @uses    ::pods_v
     * @uses    ::pods_mb_strlen
     * @uses    ::pods_mb_substr
     */
    public function test_method_pre_save_truncate() {
        $this->assertEquals( 'foo', $this->field->pre_save( 'foobar', null, null, array( 'text_max_length' => 3 ) ) );
    }

    /**
     * @covers PodsField_Text::validate
     */
    public function test_method_exists_validate() {
        $this->assertTrue( method_exists( 'PodsField_Text', 'validate' ), 'PodsField_Text::validate does not exist.' );
    }

    /**
     * @covers  PodsField_Text::validate
     * @depends test_method_exists_validate
     * @uses    PodsField_Text::strip_html
     * @uses    ::pods_v
     * @uses    ::pods_mb_strlen
     * @uses    ::pods_mb_substr
     */
    public function test_method_validate() {
        $this->assertTrue( $this->field->validate( 'foobar' ) );
    }

    /**
     * @covers  PodsField_Text::validate
     * @depends test_method_exists_validate
     * @uses    PodsField_Text::strip_html
     * @uses    ::pods_v
     * @uses    ::pods_mb_strlen
     * @uses    ::pods_mb_substr
     */
    public function test_method_validate_empty_value() {
        $this->assertTrue( $this->field->validate( '' ) );
    }

    /**
     * @covers PodsField_Text::ui
     */
    public function test_method_exists_ui() {
        $this->assertTrue( method_exists( 'PodsField_Text', 'ui' ), 'PodsField_Text::ui does not exist.' );
    }

    /**
     * @covers  PodsField_Text::ui
     * @depends test_method_exists_ui
     */
    public function test_method_ui() {
        $this->assertEquals( 'foo', $this->field->ui( 1, 'foo' ) );
    }

    /**
     * @covers PodsField_Text::strip_html
     */
    public function test_method_exists_strip_html() {
        $this->assertTrue( method_exists( 'PodsField_Text', 'strip_html' ), 'PodsField_Text::strip_html does not exist.' );
    }

    /**
     * @covers  PodsField_Text::strip_html
     * @depends test_method_exists_strip_html
     * @uses    ::pods_v
     */
    public function test_method_strip_html_array_value() {
        $this->assertEquals( 'foo bar baz', $this->field->strip_html( array( 'foo', 'bar', 'baz' ) ) );
    }

    /**
     * @covers  PodsField_Text::strip_html
     * @depends test_method_exists_strip_html
     */
    public function test_method_strip_html_empty_array_value() {
        $this->assertEmpty( $this->field->strip_html( array() ) );
    }

    /**
     * @covers  PodsField_Text::strip_html
     * @depends test_method_exists_strip_html
     * @uses    ::pods_v
     */
    public function test_method_strip_html_defaults() {
        $this->assertEquals( 'foo', $this->field->strip_html('<em>foo</em>') );
    }

    /**
     * @covers  PodsField_Text::strip_html
     * @depends test_method_exists_strip_html
     * @uses    ::pods_v
     */
    public function test_method_strip_html_default_options() {
        $this->assertEquals(
            'foo',
            $this->field->strip_html('<strong><em><a href="#"><ul><li><ol><li><b><i>foo</i></b></li></ol></li></ul></a></em></strong>' ),
            $this->field->options()
        );
    }

    /**
     * @covers  PodsField_Text::strip_html
     * @depends test_method_exists_strip_html
     * @uses    ::pods_v
     */
    public function test_method_strip_html_default_tags_allowed() {
        $options['text_allow_html']= 1;
        $options['text_allowed_html_tags'] = 'strong em a ul ol li b i';

        $this->assertEquals(
            '<strong><em><a href="#"><ul><li><ol><li><b><i>foo</i></b></li></ol></li></ul></a></em></strong>',
            $this->field->strip_html('<strong><em><a href="#"><ul><li><ol><li><b><i>foo</i></b></li></ol></li></ul></a></em></strong>', $options )
        );
    }

    /**
     * @covers  PodsField_Text::strip_html
     * @depends test_method_exists_strip_html
     * @uses    ::pods_v
     */
    public function test_method_strip_html_br_tags_allowed() {
        $options['text_allow_html']= 1;
        $options['text_allowed_html_tags'] = 'br';

        $this->assertEquals(
            'foo<br />',
            $this->field->strip_html('foo<br />', $options )
        );
    }

    /**
     * @covers  PodsField_Text::strip_html
     * @depends test_method_exists_strip_html
     * @uses    ::pods_v
     */
    public function test_method_strip_html_hr_tags_allowed() {
        $options['text_allow_html']= 1;
        $options['text_allowed_html_tags'] = 'hr';

        $this->assertEquals(
            'foo<hr />',
            $this->field->strip_html('foo<hr />', $options )
        );
    }

    /**
     * @covers  PodsField_Text::strip_html
     * @depends test_method_exists_strip_html
     * @uses    ::pods_v
     */
    public function test_method_strip_html_tags_allowed() {
        $options['text_allow_html']= 1;
        $options['text_allowed_html_tags'] = 'strong em';

        $this->assertEquals(
            '<strong><em>foo</em></strong>',
            $this->field->strip_html('<div><strong><em>foo</em></strong></div>', $options )
        );
    }
}
