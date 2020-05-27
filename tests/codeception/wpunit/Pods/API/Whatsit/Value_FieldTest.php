<?php

namespace Pods_Unit_Tests\Pods\API\Whatsit;

use Pods_Unit_Tests\Pods_WhatsitTestCase;
use Pods\API\Whatsit\Value_Field;
use Pods\Whatsit\Field;
use Pods\Whatsit\Group;

/**
 * @group  pods-whatsit
 * @group  pods-api
 * @covers Value_Field
 */
class Value_FieldTest extends Pods_WhatsitTestCase {

	/**
	 * @var Value_Field
	 */
	public $value_field;

	/**
	 * @var mixed
	 */
	public $value;

	public function setUp() {
		parent::setUp();

		$this->value_field = new Value_Field( $this->pods_object_field );
		$this->value       = wp_generate_password();

		$this->value_field->value = $this->value;
	}

	public function tearDown() {
		unset( $this->value_field, $this->value );

		parent::tearDown();
	}

	/**
	 * @covers Value_Field::__toString
	 */
	public function test_string() {
		$this->assertTrue( method_exists( $this->value_field, '__toString' ), 'Method __toString does not exist' );

		$to = (string) $this->value_field;

		$this->assertInternalType( 'string', $to );
		$this->assertEquals( $this->pods_object_field->get_identifier(), $to );
	}

	/**
	 * @covers Value_Field::offsetExists
	 * @covers Value_Field::offsetGet
	 * @covers Value_Field::offsetSet
	 * @covers Value_Field::offsetUnset
	 */
	public function test_array_access_pod() {
		$value_field = new Value_Field( $this->pods_object_pod );
		$value       = wp_generate_password();

		$value_field->value = $value;

		// Confirm methods exist.
		$this->assertTrue( method_exists( $value_field, 'offsetExists' ), 'Method offsetExists does not exist' );
		$this->assertTrue( method_exists( $value_field, 'offsetGet' ), 'Method offsetGet does not exist' );
		$this->assertTrue( method_exists( $value_field, 'offsetSet' ), 'Method offsetSet does not exist' );
		$this->assertTrue( method_exists( $value_field, 'offsetUnset' ), 'Method offsetUnset does not exist' );

		// Confirm argument get matches ArrayAccess.
		$this->assertEquals( $this->pods_object_pod->get_id(), $value_field['id'] );
		$this->assertEquals( $this->pods_object_pod->get_name(), $value_field['name'] );
		$this->assertEquals( $this->pods_object_pod->get_parent(), $value_field['parent'] );
		$this->assertEquals( $this->pods_object_pod->get_group(), $value_field['group'] );
		$this->assertEquals( $this->pods_object_pod->get_fields(), $value_field['fields'] );
		$this->assertEquals( $this->pods_object_pod->get_object_fields(), $value_field['object_fields'] );
		$this->assertEquals( $this->pods_object_pod->get_groups(), $value_field['groups'] );
		$this->assertEquals( $this->pods_object_pod->get_table_info(), $value_field['table_info'] );
		$this->assertEquals( $this->pods_object_pod->get_args(), $value_field['options'] );
		$this->assertEquals( $value, $value_field['value'] );

		// Confirm argument get matches Object __get.
		$this->assertEquals( $this->pods_object_pod->get_id(), $value_field->id );
		$this->assertEquals( $this->pods_object_pod->get_name(), $value_field->name );
		$this->assertEquals( $this->pods_object_pod->get_parent(), $value_field->parent );
		$this->assertEquals( $this->pods_object_pod->get_group(), $value_field->group );
		$this->assertEquals( $this->pods_object_pod->get_fields(), $value_field->fields );
		$this->assertEquals( $this->pods_object_pod->get_object_fields(), $value_field->object_fields );
		$this->assertEquals( $this->pods_object_pod->get_groups(), $value_field->groups );
		$this->assertEquals( $this->pods_object_pod->get_table_info(), $value_field->table_info );
		$this->assertEquals( $this->pods_object_pod->get_args(), $value_field->options );
		$this->assertEquals( $value, $value_field->value );

		$list = array( $value_field );

		$this->assertEquals( array( $this->pods_object_pod->get_id() ), wp_list_pluck( $list, 'id' ) );
		$this->assertEquals( array( $this->pods_object_pod->get_name() ), wp_list_pluck( $list, 'name' ) );
		$this->assertEquals( array( $this->pods_object_pod->get_parent() ), wp_list_pluck( $list, 'parent' ) );
		$this->assertEquals( array( $this->pods_object_pod->get_group() ), wp_list_pluck( $list, 'group' ) );
		$this->assertEquals( array( $this->pods_object_pod->get_arg( 'custom1' ) ), wp_list_pluck( $list, 'custom1' ) );
		$this->assertEquals( array( $value ), wp_list_pluck( $list, 'value' ) );

		// Test non-existent arguments and handling for ArrayAccess.
		$this->assertNull( $this->pods_object_pod->get_arg( 'fourohfour' ) );
		$this->assertEquals( $this->pods_object_pod->get_arg( 'fourohfour' ), $value_field['fourohfour'] );
		$this->assertFalse( isset( $value_field['fourohfour'] ) );

		// Test isset for ArrayAccess.
		$this->assertTrue( isset( $value_field['id'] ) );
		$this->assertTrue( isset( $value_field['name'] ) );
		$this->assertTrue( isset( $value_field['parent'] ) );
		$this->assertTrue( isset( $value_field['group'] ) );

		// Test unset handling for ArrayAccess served arguments.
		unset( $value_field['id'], $value_field['name'], $value_field['parent'], $value_field['group'], $value_field['value'] );

		// Confirm ArrayAccess arguments are now empty strings for reserved arguments.
		$this->assertEquals( $value_field['id'], '' );
		$this->assertEquals( $value_field['name'], '' );
		$this->assertEquals( $value_field['parent'], '' );
		$this->assertEquals( $value_field['group'], '' );
		$this->assertNull( $value_field['value'] );
	}

