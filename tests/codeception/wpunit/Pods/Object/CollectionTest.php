<?php

namespace Pods_Unit_Tests\Object;

use Pods_Unit_Tests\Pods_UnitTestCase;
use Pods_Object_Collection;
use Pods_Object;

/**
 * @group  pods-object
 * @group  pods-object-collection
 * @covers Pods_Object_Collection
 */
class CollectionTest extends Pods_UnitTestCase {

	/**
	 * @var array
	 */
	private $args;

	/**
	 * @var Pods_Object_Collection
	 */
	private $pods_object_collection;

	public function setUp() {
		if ( ! class_exists( 'Pods_Object_Custom' ) ) {
			eval( 'class Pods_Object_Custom extends Pods_Object { public $type = "custom"; }' );
		}

		if ( ! class_exists( 'Pods_Object_Storage_Custom' ) ) {
			eval( 'class Pods_Object_Storage_Custom extends Pods_Object_Storage { public $type = "custom"; }' );
		}

		$this->pods_object_collection = Pods_Object_Collection::get_instance();
	}

	public function tearDown() {
		Pods_Object_Collection::destroy();
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
			'object_type' => 'object', // For test reference.
			'id'          => 123,
			'name'        => 'test',
			'label'       => 'Test',
			'description' => 'Testing',
			'parent'      => '',
			'group'       => '',
		);

		$this->args = array_merge( $defaults, $args );

		/** @var Pods_Object $object */
		$object = $this->getMockBuilder( Pods_Object::class )->getMockForAbstractClass();
		$object->setup( $this->args );

