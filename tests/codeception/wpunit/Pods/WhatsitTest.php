<?php

namespace Pods_Unit_Tests\Pods;

use Pods_Unit_Tests\Pods_WhatsitTestCase;
use Pods\Whatsit;
use Pods\Whatsit\Field;
use Pods\Whatsit\Group;
use Pods\Whatsit\Object_Field;

/**
 * @group  pods-whatsit
 * @covers Whatsit
 */
class WhatsitTest extends Pods_WhatsitTestCase {

	/**
	 * @covers Whatsit::__sleep
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
	 * @covers Whatsit::jsonSerialize
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
	 * @covers Whatsit::__toString
	 */
	public function test_string() {
		$this->assertTrue( method_exists( $this->pods_object_field, '__toString' ), 'Method __toString does not exist' );

		$to = (string) $this->pods_object_field;

		$this->assertInternalType( 'string', $to );
		$this->assertEquals( $this->pods_object_field->get_identifier(), $to );
	}

	/**
	 * @covers Whatsit::from_serialized
	 */
	public function test_from_serialized() {
		$this->assertTrue( method_exists( $this->pods_object_field, 'from_serialized' ), 'Method from_serialized does not exist' );

		$serialized = serialize( $this->pods_object_field );

		$to = $this->pods_object_field->from_serialized( $serialized );

		$this->assertInstanceOf( Whatsit::class, $to );
		$this->assertEquals( $this->pods_object_field->get_object_type(), $to->get_object_type() );
		$this->assertEquals( $this->pods_object_field->get_id(), $to->get_id() );
		$this->assertEquals( $this->pods_object_field->get_name(), $to->get_name() );
		$this->assertEquals( $this->pods_object_field->get_parent(), $to->get_parent() );
		$this->assertEquals( $this->pods_object_field->get_group(), $to->get_group() );
	}

	/**
	 * @covers Whatsit::from_serialized
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
	 * @covers Whatsit::from_json
	 */
	public function test_from_json() {
		$this->assertTrue( method_exists( $this->pods_object_field, 'from_json' ), 'Method from_json does not exist' );

		$json = json_encode( $this->pods_object_field );

		$to = $this->pods_object_field->from_json( $json );

		$this->assertInstanceOf( Whatsit::class, $to );
		$this->assertEquals( $this->pods_object_field->get_object_type(), $to->get_object_type() );
		$this->assertEquals( $this->pods_object_field->get_id(), $to->get_id() );
		$this->assertEquals( $this->pods_object_field->get_name(), $to->get_name() );
		$this->assertEquals( $this->pods_object_field->get_parent(), $to->get_parent() );
		$this->assertEquals( $this->pods_object_field->get_group(), $to->get_group() );
	}

