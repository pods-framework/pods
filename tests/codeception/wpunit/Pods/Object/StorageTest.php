<?php

namespace Pods_Unit_Tests\Object;

use Pods_Unit_Tests\Pods_UnitTestCase;
use Pods__Object__Storage;
use Pods__Object;

/**
 * @group  pods-object
 * @group  pods-object-storage
 * @covers Pods__Object__Storage
 */
class StorageTest extends Pods_UnitTestCase {

	/**
	 * @var Pods__Object__Storage
	 */
	private $pods_object_storage;

	public function setUp() {
		$this->pods_object_storage = $this->getMockBuilder( Pods__Object__Storage::class )->getMockForAbstractClass();
	}

	public function tearDown() {
		unset( $this->pods_object_storage );
	}

	/**
	 * Setup and return a Pods__Object.
	 *
	 * @param array $args Object arguments.
	 *
	 * @return Pods__Object
	 */
	public function setup_pods_object( array $args = array() ) {
		$defaults = array(
			'id'          => 123,
			'name'        => 'test',
			'label'       => 'Test',
			'description' => 'Testing',
			'parent'      => '',
			'group'       => '',
		);

		$this->args = array_merge( $defaults, $args );

		/** @var Pods__Object $object */
		$object = $this->getMockBuilder( Pods__Object::class )->getMockForAbstractClass();
		$object->setup( $this->args );

		return $object;
	}

	/**
	 * @covers Pods__Object__Storage::get_storage_type
	 */
	public function test_get_storage_type() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'get_storage_type' ), 'Method get_storage_type does not exist' );

		$this->assertEquals( '', $this->pods_object_storage->get_storage_type() );
	}

	/**
	 * @covers Pods__Object__Storage::get
	 */
	public function test_get() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'get' ), 'Method get does not exist' );

		$this->assertNull( $this->pods_object_storage->get() );
	}

	/**
	 * @covers Pods__Object__Storage::find
	 */
	public function test_find() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'find' ), 'Method find does not exist' );

		$this->assertEquals( array(), $this->pods_object_storage->find() );
	}

	/**
	 * @covers Pods__Object__Storage::add
	 */
	public function test_add() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'add' ), 'Method add does not exist' );

		$object = $this->setup_pods_object();

		$this->assertFalse( $this->pods_object_storage->add( $object ) );
	}

	/**
	 * @covers Pods__Object__Storage::save
	 */
	public function test_save() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'save' ), 'Method save does not exist' );

		$object = $this->setup_pods_object();

		$this->assertFalse( $this->pods_object_storage->save( $object ) );
	}

	/**
	 * @covers Pods__Object__Storage::save_args
	 */
	public function test_save_args() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'save_args' ), 'Method save_args does not exist' );

		$object = $this->setup_pods_object();

		$this->assertFalse( $this->pods_object_storage->save_args( $object ) );
	}

	/**
	 * @covers Pods__Object__Storage::duplicate
	 */
	public function test_duplicate() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'duplicate' ), 'Method duplicate does not exist' );

		$object = $this->setup_pods_object();

		$this->assertFalse( $this->pods_object_storage->duplicate( $object ) );
	}

	/**
	 * @covers Pods__Object__Storage::delete
	 */
	public function test_delete() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'delete' ), 'Method delete does not exist' );

		$object = $this->setup_pods_object();

		$this->assertFalse( $this->pods_object_storage->delete( $object ) );
	}

	/**
	 * @covers Pods__Object__Storage::reset
	 */
	public function test_reset() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'reset' ), 'Method reset does not exist' );

		$object = $this->setup_pods_object();

		$this->assertFalse( $this->pods_object_storage->reset( $object ) );
	}

}
