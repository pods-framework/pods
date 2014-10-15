<?php
namespace Pods_Unit_Tests\Data_Functions;
use Mockery;

/**
 * Test all other data functions not covered by individual classes in this directory ( e.g. pods_v )
 *
 * @group  pods_data_functions
 */
class Test_Pods_Data_Functions extends \Pods_Unit_Tests\Pods_UnitTestCase {
	/**
	 * @covers ::pods_unslash
	 */
	public function test_method_exists_pods_unslash() {
		$this->assertTrue( function_exists( 'pods_unslash' ) , 'Function ::pods_unslash does not exist.');
	}

	/**
	 * @covers  ::pods_unslash
	 * @depends test_method_exists_pods_unslash
	 */
	public function test_method_pods_unslash_empty_string() {
		$this->assertEquals( '', pods_unslash( '' ) );
	}

	/**
	 * @covers  ::pods_unslash
	 * @depends test_method_exists_pods_unslash
	 */
	public function test_method_pods_unslash_integer() {
		$this->assertEquals( 1, pods_unslash( (int) 1 ) );
	}

	/**
	 * @covers  ::pods_unslash
	 * @depends test_method_exists_pods_unslash
	 */
	public function test_method_pods_unslash_float() {
		$this->assertEquals( 3.1415, pods_unslash( (float) 3.1415 ) );
	}

	/**
	 * @covers  ::pods_unslash
	 * @depends test_method_exists_pods_unslash
	 */
	public function test_method_pods_unslash_null() {
		$this->assertEquals( null, pods_unslash( null ) );
	}

	/**
	 * @covers  ::pods_unslash
	 * @depends test_method_exists_pods_unslash
	 */
	public function test_method_pods_unslash_string() {
		$this->assertEquals( "Is your name O'reilly?", pods_unslash( 'Is your name O\'reilly?' ) );
	}

	/**
	 * @covers  ::pods_unslash
	 * @depends test_method_exists_pods_unslash
	 */
	public function test_method_pods_unslash_array() {
		$this->assertEquals( array( "Is your name O'reilly?", "Person's Name" ), pods_unslash( array( 'Is your name O\'reilly?', 'Person\'s Name' ) ) );
	}

	/**
	 * @covers  ::pods_unslash
	 * @depends test_method_exists_pods_unslash
	 */
	public function test_method_pods_unslash_object() {
		$object      = new \StdClass;
		$object->foo = 'Is your name O\'reilly?';
		$object->bar = 'Person\'s Name';
		
		$expected      = new \StdClass;
		$expected->foo = "Is your name O'reilly?";
		$expected->bar = "Person's Name";

		$this->assertEquals( $expected, pods_unslash( $object ) );
	}
}
