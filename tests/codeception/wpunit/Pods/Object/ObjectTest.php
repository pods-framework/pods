<?php

namespace Pods_Unit_Tests\Object;

use Pods_Unit_Tests\Pods_UnitTestCase;
use Pods_Object_Collection;
use Pods_Object;

/**
 * @group  pods-object
 * @covers Pods_Object
 */
class ObjectTest extends Pods_UnitTestCase {

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
		// Setup parent.
		$parent_args = array(
			'id'          => 121,
			'name'        => 'test-parent',
			'label'       => 'Test parent',
			'description' => 'Testing parent',
		);

		$this->pods_object_parent = $this->setup_pods_object( $parent_args, 'parent' );

		// Setup group.
		$group_args = array(
			'id'          => 122,
			'name'        => 'test-group',
			'label'       => 'Test group',
			'description' => 'Testing group',
		);

		$this->pods_object_group = $this->setup_pods_object( $group_args, 'group' );

		// Setup object.
		$args = array(
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
			'object_type' => 'object', // For test reference.
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

		/** @var Pods_Object $object */
		$object = $this->getMockBuilder( Pods_Object::class )->getMockForAbstractClass();
		$object->setup( $args );

		$object_collection = Pods_Object_Collection::get_instance();
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
			'post_type'    => 'doesntmatter',
		);