		return $object;
	}

	/**
	 * @covers Pods_Object_Collection::get_instance
	 */
	public function test_get_instance() {
		$this->assertTrue( method_exists( Pods_Object_Collection::class, 'get_instance' ), 'Method get_instance does not exist' );

		$this->assertInstanceOf( Pods_Object_Collection::class, Pods_Object_Collection::get_instance() );
	}

	/**
	 * @covers Pods_Object_Collection::destroy
	 */
	public function test_destroy() {
		$this->assertTrue( method_exists( Pods_Object_Collection::class, 'destroy' ), 'Method destroy does not exist' );

		$this->pods_object_collection->register_object_type( 'custom', 'Pods_Object_Custom' );
		$this->pods_object_collection->register_storage_type( 'custom', 'Pods_Object_Storage_Custom' );
		$this->pods_object_collection->register_object( $this->setup_pods_object() );

		$this->assertCount( 1, $this->pods_object_collection->get_object_types() );
		$this->assertCount( 1, $this->pods_object_collection->get_storage_types() );
		$this->assertCount( 1, $this->pods_object_collection->get_objects() );

		Pods_Object_Collection::destroy();

		$this->pods_object_collection = Pods_Object_Collection::get_instance();

		$this->assertCount( 0, $this->pods_object_collection->get_object_types() );
		$this->assertCount( 0, $this->pods_object_collection->get_storage_types() );
		$this->assertCount( 0, $this->pods_object_collection->get_objects() );
	}

	/**
	 * @covers Pods_Object_Collection::register_object_type
	 * @covers Pods_Object_Collection::get_object_types
	 */
	public function test_register_object_type() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'register_object_type' ), 'Method register_object_type does not exist' );

		$this->pods_object_collection->register_object_type( 'custom', 'Pods_Object_Custom' );

		$this->assertCount( 1, $this->pods_object_collection->get_object_types() );
	}

	/**
	 * @covers Pods_Object_Collection::unregister_object_type
	 * @covers Pods_Object_Collection::register_object_type
	 * @covers Pods_Object_Collection::get_object_types
	 */
	public function test_unregister_object_type() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'unregister_object_type' ), 'Method unregister_object_type does not exist' );

		$this->pods_object_collection->register_object_type( 'custom', 'Pods_Object_Custom' );

		$this->assertCount( 1, $this->pods_object_collection->get_object_types() );

		$this->assertTrue( $this->pods_object_collection->unregister_object_type( 'custom' ) );

		$this->assertCount( 0, $this->pods_object_collection->get_object_types() );

		$this->assertFalse( $this->pods_object_collection->unregister_object_type( 'nope' ) );
	}

	/**
	 * @covers Pods_Object_Collection::flush_object_types
	 * @covers Pods_Object_Collection::register_object_type
	 * @covers Pods_Object_Collection::get_object_types
	 */
	public function test_flush_object_types() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'flush_object_types' ), 'Method flush_object_types does not exist' );

		$this->pods_object_collection->register_object_type( 'custom', 'Pods_Object_Custom' );

		$this->assertCount( 1, $this->pods_object_collection->get_object_types() );

		$this->pods_object_collection->flush_object_types();

		$this->assertCount( 0, $this->pods_object_collection->get_object_types() );
	}

	/**
	 * @covers Pods_Object_Collection::get_object_types
	 * @covers Pods_Object_Collection::register_object_type
	 */
	public function test_get_object_types() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'get_object_types' ), 'Method get_object_types does not exist' );

		$this->pods_object_collection->register_object_type( 'custom', 'Pods_Object_Custom' );

		$this->assertCount( 1, $this->pods_object_collection->get_object_types() );
	}

	/**
	 * @covers Pods_Object_Collection::get_object_type
	 * @covers Pods_Object_Collection::register_object_type
	 */
	public function test_get_object_type() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'get_object_type' ), 'Method get_object_type does not exist' );

		$this->pods_object_collection->register_object_type( 'custom', 'Pods_Object_Custom' );

		$this->assertEquals( 'Pods_Object_Custom', $this->pods_object_collection->get_object_type( 'custom' ) );
	}

	/**
	 * @covers Pods_Object_Collection::register_storage_type
	 * @covers Pods_Object_Collection::get_storage_types
	 */
	public function test_register_storage_type() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'register_storage_type' ), 'Method register_storage_type does not exist' );

		$this->pods_object_collection->register_storage_type( 'custom', 'Pods_Object_Storage_Custom' );

		$this->assertCount( 1, $this->pods_object_collection->get_storage_types() );
	}

	/**
	 * @covers Pods_Object_Collection::unregister_storage_type
	 * @covers Pods_Object_Collection::register_storage_type
	 * @covers Pods_Object_Collection::get_storage_types
	 */
	public function test_unregister_storage_type() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'unregister_storage_type' ), 'Method unregister_storage_type does not exist' );

		$this->pods_object_collection->register_storage_type( 'custom', 'Pods_Object_Storage_Custom' );

		$this->assertCount( 1, $this->pods_object_collection->get_storage_types() );

		$this->assertTrue( $this->pods_object_collection->unregister_storage_type( 'custom' ) );

		$this->assertCount( 0, $this->pods_object_collection->get_storage_types() );

		$this->assertFalse( $this->pods_object_collection->unregister_storage_type( 'nope' ) );
	}

	/**
	 * @covers Pods_Object_Collection::flush_storage_types
	 * @covers Pods_Object_Collection::register_storage_type
	 * @covers Pods_Object_Collection::get_storage_types
	 */
	public function test_flush_storage_types() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'flush_storage_types' ), 'Method flush_storage_types does not exist' );

		$this->pods_object_collection->register_storage_type( 'custom', 'Pods_Object_Storage_Custom' );

		$this->assertCount( 1, $this->pods_object_collection->get_storage_types() );

		$this->pods_object_collection->flush_storage_types();

		$this->assertCount( 0, $this->pods_object_collection->get_storage_types() );
	}

	/**
	 * @covers Pods_Object_Collection::get_storage_types
	 * @covers Pods_Object_Collection::register_storage_type
	 */
	public function test_get_storage_types() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'get_storage_types' ), 'Method get_storage_types does not exist' );

		$this->pods_object_collection->register_storage_type( 'custom', 'Pods_Object_Storage_Custom' );

		$this->assertCount( 1, $this->pods_object_collection->get_storage_types() );
	}

	/**
	 * @covers Pods_Object_Collection::get_storage_type
	 * @covers Pods_Object_Collection::register_storage_type
	 */
	public function test_get_storage_type() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'get_storage_type' ), 'Method get_storage_type does not exist' );

		$this->pods_object_collection->register_storage_type( 'custom', 'Pods_Object_Storage_Custom' );

		$this->assertEquals( 'Pods_Object_Storage_Custom', $this->pods_object_collection->get_storage_type( 'custom' ) );
	}

	/**
	 * @covers Pods_Object_Collection::get_storage_object
	 * @covers Pods_Object_Collection::register_storage_type
	 */
	public function test_get_storage_object() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'get_storage_object' ), 'Method get_storage_object does not exist' );

		$this->pods_object_collection->register_storage_type( 'custom', 'Pods_Object_Storage_Custom' );

		$this->assertInstanceOf( 'Pods_Object_Storage_Custom', $this->pods_object_collection->get_storage_object( 'custom' ) );
	}

	/**
	 * @covers Pods_Object_Collection::register_object
	 * @covers Pods_Object_Collection::get_objects
	 */
	public function test_register_object() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'register_object' ), 'Method register_object does not exist' );

		$object = $this->setup_pods_object();

		$this->pods_object_collection->register_object( $object );

		$this->assertCount( 1, $this->pods_object_collection->get_objects() );
	}

	/**
	 * @covers Pods_Object_Collection::unregister_object
	 * @covers Pods_Object_Collection::register_object
	 * @covers Pods_Object_Collection::get_objects
	 */
	public function test_unregister_object() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'unregister_object' ), 'Method unregister_object does not exist' );

		$object = $this->setup_pods_object();

		$this->pods_object_collection->register_object( $object );

		$this->assertCount( 1, $this->pods_object_collection->get_objects() );

		$this->assertTrue( $this->pods_object_collection->unregister_object( $object ) );

		$this->assertCount( 0, $this->pods_object_collection->get_objects() );

		// Setup another object that is not registered.
		$args = array(
			'name'   => 'fiver',
			'id'     => 555,
			'parent' => 5555,
			'group'  => 55555,
		);

		$object2 = $this->setup_pods_object( $args );

		$this->assertFalse( $this->pods_object_collection->unregister_object( $object2 ) );
	}

	/**
	 * @covers Pods_Object_Collection::flush_objects
	 * @covers Pods_Object_Collection::register_object
	 * @covers Pods_Object_Collection::get_objects
	 */
	public function test_flush_objects() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'flush_objects' ), 'Method flush_objects does not exist' );

		$object = $this->setup_pods_object();

		$this->pods_object_collection->register_object( $object );

		$args = array(
			'name'   => 'fiver',
			'id'     => 555,
			'parent' => 5555,
			'group'  => 55555,
		);

		$object2 = $this->setup_pods_object( $args );

		$this->pods_object_collection->register_object( $object2 );

		$this->assertCount( 2, $this->pods_object_collection->get_objects() );

		$this->pods_object_collection->flush_objects();

		$this->assertCount( 0, $this->pods_object_collection->get_objects() );
	}

	/**
	 * @covers Pods_Object_Collection::get_objects
	 * @covers Pods_Object_Collection::register_object
	 */
	public function test_get_objects() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'get_objects' ), 'Method get_objects does not exist' );

		$object = $this->setup_pods_object();

		$this->pods_object_collection->register_object( $object );

		$args = array(
			'name'   => 'fiver',
			'id'     => 555,
			'parent' => 5555,
			'group'  => 55555,
		);

		$object2 = $this->setup_pods_object( $args );

		$this->pods_object_collection->register_object( $object2 );

		$objects = $this->pods_object_collection->get_objects();

		$this->assertCount( 2, $objects );

		$this->assertEquals( $object, $objects[ $object->get_identifier() ] );
		$this->assertEquals( $object2, $objects[ $object2->get_identifier() ] );
	}

	/**
	 * @covers Pods_Object_Collection::get_object
	 * @covers Pods_Object_Collection::register_object
	 */
	public function test_get_object() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'get_object' ), 'Method get_object does not exist' );

		$object = $this->setup_pods_object();

		$this->pods_object_collection->register_object( $object );

		$this->assertEquals( $object, $this->pods_object_collection->get_object( $object->get_identifier() ) );
		$this->assertEquals( $object, $this->pods_object_collection->get_object( $object->get_id() ) );
	}

	/**
	 * @covers Pods_Object_Collection::register_object
	 * @covers Pods_Object_Collection::get_object
	 */
	public function test_messy_object() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'get_object' ), 'Method get_object does not exist' );

		$object = $this->setup_pods_object();

		$this->pods_object_collection->register_object( $object );

		$object->set_arg( 'something', 'anotherthing' );

		$this->assertNotEquals( $object, $this->pods_object_collection->get_object( $object->get_identifier() ) );
	}

	/**
	 * @covers Pods_Object_Collection::get_object
	 * @covers Pods_Object_Collection::register_object_type
	 * @covers Pods_Object_Collection::register_object
	 * @covers Pods_Object_Collection::get_objects
	 */
	public function test_array_object() {
		$args = array(
			'object_type' => 'custom',
			'name'        => 'fiver',
			'id'          => 555,
			'parent'      => 5555,
			'group'       => 55555,
		);

		$this->pods_object_collection->register_object_type( 'custom', 'Pods_Object_Custom' );
		$this->pods_object_collection->register_object( $args );

		$this->assertCount( 1, $this->pods_object_collection->get_objects() );

		$identifier = Pods_Object::get_identifier_from_args( $args );

		$this->assertInstanceOf( 'Pods_Object_Custom', $this->pods_object_collection->get_object( 555 ) );
		$this->assertInstanceOf( 'Pods_Object_Custom', $this->pods_object_collection->get_object( $identifier ) );
	}

	/**
	 * @covers Pods_Object_Collection::setup_object
	 * @covers Pods_Object_Collection::register_object_type
	 * @covers Pods_Object_Collection::register_object
	 */
	public function test_setup_object() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'setup_object' ), 'Method setup_object does not exist' );

		$object = $this->setup_pods_object();

		$this->pods_object_collection->register_object( $object );

		$args = array(
			'object_type' => 'custom',
			'name'        => 'fiver',
			'id'          => 555,
			'parent'      => 5555,
			'group'       => 55555,
		);

		$identifier = Pods_Object::get_identifier_from_args( $args );

		$this->pods_object_collection->register_object_type( 'custom', 'Pods_Object_Custom' );
		$this->pods_object_collection->register_object( $args );

		$this->assertEquals( $object, $this->pods_object_collection->get_object( $object->get_identifier() ) );
		$this->assertEquals( $object, $this->pods_object_collection->get_object( $object->get_id() ) );

		$this->assertInstanceOf( 'Pods_Object_Custom', $this->pods_object_collection->get_object( 555 ) );
		$this->assertInstanceOf( 'Pods_Object_Custom', $this->pods_object_collection->get_object( $identifier ) );
	}

}
