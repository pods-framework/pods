<?php
namespace Pods_Unit_Tests\Data_Functions\pods_v;
use Mockery;

/**
 * @group  pods_data_functions
 * @covers ::pods_v
 */
class Test_Pods_V extends \Pods_Unit_Tests\Pods_UnitTestCase {
	/**
	 * @covers ::pods_v
	 */
	public function test_method_pods_v_exists() {
		if ( version_compare( PODS_VERSION, '2.3.10', '>=' ) ) {
			$this->assertTrue( function_exists( 'pods_v' ), 'Function ::pods_v does not exist' );
		}

		if( version_compare( PODS_VERSION, '2.3.10', '<' ) ) {
			$this-markTestSkipped( sprintf( 'Function ::pods_v not available in PODS version %s'), PODS_VERSION );
		}
	}

	/**
	 * @depends test_method_pods_v_exists
	 */
	public function test_pods_v_returns_default_value() {
		$this->assertEquals( 'bar', pods_v( 'foo', null, 'bar' ) );
	}

	/**
	 * @depends test_method_pods_v_exists
	 */
	public function test_pods_v_returns_default_value_when_type_is_string() {
		$this->assertEquals( 'baz', pods_v( 'foo', 'bar', 'baz' ) );
	}

	/**
	 * @depends test_method_pods_v_exists
	 */
	public function test_pods_v_type_returns_default_value_when_type_is_object() {
		$object = new \StdClass;
		$object->foobar = 'foobaz';
		$this->assertEquals( 'bar', pods_v( 'foo', $object, 'bar' ) );
	}

	/**
	 * @depends test_method_pods_v_exists
	 * @uses    ::pods_unslash
	 */
	public function test_pods_v_type_get() {
		$_GET['foo'] = 'bar';
		$this->assertEquals( 'bar', pods_v( 'foo', 'get' ) );
	}

	/**
	 * @depends test_method_pods_v_exists
	 * @uses    ::pods_unslash
	 */
	public function test_pods_v_type_post() {
		$_POST['foo'] = 'bar';
		$this->assertEquals( 'bar', pods_v( 'foo', 'post' ) );
	}

	/**
	 * @depends test_method_pods_v_exists
	 * @uses    ::pods_unslash
	 */
	public function test_pods_v_type_request() {
		$_REQUEST['foo'] = 'bar';
		$this->assertEquals( 'bar', pods_v( 'foo', 'request' ) );
	}

	/**
	 * @depends test_method_pods_v_exists
	 */
	public function test_pods_v_returns_default_value_when_type_is_array() {
		$this->assertEquals( 'baz', pods_v( 'foo', array( 'foobar' => 'foobaz' ), 'baz' ) );
	}

	/**
	 * @depends test_method_pods_v_exists
	 */
	public function test_pods_v_type_is_array() {
		$this->assertEquals( 'bar', pods_v( 'foo', array( 'foo' => 'bar', 'foobar' => 'foobaz' ) ) );
		$this->assertEquals( 'foobaz', pods_v( 'foobar', array( 'foo' => 'bar', 'foobar' => 'foobaz' ) ) );
	}

	/**
	 * @depends test_method_pods_v_exists
	 */
	public function test_pods_v_type_is_object() {
		$object = new \StdClass;
		$object->foo = 'bar';
		$this->assertEquals( 'bar', pods_v( 'foo', $object ) );
	}
}
