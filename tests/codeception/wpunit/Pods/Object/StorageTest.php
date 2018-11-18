<?php

namespace Pods_Unit_Tests\Object;

use Pods_Unit_Tests\Pods_UnitTestCase;
use Pods_Object_Storage;
use Pods_Object;

/**
 * @group  pods_object
 * @covers Pods_Object_Storage
 */
class StorageTest extends Pods_UnitTestCase {

	/**
	 * @var Pods_Object_Storage
	 */
	private $pods_object_storage;

	public function setUp() {
		$this->pods_object_storage = $this->getMockBuilder( Pods_Object_Storage::class )->getMockForAbstractClass();
	}

	public function tearDown() {
		unset( $this->pods_object_storage );
	}

	/**
	 * @covers Pods_Object_Storage::get
	 */
	public function test_get() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'get' ), 'Method get does not exist' );

		$this->assertNull( $this->pods_object_storage->get() );
	}

	/**
	 * @covers Pods_Object_Storage::find
	 */
	public function test_find() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'find' ), 'Method find does not exist' );

		$this->assertEquals( array(), $this->pods_object_storage->find() );
	}

	/**
	 * @covers Pods_Object_Storage::add
	 */
	public function test_add() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'add' ), 'Method add does not exist' );

		$this->assertFalse( $this->pods_object_storage->add() );
	}

	/**
	 * @covers Pods_Object_Storage::save
	 */
	public function test_save() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'save' ), 'Method save does not exist' );

		$this->assertFalse( $this->pods_object_storage->save() );
	}

	/**
	 * @covers Pods_Object_Storage::duplicate
	 */
	public function test_duplicate() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'duplicate' ), 'Method duplicate does not exist' );

		$this->assertFalse( $this->pods_object_storage->duplicate() );
	}

	/**
	 * @covers Pods_Object_Storage::delete
	 */
	public function test_delete() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'delete' ), 'Method delete does not exist' );

		$this->assertFalse( $this->pods_object_storage->delete() );
	}

	/**
	 * @covers Pods_Object_Storage::reset
	 */
	public function test_reset() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'reset' ), 'Method reset does not exist' );

		$this->assertFalse( $this->pods_object_storage->reset() );
	}

}
