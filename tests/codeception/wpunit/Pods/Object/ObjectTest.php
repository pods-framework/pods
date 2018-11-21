<?php

namespace Pods_Unit_Tests\Object;

use Pods_Unit_Tests\Pods_ObjectTestCase;
use Pods_Object;

/**
 * @group  pods-object
 * @covers Pods_Object
 */
class ObjectTest extends Pods_ObjectTestCase {

	/**
	 * @covers Pods_Object::__sleep
	 */
	public function test_serialization() {
		$this->assertTrue( method_exists( $this->pods_object_field, '__sleep' ), 'Method __sleep does not exist' );

		$serialized = serialize( $this->pods_object_field );

		$class_name = get_class( $this->pods_object_field );

		// Convert serialized object to array for testing against.
		$serialized_pattern = sprintf( 'O:%d:"%s":', strlen( $class_name ), $class_name );

		$serialized = str_replace( $serialized_pattern, 'a:', $serialized );
		$serialized = str_replace( "s:7:\"\0*\0args\"", 's:4:"args"', $serialized );

		$to = unserialize( $serialized );

		$this->assertInternalType( 'array', $to );
		$this->assertArrayHasKey( 'args', $to );
		$this->assertInternalType( 'array', $to['args'] );

		$to = $to['args'];

		$this->assertEquals( $this->pods_object_field->get_args(), $to );
		$this->assertEquals( $this->pods_object_field->get_id(), $to['id'] );
		$this->assertEquals( $this->pods_object_field->get_name(), $to['name'] );
		$this->assertEquals( $this->pods_object_field->get_parent(), $to['parent'] );
		$this->assertEquals( $this->pods_object_field->get_group(), $to['group'] );
	}

	/**
	 * @covers Pods_Object::jsonSerialize
	 */
	public function test_json() {
		$this->assertTrue( method_exists( $this->pods_object_field, 'jsonSerialize' ), 'Method jsonSerialize does not exist' );

		$json = json_encode( $this->pods_object_field );

		$to = json_decode( $json, true );

		$this->assertInternalType( 'array', $to );
		$this->assertEquals( $this->pods_object_field->get_args(), $to );
		$this->assertEquals( $this->pods_object_field->get_id(), $to['id'] );
		$this->assertEquals( $this->pods_object_field->get_name(), $to['name'] );
		$this->assertEquals( $this->pods_object_field->get_parent(), $to['parent'] );
		$this->assertEquals( $this->pods_object_field->get_group(), $to['group'] );
	}

	/**
	 * @covers Pods_Object::__toString
	 */
	public function test_string() {
		$this->assertTrue( method_exists( $this->pods_object_field, '__toString' ), 'Method __toString does not exist' );

		$to = (string) $this->pods_object_field;

		$this->assertInternalType( 'string', $to );
		$this->assertEquals( $this->pods_object_field->get_identifier(), $to );
	}

	/**
	 * @covers Pods_Object::from_serialized
	 */
	public function test_from_serialized() {
		$this->assertTrue( method_exists( $this->pods_object_field, 'from_serialized' ), 'Method from_serialized does not exist' );

		$serialized = serialize( $this->pods_object_field );

		$to = $this->pods_object_field->from_serialized( $serialized );

		$this->assertInstanceOf( Pods_Object::class, $to );
		$this->assertEquals( $this->pods_object_field->get_object_type(), $to->get_object_type() );
		$this->assertEquals( $this->pods_object_field->get_id(), $to->get_id() );
		$this->assertEquals( $this->pods_object_field->get_name(), $to->get_name() );
		$this->assertEquals( $this->pods_object_field->get_parent(), $to->get_parent() );
		$this->assertEquals( $this->pods_object_field->get_group(), $to->get_group() );
	}

	/**
	 * @covers Pods_Object::from_serialized
	 */
	public function test_from_serialized_args() {
		$this->assertTrue( method_exists( $this->pods_object_field, 'from_serialized' ), 'Method from_serialized does not exist' );

		$serialized = serialize( $this->pods_object_field );

		$to = $this->pods_object_field->from_serialized( $serialized, true );

		$this->assertInternalType( 'array', $to );
		$this->assertEquals( $this->pods_object_field->get_args(), $to );
		$this->assertEquals( $this->pods_object_field->get_id(), $to['id'] );
		$this->assertEquals( $this->pods_object_field->get_name(), $to['name'] );
		$this->assertEquals( $this->pods_object_field->get_parent(), $to['parent'] );
		$this->assertEquals( $this->pods_object_field->get_group(), $to['group'] );
	}

