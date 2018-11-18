<?php

namespace Pods_Unit_Tests\Object;

use Pods_Unit_Tests\Pods_UnitTestCase;
use Pods_Object_Collection;
use Pods_Object;

/**
 * @group  pods_object
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
		$this->pods_object_collection = Pods_Object_Collection::get_instance();
	}

	public function tearDown() {
		$this->pods_object_collection->flush_objects();

		unset( $this->pods_object_collection );
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

}
