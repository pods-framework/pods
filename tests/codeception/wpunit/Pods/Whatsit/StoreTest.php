<?php

namespace Pods_Unit_Tests\Pods\Whatsit;

use Pods_Unit_Tests\Pods_UnitTestCase;
use Pods\Whatsit\Store;
use Pods\Whatsit;

/**
 * @class Whatsit__Storage__Custom
 * @class Whatsit__Custom
 */

/**
 * @group  pods-whatsit
 * @group  pods-whatsit-store
 * @covers Store
 */
class StoreTest extends Pods_UnitTestCase {

	/**
	 * @var array
	 */
	private $args;

	/**
	 * @var Store
	 */
	private $pods_object_collection;

	public function setUp(): void {
		if ( ! class_exists( __NAMESPACE__ . '\Whatsit__Custom' ) ) {
			eval( 'namespace ' . __NAMESPACE__ . '; class Whatsit__Custom extends \Pods\Whatsit { protected static $type = "custom"; }' );
		}

		if ( ! class_exists( __NAMESPACE__ . '\Whatsit__Storage__Custom' ) ) {
			eval( 'namespace ' . __NAMESPACE__ . '; class Whatsit__Storage__Custom extends \Pods\Whatsit\Storage { protected static $type = "custom"; }' );
		}

		$this->pods_object_collection = Store::get_instance();
	}