	/**
	 * @covers Pods_Object::from_json
	 */
	public function test_from_json() {
		$this->assertTrue( method_exists( $this->pods_object_field, 'from_json' ), 'Method from_json does not exist' );

		$json = json_encode( $this->pods_object_field );

		$to = $this->pods_object_field->from_json( $json );

		$this->assertInstanceOf( Pods_Object::class, $to );
		$this->assertEquals( $this->pods_object_field->get_object_type(), $to->get_object_type() );
		$this->assertEquals( $this->pods_object_field->get_id(), $to->get_id() );
		$this->assertEquals( $this->pods_object_field->get_name(), $to->get_name() );
		$this->assertEquals( $this->pods_object_field->get_parent(), $to->get_parent() );
		$this->assertEquals( $this->pods_object_field->get_group(), $to->get_group() );
	}

	/**
	 * @covers Pods_Object::from_json
	 */
	public function test_from_json_args() {
		$this->assertTrue( method_exists( $this->pods_object_field, 'from_json' ), 'Method from_json does not exist' );

		$json = json_encode( $this->pods_object_field );

		$to = $this->pods_object_field->from_json( $json, true );

		$this->assertInternalType( 'array', $to );
		$this->assertEquals( $this->pods_object_field->get_args(), $to );
		$this->assertEquals( $this->pods_object_field->get_id(), $to['id'] );
		$this->assertEquals( $this->pods_object_field->get_name(), $to['name'] );
		$this->assertEquals( $this->pods_object_field->get_parent(), $to['parent'] );
		$this->assertEquals( $this->pods_object_field->get_group(), $to['group'] );
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
		$this->assertTrue( method_exists( $this->pods_object_field, 'offsetExists' ), 'Method offsetExists does not exist' );
		$this->assertTrue( method_exists( $this->pods_object_field, 'offsetGet' ), 'Method offsetGet does not exist' );
		$this->assertTrue( method_exists( $this->pods_object_field, 'offsetSet' ), 'Method offsetSet does not exist' );
		$this->assertTrue( method_exists( $this->pods_object_field, 'offsetUnset' ), 'Method offsetUnset does not exist' );

		// Confirm argument get matches ArrayAccess.
		$this->assertEquals( $this->pods_object_field->get_id(), $this->pods_object_field['id'] );
		$this->assertEquals( $this->pods_object_field->get_name(), $this->pods_object_field['name'] );
		$this->assertEquals( $this->pods_object_field->get_parent(), $this->pods_object_field['parent'] );
		$this->assertEquals( $this->pods_object_field->get_group(), $this->pods_object_field['group'] );

		$list = array( $this->pods_object_field );

		$this->assertEquals( array( $this->pods_object_field->get_id() ), wp_list_pluck( $list, 'id' ) );
		$this->assertEquals( array( $this->pods_object_field->get_name() ), wp_list_pluck( $list, 'name' ) );
		$this->assertEquals( array( $this->pods_object_field->get_parent() ), wp_list_pluck( $list, 'parent' ) );
		$this->assertEquals( array( $this->pods_object_field->get_group() ), wp_list_pluck( $list, 'group' ) );
		$this->assertEquals( array( $this->pods_object_field->get_arg( 'custom1' ) ), wp_list_pluck( $list, 'custom1' ) );

		// Test non-existent arguments and handling for ArrayAccess.
		$this->assertNull( $this->pods_object_field->get_arg( 'fourohfour' ) );
		$this->assertEquals( $this->pods_object_field->get_arg( 'fourohfour' ), $this->pods_object_field['fourohfour'] );
		$this->assertFalse( isset( $this->pods_object_field['fourohfour'] ) );

		// Test isset for ArrayAccess.
		$this->assertTrue( isset( $this->pods_object_field['id'] ) );
		$this->assertTrue( isset( $this->pods_object_field['name'] ) );
		$this->assertTrue( isset( $this->pods_object_field['parent'] ) );
		$this->assertTrue( isset( $this->pods_object_field['group'] ) );

		// Test unset handling for ArrayAccess served arguments.
		unset( $this->pods_object_field['id'], $this->pods_object_field['name'], $this->pods_object_field['parent'], $this->pods_object_field['group'] );

		// Confirm ArrayAccess arguments are now empty strings for reserved arguments.
		$this->assertEquals( $this->pods_object_field['id'], '' );
		$this->assertEquals( $this->pods_object_field['name'], '' );
		$this->assertEquals( $this->pods_object_field['parent'], '' );
		$this->assertEquals( $this->pods_object_field['group'], '' );
	}

