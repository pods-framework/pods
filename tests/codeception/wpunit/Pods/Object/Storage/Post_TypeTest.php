<?php

namespace Pods_Unit_Tests\Object;

use Pods_Unit_Tests\Pods_UnitTestCase;
use Pods_Object_Storage_Post_Type;
use Pods_Object_Collection;
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

	/**
	 * @var array
	 */
	private $parent_args;

	/**
	 * @var array
	 */
	private $group_args;

	/**
	 * @var array
	 */
	private $args;

	/**
	 * @var Pods_Object
	 */
	private $pods_object_parent;

	/**
	 * @var Pods_Object
	 */
	private $pods_object_group;

	/**
	 * @var Pods_Object
	 */
	private $pods_object;

	public function setUp() {
		$this->pods_object_storage_post_type = new Pods_Object_Storage_Post_Type();

		// Setup parent.
		$parent_args = array(
			'object_type' => 'pod',
			'id'          => 121,
			'name'        => 'test-parent',
			'label'       => 'Test parent',
			'description' => 'Testing parent',
		);

		$this->pods_object_parent = $this->setup_pods_object( $parent_args, 'parent' );

		// Setup group.
		$group_args = array(
			'object_type' => 'group',
			'id'          => 122,
			'name'        => 'test-group',
			'label'       => 'Test group',
			'description' => 'Testing group',
		);

		$this->pods_object_group = $this->setup_pods_object( $group_args, 'group' );

		// Setup object.
		$args = array(
			'object_type' => 'field',
			'id'          => 123,
			'name'        => 'test',
			'label'       => 'Test',
			'description' => 'Testing',
			'parent'      => $this->pods_object_parent->get_id(),
			'group'       => $this->pods_object_group->get_identifier(),
		);

		$this->pods_object = $this->setup_pods_object( $args );
	}

	public function tearDown() {
		unset( $this->pods_object_parent );
		unset( $this->pods_object_group );
		unset( $this->pods_object );

		$this->parent_args = array();
		$this->group_args  = array();
		$this->args        = array();

		Pods_Object_Collection::get_instance()->flush_objects();

		unset( $this->pods_object_storage_post_type );

		pods_api()->cache_flush_pods();
	}

	/**
	 * Setup and return a Pods_Object.
	 *
	 * @param array  $args Object arguments.
	 * @param string $type Object type.
	 *
	 * @return Pods_Object
	 */
	public function setup_pods_object( array $args = array(), $type = 'object' ) {
		$defaults = array(
			'object_type' => 'pod',
			'id'          => '',
			'name'        => '',
			'label'       => '',
			'description' => '',
			'parent'      => '',
			'group'       => '',
		);

		$args = array_merge( $defaults, $args );

		if ( 'parent' === $type ) {
			$this->parent_args = $args;
		} elseif ( 'group' === $type ) {
			$this->group_args = $args;
		} else {
			$this->args = $args;
		}

		$object_collection = Pods_Object_Collection::get_instance();

		$class_name = $object_collection->get_object_type( $args['object_type'] );

		/** @var Pods_Object $object */
		$object = new $class_name;
		$object->setup( $args );

		$object_collection->register_object( $object );

		return $object;
	}

	/**
	 * Setup WP Post from Pods_Object.
	 *
	 * @param Pods_Object $object
	 *
	 * @return int|\WP_Error
	 */
	public function setup_wp_post( Pods_Object $object ) {
		$args = array(
			'post_title'   => $object->get_arg( 'label' ),
			'post_name'    => $object->get_name(),
			'post_content' => $object->get_arg( 'description' ),
			'post_parent'  => $object->get_arg( 'parent' ),
			'post_type'    => '_pods_' . $object->get_object_type(),
		);

		$post_id = wp_insert_post( $args );

		$object->set_arg( 'id', $post_id );

		return $post_id;
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

	/**
	 * @covers Pods_Object_Storage_Post_Type::to_object
	 */
	public function test_to_object() {
		$this->assertTrue( method_exists( $this->pods_object_storage_post_type, 'to_object' ), 'Method to_object does not exist' );

		$parent_post_id = $this->setup_wp_post( $this->pods_object_parent );

		$this->setup_wp_post( $this->pods_object_group );

		// Update object ID to match.
		$this->pods_object->set_arg( 'parent', $parent_post_id );

		$post_id = $this->setup_wp_post( $this->pods_object );

		update_post_meta( $post_id, 'group', $this->pods_object_group->get_identifier() );

		$post = get_post( $post_id );

		$to = $this->pods_object_storage_post_type->to_object( $post );

		$this->assertInstanceOf( Pods_Object::class, $to );
		$this->assertEquals( $this->pods_object->get_object_type(), $to->get_object_type() );
		$this->assertEquals( $this->pods_object->get_id(), $to->get_id() );
		$this->assertEquals( $this->pods_object->get_name(), $to->get_name() );
		$this->assertEquals( $this->pods_object->get_parent(), $to->get_parent() );
		$this->assertEquals( $this->pods_object->get_group(), $to->get_group() );
	}

}
