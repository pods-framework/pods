<?php

namespace Pods_Unit_Tests;

use Mockery;
use PodsObject;

/**
 * @group  pods_object
 * @covers PodsObject
 */
class PodsObjectTest extends Pods_UnitTestCase {

	/**
	 * @var array
	 */
	private $args;

	/**
	 * @var PodsObject
	 */
	private $pods_object;

	public function setUp() {
		$this->args = array(
			'id'          => 123,
			'name'        => 'test',
			'label'       => 'Test',
			'description' => 'Testing',
			'parent'      => '',
			'group'       => '',
		);

		$this->pods_object = $this->getMockBuilder( PodsObject::class )->getMockForAbstractClass();
		$this->pods_object->setup( $this->args );
	}

	public function tearDown() {
		unset( $this->pods_object );
	}

	/**
	 * @covers PodsObject::__sleep
	 */
	public function test_serialization() {
		$this->assertTrue( method_exists( $this->pods_object, '__sleep' ), 'Method __sleep does not exist' );

		$serialized = serialize( $this->pods_object );

		$class_name = get_class( $this->pods_object );

		// Convert serialized object to array for testing against.
		$serialized_pattern = sprintf( 'O:%d:"%s":', strlen( $class_name ), $class_name );

		$serialized = str_replace( $serialized_pattern, 'a:', $serialized );
		$serialized = str_replace( "s:8:\"\0*\0_args\"", 's:5:"_args"', $serialized );

		$to = unserialize( $serialized );

		$this->assertInternalType( 'array', $to );
		$this->assertArrayHasKey( '_args', $to );
		$this->assertInternalType( 'array', $to['_args'] );

		$to = $to['_args'];

		$this->assertEquals( $this->pods_object->get_args(), $to );
		$this->assertEquals( $this->pods_object->get_id(), $to['id'] );
		$this->assertEquals( $this->pods_object->get_name(), $to['name'] );
		$this->assertEquals( $this->pods_object->get_parent(), $to['parent'] );
		$this->assertEquals( $this->pods_object->get_group(), $to['group'] );
	}

	/**
	 * @covers PodsObject::jsonSerialize
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
	 * @covers PodsObject::__toString
	 */
	public function test_string() {
		$this->assertTrue( method_exists( $this->pods_object, '__toString' ), 'Method __toString does not exist' );

		$to = (string) $this->pods_object;

		$this->assertInternalType( 'string', $to );
		$this->assertEquals( $this->pods_object->get_identifier(), $to );
	}

	/**
	 * @covers PodsObject::from_serialized
	 */
	public function test_from_serialized() {
		$this->assertTrue( method_exists( $this->pods_object, 'from_serialized' ), 'Method from_serialized does not exist' );

		$serialized = serialize( $this->pods_object );

		$to = $this->pods_object->from_serialized( $serialized );

		$this->assertInstanceOf( PodsObject::class, $to );
		$this->assertEquals( $this->pods_object->get_object_type(), $to->get_object_type() );
		$this->assertEquals( $this->pods_object->get_id(), $to->get_id() );
		$this->assertEquals( $this->pods_object->get_name(), $to->get_name() );
		$this->assertEquals( $this->pods_object->get_parent(), $to->get_parent() );
		$this->assertEquals( $this->pods_object->get_group(), $to->get_group() );
	}

	/**
	 * @covers PodsObject::from_serialized
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
	 * @covers PodsObject::from_json
	 */
	public function test_from_json() {
		$this->assertTrue( method_exists( $this->pods_object, 'from_json' ), 'Method from_json does not exist' );

		$json = json_encode( $this->pods_object );

		$to = $this->pods_object->from_json( $json );

		$this->assertInstanceOf( PodsObject::class, $to );
		$this->assertEquals( $this->pods_object->get_object_type(), $to->get_object_type() );
		$this->assertEquals( $this->pods_object->get_id(), $to->get_id() );
		$this->assertEquals( $this->pods_object->get_name(), $to->get_name() );
		$this->assertEquals( $this->pods_object->get_parent(), $to->get_parent() );
		$this->assertEquals( $this->pods_object->get_group(), $to->get_group() );
	}

	/**
	 * @covers PodsObject::from_json
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
	 * @covers PodsObject::from_wp_post
	 */
	public function test_from_wp_post() {
		$this->assertTrue( method_exists( $this->pods_object, 'from_wp_post' ), 'Method from_wp_post does not exist' );

		$args = array(
			'post_title'   => $this->pods_object->get_arg( 'label' ),
			'post_name'    => $this->pods_object->get_name(),
			'post_content' => $this->pods_object->get_arg( 'description' ),
			'post_parent'  => $this->pods_object->get_arg( 'parent' ),
			'post_type'    => 'doesntmatter',
		);

		$post_id = wp_insert_post( $args );

		// Update object ID to match.
		$this->pods_object->set_arg( 'id', $post_id );

		$post = get_post( $post_id );

		$to = $this->pods_object->from_wp_post( $post );

		$this->assertInstanceOf( PodsObject::class, $to );
		$this->assertEquals( $this->pods_object->get_object_type(), $to->get_object_type() );
		$this->assertEquals( $this->pods_object->get_id(), $to->get_id() );
		$this->assertEquals( $this->pods_object->get_name(), $to->get_name() );
		$this->assertEquals( $this->pods_object->get_parent(), $to->get_parent() );
		$this->assertEquals( $this->pods_object->get_group(), $to->get_group() );
	}