	/**
	 * @covers Pods_Object::setup
	 * @covers Pods_Object::get_args
	 * @covers Pods_Object::get_arg
	 * @covers Pods_Object::get_group
	 */
	public function test_setup() {
		$this->assertTrue( method_exists( $this->pods_object_field, 'setup' ), 'Method setup does not exist' );

		$args = array(
			'name'       => 'fiver',
			'id'         => 555,
			'parent'     => 5555,
			'group'      => 55555,
			'fourohfour' => 405,
		);

		$this->pods_object_field->setup( $args );

		$this->assertArraySubset( $args, $this->pods_object_field->get_args() );
		$this->assertEquals( '', $this->pods_object_field->get_arg( 'label' ) );
		$this->assertEquals( '', $this->pods_object_field->get_arg( 'description' ) );

		$args2 = array(
			'name'   => 'seven',
			'id'     => 777,
			'parent' => 7777,
		);

		$this->pods_object_field->setup( $args2 );

		$this->assertArraySubset( $args2, $this->pods_object_field->get_args() );
		$this->assertEquals( '', $this->pods_object_field->get_group() );
		$this->assertEquals( '', $this->pods_object_field->get_arg( 'label' ) );
		$this->assertEquals( '', $this->pods_object_field->get_arg( 'description' ) );
		$this->assertNull( $this->pods_object_field->get_arg( 'fourohfour' ) );
	}

	/**
	 * @covers Pods_Object::get_arg
	 * @covers Pods_Object::set_arg
	 */
	public function test_get_set_arg() {
		// Confirm methods exist.
		$this->assertTrue( method_exists( $this->pods_object_field, 'get_arg' ), 'Method get_arg does not exist' );
		$this->assertTrue( method_exists( $this->pods_object_field, 'set_arg' ), 'Method set_arg does not exist' );

		// Confirm get_arg matches method access.
		$this->assertEquals( $this->pods_object_field->get_id(), $this->pods_object_field->get_arg( 'id' ) );
		$this->assertEquals( $this->pods_object_field->get_name(), $this->pods_object_field->get_arg( 'name' ) );
		$this->assertEquals( $this->pods_object_field->get_parent(), $this->pods_object_field->get_arg( 'parent' ) );
		$this->assertEquals( $this->pods_object_field->get_group(), $this->pods_object_field->get_arg( 'group' ) );

		// Test non-existent arguments and handling for ArrayAccess.
		$this->assertNull( $this->pods_object_field->get_arg( 'fourohfour' ) );

		$args = array(
			'name'       => 'fiver',
			'id'         => 555,
			'parent'     => 5555,
			'group'      => 55555,
			'fourohfour' => 405,
		);

		$this->pods_object_field->set_arg( 'name', $args['name'] );
		$this->pods_object_field->set_arg( 'id', $args['id'] );
		$this->pods_object_field->set_arg( 'parent', $args['parent'] );
		$this->pods_object_field->set_arg( 'group', $args['group'] );
		$this->pods_object_field->set_arg( 'fourohfour', $args['fourohfour'] );

		$this->assertEquals( $this->pods_object_field->get_arg( 'name' ), $args['name'] );
		$this->assertEquals( $this->pods_object_field->get_arg( 'id' ), $args['id'] );
		$this->assertEquals( $this->pods_object_field->get_arg( 'parent' ), $args['parent'] );
		$this->assertEquals( $this->pods_object_field->get_arg( 'group' ), $args['group'] );
		$this->assertEquals( $this->pods_object_field->get_arg( 'fourohfour' ), $args['fourohfour'] );
	}

	/**
	 * @covers Pods_Object::is_valid
	 */
	public function test_is_valid() {
		$this->assertTrue( method_exists( $this->pods_object_field, 'is_valid' ), 'Method is_valid does not exist' );

		$this->assertTrue( $this->pods_object_field->is_valid() );

		$args = array(
			'name'   => '',
			'id'     => '',
			'parent' => '',
			'group'  => '',
		);

		$this->pods_object_field->setup( $args );

		$this->assertFalse( $this->pods_object_field->is_valid() );
	}

	/**
	 * @covers Pods_Object::get_identifier_from_args
	 */
	public function test_get_identifier_from_args() {
		$this->assertTrue( method_exists( $this->pods_object_field, 'get_identifier_from_args' ), 'Method get_identifier_from_args does not exist' );

		$this->assertEquals( $this->pods_object_field->get_identifier(), Pods_Object::get_identifier_from_args( $this->field_args ) );

		$identifier = sprintf( '%s/%s/%s', $this->pods_object_field->get_object_type(), $this->pods_object_field->get_parent(), $this->pods_object_field->get_name() );

		$this->assertEquals( $identifier, Pods_Object::get_identifier_from_args( $this->pods_object_field->get_args() ) );

		// Unset parent.
		$this->pods_object_field->set_arg( 'parent', null );

		$identifier = sprintf( '%s/%s', $this->pods_object_field->get_object_type(), $this->pods_object_field->get_name() );

		$this->assertEquals( $identifier, Pods_Object::get_identifier_from_args( $this->pods_object_field->get_args() ) );
	}

