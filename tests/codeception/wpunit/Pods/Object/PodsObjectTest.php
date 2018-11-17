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
	 * @var PodsObject
	 */
	private $pods_object;

	public function setUp() {
		$args = array(
			'id'          => 123,
			'name'        => 'test',
			'label'       => 'Test',
			'description' => 'Testing',
			'parent'      => '',
			'group'       => '',
		);

		$this->pods_object = $this->getMockBuilder( PodsObject::class )->getMockForAbstractClass();
		$this->pods_object->setup( $args );
	}

	public function tearDown() {
		unset( $this->pods_object );
	}

	/**
	 * @covers PodsObject::__sleep
	 */
	public function test_serialization() {
		$this->assertTrue( method_exists( $this->pods_object, '__sleep' ), 'Method from_serialized does not exist' );

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
	 * @covers PodsObject::from_serialized
	 */
	public function test_from_serialized() {
		$this->assertTrue( method_exists( $this->pods_object, 'from_serialized' ), 'Method from_serialized does not exist' );

		$serialized = serialize( $this->pods_object );

		$to = $this->pods_object->from_serialized( $serialized );

		$this->assertInstanceOf( PodsObject::class, $to );
		$this->assertEquals( $this->pods_object->get_type(), $to->get_type() );
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
		$this->assertTrue( method_exists( $this->pods_object, 'from_json' ), 'Method from_serialized does not exist' );

		$json = json_encode( $this->pods_object );

		$to = $this->pods_object->from_json( $json );

		$this->assertInstanceOf( PodsObject::class, $to );
		$this->assertEquals( $this->pods_object->get_type(), $to->get_type() );
		$this->assertEquals( $this->pods_object->get_id(), $to->get_id() );
		$this->assertEquals( $this->pods_object->get_name(), $to->get_name() );
		$this->assertEquals( $this->pods_object->get_parent(), $to->get_parent() );
		$this->assertEquals( $this->pods_object->get_group(), $to->get_group() );
	}

	/**
	 * @covers PodsObject::from_json
	 */
	public function test_from_json_args() {
		$this->assertTrue( method_exists( $this->pods_object, 'from_json' ), 'Method from_serialized does not exist' );

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
		$this->assertTrue( method_exists( $this->pods_object, 'from_wp_post' ), 'Method from_serialized does not exist' );

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
		$this->assertEquals( $this->pods_object->get_type(), $to->get_type() );
		$this->assertEquals( $this->pods_object->get_id(), $to->get_id() );
		$this->assertEquals( $this->pods_object->get_name(), $to->get_name() );
		$this->assertEquals( $this->pods_object->get_parent(), $to->get_parent() );
		$this->assertEquals( $this->pods_object->get_group(), $to->get_group() );
	}

	/**
	 * @covers PodsObject::from_wp_post
	 */
	public function test_from_wp_post_args() {
		$this->assertTrue( method_exists( $this->pods_object, 'from_wp_post' ), 'Method from_serialized does not exist' );

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
}
