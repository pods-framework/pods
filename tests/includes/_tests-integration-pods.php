<?php
/**
 * @package Pods
 * @category Tests
 */
namespace Pods_Integration_Tests;

/**
 * @group pods
 */
class Test_Pods extends \Pods_Unit_Tests\Pods_UnitTestCase {

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
	 * @var int
	 * @since 3.0
	 */
	private $pod_id;

	public function setUp() {
		//Create a pod
		$this->pod_id = pods_api()->save_pod( array( 'storage' => 'table', 'type' => 'pod', 'name' => 'foo' ) );
		$this->pod    = pods( 'foo' );

		//register the fields
		$params = array(
			'pod'    => 'foo',
			'pod_id' => $this->pod_id,
			'name'   => 'description',
			'type'   => 'text'
		);
		$this->fields['description']['id'] = pods_api()->save_field( $params );

		$params = array(
			'pod'    => 'foo',
			'pod_id' => $this->pod_id,
			'name'   => 'start_date',
			'type'   => 'datetime'
		);
		$this->fields['start_date']['id'] = pods_api()->save_field( $params );

		//add item
		$this->item = $this->pod->save( array( 'name' => 'Sample Event', 'description' => 'My first event',  'start_date' => 'May 5, 2014 11:00 PM' ) );
	}

	public function tearDown() {
		pods_api()->delete_pod( array( 'id' => $this->pod_id ) );
		unset( $this->pod );
	}

	/**
	 * @covers Pods::valid
	 * @since  3.0
	 */
	public function test_method_valid() {
		$this->assertTrue( $this->pod->valid() );
	}

	/**
	 * @covers Pods::exists
	 * @uses   ::pods
	 * @since  3.0
	 */
	public function test_method_exists() {
		$pod = pods( 'foo', $this->item );
		$this->assertTrue( $pod->exists(), __( 'Item %s does not exist', 'pods' ) );
	}

	/**
	 * @covers Pods::row
	 * uses    ::pods
	 * @since  3.0
	 */
	public function test_method_row() {
		$pod = pods( 'foo', $this->item );
		$row = $pod->row();
		$this->assertInternalType( 'array', $row, __( 'Pods::row did not return an array' ) );
	}

	/**
	 * @covers Pods::find
	 * @since  3.0
	 */
	public function test_method_find() {
		$pod = $this->pod->find();
		$this->assertInstanceOf( 'pods', $pod );
	}

	/**
	 * @covers Pods::fetch
	 * @since  3.0
	 */
	public function test_method_fetch() {
		$this->assertInternalType( 'array', $this->pod->fetch() );
	}

	/**
	 * @covers Pods::display
	 * @uses   Pods::fetch
	 * @since  3.0
	 */
	public function test_method_display() {
		$this->pod->fetch();
		$this->assertEquals( 'Sample Event',         $this->pod->display( 'name' ) );
		$this->assertEquals( 'My first event',       $this->pod->display( 'description' ) );
		$this->assertEquals( 'May 5, 2014 11:00 PM', $this->pod->display( 'start_date' ) );
	}

	/**
	 * @covers Pods::save
	 * since   3.0
	 */
	public function test_method_save()
	{
		$foo = $this->pod->save( 'bar', 'baz');
		$this->assertInternalType( 'int', $foo );
	}
}