	/**
	 * @covers Pods_Object::get_identifier
	 */
	public function test_get_identifier() {
		$this->assertTrue( method_exists( $this->pods_object_field, 'get_identifier' ), 'Method get_identifier does not exist' );

		$identifier = sprintf( '%s/%s/%s', $this->pods_object_field->get_object_type(), $this->pods_object_field->get_parent(), $this->pods_object_field->get_name() );

		$this->assertEquals( $identifier, $this->pods_object_field->get_identifier() );

		// Unset parent.
		$this->pods_object_field->set_arg( 'parent', null );

		$identifier = sprintf( '%s/%s', $this->pods_object_field->get_object_type(), $this->pods_object_field->get_name() );

		$this->assertEquals( $identifier, $this->pods_object_field->get_identifier() );
	}

	/**
	 * @covers Pods_Object::get_args
	 */
	public function test_get_args() {
		$this->assertTrue( method_exists( $this->pods_object_field, 'get_args' ), 'Method get_args does not exist' );

		$this->assertEquals( $this->field_args, $this->pods_object_field->get_args() );
	}

	/**
	 * Provide get_* methods to be tested.
	 *
	 * @return array
	 */
	public function provider_methods() {
		return array(
			array( 'object_type' ),
			array( 'storage_type' ),
			array( 'name' ),
			array( 'id' ),
			array( 'parent' ),
			array( 'group' ),
			array( 'label' ),
			array( 'description' ),
		);
	}

	/**
	 * @covers       Pods_Object::get_object_type
	 * @covers       Pods_Object::get_storage_type
	 * @covers       Pods_Object::get_name
	 * @covers       Pods_Object::get_id
	 * @covers       Pods_Object::get_parent
	 * @covers       Pods_Object::get_group
	 * @covers       Pods_Object::get_label
	 * @covers       Pods_Object::get_description
	 *
	 * @dataProvider provider_methods
	 *
	 * @param string $property Property to test.
	 */
	public function test_get( $property ) {
		$method = 'get_' . $property;

		codecept_debug( 'Method: ' . $method );

		$this->assertEquals( $this->field_args[ $property ], call_user_func( array(
			$this->pods_object_field,
			$method
		) ) );
	}

	/**
	 * Provide parent get_* methods to be tested.
	 *
	 * @return array
	 */
	public function provider_parent_methods() {
		return array(
			array( 'object_type' ),
			array( 'storage_type' ),
			array( 'name' ),
			array( 'id' ),
			array( 'label' ),
			array( 'description' ),
		);
	}

	/**
	 * @covers       Pods_Object::get_parent_object_type
	 * @covers       Pods_Object::get_parent_storage_type
	 * @covers       Pods_Object::get_parent_name
	 * @covers       Pods_Object::get_parent_id
	 * @covers       Pods_Object::get_parent_label
	 * @covers       Pods_Object::get_parent_description
	 *
	 * @dataProvider provider_parent_methods
	 *
	 * @param string $property Property to test.
	 */
	public function test_get_parent( $property ) {
		$method = 'get_parent_' . $property;

		codecept_debug( 'Method: ' . $method );

		$this->assertEquals( $this->pod_args[ $property ], call_user_func( array(
			$this->pods_object_field,
			$method
		) ) );
	}

	/**
	 * Provide group get_* methods to be tested.
	 *
	 * @return array
	 */
	public function provider_group_methods() {
		return array(
			array( 'object_type' ),
			array( 'storage_type' ),
			array( 'name' ),
			array( 'id' ),
			array( 'label' ),
			array( 'description' ),
		);
	}

	/**
	 * @covers       Pods_Object::get_group_object_type
	 * @covers       Pods_Object::get_group_storage_type
	 * @covers       Pods_Object::get_group_name
	 * @covers       Pods_Object::get_group_id
	 * @covers       Pods_Object::get_group_label
	 * @covers       Pods_Object::get_group_description
	 *
	 * @dataProvider provider_group_methods
	 *
	 * @param string $property Property to test.
	 */
	public function test_get_group( $property ) {
		$method = 'get_group_' . $property;

		codecept_debug( 'Method: ' . $method );

		$this->assertEquals( $this->group_args[ $property ], call_user_func( array(
			$this->pods_object_field,
			$method
		) ) );
	}

	/**
	 * @covers Pods_Object::get_fields
	 */
	public function test_get_fields() {
		$this->assertTrue( method_exists( $this->pods_object_pod, 'get_fields' ), 'Method get_fields does not exist' );

		$fields = $this->pods_object_pod->get_fields();

		$this->assertCount( 1, $fields );
		$this->assertInstanceOf( Pods_Object::class, reset( $fields ) );
	}

}
