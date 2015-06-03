<?php
/**
 * @package Pods
 * @category Tests
 */
namespace Pods_Unit_Tests;
use Mockery;

/**
 * @group pods
 */

if ( ! class_exists( 'Pods' ) ) {
	require_once PODS_TEST_PLUGIN_DIR . '/classes/Pods.php';
}

class Test_Pods extends Pods_UnitTestCase {

	/**
	 * The pods system under test
	 *
	 * @var   \Pods
	 * @since 3.0
	 */
	private $pods;

	public function setUp() {
		$this->pods = pods();
	}

	public function tearDown() {
		unset( $this->pods );
	}

	/**
	 * Test the add method when passing empty parameters
	 *
	 * @covers Pods::add
	 * @since  3.0
	 */
	public function test_method_add_empty() {
		$this->assertTrue( method_exists( $this->pods, 'add' ), 'Method add does not exist' );

		$return = $this->pods->add( null, null );

		$this->assertInternalType( 'int', $return );
		$this->assertEquals( 0, $return );
	}

	/**
	 * @covers Pods::exists
	 * @since  3.0
	 */
	public function test_method_exists_exists() {
		$this->assertTrue( method_exists( $this->pods, 'exists' ), 'Method exists does not exist' );
	}

	/**
	 * Test pod does not exist
	 *
	 * @covers Pods::exists
	 * @since  3.0
	 */
	public function test_method_exists_false() {
		$this->assertFalse( $this->pods->exists() );
	}

	/**
	 * @covers Pods::exists
	 * @since  3.0
	 */
	public function test_method_exists() {
		$this->setReflectionPropertyValue( $this->pods, 'row', 'foo' );
		$this->assertTrue( $this->pods->exists() );
	}

	/**
	 * @covers Pods::valid
	 * @since  3.0
	 */
	public function test_method_exists_valid() {
		$this->assertTrue( method_exists( $this->pods, 'valid' ), 'Method valid does not exist' );
	}

	/**
	 * Test for invalid pod
	 *
	 * @covers  Pods::valid
	 * @depends test_method_exists_valid
	 * @since   3.0
	 */
	public function test_method_valid_invalid() {
		$this->assertFalse( $this->pods->valid() );
	}

	/**
	 * @covers Pods::valid
	 * @depends test_method_exists_valid
	 * @since  3.0
	 */
	public function test_method_valid_iterator() {
		$this->setReflectionPropertyValue( $this->pods, 'pod_id', 1 );
		$this->setReflectionPropertyValue( $this->pods, 'iterator', true );
		$this->assertFalse( $this->pods->valid() );
	}

	/**
	 * @covers Pods::valid
	 * @depends test_method_exists_valid
	 * @since  3.0
	 */
	public function test_method_valid() {
		$this->setReflectionPropertyValue( $this->pods, 'pod_id', 1 );
		$this->assertTrue( $this->pods->valid() );
	}

	/**
	 * @covers Pods::is_iterator
	 * @since  3.0
	 */
	public function test_method_is_iterator() {
		$this->assertTrue( method_exists( $this->pods, 'is_iterator' ), 'Method is_iterator does not exist' );
		$this->setReflectionPropertyValue( $this->pods, 'iterator', true );
		$this->assertTrue( $this->pods->is_iterator() );
	}

	/**
	 * @covers Pods::stop_iterator
	 * @since  3.0
	 */
	public function test_method_stop_iterator() {
		$this->assertTrue( method_exists( $this->pods, 'stop_iterator' ), 'Method stop_iterator does not exist' );
		$this->setReflectionPropertyValue( $this->pods, 'iterator', true );
		$this->pods->stop_iterator();
		$this->assertFalse( $this->getReflectionPropertyValue( $this->pods, 'iterator' ) );
	}

	/**
	 * @covers Pods::rewind
	 * @since  3.0
	 */
	public function test_method_rewind_exists() {
		$this->assertTrue( method_exists( $this->pods, 'rewind' ), 'Method rewind does not exist' );
	}

	/**
	 * @covers  Pods::rewind
	 * @depends test_method_rewind_exists
	 * @since   3.0
	 */
	public function test_method_rewind() {
		$this->setReflectionPropertyValue( $this->pods, 'iterator', true );
		$this->assertNull( $this->pods->rewind(), 'Pods::rewind did not return null' );
		$this->assertEquals( 0, $this->getReflectionPropertyValue( $this->pods, 'row_number' ) );
	}

