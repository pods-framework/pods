<?php

namespace Pods_Unit_Tests\Pods;

use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * @group  pods
 * @covers Pods
 */
class PodsTest extends Pods_UnitTestCase {

	/**
	 * The pods system under test
	 * @var   \Pods
	 */
	private $pod;

	public function setUp(): void {
		$this->pod = pods();
	}

	public function tearDown(): void {
		unset( $this->pod );
	}

	/**
	 * Test the add method when passing empty parameters
	 * @covers Pods::add
	 */
	public function test_method_add_empty() {
		$this->assertTrue( method_exists( $this->pod, 'add' ), 'Method add does not exist' );

		$return = $this->pod->add( null, null );

		$this->assertInternalType( 'int', $return );
		$this->assertEquals( 0, $return );
	}

	/**
	 * @covers Pods::exists
	 */
	public function test_method_exists_exists() {
		$this->assertTrue( method_exists( $this->pod, 'exists' ), 'Method exists does not exist' );
	}

	/**
	 * Test pod does not exist
	 * @covers Pods::exists
	 */
	public function test_method_exists_false() {
		$this->assertFalse( $this->pod->exists() );
	}

	/**
	 * @covers Pods::exists
	 */
	public function test_method_exists() {
		$this->setReflectionPropertyValue( $this->pod->data, 'row', 'foo' );
		$this->assertTrue( $this->pod->exists() );
	}

	/**
	 * @covers Pods::valid
	 */
	public function test_method_exists_valid() {
		$this->assertTrue( method_exists( $this->pod, 'valid' ), 'Method valid does not exist' );
	}

	/**
	 * Test for invalid pod
	 * @covers  Pods::valid
	 * @depends test_method_exists_valid
	 */
	public function test_method_valid_invalid() {
		$this->assertFalse( $this->pod->valid() );
	}

	/**
	 * @covers Pods::is_iterator
	 */
	public function test_method_is_iterator() {
		$this->assertTrue( method_exists( $this->pod, 'is_iterator' ), 'Method is_iterator does not exist' );
		$this->setReflectionPropertyValue( $this->pod, 'iterator', true );
		$this->assertTrue( $this->pod->is_iterator() );
	}

	/**
	 * @covers Pods::stop_iterator
	 */
	public function test_method_stop_iterator() {
		$this->assertTrue( method_exists( $this->pod, 'stop_iterator' ), 'Method stop_iterator does not exist' );
		$this->setReflectionPropertyValue( $this->pod, 'iterator', true );
		$this->pod->stop_iterator();
		$this->assertFalse( $this->getReflectionPropertyValue( $this->pod, 'iterator' ) );
	}

	/**
	 * @covers Pods::rewind
	 */
	public function test_method_rewind_exists() {
		$this->assertTrue( method_exists( $this->pod, 'rewind' ), 'Method rewind does not exist' );
	}

	/**
	 * @covers  Pods::rewind
	 * @depends test_method_rewind_exists
	 */
	public function test_method_rewind() {
		$this->setReflectionPropertyValue( $this->pod, 'iterator', false );
		$this->assertNull( $this->pod->rewind(), 'Pods::rewind did not return null' );
		$this->assertEquals( 0, $this->getReflectionPropertyValue( $this->pod->data, 'row_number' ) );
	}

	/**
	 * @covers Pods::current
	 */
	public function test_method_current_exists() {
		$this->assertTrue( method_exists( $this->pod, 'current' ), 'Method current does not exist' );
	}

	/**
	 * Test current when iterator = false
	 * @covers  Pods::current
	 * @depends test_method_current_exists
	 */
	public function test_method_current_iterator_false() {
		$this->setReflectionPropertyValue( $this->pod, 'iterator', false );
		$this->assertFalse( $this->pod->current() );
	}

	/**
	 * Test current when iterator = true
	 * @covers  Pods::current
	 * @depends test_method_current_exists
	 */
	public function test_method_current_iterator_true() {
		$this->setReflectionPropertyValue( $this->pod, 'iterator', true );
		$this->assertFalse( $this->pod->current() );
	}

	/**
	 * @covers Pods::key
	 */
	public function test_method_key_exists() {
		$this->assertTrue( method_exists( $this->pod, 'key' ) );
	}

	/**
	 * Test key when iterator = false
	 * @covers  Pods::key
	 * @depends test_method_key_exists
	 */
	public function test_method_key_iterator_false() {
		$this->setReflectionPropertyValue( $this->pod, 'iterator', false );
		$this->setReflectionPropertyValue( $this->pod->data, 'row_number', 22 );
		$this->assertEquals( 22, $this->pod->key() );
	}

