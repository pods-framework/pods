<?php
namespace Pods_Unit_Tests;

/**
 * @group pods
 */
class Test_Pods extends Pods_UnitTestCase {

	/**
	 * The pod name
	 *
	 * @var string
	 * @since 3.0
	 */
	private $pod_name = 'foo';

	/**
	 * The id of the test pods
	 *
	 * @var string
	 * @since 3.0
	 */
	private $pod_id;

	public function setUp() {
		//Create a pod
		$this->pod_id = pods_api()->save_pod( array( 'storage' => 'table', 'type' => 'post_type', 'name' => 'foo' ) );
		$this->pod = pods( 'foo' );
	}

	public function tearDown() {
		unset( $this->pod );
	}

	/**
	 * Test the save method when passing no parameters
	 *
	 * @covers Pods::save
	 * @since  3.0
	 */
	public function test_method_save_no_params() {
		$this->assertNull( $this->pod->save() );
	}

	/**
	 * @covers Pods::save
	 * since   3.0
	 */
	public function test_method_save()
	{
		$foo = @$this->pod->save( 'bar', 'baz');
		$this->assertInternalType( 'int', $foo );
	}

	/**
	 * @covers Pods::valid
	 * @since  3.0
	 */
	public function test_method_valid() {
		$this->assertTrue( $this->pod->valid() );
	}
}