	/**
	 * Test rewind when iterator is false
	 *
	 * @covers  Pods::rewind
	 * @depends test_method_rewind_exists
	 * @since   3.0
	 */
	public function test_method_rewind_iterator_false() {
		$this->setReflectionPropertyValue( $this->pods, 'iterator', false );
		$this->assertFalse( $this->pods->rewind() );
	}

	/**
	 * @covers Pods::current
	 * @since  3.0
	 */
	public function test_method_current_exists() {
		$this->assertTrue( method_exists( $this->pods, 'current' ), 'Method current does not exist' );
	}

	/**
	 * Test current when iterator = false
	 *
	 * @covers  Pods::current
	 * @depends test_method_current_exists
	 * @since   3.0
	 */
	public function test_method_current_iterator_false() {
		$this->setReflectionPropertyValue( $this->pods, 'iterator', false );
		$this->assertFalse( $this->pods->current() );
	}

	/**
	 * Test current when iterator = true
	 *
	 * @covers  Pods::current
	 * @depends test_method_current_exists
	 * @since   3.0
	 */
	public function test_method_current_iterator_true() {
		$this->setReflectionPropertyValue( $this->pods, 'iterator', true );
		$this->assertFalse( $this->pods->current() );
	}

	/**
	 * @covers Pods::key
	 * @since  3.0
	 */
	public function test_method_key_exists() {
		$this->assertTrue( method_exists( $this->pods, 'key' ) );
	}

	/**
	 * Test key when iterator = false
	 *
	 * @covers  Pods::key
	 * @depends test_method_key_exists
	 * @since   3.0
	 */
	public function test_method_key_iterator_false() {
		$this->setReflectionPropertyValue( $this->pods, 'iterator', false );
		$this->assertFalse( $this->pods->key() );
	}

	/**
	 * Test current when iterator = true
	 *
	 * @covers  Pods::key
	 * @depends test_method_key_exists
	 * @since   3.0
	 */
	public function test_method_key() {
		$this->setReflectionPropertyValue( $this->pods, 'iterator', true );
		$this->setReflectionPropertyValue( $this->pods, 'row_number', 22 );
		$this->assertEquals( 22, $this->pods->key() );
	}

	/**
	 * @covers Pods::next
	 * @since  3.0
	 */
	public function test_method_next_exists() {
		$this->assertTrue( method_exists( $this->pods, 'next' ) );
	}

	/**
	 * Test next when iterator = false
	 *
	 * @covers  Pods::next
	 * @depends test_method_next_exists
	 * @since   3.0
	 */
	public function test_method_next_iterator_false() {
		$this->setReflectionPropertyValue( $this->pods, 'iterator', false );
		$this->assertFalse( $this->pods->next() );
	}

	/**
	 * Test next when iterator = true
	 *
	 * @covers  Pods::next
	 * @depends test_method_next_exists
	 * @since   3.0
	 */
	public function test_method_next() {
		$this->setReflectionPropertyValue( $this->pods, 'iterator', true );
		$this->setReflectionPropertyValue( $this->pods, 'row_number', 19 );
		$this->assertNull( $this->pods->next() );
		$this->assertEquals( 20, $this->getReflectionPropertyValue( $this->pods, 'row_number' ), 'The row number was not incremented correctly' );
	}

	/**
	 * @covers Pods::input
	 * @since  3.0
	 */
	public function test_method_exists_input() {
		$this->assertTrue( method_exists( $this->pods, 'input'), 'Method input does not exist' );
	}

	/**
	 * Test input when field parameter is string and does not exist
	 *
	 * @covers  Pods::input
	 * @depends test_method_exists_input
	 * @since   3.0
	 */
	public function test_method_input_field_string_missing_field() {
		$this->expectOutputString( '' );
		$this->pods->input( 'foo' );
	}

	/**
	 * Test input when field parameter is empty array
	 *
	 * @covers  Pods::input
	 * @depends test_method_exists_input
	 * @since   3.0
	 */
	public function test_method_input_field_empty_array() {
		$this->expectOutputString( '' );
		$this->pods->input( array() );
	}

	/**
	 * @covers Pods::row
	 * @since 3.0
	 */
	public function test_method_exists_row() {
		$this->assertTrue( method_exists( $this->pods, 'row' ), 'Method row does not exist' );
	}