	/**
	 * @covers Whatsit::from_json
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
	 * @covers Whatsit::offsetExists
	 * @covers Whatsit::offsetGet
	 * @covers Whatsit::offsetSet
	 * @covers Whatsit::offsetUnset
	 * @covers Whatsit::get_arg
	 * @covers Whatsit::set_arg
	 */
	public function test_array_access_pod() {
		// Confirm methods exist.
		$this->assertTrue( method_exists( $this->pods_object_pod, 'offsetExists' ), 'Method offsetExists does not exist' );
		$this->assertTrue( method_exists( $this->pods_object_pod, 'offsetGet' ), 'Method offsetGet does not exist' );
		$this->assertTrue( method_exists( $this->pods_object_pod, 'offsetSet' ), 'Method offsetSet does not exist' );
		$this->assertTrue( method_exists( $this->pods_object_pod, 'offsetUnset' ), 'Method offsetUnset does not exist' );

		// Confirm argument get matches ArrayAccess.
		$this->assertEquals( $this->pods_object_pod->get_id(), $this->pods_object_pod['id'] );
		$this->assertEquals( $this->pods_object_pod->get_name(), $this->pods_object_pod['name'] );
		$this->assertEquals( $this->pods_object_pod->get_parent(), $this->pods_object_pod['parent'] );
		$this->assertEquals( $this->pods_object_pod->get_group(), $this->pods_object_pod['group'] );
		$this->assertEquals( $this->pods_object_pod->get_fields(), $this->pods_object_pod['fields'] );
		$this->assertEquals( $this->pods_object_pod->get_object_fields(), $this->pods_object_pod['object_fields'] );
		$this->assertEquals( $this->pods_object_pod->get_all_fields(), $this->pods_object_pod['all_fields'] );
		$this->assertEquals( $this->pods_object_pod->get_groups(), $this->pods_object_pod['groups'] );
		$this->assertEquals( $this->pods_object_pod->get_table_info(), $this->pods_object_pod['table_info'] );
		$this->assertEquals( $this->pods_object_pod->get_args(), $this->pods_object_pod['options'] );

		// Confirm argument get matches Object __get.
		$this->assertEquals( $this->pods_object_pod->get_id(), $this->pods_object_pod->id );
		$this->assertEquals( $this->pods_object_pod->get_name(), $this->pods_object_pod->name );
		$this->assertEquals( $this->pods_object_pod->get_parent(), $this->pods_object_pod->parent );
		$this->assertEquals( $this->pods_object_pod->get_group(), $this->pods_object_pod->group );
		$this->assertEquals( $this->pods_object_pod->get_fields(), $this->pods_object_pod->fields );
		$this->assertEquals( $this->pods_object_pod->get_object_fields(), $this->pods_object_pod->object_fields );
		$this->assertEquals( $this->pods_object_pod->get_all_fields(), $this->pods_object_pod->all_fields );
		$this->assertEquals( $this->pods_object_pod->get_groups(), $this->pods_object_pod->groups );
		$this->assertEquals( $this->pods_object_pod->get_table_info(), $this->pods_object_pod->table_info );
		$this->assertEquals( $this->pods_object_pod->get_args(), $this->pods_object_pod->options );

		$list = array( $this->pods_object_pod );

		$this->assertEquals( array( $this->pods_object_pod->get_id() ), wp_list_pluck( $list, 'id' ) );
		$this->assertEquals( array( $this->pods_object_pod->get_name() ), wp_list_pluck( $list, 'name' ) );
		$this->assertEquals( array( $this->pods_object_pod->get_parent() ), wp_list_pluck( $list, 'parent' ) );
		$this->assertEquals( array( $this->pods_object_pod->get_group() ), wp_list_pluck( $list, 'group' ) );
		$this->assertEquals( array( $this->pods_object_pod->get_arg( 'custom1' ) ), wp_list_pluck( $list, 'custom1' ) );

		// Test non-existent arguments and handling for ArrayAccess.
		$this->assertNull( $this->pods_object_pod->get_arg( 'fourohfour' ) );
		$this->assertEquals( $this->pods_object_pod->get_arg( 'fourohfour' ), $this->pods_object_pod['fourohfour'] );
		$this->assertFalse( isset( $this->pods_object_pod['fourohfour'] ) );

		// Test isset for ArrayAccess.
		$this->assertTrue( isset( $this->pods_object_pod['id'] ) );
		$this->assertTrue( isset( $this->pods_object_pod['name'] ) );
		$this->assertTrue( isset( $this->pods_object_pod['parent'] ) );
		$this->assertTrue( isset( $this->pods_object_pod['group'] ) );

		// Test unset handling for ArrayAccess served arguments.
		unset( $this->pods_object_pod['id'], $this->pods_object_pod['name'], $this->pods_object_pod['parent'], $this->pods_object_pod['group'] );

		// Confirm ArrayAccess arguments are now empty strings for reserved arguments.
		$this->assertEquals( $this->pods_object_pod['id'], '' );
		$this->assertEquals( $this->pods_object_pod['name'], '' );
		$this->assertEquals( $this->pods_object_pod['parent'], '' );
		$this->assertEquals( $this->pods_object_pod['group'], '' );
	}

