<?php

namespace Pods_Unit_Tests\Pods\Whatsit;

use Pods_Unit_Tests\Pods_UnitTestCase;
use Pods\Whatsit\Storage;
use Pods\Whatsit;

/**
 * @group  pods-whatsit
 * @group  pods-whatsit-storage
 * @covers Storage
 */
class StorageTest extends Pods_UnitTestCase {

	/**
	 * @var Storage
	 */
	private $pods_object_storage;

	public function setUp(): void {
		$this->pods_object_storage = $this->getMockBuilder( Storage::class )->getMockForAbstractClass();
	}

	public function tearDown(): void {
		unset( $this->pods_object_storage );
	}

	/**
	 * Setup and return a Whatsit.
	 *
	 * @param array $args Object arguments.
	 *
	 * @return Whatsit
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

		/** @var Whatsit $object */
		$object = $this->getMockBuilder( Whatsit::class )->getMockForAbstractClass();
		$object->setup( $this->args );

		return $object;
	}

	/**
	 * @covers Storage::get_object_storage_type
	 */
	public function test_get_object_storage_type() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'get_object_storage_type' ), 'Method get_object_storage_type does not exist' );

		$this->assertEquals( '', $this->pods_object_storage->get_object_storage_type() );
	}

	/**
	 * @covers Storage::get
	 */
	public function test_get() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'get' ), 'Method get does not exist' );

		$this->assertNull( $this->pods_object_storage->get() );
	}

	/**
	 * @covers Storage::find
	 */
	public function test_find() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'find' ), 'Method find does not exist' );

		$this->assertEquals( array(), $this->pods_object_storage->find() );
	}

	/**
	 * @covers Storage::add
	 */
	public function test_add() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'add' ), 'Method add does not exist' );

		$object = $this->setup_pods_object();

		$this->assertFalse( $this->pods_object_storage->add( $object ) );
	}

	/**
	 * @covers Storage::save
	 */
	public function test_save() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'save' ), 'Method save does not exist' );

		$object = $this->setup_pods_object();

		$this->assertFalse( $this->pods_object_storage->save( $object ) );
	}

	/**
	 * @covers Storage::save_args
	 */
	public function test_save_args() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'save_args' ), 'Method save_args does not exist' );

		$object = $this->setup_pods_object();

		$this->assertFalse( $this->pods_object_storage->save_args( $object ) );
	}

	/**
	 * @covers Storage::duplicate
	 */
	public function test_duplicate() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'duplicate' ), 'Method duplicate does not exist' );

		$object = $this->setup_pods_object();

		$this->assertFalse( $this->pods_object_storage->duplicate( $object ) );
	}

	/**
	 * @covers Storage::delete
	 */
	public function test_delete() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'delete' ), 'Method delete does not exist' );

		$object = $this->setup_pods_object();

		$this->assertFalse( $this->pods_object_storage->delete( $object ) );
	}

	/**
	 * @covers Storage::reset
	 */
	public function test_reset() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'reset' ), 'Method reset does not exist' );

		$object = $this->setup_pods_object();

		$this->assertFalse( $this->pods_object_storage->reset( $object ) );
	}

}