	/**
	 * @covers  Pods::row
	 * @depends test_method_exists_row
	 * @since   3.0
	 */
	public function test_method_row_false() {
		$this->assertFalse( $this->pods->row() );
	}

	/**
	 * @covers  Pods::row
	 * @depends test_method_exists_row
	 * @since   3.0
	 */
	public function test_method_row() {
		$this->setReflectionPropertyValue( $this->pods, 'row', array() );
		$this->assertInternalType( 'array', $this->pods->row() );
	}

	/**
	 * @covers Pods::data
	 * @since  3.0
	 */
	public function test_method_exists_data() {
		$this->assertTrue( method_exists( $this->pods, 'data' ), 'Method data does not exist' );
	}

	/**
	 * @covers  Pods::data
	 * @depends test_method_exists_data
	 * @since   3.0
	 */
	public function test_method_data_empty_rows() {
		$this->setReflectionPropertyValue( $this->pods, 'rows', array() );
		$this->assertFalse( $this->pods->data() );
	}

	/**
	 * @covers  Pods::data
	 * @depends test_method_exists_data
	 * @since   3.0
	 */
	public function test_method_data() {
		$this->setReflectionPropertyValue( $this->pods, 'rows', array( 'foo' => 'bar' ) );
		$this->assertEquals( array( 'foo' => 'bar' ), $this->pods->data() );
	}

	/**
	 * @covers Pods::__get
	 * @since  3.0
	 */
	public function test_method_exists_get() {
		$this->assertTrue( method_exists( $this->pods, '__get' ), 'Method __get does not exist' );
	}

	/**
	 * Test the get method when the property does exist
	 *
	 * @covers  Pods::__get
	 * @depends test_method_exists_get
	 * @since   3.0
	 */
	public function test_method_get() {
		$this->pods->data->field_foo  = 'bar';

		$this->assertEquals( 'bar', $this->pods->field_foo );
	}

	/**
	 * Test the get method when the property does exist in the deprecated class
	 *
	 * @covers  Pods::__get
	 * @depends test_method_exists_get
	 * @since   3.0
	 */
	public function test_method_get_deprecated_property() {
		$deprecated             = Mockery::mock( 'Pods_Deprecated' );
		$deprecated->foo        = 'bar';
		$this->pods->deprecated = $deprecated;

		$this->assertEquals( 'bar', @$this->pods->foo );
	}

	/**
	 * Test the get method error when the property does exist in the deprecated class
	 *
	 * @covers  Pods::__get
	 * @depends test_method_exists_get
	 * @since   3.0
	 */
	public function test_method_get_deprecated_property_error() {
		$deprecated             = Mockery::mock( 'Pods_Deprecated' );
		$deprecated->foo        = 'bar';
		$this->pods->deprecated = $deprecated;

		// @todo needs fixing
		$this->setExpectedException( 'PHPUnit_Framework_Error_Notice' );
		$test = $this->pods->foo;
	}

	/**
	 * @covers Pods::__call
	 * @since  3.0
	 */
	public function test_method_exists_call() {

		$this->assertTrue( method_exists( $this->pods, '__call' ), 'Method __call does not exist' );
	}

	/**
	 * Test the __call method when the called method does not exist in the deprecated class
	 *
	 * @covers  Pods::__call
	 * @depends test_method_exists_call
	 * @since   3.0
	 */
	public function test_method_call_method_does_not_exist() {
		$deprecated = Mockery::mock( 'Pods_Deprecated' );
		$this->pods->deprecated = $deprecated;

		$this->assertNull( @$this->pods->__call( 'foo', array() ) );
	}

	/**
	 * Test the __call method error when the called method does not exist in the deprecated class
	 *
	 * @covers  Pods::__call
	 * @depends test_method_exists_call
	 * @since   3.0
	 */
	public function test_method_call_deprecated_method_error() {
		$deprecated = Mockery::mock( 'Pods_Deprecated' );
		$this->pods->deprecated = $deprecated;

		$this->setExpectedException( 'PHPUnit_Framework_Error_Notice' );
		$test = $this->pods->__call( 'foo', array() );
	}

	/**
	 * @covers Pods::id
	 * @since  3.0
	 */
	public function test_method_id_field_does_not_exist() {

		$this->assertTrue( method_exists( $this->pods, 'id' ), 'Method id does not exist' );

		$data = new \stdClass();
		$data->field_id = 1;
		$this->setReflectionPropertyValue( $this->pods, 'data', $data );

		$this->assertNull( $this->pods->id() );
	}
}