	public function tearDown(): void {
		Store::destroy();
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
			'object_type' => 'object', // For test reference.
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
	 * @covers Store::get_instance
	 */
	public function test_get_instance() {
		$this->assertTrue( method_exists( Store::class, 'get_instance' ), 'Method get_instance does not exist' );

		$this->assertInstanceOf( Store::class, Store::get_instance() );
	}

	/**
	 * @covers Store::destroy
	 */
	public function test_destroy() {
		$this->assertTrue( method_exists( Store::class, 'destroy' ), 'Method destroy does not exist' );

		$this->pods_object_collection->register_object_type( 'custom', Whatsit__Custom::class );
		$this->pods_object_collection->register_storage_type( 'custom', Whatsit__Storage__Custom::class );

		$object = $this->setup_pods_object( array( 'object_type' => 'custom' ) );

		$this->pods_object_collection->register_object( $object );

		$this->assertCount( 10, $this->pods_object_collection->get_object_types() );
		$this->assertCount( 3, $this->pods_object_collection->get_storage_types() );
		$this->assertCount( 4, $this->pods_object_collection->get_objects() );

		Store::destroy();

		$this->pods_object_collection = Store::get_instance();

		$this->assertCount( 9, $this->pods_object_collection->get_object_types() );
		$this->assertCount( 2, $this->pods_object_collection->get_storage_types() );
		$this->assertCount( 3, $this->pods_object_collection->get_objects() );
	}

	/**
	 * @covers Store::register_object_type
	 * @covers Store::get_object_types
	 */
	public function test_register_object_type() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'register_object_type' ), 'Method register_object_type does not exist' );

		$this->pods_object_collection->register_object_type( 'custom', Whatsit__Custom::class );

		$this->assertCount( 10, $this->pods_object_collection->get_object_types() );
	}

	/**
	 * @covers Store::unregister_object_type
	 * @covers Store::register_object_type
	 * @covers Store::get_object_types
	 */
	public function test_unregister_object_type() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'unregister_object_type' ), 'Method unregister_object_type does not exist' );

		$this->pods_object_collection->register_object_type( 'custom', Whatsit__Custom::class );

		$this->assertCount( 10, $this->pods_object_collection->get_object_types() );

		$this->assertTrue( $this->pods_object_collection->unregister_object_type( 'custom' ) );

		$this->assertCount( 9, $this->pods_object_collection->get_object_types() );

		$this->assertFalse( $this->pods_object_collection->unregister_object_type( 'nope' ) );
	}

	/**
	 * @covers Store::flush_object_types
	 * @covers Store::register_object_type
	 * @covers Store::get_object_types
	 */
	public function test_flush_object_types() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'flush_object_types' ), 'Method flush_object_types does not exist' );

		$this->pods_object_collection->register_object_type( 'custom', Whatsit__Custom::class );

		$this->assertCount( 10, $this->pods_object_collection->get_object_types() );

		$this->pods_object_collection->flush_object_types();

		$this->assertCount( 9, $this->pods_object_collection->get_object_types() );
	}

	/**
	 * @covers Store::get_object_types
	 * @covers Store::register_object_type
	 */
	public function test_get_object_types() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'get_object_types' ), 'Method get_object_types does not exist' );

		$this->pods_object_collection->register_object_type( 'custom', Whatsit__Custom::class );

		$this->assertCount( 10, $this->pods_object_collection->get_object_types() );
	}

	/**
	 * @covers Store::get_object_type
	 * @covers Store::register_object_type
	 */
	public function test_get_object_type() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'get_object_type' ), 'Method get_object_type does not exist' );

		$this->pods_object_collection->register_object_type( 'custom', Whatsit__Custom::class );

		$this->assertEquals( Whatsit__Custom::class, $this->pods_object_collection->get_object_type( 'custom' ) );
	}

	/**
	 * @covers Store::register_storage_type
	 * @covers Store::get_storage_types
	 */
	public function test_register_storage_type() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'register_storage_type' ), 'Method register_storage_type does not exist' );

		$this->pods_object_collection->register_storage_type( 'custom', Whatsit__Storage__Custom::class );

		$this->assertCount( 3, $this->pods_object_collection->get_storage_types() );
	}

	/**
	 * @covers Store::unregister_storage_type
	 * @covers Store::register_storage_type
	 * @covers Store::get_storage_types
	 */
	public function test_unregister_storage_type() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'unregister_storage_type' ), 'Method unregister_storage_type does not exist' );

		$this->pods_object_collection->register_storage_type( 'custom', Whatsit__Storage__Custom::class );

		$this->assertCount( 3, $this->pods_object_collection->get_storage_types() );

		$this->assertTrue( $this->pods_object_collection->unregister_storage_type( 'custom' ) );

		$this->assertCount( 2, $this->pods_object_collection->get_storage_types() );

		$this->assertFalse( $this->pods_object_collection->unregister_storage_type( 'nope' ) );
	}

	/**
	 * @covers Store::flush_storage_types
	 * @covers Store::register_storage_type
	 * @covers Store::get_storage_types
	 */
	public function test_flush_storage_types() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'flush_storage_types' ), 'Method flush_storage_types does not exist' );

		$this->pods_object_collection->register_storage_type( 'custom', Whatsit__Storage__Custom::class );

		$this->assertCount( 3, $this->pods_object_collection->get_storage_types() );

		$this->pods_object_collection->flush_storage_types();

		$this->assertCount( 2, $this->pods_object_collection->get_storage_types() );
	}

	/**
	 * @covers Store::get_storage_types
	 * @covers Store::register_storage_type
	 */
	public function test_get_storage_types() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'get_storage_types' ), 'Method get_storage_types does not exist' );

		$this->pods_object_collection->register_storage_type( 'custom', Whatsit__Storage__Custom::class );

		$this->assertCount( 3, $this->pods_object_collection->get_storage_types() );
	}

	/**
	 * @covers Store::get_storage_type
	 * @covers Store::register_storage_type
	 */
	public function test_get_storage_type() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'get_storage_type' ), 'Method get_storage_type does not exist' );

		$this->pods_object_collection->register_storage_type( 'custom', Whatsit__Storage__Custom::class );

		$this->assertEquals( Whatsit__Storage__Custom::class, $this->pods_object_collection->get_storage_type( 'custom' ) );
	}

	/**
	 * @covers Store::get_storage_object
	 * @covers Store::register_storage_type
	 */
	public function test_get_storage_object() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'get_storage_object' ), 'Method get_storage_object does not exist' );

		$this->pods_object_collection->register_storage_type( 'custom', Whatsit__Storage__Custom::class );

		$this->assertInstanceOf( Whatsit__Storage__Custom::class, $this->pods_object_collection->get_storage_object( 'custom' ) );
	}

	/**
	 * @covers Store::register_object
	 * @covers Store::get_objects
	 */
	public function test_register_object() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'register_object' ), 'Method register_object does not exist' );

		$object = $this->setup_pods_object();

		$this->pods_object_collection->register_object( $object );

		$this->assertCount( 4, $this->pods_object_collection->get_objects() );
	}

	/**
	 * @covers Store::unregister_object
	 * @covers Store::register_object
	 * @covers Store::get_objects
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
	 * @covers Store::flush_objects
	 * @covers Store::register_object
	 * @covers Store::get_objects
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
	 * @covers Store::delete_objects
	 * @covers Store::flush_objects
	 * @covers Store::register_object
	 * @covers Store::get_objects
	 */
	public function test_delete_objects() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'delete_objects' ), 'Method delete_objects does not exist' );

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

		$this->pods_object_collection->delete_objects();

		$this->assertCount( 3, $this->pods_object_collection->get_objects() );
	}

	/**
	 * @covers Store::get_objects
	 * @covers Store::register_object
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
	 * @covers Store::get_object
	 * @covers Store::register_object
	 */
	public function test_get_object() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'get_object' ), 'Method get_object does not exist' );

		$object = $this->setup_pods_object();

		$this->pods_object_collection->register_object( $object );

		$this->assertEquals( $object, $this->pods_object_collection->get_object( $object->get_identifier() ) );
		$this->assertEquals( $object, $this->pods_object_collection->get_object( $object->get_id() ) );
	}

	/**
	 * @covers Store::register_object
	 * @covers Store::get_object
	 */
	public function test_messy_object() {
		$this->assertTrue( method_exists( $this->pods_object_collection, 'get_object' ), 'Method get_object does not exist' );

		$object = $this->setup_pods_object();

		$this->pods_object_collection->register_object( $object );

		$object->set_arg( 'something', 'anotherthing' );

		$this->assertNotEquals( $object, $this->pods_object_collection->get_object( $object->get_identifier() ) );
	}

	/**
	 * @covers Store::get_object
	 * @covers Store::register_object_type
	 * @covers Store::register_object
	 * @covers Store::get_objects
	 */
	public function test_array_object() {
		$args = array(
			'object_type' => 'custom',
			'name'        => 'fiver',
			'id'          => 555,
			'parent'      => 5555,
			'group'       => 55555,
		);

		$this->pods_object_collection->register_object_type( 'custom', Whatsit__Custom::class );
		$this->pods_object_collection->register_object( $args );

		$this->assertCount( 4, $this->pods_object_collection->get_objects() );

		$identifier = Whatsit::get_identifier_from_args( $args );

		$this->assertInstanceOf( Whatsit__Custom::class, $this->pods_object_collection->get_object( 555 ) );
		$this->assertInstanceOf( Whatsit__Custom::class, $this->pods_object_collection->get_object( $identifier ) );
	}

	/**
	 * @covers Store::setup_object
	 * @covers Store::register_object_type
	 * @covers Store::register_object
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

		$identifier = Whatsit::get_identifier_from_args( $args );

		$this->pods_object_collection->register_object_type( 'custom', Whatsit__Custom::class );
		$this->pods_object_collection->register_object( $args );

		$this->assertEquals( $object, $this->pods_object_collection->get_object( $object->get_identifier() ) );
		$this->assertEquals( $object, $this->pods_object_collection->get_object( $object->get_id() ) );

		$this->assertInstanceOf( Whatsit__Custom::class, $this->pods_object_collection->get_object( 555 ) );
		$this->assertInstanceOf( Whatsit__Custom::class, $this->pods_object_collection->get_object( $identifier ) );
	}

}