	/**
	 * Test current when iterator = true
	 * @covers  Pods::key
	 * @depends test_method_key_exists
	 */
	public function test_method_key() {
		$this->setReflectionPropertyValue( $this->pod, 'iterator', true );
		$this->setReflectionPropertyValue( $this->pod->data, 'row_number', 22 );
		$this->assertEquals( 22, $this->pod->key() );
	}

	/**
	 * @covers Pods::next
	 */
	public function test_method_next_exists() {
		$this->assertTrue( method_exists( $this->pod, 'next' ) );
	}

	/**
	 * Test next when iterator = false
	 * @covers  Pods::next
	 * @depends test_method_next_exists
	 */
	public function test_method_next_iterator_false() {
		$this->setReflectionPropertyValue( $this->pod, 'iterator', false );
		$this->setReflectionPropertyValue( $this->pod->data, 'row_number', 19 );
		$this->assertNull( $this->pod->next() );
		$this->assertEquals( 20, $this->getReflectionPropertyValue( $this->pod->data, 'row_number' ), 'The row number was not incremented correctly' );
	}

	/**
	 * Test next when iterator = true
	 * @covers  Pods::next
	 * @depends test_method_next_exists
	 */
	public function test_method_next() {
		$this->setReflectionPropertyValue( $this->pod, 'iterator', true );
		$this->setReflectionPropertyValue( $this->pod->data, 'row_number', 19 );
		$this->assertNull( $this->pod->next() );
		$this->assertEquals( 20, $this->getReflectionPropertyValue( $this->pod->data, 'row_number' ), 'The row number was not incremented correctly' );
	}

	/**
	 * @covers Pods::input
	 */
	public function test_method_exists_input() {
		$this->assertTrue( method_exists( $this->pod, 'input' ), 'Method input does not exist' );
	}

	/**
	 * Test input when field parameter is string and does not exist
	 * @covers  Pods::input
	 * @depends test_method_exists_input
	 */
	public function test_method_input_field_string_missing_field() {
		$this->expectOutputString( '' );
		$this->pod->input( 'foo' );
	}

	/**
	 * Test input when field parameter is empty array
	 * @covers  Pods::input
	 * @depends test_method_exists_input
	 */
	public function test_method_input_field_empty_array() {
		$this->expectOutputString( '' );
		$this->pod->input( array() );
	}

	/**
	 * @covers Pods::row
	 */
	public function test_method_exists_row() {
		$this->assertTrue( method_exists( $this->pod, 'row' ), 'Method row does not exist' );
	}

	/**
	 * @covers  Pods::row
	 * @depends test_method_exists_row
	 */
	public function test_method_row_false() {
		$this->assertFalse( $this->pod->row() );
	}

	/**
	 * @covers  Pods::row
	 * @depends test_method_exists_row
	 */
	public function test_method_row() {
		$this->setReflectionPropertyValue( $this->pod->data, 'row', array() );
		$this->assertInternalType( 'array', $this->pod->row() );
	}

	/**
	 * @covers Pods::data
	 */
	public function test_method_exists_data() {
		$this->assertTrue( method_exists( $this->pod, 'data' ), 'Method data does not exist' );
	}

	/**
	 * @covers  Pods::data
	 * @depends test_method_exists_data
	 */
	public function test_method_data_empty_rows() {
		$this->setReflectionPropertyValue( $this->pod->data, 'rows', array() );
		$this->assertFalse( $this->pod->data() );
	}

	/**
	 * @covers  Pods::data
	 * @depends test_method_exists_data
	 */
	public function test_method_data() {
		$this->setReflectionPropertyValue( $this->pod->data, 'rows', array( 'foo' => 'bar' ) );
		$this->assertEquals( array( 'foo' => 'bar' ), $this->pod->data() );
	}

	/**
	 * @covers Pods::__get
	 */
	public function test_method_exists_get() {
		$this->assertTrue( method_exists( $this->pod, '__get' ), 'Method __get does not exist' );
	}

	/**
	 * Test the get method when the property does exist in the deprecated class
	 * @covers  Pods::__get
	 * @depends test_method_exists_get
	 */
	public function test_method_get_deprecated_property() {
		$this->assertNull( $this->pod->datatype );
		$this->assertNull( $this->pod->datatype_id );

		$this->expectDeprecated();
	}

	/**
	 * @covers Pods::__call
	 */
	public function test_method_exists_call() {
		$this->assertTrue( method_exists( $this->pod, '__call' ), 'Method __call does not exist' );
	}

	/**
	 * Test the __call method when the called method does not exist in the deprecated class
	 * @covers  Pods::__call
	 * @depends test_method_exists_call
	 */
	public function test_method_call_method_does_not_exist() {
		$this->assertNull( $this->pod->__call( 'foo', array() ) );

		$this->expectDeprecated();
	}
}
