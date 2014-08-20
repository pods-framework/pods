<?php
namespace Pods_Unit_Tests;

/**
 * @group pods
 */
class Test_Pods extends Pods_UnitTestCase {

	/**
	 * The pods system under test
	 * 
	 * @var   \Pods
	 * @since 3.0 
	 */
	private $pods;

	public function setUp() {
		$this->pods = new \Pods();
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
	 * @sincd  3.0
	 */
	public function test_method_exists_false() {
		$this->assertFalse( $this->pods->exists() );
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
}