	/**
	 * @covers Whatsit::offsetExists
	 * @covers Whatsit::offsetGet
	 * @covers Whatsit::offsetSet
	 * @covers Whatsit::offsetUnset
	 * @covers Whatsit::get_arg
	 * @covers Whatsit::set_arg
	 */
	public function test_array_access_group() {
		// Confirm methods exist.
		$this->assertTrue( method_exists( $this->pods_object_group, 'offsetExists' ), 'Method offsetExists does not exist' );
		$this->assertTrue( method_exists( $this->pods_object_group, 'offsetGet' ), 'Method offsetGet does not exist' );
		$this->assertTrue( method_exists( $this->pods_object_group, 'offsetSet' ), 'Method offsetSet does not exist' );
		$this->assertTrue( method_exists( $this->pods_object_group, 'offsetUnset' ), 'Method offsetUnset does not exist' );

		// Confirm argument get matches ArrayAccess.
		$this->assertEquals( $this->pods_object_group->get_id(), $this->pods_object_group['id'] );
		$this->assertEquals( $this->pods_object_group->get_name(), $this->pods_object_group['name'] );
		$this->assertEquals( $this->pods_object_group->get_parent(), $this->pods_object_group['parent'] );
		$this->assertEquals( $this->pods_object_group->get_group(), $this->pods_object_group['group'] );
		$this->assertEquals( $this->pods_object_group->get_fields(), $this->pods_object_group['fields'] );
		$this->assertEquals( $this->pods_object_group->get_object_fields(), $this->pods_object_group['object_fields'] );
		$this->assertEquals( $this->pods_object_group->get_all_fields(), $this->pods_object_group['all_fields'] );
		$this->assertEquals( $this->pods_object_group->get_groups(), $this->pods_object_group['groups'] );
		$this->assertEquals( $this->pods_object_group->get_table_info(), $this->pods_object_group['table_info'] );
		$this->assertEquals( $this->pods_object_group->get_args(), $this->pods_object_group['options'] );

		// Confirm argument get matches Object __get.
		$this->assertEquals( $this->pods_object_group->get_id(), $this->pods_object_group->id );
		$this->assertEquals( $this->pods_object_group->get_name(), $this->pods_object_group->name );
		$this->assertEquals( $this->pods_object_group->get_parent(), $this->pods_object_group->parent );
		$this->assertEquals( $this->pods_object_group->get_group(), $this->pods_object_group->group );
		$this->assertEquals( $this->pods_object_group->get_fields(), $this->pods_object_group->fields );
		$this->assertEquals( $this->pods_object_group->get_object_fields(), $this->pods_object_group->object_fields );
		$this->assertEquals( $this->pods_object_group->get_all_fields(), $this->pods_object_group->all_fields );
		$this->assertEquals( $this->pods_object_group->get_groups(), $this->pods_object_group->groups );
		$this->assertEquals( $this->pods_object_group->get_table_info(), $this->pods_object_group->table_info );
		$this->assertEquals( $this->pods_object_group->get_args(), $this->pods_object_group->options );

		$list = array( $this->pods_object_group );

		$this->assertEquals( array( $this->pods_object_group->get_id() ), wp_list_pluck( $list, 'id' ) );
		$this->assertEquals( array( $this->pods_object_group->get_name() ), wp_list_pluck( $list, 'name' ) );
		$this->assertEquals( array( $this->pods_object_group->get_parent() ), wp_list_pluck( $list, 'parent' ) );
		$this->assertEquals( array( $this->pods_object_group->get_group() ), wp_list_pluck( $list, 'group' ) );
		$this->assertEquals( array( $this->pods_object_group->get_arg( 'custom1' ) ), wp_list_pluck( $list, 'custom1' ) );

		// Test non-existent arguments and handling for ArrayAccess.
		$this->assertNull( $this->pods_object_group->get_arg( 'fourohfour' ) );
		$this->assertEquals( $this->pods_object_group->get_arg( 'fourohfour' ), $this->pods_object_group['fourohfour'] );
		$this->assertFalse( isset( $this->pods_object_group['fourohfour'] ) );

		// Test isset for ArrayAccess.
		$this->assertTrue( isset( $this->pods_object_group['id'] ) );
		$this->assertTrue( isset( $this->pods_object_group['name'] ) );
		$this->assertTrue( isset( $this->pods_object_group['parent'] ) );
		$this->assertTrue( isset( $this->pods_object_group['group'] ) );

		// Test unset handling for ArrayAccess served arguments.
		unset( $this->pods_object_group['id'], $this->pods_object_group['name'], $this->pods_object_group['parent'], $this->pods_object_group['group'] );

		// Confirm ArrayAccess arguments are now empty strings for reserved arguments.
		$this->assertEquals( $this->pods_object_group['id'], '' );
		$this->assertEquals( $this->pods_object_group['name'], '' );
		$this->assertEquals( $this->pods_object_group['parent'], '' );
		$this->assertEquals( $this->pods_object_group['group'], '' );
	}

