<?php

namespace Pods_Unit_Tests\Object;

use Pods_Unit_Tests\Pods_UnitTestCase;
use Pods__Object__Collection;
use Pods__Object;

/**
 * @group  pods-object
 * @group  pods-object-collection
 * @covers Pods__Object__Collection
 */
class CollectionTest extends Pods_UnitTestCase {

	/**
	 * @var array
	 */
	private $args;

	/**
	 * @var Pods__Object__Collection
	 */
	private $pods_object_collection;

	public function setUp() {
		if ( ! class_exists( 'Pods__Object__Custom' ) ) {
			eval( 'class Pods__Object__Custom extends Pods__Object { protected static $type = "custom"; }' );
		}

		if ( ! class_exists( 'Pods__Object__Storage__Custom' ) ) {
			eval( 'class Pods__Object__Storage__Custom extends Pods__Object__Storage { protected static $type = "custom"; }' );
		}

		$this->pods_object_collection = Pods__Object__Collection::get_instance();
	}

	public function tearDown() {
		Pods__Object__Collection::destroy();
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
			'object_type' => 'object', // For test reference.
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
	 * @covers Pods__Object__Collection::get_instance
	 */
	public function test_get_instance() {
		$this->assertTrue( method_exists( Pods__Object__Collection::class, 'get_instance' ), 'Method get_instance does not exist' );

		$this->assertInstanceOf( Pods__Object__Collection::class, Pods__Object__Collection::get_instance() );
	}

	/**
	 * @covers Pods__Object__Collection::destroy
	 */
	public function test_destroy() {
		$this->assertTrue( method_exists( Pods__Object__Collection::class, 'destroy' ), 'Method destroy does not exist' );

		$this->pods_object_collection->register_object_type( 'custom', 'Pods__Object__Custom' );
		$this->pods_object_collection->register_storage_type( 'custom', 'Pods__Object__Storage__Custom' );

		$object = $this->setup_pods_object( array( 'object_type' => 'custom' ) );

		$this->pods_object_collection->register_object( $object );

		$this->assertCount( 4, $this->pods_object_collection->get_object_types() );
		$this->assertCount( 2, $this->pods_object_collection->get_storage_types() );
		$this->assertCount( 4, $this->pods_object_collection->get_objects() );

		Pods__Object__Collection::destroy();

		$this->pods_object_collection = Pods__Object__Collection::get_instance();

		$this->assertCount( 3, $this->pods_object_collection->get_object_types() );
		$this->assertCount( 1, $this->pods_object_collection->get_storage_types() );
		$this->assertCount( 3, $this->pods_object_collection->get_objects() );
	}

	/**
	 * @covers Pods__Object__Collection::register_object_type
	 * @covers Pods__Object__Collection::get_object_types
	 */
	public function test_register_object_type() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'register_object_type' ), 'Method register_object_type does not exist' );

		$this->pods_object_collection->register_object_type( 'custom', 'Pods__Object__Custom' );

		$this->assertCount( 4, $this->pods_object_collection->get_object_types() );
	}

	/**
	 * @covers Pods__Object__Collection::unregister_object_type
	 * @covers Pods__Object__Collection::register_object_type
	 * @covers Pods__Object__Collection::get_object_types
	 */
	public function test_unregister_object_type() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'unregister_object_type' ), 'Method unregister_object_type does not exist' );

		$this->pods_object_collection->register_object_type( 'custom', 'Pods__Object__Custom' );

		$this->assertCount( 4, $this->pods_object_collection->get_object_types() );

		$this->assertTrue( $this->pods_object_collection->unregister_object_type( 'custom' ) );

		$this->assertCount( 3, $this->pods_object_collection->get_object_types() );

		$this->assertFalse( $this->pods_object_collection->unregister_object_type( 'nope' ) );
	}

	/**
	 * @covers Pods__Object__Collection::flush_object_types
	 * @covers Pods__Object__Collection::register_object_type
	 * @covers Pods__Object__Collection::get_object_types
	 */
	public function test_flush_object_types() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'flush_object_types' ), 'Method flush_object_types does not exist' );

		$this->pods_object_collection->register_object_type( 'custom', 'Pods__Object__Custom' );

		$this->assertCount( 4, $this->pods_object_collection->get_object_types() );

		$this->pods_object_collection->flush_object_types();

		$this->assertCount( 3, $this->pods_object_collection->get_object_types() );
	}

	/**
	 * @covers Pods__Object__Collection::get_object_types
	 * @covers Pods__Object__Collection::register_object_type
	 */
	public function test_get_object_types() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'get_object_types' ), 'Method get_object_types does not exist' );

		$this->pods_object_collection->register_object_type( 'custom', 'Pods__Object__Custom' );

		$this->assertCount( 4, $this->pods_object_collection->get_object_types() );
	}

	/**
	 * @covers Pods__Object__Collection::get_object_type
	 * @covers Pods__Object__Collection::register_object_type
	 */
	public function test_get_object_type() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'get_object_type' ), 'Method get_object_type does not exist' );

		$this->pods_object_collection->register_object_type( 'custom', 'Pods__Object__Custom' );

		$this->assertEquals( 'Pods__Object__Custom', $this->pods_object_collection->get_object_type( 'custom' ) );
	}

	/**
	 * @covers Pods__Object__Collection::register_storage_type
	 * @covers Pods__Object__Collection::get_storage_types
	 */
	public function test_register_storage_type() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'register_storage_type' ), 'Method register_storage_type does not exist' );

		$this->pods_object_collection->register_storage_type( 'custom', 'Pods__Object__Storage__Custom' );

		$this->assertCount( 2, $this->pods_object_collection->get_storage_types() );
	}

	/**
	 * @covers Pods__Object__Collection::unregister_storage_type
	 * @covers Pods__Object__Collection::register_storage_type
	 * @covers Pods__Object__Collection::get_storage_types
	 */
	public function test_unregister_storage_type() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'unregister_storage_type' ), 'Method unregister_storage_type does not exist' );

		$this->pods_object_collection->register_storage_type( 'custom', 'Pods__Object__Storage__Custom' );

		$this->assertCount( 2, $this->pods_object_collection->get_storage_types() );

		$this->assertTrue( $this->pods_object_collection->unregister_storage_type( 'custom' ) );

		$this->assertCount( 1, $this->pods_object_collection->get_storage_types() );

		$this->assertFalse( $this->pods_object_collection->unregister_storage_type( 'nope' ) );
	}

	/**
	 * @covers Pods__Object__Collection::flush_storage_types
	 * @covers Pods__Object__Collection::register_storage_type
	 * @covers Pods__Object__Collection::get_storage_types
	 */
	public function test_flush_storage_types() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'flush_storage_types' ), 'Method flush_storage_types does not exist' );

		$this->pods_object_collection->register_storage_type( 'custom', 'Pods__Object__Storage__Custom' );

		$this->assertCount( 2, $this->pods_object_collection->get_storage_types() );

		$this->pods_object_collection->flush_storage_types();

		$this->assertCount( 1, $this->pods_object_collection->get_storage_types() );
	}

	/**
	 * @covers Pods__Object__Collection::get_storage_types
	 * @covers Pods__Object__Collection::register_storage_type
	 */
	public function test_get_storage_types() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'get_storage_types' ), 'Method get_storage_types does not exist' );

		$this->pods_object_collection->register_storage_type( 'custom', 'Pods__Object__Storage__Custom' );

		$this->assertCount( 2, $this->pods_object_collection->get_storage_types() );
	}

	/**
	 * @covers Pods__Object__Collection::get_storage_type
	 * @covers Pods__Object__Collection::register_storage_type
	 */
	public function test_get_storage_type() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'get_storage_type' ), 'Method get_storage_type does not exist' );

		$this->pods_object_collection->register_storage_type( 'custom', 'Pods__Object__Storage__Custom' );

		$this->assertEquals( 'Pods__Object__Storage__Custom', $this->pods_object_collection->get_storage_type( 'custom' ) );
	}

	/**
	 * @covers Pods__Object__Collection::get_storage_object
	 * @covers Pods__Object__Collection::register_storage_type
	 */
	public function test_get_storage_object() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'get_storage_object' ), 'Method get_storage_object does not exist' );

		$this->pods_object_collection->register_storage_type( 'custom', 'Pods__Object__Storage__Custom' );

		$this->assertInstanceOf( 'Pods__Object__Storage__Custom', $this->pods_object_collection->get_storage_object( 'custom' ) );
	}

	/**
	 * @covers Pods__Object__Collection::register_object
	 * @covers Pods__Object__Collection::get_objects
	 */
	public function test_register_object() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'register_object' ), 'Method register_object does not exist' );

		$object = $this->setup_pods_object();

		$this->pods_object_collection->register_object( $object );

		$this->assertCount( 4, $this->pods_object_collection->get_objects() );
	}

	/**
	 * @covers Pods__Object__Collection::unregister_object
	 * @covers Pods__Object__Collection::register_object
	 * @covers Pods__Object__Collection::get_objects
	 */
	public function test_unregister_object() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'unregister_object' ), 'Method unregister_object does not exist' );

		$object = $this->setup_pods_object();

		$this->pods_object_collection->register_object( $object );

		$this->assertCount( 4, $this->pods_object_collection->get_objects() );

		$this->assertTrue( $this->pods_object_collection->unregister_object( $object ) );

		$objects = $this->pods_object_collection->get_objects();

		$this->assertCount( 3, $objects );

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
	 * @covers Pods__Object__Collection::flush_objects
	 * @covers Pods__Object__Collection::register_object
	 * @covers Pods__Object__Collection::get_objects
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

		$this->assertCount( 5, $this->pods_object_collection->get_objects() );

		$this->pods_object_collection->flush_objects();

		$this->assertCount( 3, $this->pods_object_collection->get_objects() );
	}

	/**
	 * @covers Pods__Object__Collection::get_objects
	 * @covers Pods__Object__Collection::register_object
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

		$this->assertCount( 5, $objects );

		$this->assertEquals( $object, $objects[ $object->get_identifier() ] );
		$this->assertEquals( $object2, $objects[ $object2->get_identifier() ] );
	}

	/**
	 * @covers Pods__Object__Collection::get_object
	 * @covers Pods__Object__Collection::register_object
	 */
	public function test_get_object() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'get_object' ), 'Method get_object does not exist' );

		$object = $this->setup_pods_object();

		$this->pods_object_collection->register_object( $object );

		$this->assertEquals( $object, $this->pods_object_collection->get_object( $object->get_identifier() ) );
		$this->assertEquals( $object, $this->pods_object_collection->get_object( $object->get_id() ) );
	}

	/**
	 * @covers Pods__Object__Collection::register_object
	 * @covers Pods__Object__Collection::get_object
	 */
	public function test_messy_object() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'get_object' ), 'Method get_object does not exist' );

		$object = $this->setup_pods_object();

		$this->pods_object_collection->register_object( $object );

		$object->set_arg( 'something', 'anotherthing' );

		$this->assertNotEquals( $object, $this->pods_object_collection->get_object( $object->get_identifier() ) );
	}

	/**
	 * @covers Pods__Object__Collection::get_object
	 * @covers Pods__Object__Collection::register_object_type
	 * @covers Pods__Object__Collection::register_object
	 * @covers Pods__Object__Collection::get_objects
	 */
	public function test_array_object() {
		$args = array(
			'object_type' => 'custom',
			'name'        => 'fiver',
			'id'          => 555,
			'parent'      => 5555,
			'group'       => 55555,
		);

		$this->pods_object_collection->register_object_type( 'custom', 'Pods__Object__Custom' );
		$this->pods_object_collection->register_object( $args );

		$this->assertCount( 4, $this->pods_object_collection->get_objects() );

		$identifier = Pods__Object::get_identifier_from_args( $args );

		$this->assertInstanceOf( 'Pods__Object__Custom', $this->pods_object_collection->get_object( 555 ) );
		$this->assertInstanceOf( 'Pods__Object__Custom', $this->pods_object_collection->get_object( $identifier ) );
	}

	/**
	 * @covers Pods__Object__Collection::setup_object
	 * @covers Pods__Object__Collection::register_object_type
	 * @covers Pods__Object__Collection::register_object
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

		$identifier = Pods__Object::get_identifier_from_args( $args );

		$this->pods_object_collection->register_object_type( 'custom', 'Pods__Object__Custom' );
		$this->pods_object_collection->register_object( $args );

		$this->assertEquals( $object, $this->pods_object_collection->get_object( $object->get_identifier() ) );
		$this->assertEquals( $object, $this->pods_object_collection->get_object( $object->get_id() ) );

		$this->assertInstanceOf( 'Pods__Object__Custom', $this->pods_object_collection->get_object( 555 ) );
		$this->assertInstanceOf( 'Pods__Object__Custom', $this->pods_object_collection->get_object( $identifier ) );
	}

}