		return wp_insert_post( $args );
	}

	/**
	 * @covers Pods_Object::__sleep
	 */
	public function test_serialization() {
		$this->assertTrue( method_exists( $this->pods_object, '__sleep' ), 'Method __sleep does not exist' );

		$serialized = serialize( $this->pods_object );

		$class_name = get_class( $this->pods_object );

		// Convert serialized object to array for testing against.
		$serialized_pattern = sprintf( 'O:%d:"%s":', strlen( $class_name ), $class_name );

		$serialized = str_replace( $serialized_pattern, 'a:', $serialized );
		$serialized = str_replace( "s:7:\"\0*\0args\"", 's:4:"args"', $serialized );

		$to = unserialize( $serialized );

		$this->assertInternalType( 'array', $to );
		$this->assertArrayHasKey( 'args', $to );
		$this->assertInternalType( 'array', $to['args'] );

		$to = $to['args'];

		$this->assertEquals( $this->pods_object->get_args(), $to );
		$this->assertEquals( $this->pods_object->get_id(), $to['id'] );
		$this->assertEquals( $this->pods_object->get_name(), $to['name'] );
		$this->assertEquals( $this->pods_object->get_parent(), $to['parent'] );
		$this->assertEquals( $this->pods_object->get_group(), $to['group'] );
	}

	/**
	 * @covers Pods_Object::jsonSerialize
	 */
	public function test_json() {
		$this->assertTrue( method_exists( $this->pods_object, 'jsonSerialize' ), 'Method jsonSerialize does not exist' );

		$json = json_encode( $this->pods_object );

		$to = json_decode( $json, true );

		$this->assertInternalType( 'array', $to );
		$this->assertEquals( $this->pods_object->get_args(), $to );
		$this->assertEquals( $this->pods_object->get_id(), $to['id'] );
		$this->assertEquals( $this->pods_object->get_name(), $to['name'] );
		$this->assertEquals( $this->pods_object->get_parent(), $to['parent'] );
		$this->assertEquals( $this->pods_object->get_group(), $to['group'] );
	}

	/**
	 * @covers Pods_Object::__toString
	 */
	public function test_string() {
		$this->assertTrue( method_exists( $this->pods_object, '__toString' ), 'Method __toString does not exist' );

		$to = (string) $this->pods_object;

		$this->assertInternalType( 'string', $to );
		$this->assertEquals( $this->pods_object->get_identifier(), $to );
	}

	/**
	 * @covers Pods_Object::from_serialized
	 */
	public function test_from_serialized() {
		$this->assertTrue( method_exists( $this->pods_object, 'from_serialized' ), 'Method from_serialized does not exist' );

		$serialized = serialize( $this->pods_object );

		$to = $this->pods_object->from_serialized( $serialized );

		$this->assertInstanceOf( Pods_Object::class, $to );
		$this->assertEquals( $this->pods_object->get_object_type(), $to->get_object_type() );
		$this->assertEquals( $this->pods_object->get_id(), $to->get_id() );
		$this->assertEquals( $this->pods_object->get_name(), $to->get_name() );
		$this->assertEquals( $this->pods_object->get_parent(), $to->get_parent() );
		$this->assertEquals( $this->pods_object->get_group(), $to->get_group() );
	}

	/**
	 * @covers Pods_Object::from_serialized
	 */
	public function test_from_serialized_args() {
		$this->assertTrue( method_exists( $this->pods_object, 'from_serialized' ), 'Method from_serialized does not exist' );

		$serialized = serialize( $this->pods_object );

		$to = $this->pods_object->from_serialized( $serialized, true );

		$this->assertInternalType( 'array', $to );
		$this->assertEquals( $this->pods_object->get_args(), $to );
		$this->assertEquals( $this->pods_object->get_id(), $to['id'] );
		$this->assertEquals( $this->pods_object->get_name(), $to['name'] );
		$this->assertEquals( $this->pods_object->get_parent(), $to['parent'] );
		$this->assertEquals( $this->pods_object->get_group(), $to['group'] );
	}

	/**
	 * @covers Pods_Object::from_json
	 */
	public function test_from_json() {
		$this->assertTrue( method_exists( $this->pods_object, 'from_json' ), 'Method from_json does not exist' );

		$json = json_encode( $this->pods_object );

		$to = $this->pods_object->from_json( $json );

		$this->assertInstanceOf( Pods_Object::class, $to );
		$this->assertEquals( $this->pods_object->get_object_type(), $to->get_object_type() );
		$this->assertEquals( $this->pods_object->get_id(), $to->get_id() );
		$this->assertEquals( $this->pods_object->get_name(), $to->get_name() );
		$this->assertEquals( $this->pods_object->get_parent(), $to->get_parent() );
		$this->assertEquals( $this->pods_object->get_group(), $to->get_group() );
	}

	/**
	 * @covers Pods_Object::from_json
	 */
	public function test_from_json_args() {
		$this->assertTrue( method_exists( $this->pods_object, 'from_json' ), 'Method from_json does not exist' );

		$json = json_encode( $this->pods_object );

		$to = $this->pods_object->from_json( $json, true );

		$this->assertInternalType( 'array', $to );
		$this->assertEquals( $this->pods_object->get_args(), $to );
		$this->assertEquals( $this->pods_object->get_id(), $to['id'] );
		$this->assertEquals( $this->pods_object->get_name(), $to['name'] );
		$this->assertEquals( $this->pods_object->get_parent(), $to['parent'] );
		$this->assertEquals( $this->pods_object->get_group(), $to['group'] );
	}

	/**
	 * @covers Pods_Object::from_wp_post
	 */
	public function test_from_wp_post() {
		$this->assertTrue( method_exists( $this->pods_object, 'from_wp_post' ), 'Method from_wp_post does not exist' );

		$parent_post_id = $this->setup_wp_post( $this->pods_object_parent );
		$group_post_id  = $this->setup_wp_post( $this->pods_object_group );

		// Update object ID to match.
		$this->pods_object->set_arg( 'parent', $parent_post_id );

		$post_id = $this->setup_wp_post( $this->pods_object );

		// Update object ID and group to match.
		$this->pods_object->set_arg( 'id', $post_id );

		update_post_meta( $post_id, 'group', $this->pods_object_group->get_identifier() );

		$post = get_post( $post_id );

		$to = $this->pods_object->from_wp_post( $post );

		$this->assertInstanceOf( Pods_Object::class, $to );
		$this->assertEquals( $this->pods_object->get_object_type(), $to->get_object_type() );
		$this->assertEquals( $this->pods_object->get_id(), $to->get_id() );
		$this->assertEquals( $this->pods_object->get_name(), $to->get_name() );
		$this->assertEquals( $this->pods_object->get_parent(), $to->get_parent() );
		$this->assertEquals( $this->pods_object->get_group(), $to->get_group() );
	}

	/**
	 * @covers Pods_Object::from_wp_post
	 */
	public function test_from_wp_post_args() {
		$this->assertTrue( method_exists( $this->pods_object, 'from_wp_post' ), 'Method from_wp_post does not exist' );

		$parent_post_id = $this->setup_wp_post( $this->pods_object_parent );
		$group_post_id  = $this->setup_wp_post( $this->pods_object_group );

		// Update object ID to match.
		$this->pods_object->set_arg( 'parent', $parent_post_id );

		$post_id = $this->setup_wp_post( $this->pods_object );

		// Update object ID and group to match.
		$this->pods_object->set_arg( 'id', $post_id );

		update_post_meta( $post_id, 'group', $this->pods_object_group->get_identifier() );

		$to = $this->pods_object->from_wp_post( $post_id, true );

		$this->assertInternalType( 'array', $to );
		$this->assertEquals( $this->pods_object->get_args(), $to );
		$this->assertEquals( $this->pods_object->get_id(), $to['id'] );
		$this->assertEquals( $this->pods_object->get_name(), $to['name'] );
		$this->assertEquals( $this->pods_object->get_parent(), $to['parent'] );
		$this->assertEquals( $this->pods_object->get_group(), $to['group'] );
	}

	/**
	 * @covers Pods_Object::offsetExists
	 * @covers Pods_Object::offsetGet
	 * @covers Pods_Object::offsetSet
	 * @covers Pods_Object::offsetUnset
	 * @covers Pods_Object::get_arg
	 * @covers Pods_Object::set_arg
	 */
	public function test_array_access() {
		// Confirm methods exist.
		$this->assertTrue( method_exists( $this->pods_object, 'offsetExists' ), 'Method offsetExists does not exist' );
		$this->assertTrue( method_exists( $this->pods_object, 'offsetGet' ), 'Method offsetGet does not exist' );
		$this->assertTrue( method_exists( $this->pods_object, 'offsetSet' ), 'Method offsetSet does not exist' );
		$this->assertTrue( method_exists( $this->pods_object, 'offsetUnset' ), 'Method offsetUnset does not exist' );

		// Confirm argument get matches ArrayAccess.
		$this->assertEquals( $this->pods_object->get_id(), $this->pods_object['id'] );
		$this->assertEquals( $this->pods_object->get_name(), $this->pods_object['name'] );
		$this->assertEquals( $this->pods_object->get_parent(), $this->pods_object['parent'] );
		$this->assertEquals( $this->pods_object->get_group(), $this->pods_object['group'] );

		// Test non-existent arguments and handling for ArrayAccess.
		$this->assertNull( $this->pods_object->get_arg( 'fourohfour' ) );
		$this->assertEquals( $this->pods_object->get_arg( 'fourohfour' ), $this->pods_object['fourohfour'] );
		$this->assertFalse( isset( $this->pods_object['fourohfour'] ) );

		// Test isset for ArrayAccess.
		$this->assertTrue( isset( $this->pods_object['id'] ) );
		$this->assertTrue( isset( $this->pods_object['name'] ) );
		$this->assertTrue( isset( $this->pods_object['parent'] ) );
		$this->assertTrue( isset( $this->pods_object['group'] ) );

		// Test unset handling for ArrayAccess served arguments.
		unset( $this->pods_object['id'], $this->pods_object['name'], $this->pods_object['parent'], $this->pods_object['group'] );

		// Confirm ArrayAccess arguments are now empty strings for reserved arguments.
		$this->assertEquals( $this->pods_object['id'], '' );
		$this->assertEquals( $this->pods_object['name'], '' );
		$this->assertEquals( $this->pods_object['parent'], '' );
		$this->assertEquals( $this->pods_object['group'], '' );
	}

	/**
	 * @covers Pods_Object::setup
	 * @covers Pods_Object::get_args
	 * @covers Pods_Object::get_arg
	 * @covers Pods_Object::get_group
	 */
	public function test_setup() {
		$this->assertTrue( method_exists( $this->pods_object, 'setup' ), 'Method setup does not exist' );

		$args = array(
			'name'       => 'fiver',
			'id'         => 555,
			'parent'     => 5555,
			'group'      => 55555,
			'fourohfour' => 405,
		);

		$this->pods_object->setup( $args );

		$this->assertArraySubset( $args, $this->pods_object->get_args() );
		$this->assertEquals( '', $this->pods_object->get_arg( 'label' ) );
		$this->assertEquals( '', $this->pods_object->get_arg( 'description' ) );

		$args2 = array(
			'name'   => 'seven',
			'id'     => 777,
			'parent' => 7777,
		);

		$this->pods_object->setup( $args2 );

		$this->assertArraySubset( $args2, $this->pods_object->get_args() );
		$this->assertEquals( '', $this->pods_object->get_group() );
		$this->assertEquals( '', $this->pods_object->get_arg( 'label' ) );
		$this->assertEquals( '', $this->pods_object->get_arg( 'description' ) );
		$this->assertEquals( null, $this->pods_object->get_arg( 'fourohfour' ) );
	}

	/**
	 * @covers Pods_Object::get_arg
	 * @covers Pods_Object::set_arg
	 */
	public function test_get_set_arg() {
		// Confirm methods exist.
		$this->assertTrue( method_exists( $this->pods_object, 'get_arg' ), 'Method get_arg does not exist' );
		$this->assertTrue( method_exists( $this->pods_object, 'set_arg' ), 'Method set_arg does not exist' );

		// Confirm get_arg matches method access.
		$this->assertEquals( $this->pods_object->get_id(), $this->pods_object->get_arg( 'id' ) );
		$this->assertEquals( $this->pods_object->get_name(), $this->pods_object->get_arg( 'name' ) );
		$this->assertEquals( $this->pods_object->get_parent(), $this->pods_object->get_arg( 'parent' ) );
		$this->assertEquals( $this->pods_object->get_group(), $this->pods_object->get_arg( 'group' ) );

		// Test non-existent arguments and handling for ArrayAccess.
		$this->assertNull( $this->pods_object->get_arg( 'fourohfour' ) );

		$args = array(
			'name'       => 'fiver',
			'id'         => 555,
			'parent'     => 5555,
			'group'      => 55555,
			'fourohfour' => 405,
		);

		$this->pods_object->set_arg( 'name', $args['name'] );
		$this->pods_object->set_arg( 'id', $args['id'] );
		$this->pods_object->set_arg( 'parent', $args['parent'] );
		$this->pods_object->set_arg( 'group', $args['group'] );
		$this->pods_object->set_arg( 'fourohfour', $args['fourohfour'] );

		$this->assertEquals( $this->pods_object->get_arg( 'name' ), $args['name'] );
		$this->assertEquals( $this->pods_object->get_arg( 'id' ), $args['id'] );
		$this->assertEquals( $this->pods_object->get_arg( 'parent' ), $args['parent'] );
		$this->assertEquals( $this->pods_object->get_arg( 'group' ), $args['group'] );
		$this->assertEquals( $this->pods_object->get_arg( 'fourohfour' ), $args['fourohfour'] );
	}

	/**
	 * @covers Pods_Object::is_valid
	 */
	public function test_is_valid() {
		$this->assertTrue( method_exists( $this->pods_object, 'is_valid' ), 'Method is_valid does not exist' );

		$this->assertTrue( $this->pods_object->is_valid() );

		$args = array(
			'name'   => '',
			'id'     => '',
			'parent' => '',
			'group'  => '',
		);

		$this->pods_object->setup( $args );

		$this->assertFalse( $this->pods_object->is_valid() );
	}

	/**
	 * @covers Pods_Object::get_identifier_from_args
	 */
	public function test_get_identifier_from_args() {
		$this->assertTrue( method_exists( $this->pods_object, 'get_identifier_from_args' ), 'Method get_identifier_from_args does not exist' );

		$this->assertEquals( $this->pods_object->get_identifier(), Pods_Object::get_identifier_from_args( $this->args ) );

		$identifier = sprintf( '%s/%s/%s', $this->pods_object->get_object_type(), $this->pods_object->get_parent(), $this->pods_object->get_name() );

		$this->assertEquals( $identifier, Pods_Object::get_identifier_from_args( $this->pods_object->get_args() ) );

		// Unset parent.
		$this->pods_object->set_arg( 'parent', null );

		$identifier = sprintf( '%s/%s', $this->pods_object->get_object_type(), $this->pods_object->get_name() );

		$this->assertEquals( $identifier, Pods_Object::get_identifier_from_args( $this->pods_object->get_args() ) );
	}

	/**
	 * @covers Pods_Object::get_identifier
	 */
	public function test_get_identifier() {
		$this->assertTrue( method_exists( $this->pods_object, 'get_identifier' ), 'Method get_identifier does not exist' );

		$identifier = sprintf( '%s/%s/%s', $this->pods_object->get_object_type(), $this->pods_object->get_parent(), $this->pods_object->get_name() );

		$this->assertEquals( $identifier, $this->pods_object->get_identifier() );

		// Unset parent.
		$this->pods_object->set_arg( 'parent', null );

		$identifier = sprintf( '%s/%s', $this->pods_object->get_object_type(), $this->pods_object->get_name() );

		$this->assertEquals( $identifier, $this->pods_object->get_identifier() );
	}

	/**
	 * @covers Pods_Object::get_args
	 */
	public function test_get_args() {
		$this->assertTrue( method_exists( $this->pods_object, 'get_args' ), 'Method get_args does not exist' );

		$this->assertEquals( $this->args, $this->pods_object->get_args() );
	}

	/**
	 * Provide get_* methods to be tested.
	 *
	 * @return array
	 */
	public function provider_methods() {
		return array(
			array( 'object_type' ),
			array( 'name' ),
			array( 'id' ),
			array( 'parent' ),
			array( 'group' ),
		);
	}

	/**
	 * @covers       Pods_Object::get_name
	 * @covers       Pods_Object::get_id
	 * @covers       Pods_Object::get_parent
	 * @covers       Pods_Object::get_group
	 *
	 * @dataProvider provider_methods
	 *
	 * @param string $property Property to test.
	 */
	public function test_get( $property ) {
		$method = 'get_' . $property;

		codecept_debug( 'Method: ' . $method );

		$this->assertEquals( $this->args[ $property ], call_user_func( array( $this->pods_object, $method ) ) );
	}

	/**
	 * Provide parent get_* methods to be tested.
	 *
	 * @return array
	 */
	public function provider_parent_methods() {
		return array(
			array( 'object_type' ),
			array( 'name' ),
			array( 'id' ),
			array( 'parent' ),
			array( 'group' ),
		);
	}

	/**
	 * @covers       Pods_Object::get_parent_name
	 * @covers       Pods_Object::get_parent_id
	 *
	 * @dataProvider provider_parent_methods
	 *
	 * @param string $property Property to test.
	 */
	public function test_get_parent( $property ) {
		$method = 'get_parent_' . $property;

		codecept_debug( 'Method: ' . $method );

		$this->assertEquals( $this->parent_args[ $property ], call_user_func( array( $this->pods_object, $method ) ) );
	}

	/**
	 * Provide group get_* methods to be tested.
	 *
	 * @return array
	 */
	public function provider_group_methods() {
		return array(
			array( 'object_type' ),
			array( 'name' ),
			array( 'id' ),
			array( 'parent' ),
			array( 'group' ),
		);
	}

	/**
	 * @covers       Pods_Object::get_group_name
	 * @covers       Pods_Object::get_group_id
	 *
	 * @dataProvider provider_group_methods
	 *
	 * @param string $property Property to test.
	 */
	public function test_get_group( $property ) {
		$method = 'get_group_' . $property;

		codecept_debug( 'Method: ' . $method );

		$this->assertEquals( $this->group_args[ $property ], call_user_func( array( $this->pods_object, $method ) ) );
	}

}