	/**
	 * @covers Value_Field::init
	 * @covers Value_Field::__toString
	 */
	public function test_init() {
		$this->assertTrue( method_exists( Value_Field::class, 'init' ), 'Method init does not exist' );

		$value_field = Value_Field::init( $this->pods_object_group );

		$this->assertInstanceOf( Value_Field::class, $value_field );

		$to = (string) $value_field;

		$this->assertInternalType( 'string', $to );
		$this->assertEquals( $this->pods_object_group->get_identifier(), $to );
	}

	/**
	 * @covers Value_Field::offsetGet
	 * @covers Value_Field::offsetSet
	 * @covers Value_Field::__get
	 * @covers Value_Field::__set
	 */
	public function test_backcompat_pod() {
		$value_field = new Value_Field( $this->pods_object_pod );

		$this->assertCount( 1, $value_field['fields'] );
		$this->assertCount( 1, $value_field['groups'] );
		$this->assertCount( 24, $value_field['object_fields'] );
		$this->assertCount( 27, $value_field['table_info'] );

		$this->assertArrayHasKey( 'test-field', $value_field['fields'] );
		$this->assertEquals( 'Test field', $value_field['fields']['test-field']['label'] );
		$this->assertEquals( 'Test field', $value_field['fields']['test-field']['options']['label'] );
		$this->assertArrayHasKey( 'test-group', $value_field['groups'] );
		$this->assertEquals( 'Test group', $value_field['groups']['test-group']['label'] );
		$this->assertEquals( 'Test group', $value_field['groups']['test-group']['options']['label'] );

		if ( ! pods_version_check( 'php', '7.0' ) ) {
			return;
		}

		$value_field['fields']['test-field']['options']['label'] = 'Something else';

		// Backcompat does not throw PHP errors but does not save the variables.
		// This is acceptable because the PHP errors cause more breakage.
		$this->assertNotEquals( 'Something else', $value_field['fields']['test-field']['label'] );
		$this->assertNotEquals( 'Something else', $value_field['fields']['test-field']['options']['label'] );
	}

}
