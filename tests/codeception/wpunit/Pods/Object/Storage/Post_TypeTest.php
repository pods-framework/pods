<?php

namespace Pods_Unit_Tests\Object;

use Pods_Unit_Tests\Pods_UnitTestCase;
use Pods_Object_Storage_Post_Type;
use Pods_Object;
use WP_Post;

/**
 * @group  pods-object
 * @group  pods-object-storage
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
	 * Setup and return a Pods_Object.
	 *
	 * @param array $args Object arguments.
	 *
	 * @return Pods_Object
	 */
	public function setup_pods_object( array $args = array() ) {
		$defaults = array(
			'id'          => 123,
			'name'        => 'test',
			'label'       => 'Test',
			'description' => 'Testing',
			'parent'      => '',
			'group'       => '',
			'custom1'     => 'value1',
		);

		$this->args = array_merge( $defaults, $args );

		/** @var Pods_Object $object */
		$object = $this->getMockBuilder( Pods_Object::class )->getMockForAbstractClass();
		$object->setup( $this->args );

		return $object;
	}

	/**
	 * @covers Pods_Object_Storage_Post_Type::get_storage_type
	 */
	public function test_get_storage_type() {
		$this->assertTrue( method_exists( $this->pods_object_storage_post_type, 'get_storage_type' ), 'Method get_storage_type does not exist' );

		$this->assertEquals( 'post_type', $this->pods_object_storage_post_type->get_storage_type() );
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

		$object = $this->setup_pods_object();

		$object->set_arg( 'id', null );

		$id = $this->pods_object_storage_post_type->add( $object );

		$this->assertInternalType( 'integer', $id );
		$this->assertInstanceOf( WP_Post::class, get_post( $id ) );
	}

	/**
	 * @covers Pods_Object_Storage_Post_Type::save
	 */
	public function test_save() {
		$this->assertTrue( method_exists( $this->pods_object_storage_post_type, 'save' ), 'Method save does not exist' );

		$object = $this->setup_pods_object();

		$new_id = $this->pods_object_storage_post_type->add( $object );

		$object->set_arg( 'id', $new_id );
		$object->set_arg( 'label', 'New label' );

		$id = $this->pods_object_storage_post_type->save( $object );

		$this->assertInternalType( 'integer', $id );
		$this->assertInstanceOf( WP_Post::class, get_post( $id ) );
	}

	/**
	 * @covers Pods_Object_Storage::save_args
	 */
	public function test_save_args() {
		$this->assertTrue( method_exists( $this->pods_object_storage_post_type, 'save_args' ), 'Method save_args does not exist' );

		$object = $this->setup_pods_object();

		$new_id = $this->pods_object_storage_post_type->add( $object );

		$object->set_arg( 'id', $new_id );

		$this->assertTrue( $this->pods_object_storage_post_type->save_args( $object ) );
		$this->assertEquals( $object->get_arg( 'group' ), get_post_meta( $new_id, 'group', true ) );
		$this->assertEquals( $object->get_arg( 'custom1' ), get_post_meta( $new_id, 'custom1', true ) );
	}

	/**
	 * @covers Pods_Object_Storage_Post_Type::duplicate
	 */
	public function test_duplicate() {
		$this->assertTrue( method_exists( $this->pods_object_storage_post_type, 'duplicate' ), 'Method duplicate does not exist' );

		$object = $this->setup_pods_object();

		$new_id = $this->pods_object_storage_post_type->add( $object );

		$object->set_arg( 'id', $new_id );

		$id = $this->pods_object_storage_post_type->duplicate( $object );

		$this->assertInternalType( 'integer', $id );
		$this->assertInstanceOf( WP_Post::class, get_post( $id ) );
	}

	/**
	 * @covers Pods_Object_Storage_Post_Type::delete
	 */
	public function test_delete() {
		$this->assertTrue( method_exists( $this->pods_object_storage_post_type, 'delete' ), 'Method delete does not exist' );

		$object = $this->setup_pods_object();

		$new_id = $this->pods_object_storage_post_type->add( $object );

		$object->set_arg( 'id', $new_id );

		$this->assertTrue( $this->pods_object_storage_post_type->delete( $object ) );
	}

	/**
	 * @covers Pods_Object_Storage_Post_Type::reset
	 */
	public function test_reset() {
		$this->assertTrue( method_exists( $this->pods_object_storage_post_type, 'reset' ), 'Method reset does not exist' );

		$object = $this->setup_pods_object();

		$this->assertFalse( $this->pods_object_storage_post_type->reset( $object ) );
	}

}
