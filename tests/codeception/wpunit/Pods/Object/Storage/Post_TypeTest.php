<?php

namespace Pods_Unit_Tests\Object;

use Pods_Unit_Tests\Pods_UnitTestCase;
use Pods_Object_Storage_Post_Type;
use Pods_Object;

/**
 * @group  pods_object
 * @covers Pods_Object_Storage_Post_Type
 */
class Post_TypeTest extends Pods_UnitTestCase {

	/**
	 * @var Pods_Object_Storage_Post_Type
	 */
	private $pods_object_storage_post_type;

	public function setUp() {
		$this->pods_object_storage_post_type = new Pods_Object_Storage_Post_Type();
	}

	public function tearDown() {
		unset( $this->pods_object_storage_post_type );
	}

	/**
	 * @covers Pods_Object_Storage_Post_Type::get
	 */
	public function test_get() {
		$this->assertTrue( method_exists( $this->pods_object_storage_post_type, 'get' ), 'Method get does not exist' );

		$this->assertNull( $this->pods_object_storage_post_type->get() );
	}

	/**
	 * @covers Pods_Object_Storage_Post_Type::find
	 */
	public function test_find() {
		$this->assertTrue( method_exists( $this->pods_object_storage_post_type, 'find' ), 'Method find does not exist' );

		$this->assertEquals( array(), $this->pods_object_storage_post_type->find() );
	}

	/**
	 * @covers Pods_Object_Storage_Post_Type::add
	 */
	public function test_add() {
		$this->assertTrue( method_exists( $this->pods_object_storage_post_type, 'add' ), 'Method add does not exist' );

		$this->assertFalse( $this->pods_object_storage_post_type->add() );
	}

	/**
	 * @covers Pods_Object_Storage_Post_Type::save
	 */
	public function test_save() {
		$this->assertTrue( method_exists( $this->pods_object_storage_post_type, 'save' ), 'Method save does not exist' );

		$this->assertFalse( $this->pods_object_storage_post_type->save() );
	}

	/**
	 * @covers Pods_Object_Storage_Post_Type::duplicate
	 */
	public function test_duplicate() {
		$this->assertTrue( method_exists( $this->pods_object_storage_post_type, 'duplicate' ), 'Method duplicate does not exist' );

		$this->assertFalse( $this->pods_object_storage_post_type->duplicate() );
	}

	/**
	 * @covers Pods_Object_Storage_Post_Type::delete
	 */
	public function test_delete() {
		$this->assertTrue( method_exists( $this->pods_object_storage_post_type, 'delete' ), 'Method delete does not exist' );

		$this->assertFalse( $this->pods_object_storage_post_type->delete() );
	}

	/**
	 * @covers Pods_Object_Storage_Post_Type::reset
	 */
	public function test_reset() {
		$this->assertTrue( method_exists( $this->pods_object_storage_post_type, 'reset' ), 'Method reset does not exist' );

		$this->assertFalse( $this->pods_object_storage_post_type->reset() );
	}

}