	/**
	 * @covers Whatsit::offsetExists
	 * @covers Whatsit::offsetGet
	 * @covers Whatsit::offsetSet
	 * @covers Whatsit::offsetUnset
	 * @covers Whatsit::get_arg
	 * @covers Whatsit::set_arg
	 */
	public function test_array_access_field() {
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
		$this->assertEquals( $this->pods_object_field->get_fields(), $this->pods_object_field['fields'] );
		$this->assertEquals( $this->pods_object_field->get_object_fields(), $this->pods_object_field['object_fields'] );
		$this->assertEquals( $this->pods_object_field->get_all_fields(), $this->pods_object_field['all_fields'] );
		$this->assertEquals( $this->pods_object_field->get_groups(), $this->pods_object_field['groups'] );
		$this->assertEquals( $this->pods_object_field->get_table_info(), $this->pods_object_field['table_info'] );
		$this->assertEquals( $this->pods_object_field->get_args(), $this->pods_object_field['options'] );

		// Confirm argument get matches Object __get.
		$this->assertEquals( $this->pods_object_field->get_id(), $this->pods_object_field->id );
		$this->assertEquals( $this->pods_object_field->get_name(), $this->pods_object_field->name );
		$this->assertEquals( $this->pods_object_field->get_parent(), $this->pods_object_field->parent );
		$this->assertEquals( $this->pods_object_field->get_group(), $this->pods_object_field->group );
		$this->assertEquals( $this->pods_object_field->get_fields(), $this->pods_object_field->fields );
		$this->assertEquals( $this->pods_object_field->get_object_fields(), $this->pods_object_field->object_fields );
		$this->assertEquals( $this->pods_object_field->get_all_fields(), $this->pods_object_field->all_fields );
		$this->assertEquals( $this->pods_object_field->get_groups(), $this->pods_object_field->groups );
		$this->assertEquals( $this->pods_object_field->get_table_info(), $this->pods_object_field->table_info );
		$this->assertEquals( $this->pods_object_field->get_args(), $this->pods_object_field->options );

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
	 * @covers Whatsit::setup
	 * @covers Whatsit::get_args
	 * @covers Whatsit::get_arg
	 * @covers Whatsit::get_group
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
	 * @covers Whatsit::get_arg
	 * @covers Whatsit::set_arg
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
	 * @covers Whatsit::is_valid
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
	 * @covers Whatsit::get_identifier_from_args
	 */
	public function test_get_identifier_from_args() {
		$this->assertTrue( method_exists( $this->pods_object_field, 'get_identifier_from_args' ), 'Method get_identifier_from_args does not exist' );

		$this->assertEquals( $this->pods_object_field->get_identifier(), Whatsit::get_identifier_from_args( $this->field_args ) );

		$identifier = sprintf( '%s/%s/%s', $this->pods_object_field->get_object_type(), $this->pods_object_field->get_parent(), $this->pods_object_field->get_name() );

		$this->assertEquals( $identifier, Whatsit::get_identifier_from_args( $this->pods_object_field->get_args() ) );

		// Unset parent.
		$this->pods_object_field->set_arg( 'parent', null );

		$identifier = sprintf( '%s/%s', $this->pods_object_field->get_object_type(), $this->pods_object_field->get_name() );

		$this->assertEquals( $identifier, Whatsit::get_identifier_from_args( $this->pods_object_field->get_args() ) );
	}

	/**
	 * @covers Whatsit::get_identifier
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
	 * @covers Whatsit::set_arg
	 * @covers Whatsit::get_arg
	 */
	public function test_set_arg() {
		$this->assertTrue( method_exists( $this->pods_object_field, 'set_arg' ), 'Method set_arg does not exist' );

		$this->pods_object_field->set_arg( 'custom5', 'Test5' );

		$this->assertEquals( 'Test5', $this->pods_object_field->get_arg( 'custom5', 'Nope' ) );
	}

	/**
	 * @covers Whatsit::get_arg
	 */
	public function test_get_arg() {
		$this->assertTrue( method_exists( $this->pods_object_field, 'get_arg' ), 'Method get_arg does not exist' );

		$this->assertEquals( 'value1-test-field', $this->pods_object_field->get_arg( 'custom1' ) );

		// Test default arg handling.
		$this->assertNull( $this->pods_object_field->get_arg( 'custom6' ) );
		$this->assertEquals( 'Nope', $this->pods_object_field->get_arg( 'custom6', 'Nope' ) );
	}

	/**
	 * @covers Whatsit::get_args
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
			array( 'object_storage_type' ),
			array( 'name' ),
			array( 'id' ),
			array( 'parent' ),
			array( 'group' ),
			array( 'label' ),
			array( 'description' ),
			array( 'type' ),
		);
	}

	/**
	 * @covers       Whatsit::get_object_type
	 * @covers       Whatsit::get_object_storage_type
	 * @covers       Whatsit::get_name
	 * @covers       Whatsit::get_id
	 * @covers       Whatsit::get_parent
	 * @covers       Whatsit::get_group
	 * @covers       Whatsit::get_label
	 * @covers       Whatsit::get_description
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
			array( 'object_storage_type' ),
			array( 'name' ),
			array( 'id' ),
			array( 'label' ),
			array( 'description' ),
			array( 'type' ),
		);
	}

	/**
	 * @covers       Whatsit::get_parent_object_type
	 * @covers       Whatsit::get_parent_object_storage_type
	 * @covers       Whatsit::get_parent_name
	 * @covers       Whatsit::get_parent_id
	 * @covers       Whatsit::get_parent_label
	 * @covers       Whatsit::get_parent_description
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
			array( 'object_storage_type' ),
			array( 'name' ),
			array( 'id' ),
			array( 'label' ),
			array( 'description' ),
			array( 'type' ),
		);
	}

	/**
	 * @covers       Whatsit::get_group_object_type
	 * @covers       Whatsit::get_group_object_storage_type
	 * @covers       Whatsit::get_group_name
	 * @covers       Whatsit::get_group_id
	 * @covers       Whatsit::get_group_label
	 * @covers       Whatsit::get_group_description
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
	 * @covers Whatsit::get_fields
	 */
	public function test_get_fields_pod() {
		$this->assertTrue( method_exists( $this->pods_object_pod, 'get_fields' ), 'Method get_fields does not exist' );

		$fields = $this->pods_object_pod->get_fields();

		$this->assertCount( 1, $fields );
		$this->assertInstanceOf( Field::class, reset( $fields ) );

		// Confirm internal cache still works.
		$fields = $this->pods_object_pod->get_fields();

		$this->assertCount( 1, $fields );
		$this->assertInstanceOf( Field::class, reset( $fields ) );
	}

	/**
	 * @covers Whatsit::get_fields
	 */
	public function test_get_fields_group() {
		$this->assertTrue( method_exists( $this->pods_object_group, 'get_fields' ), 'Method get_fields does not exist' );

		$fields = $this->pods_object_group->get_fields();

		$this->assertCount( 1, $fields );
		$this->assertInstanceOf( Field::class, reset( $fields ) );

		// Confirm internal cache still works.
		$fields = $this->pods_object_group->get_fields();

		$this->assertCount( 1, $fields );
		$this->assertInstanceOf( Field::class, reset( $fields ) );
	}

	/**
	 * @covers Whatsit::get_fields
	 */
	public function test_get_fields_field() {
		$this->assertTrue( method_exists( $this->pods_object_field, 'get_fields' ), 'Method get_fields does not exist' );

		$fields = $this->pods_object_field->get_fields();

		$this->assertCount( 0, $fields );

		// Confirm internal cache still works.
		$fields = $this->pods_object_field->get_fields();

		$this->assertCount( 0, $fields );
	}

	/**
	 * @covers Whatsit::get_object_fields
	 */
	public function test_get_object_fields_pod() {
		$this->assertTrue( method_exists( $this->pods_object_pod, 'get_object_fields' ), 'Method get_object_fields does not exist' );

		$fields = $this->pods_object_pod->get_object_fields();

		// Post types have 24 object fields.
		$this->assertCount( 24, $fields );
		$this->assertInstanceOf( Field::class, reset( $fields ) );
	}

	/**
	 * @covers Whatsit::get_object_fields
	 */
	public function test_get_object_fields_group() {
		$this->assertTrue( method_exists( $this->pods_object_group, 'get_object_fields' ), 'Method get_object_fields does not exist' );

		$fields = $this->pods_object_group->get_object_fields();

		$this->assertCount( 0, $fields );
	}

	/**
	 * @covers Whatsit::get_object_fields
	 */
	public function test_get_object_fields_field() {
		$this->assertTrue( method_exists( $this->pods_object_field, 'get_object_fields' ), 'Method get_object_fields does not exist' );

		$fields = $this->pods_object_field->get_object_fields();

		$this->assertCount( 0, $fields );
	}

	/**
	 * @covers Whatsit::get_all_fields
	 */
	public function test_get_all_fields_pod() {
		$this->assertTrue( method_exists( $this->pods_object_pod, 'get_all_fields' ), 'Method get_all_fields does not exist' );

		$fields = $this->pods_object_pod->get_all_fields();

		// Post types have 24 object fields.
		$this->assertCount( 25, $fields );
		$this->assertInstanceOf( Field::class, reset( $fields ) );

		// Confirm internal cache still works.
		$fields = $this->pods_object_pod->get_all_fields();

		$this->assertCount( 25, $fields );
		$this->assertInstanceOf( Field::class, reset( $fields ) );
	}

	/**
	 * @covers Whatsit::get_all_fields
	 */
	public function test_get_all_fields_group() {
		$this->assertTrue( method_exists( $this->pods_object_group, 'get_all_fields' ), 'Method get_all_fields does not exist' );

		$fields = $this->pods_object_group->get_all_fields();

		$this->assertCount( 1, $fields );
		$this->assertInstanceOf( Field::class, reset( $fields ) );

		// Confirm internal cache still works.
		$fields = $this->pods_object_group->get_all_fields();

		$this->assertCount( 1, $fields );
		$this->assertInstanceOf( Field::class, reset( $fields ) );
	}

	/**
	 * @covers Whatsit::get_all_fields
	 */
	public function test_get_all_fields_field() {
		$this->assertTrue( method_exists( $this->pods_object_field, 'get_all_fields' ), 'Method get_all_fields does not exist' );

		$fields = $this->pods_object_field->get_all_fields();

		$this->assertCount( 0, $fields );

		// Confirm internal cache still works.
		$fields = $this->pods_object_field->get_all_fields();

		$this->assertCount( 0, $fields );
	}

	/**
	 * @covers Whatsit::get_groups
	 */
	public function test_get_groups_pod() {
		$this->assertTrue( method_exists( $this->pods_object_pod, 'get_groups' ), 'Method get_groups does not exist' );

		$groups = $this->pods_object_pod->get_groups();

		$this->assertCount( 1, $groups );
		$this->assertInstanceOf( Group::class, reset( $groups ) );
	}

	/**
	 * @covers Whatsit::get_groups
	 */
	public function test_get_groups_group() {
		$this->assertTrue( method_exists( $this->pods_object_group, 'get_groups' ), 'Method get_groups does not exist' );

		$groups = $this->pods_object_group->get_groups();

		$this->assertCount( 0, $groups );
	}

	/**
	 * @covers Whatsit::get_groups
	 */
	public function test_get_groups_field() {
		$this->assertTrue( method_exists( $this->pods_object_field, 'get_groups' ), 'Method get_groups does not exist' );

		$groups = $this->pods_object_field->get_groups();

		$this->assertCount( 0, $groups );
	}

	/**
	 * @covers Whatsit::get_table_info
	 */
	public function test_get_table_info_pod() {
		$this->assertTrue( method_exists( $this->pods_object_pod, 'get_table_info' ), 'Method get_table_info does not exist' );

		$table_info = $this->pods_object_pod->get_table_info();

		$this->assertCount( 28, $table_info );
	}

	/**
	 * @covers Whatsit::get_table_info
	 */
	public function test_get_table_info_group() {
		$this->assertTrue( method_exists( $this->pods_object_group, 'get_table_info' ), 'Method get_table_info does not exist' );

		$table_info = $this->pods_object_group->get_table_info();

		$this->assertCount( 0, $table_info );
	}

	/**
	 * @covers Whatsit::get_table_info
	 */
	public function test_get_table_info_field() {
		$this->assertTrue( method_exists( $this->pods_object_field, 'get_table_info' ), 'Method get_table_info does not exist' );

		$table_info = $this->pods_object_field->get_table_info();

		$this->assertCount( 0, $table_info );
	}

	/**
	 * @covers Whatsit::get_field
	 * @covers Whatsit::fetch_field
	 */
	public function test_get_field_with_alias() {
		$this->assertTrue( method_exists( $this->pods_object_pod, 'get_field' ), 'Method get_field does not exist' );

		$aliases = [
			'id'        => 'ID',
			'title'     => 'post_title',
			'name'      => 'post_title',
			'content'   => 'post_content',
			'excerpt'   => 'post_excerpt',
			'author'    => 'post_author',
			'created'   => 'post_date',
			'date'      => 'post_date',
			'status'    => 'post_status',
			'slug'      => 'post_name',
			'permalink' => 'post_name',
			'modified'  => 'post_modified',
			'parent'    => 'post_parent',
			'type'      => 'post_type',
		];

		foreach ( $aliases as $alias => $expected_field ) {
			$found_field = $this->pods_object_pod->get_field( $alias );

			$this->assertInstanceOf( Object_Field::class, $found_field );
			$this->assertEquals( $expected_field, $found_field->get_name() );
		}
	}

	/**
	 * @covers Whatsit::get_fields
	 * @covers Whatsit::get_groups
	 * @covers Whatsit::get_object_fields
	 * @covers Whatsit::get_table_info
	 * @covers Whatsit::get_args
	 */
	public function test_backcompat_pod() {
		$this->assertCount( 1, $this->pods_object_pod['fields'] );
		$this->assertCount( 1, $this->pods_object_pod['groups'] );
		$this->assertCount( 24, $this->pods_object_pod['object_fields'] );
		$this->assertCount( 28, $this->pods_object_pod['table_info'] );

		$this->assertArrayHasKey( 'test-field', $this->pods_object_pod['fields'] );
		$this->assertEquals( 'Test field', $this->pods_object_pod['fields']['test-field']['label'] );
		$this->assertEquals( 'Test field', $this->pods_object_pod['fields']['test-field']['options']['label'] );
		$this->assertArrayHasKey( 'test-group', $this->pods_object_pod['groups'] );
		$this->assertEquals( 'Test group', $this->pods_object_pod['groups']['test-group']['label'] );
		$this->assertEquals( 'Test group', $this->pods_object_pod['groups']['test-group']['options']['label'] );

		if ( ! pods_version_check( 'php', '7.0' ) ) {
			return;
		}

		$this->pods_object_pod['fields']['test-field']['options']['label'] = 'Something else';

		// Backcompat does not throw PHP errors but does not save the variables.
		// This is acceptable because the PHP errors cause more breakage.
		$this->assertNotEquals( 'Something else', $this->pods_object_pod['fields']['test-field']['label'] );
		$this->assertNotEquals( 'Something else', $this->pods_object_pod['fields']['test-field']['options']['label'] );
	}

}