	/**
	 * @covers PodsObject::from_wp_post
	 */
	public function test_from_wp_post_args() {
		$this->assertTrue( method_exists( $this->pods_object, 'from_wp_post' ), 'Method from_wp_post does not exist' );

		$args = array(
			'post_title'   => $this->pods_object->get_arg( 'label' ),
			'post_name'    => $this->pods_object->get_name(),
			'post_content' => $this->pods_object->get_arg( 'description' ),
			'post_parent'  => $this->pods_object->get_arg( 'parent' ),
			'post_type'    => 'doesntmatter',
		);

		$post_id = wp_insert_post( $args );

		// Update object ID to match.
		$this->pods_object->set_arg( 'id', $post_id );

		$to = $this->pods_object->from_wp_post( $post_id, true );

		$this->assertInternalType( 'array', $to );
		$this->assertEquals( $this->pods_object->get_args(), $to );
		$this->assertEquals( $this->pods_object->get_id(), $to['id'] );
		$this->assertEquals( $this->pods_object->get_name(), $to['name'] );
		$this->assertEquals( $this->pods_object->get_parent(), $to['parent'] );
		$this->assertEquals( $this->pods_object->get_group(), $to['group'] );
	}

	/**
	 * @covers PodsObject::offsetExists
	 * @covers PodsObject::offsetGet
	 * @covers PodsObject::offsetSet
	 * @covers PodsObject::offsetUnset
	 * @covers PodsObject::get_arg
	 * @covers PodsObject::set_arg
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

		// Test unset handling for ArrayAccess for reserved arguments.
		unset( $this->pods_object['id'], $this->pods_object['name'], $this->pods_object['parent'], $this->pods_object['group'] );

		// Confirm ArrayAccess arguments are now empty strings for reserved arguments.
		$this->assertEquals( $this->pods_object['id'], '' );
		$this->assertEquals( $this->pods_object['name'], '' );
		$this->assertEquals( $this->pods_object['parent'], '' );
		$this->assertEquals( $this->pods_object['group'], '' );
	}

	/**
	 * @covers PodsObject::setup
	 * @covers PodsObject::get_args
	 * @covers PodsObject::get_arg
	 * @covers PodsObject::get_group
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
	 * @covers PodsObject::get_arg
	 * @covers PodsObject::set_arg
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
	 * @covers PodsObject::is_valid
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
	 * @covers PodsObject::get_identifier
	 */
	public function test_get_identifier() {
		$this->assertTrue( method_exists( $this->pods_object, 'get_identifier' ), 'Method get_identifier does not exist' );

		$identifier = sprintf( '%s/%s', $this->pods_object->get_object_type(), $this->pods_object->get_name() );

		$this->assertEquals( $identifier, $this->pods_object->get_identifier() );

		// Set parent.
		$this->pods_object->set_arg( 'parent', 555 );

		$identifier = sprintf( '%s/%s/%s', $this->pods_object->get_object_type(), $this->pods_object->get_parent(), $this->pods_object->get_name() );

		$this->assertEquals( $identifier, $this->pods_object->get_identifier() );
	}

	/**
	 * @covers PodsObject::get_object_type
	 */
	public function test_get_object_type() {
		$this->assertTrue( method_exists( $this->pods_object, 'get_object_type' ), 'Method get_object_type does not exist' );

		$this->assertEquals( 'object', $this->pods_object->get_object_type() );
	}

	/**
	 * @covers PodsObject::get_args
	 */
	public function test_get_args() {
		$this->assertTrue( method_exists( $this->pods_object, 'get_args' ), 'Method get_args does not exist' );

		$this->assertEquals( $this->args, $this->pods_object->get_args() );
	}

	/**
	 * @covers PodsObject::get_name
	 */
	public function test_get_name() {
		$this->assertTrue( method_exists( $this->pods_object, 'get_name' ), 'Method get_name does not exist' );

		$this->assertEquals( $this->args['name'], $this->pods_object->get_name() );
	}

	/**
	 * @covers PodsObject::get_id
	 */
	public function test_get_id() {
		$this->assertTrue( method_exists( $this->pods_object, 'get_id' ), 'Method get_id does not exist' );

		$this->assertEquals( $this->args['id'], $this->pods_object->get_id() );
	}

	/**
	 * @covers PodsObject::get_parent
	 */
	public function test_get_parent() {
		$this->assertTrue( method_exists( $this->pods_object, 'get_parent' ), 'Method get_parent does not exist' );

		$this->assertEquals( $this->args['parent'], $this->pods_object->get_parent() );
	}

	/**
	 * @covers PodsObject::get_group
	 */
	public function test_get_group() {
		$this->assertTrue( method_exists( $this->pods_object, 'get_group' ), 'Method get_group does not exist' );

		$this->assertEquals( $this->args['group'], $this->pods_object->get_group() );
	}
}
