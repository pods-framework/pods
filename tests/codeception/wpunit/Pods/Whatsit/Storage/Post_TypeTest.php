<?php

namespace Pods_Unit_Tests\Pods\Whatsit;

use Pods_Unit_Tests\Pods_WhatsitTestCase;
use Pods\Whatsit\Storage\Post_Type;
use Pods\Whatsit\Storage\Collection;
use Pods\Whatsit;
use WP_Post;

/**
 * @group  pods-whatsit
 * @group  pods-whatsit-storage
 * @covers Post_Type
 */
class Post_TypeTest extends Pods_WhatsitTestCase {

	public function tearDown() {
		// Reset fallback mode.
		$this->pods_object_storage->fallback_mode( true );

		parent::tearDown();
	}

	/**
	 * @covers Post_Type::get_storage_type
	 */
	public function test_get_storage_type() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'get_storage_type' ), 'Method get_storage_type does not exist' );

		$this->assertEquals( 'post_type', $this->pods_object_storage->get_storage_type() );
	}

	/**
	 * @covers Post_Type::get
	 */
	public function test_get() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'get' ), 'Method get does not exist' );

		$this->assertNull( $this->pods_object_storage->get() );
	}

	/**
	 * @covers Post_Type::find
	 * @covers Collection::find
	 */
	public function test_find() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'find' ), 'Method find does not exist' );

		$this->assertCount( 0, $this->pods_object_storage->find() );
	}

	/**
	 * @covers Post_Type::find
	 * @covers Collection::find
	 */
	public function test_find_object_type() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'find' ), 'Method find does not exist' );

		$args = array(
			'object_type' => 'pod',
			'refresh'     => true,
		);

		$this->assertCount( 4, $this->pods_object_storage->find( $args ) );

		$this->setup_pods_object();

		$this->assertCount( 5, $this->pods_object_storage->find( $args ) );
	}

	/**
	 * @covers Post_Type::find
	 * @covers Collection::find
	 */
	public function test_find_secondary() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'find' ), 'Method find does not exist' );

		$args = array(
			'object_type' => $this->pods_object_field->get_object_type(),
			'parent'      => $this->pods_object_field->get_parent(),
			'type'        => $this->pods_object_field->get_arg( 'type' ),
		);

		$this->assertCount( 1, $this->pods_object_storage->find( $args ) );

		$args = array(
			'object_type' => $this->pods_object_pod->get_object_type(),
			'object'      => $this->pods_object_pod->get_arg( 'object' ),
		);

		$this->assertCount( 1, $this->pods_object_storage->find( $args ) );

		$args = array(
			'object_type' => $this->pods_object_field->get_object_type(),
			'parent'      => $this->pods_object_field->get_parent(),
			'group'       => $this->pods_object_field->get_arg( 'group' ),
		);

		$this->assertCount( 1, $this->pods_object_storage->find( $args ) );
	}

	/**
	 * @covers Post_Type::find
	 * @covers Collection::find
	 */
	public function test_find_args() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'find' ), 'Method find does not exist' );

		$args = array(
			'object_type' => $this->pods_object_field->get_object_type(),
			'parent'      => $this->pods_object_field->get_parent(),
			'args'        => array(
				'custom1' => $this->pods_object_field->get_arg( 'custom1' ),
			),
		);

		$this->assertCount( 1, $this->pods_object_storage->find( $args ) );

		$args = array(
			'object_type' => $this->pods_object_field->get_object_type(),
			'parent'      => $this->pods_object_field->get_parent(),
			'args'        => array(
				'custom1' => 'something-else',
			),
		);

		$this->assertCount( 0, $this->pods_object_storage->find( $args ) );

		$args = array(
			'object_type' => $this->pods_object_field->get_object_type(),
			'parent'      => $this->pods_object_field->get_parent(),
			'args'        => array(
				'custom1' => '',
			),
		);

		$this->assertCount( 0, $this->pods_object_storage->find( $args ) );

		$args = array(
			'object_type' => $this->pods_object_field->get_object_type(),
			'parent'      => $this->pods_object_field->get_parent(),
			'args'        => array(
				'custom2' => null,
			),
		);

		$this->assertCount( 1, $this->pods_object_storage->find( $args ) );
	}

	/**
	 * @covers Post_Type::find
	 * @covers Collection::find
	 */
	public function test_find_id() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'find' ), 'Method find does not exist' );

		$args = array(
			'object_type' => $this->pods_object_field->get_object_type(),
			'id'          => $this->pods_object_field->get_id(),
		);

		$this->assertCount( 1, $this->pods_object_storage->find( $args ) );

		$args = array(
			'object_type' => $this->pods_object_group->get_object_type(),
			'id'          => $this->pods_object_group->get_id(),
		);

		$this->assertCount( 1, $this->pods_object_storage->find( $args ) );

		$args = array(
			'object_type' => $this->pods_object_pod->get_object_type(),
			'id'          => $this->pods_object_pod->get_id(),
		);

		$this->assertCount( 1, $this->pods_object_storage->find( $args ) );
	}

	/**
	 * @covers Post_Type::find
	 * @covers Collection::find
	 */
	public function test_find_name() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'find' ), 'Method find does not exist' );

		$args = array(
			'object_type' => $this->pods_object_field->get_object_type(),
			'name'        => $this->pods_object_field->get_name(),
		);

		$this->assertCount( 1, $this->pods_object_storage->find( $args ) );

		$args = array(
			'object_type' => $this->pods_object_group->get_object_type(),
			'name'        => $this->pods_object_group->get_name(),
		);

		$this->assertCount( 1, $this->pods_object_storage->find( $args ) );

		$args = array(
			'object_type' => $this->pods_object_pod->get_object_type(),
			'name'        => $this->pods_object_pod->get_name(),
		);

		$this->assertCount( 1, $this->pods_object_storage->find( $args ) );
	}

	/**
	 * @covers Post_Type::find
	 * @covers Collection::find
	 */
	public function test_find_parent() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'find' ), 'Method find does not exist' );

		$args = array(
			'object_type' => $this->pods_object_field->get_object_type(),
			'parent'      => $this->pods_object_pod->get_id(),
		);

		$this->assertCount( 1, $this->pods_object_storage->find( $args ) );

		$args = array(
			'object_type' => $this->pods_object_group->get_object_type(),
			'parent'      => $this->pods_object_pod->get_id(),
		);

		$this->assertCount( 1, $this->pods_object_storage->find( $args ) );

		$args = array(
			'object_type' => $this->pods_object_pod->get_object_type(),
		);

		// Post type + Collection (_pods_pod, _pods_group, _pods_field)
		$this->assertCount( 4, $this->pods_object_storage->find( $args ) );

		$this->pods_object_storage->fallback_mode( false );

		// Post type only
		$this->assertCount( 1, $this->pods_object_storage->find( $args ) );
	}

	/**
	 * @covers Post_Type::find
	 * @covers Collection::find
	 */
	public function test_find_status() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'find' ), 'Method find does not exist' );

		$args = array(
			'object_type' => 'pod',
			'status'      => 'draft',
		);

		$this->assertCount( 0, $this->pods_object_storage->find( $args ) );

		$args = array(
			'object_type' => 'pod',
			'status'      => 'publish',
		);

		$this->assertCount( 4, $this->pods_object_storage->find( $args ) );
	}

	/**
	 * @covers Post_Type::find
	 * @covers Collection::find
	 */
	public function test_find_order() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'find' ), 'Method find does not exist' );

		// Add test pods.
		$args = array(
			'object_type' => 'pod',
			'name'        => 'fiver',
			'label'       => 'Fiver',
			'type'        => 'custom',
			'fourohfour'  => 405,
		);

		$this->setup_pods_object( $args );

		$args2 = array(
			'object_type' => 'pod',
			'name'        => 'sixer',
			'label'       => 'Sixer',
			'type'        => 'custom',
			'fourohfour'  => 405,
		);

		$this->setup_pods_object( $args2 );

		// Do find.
		$find_args = array(
			'object_type' => 'pod',
			'type'        => 'custom',
			'order'       => 'ASC',
		);

		$objects = $this->pods_object_storage->find( $find_args );

		$this->assertCount( 2, $objects );
		$this->assertEquals( array( 'fiver' => 'fiver', 'sixer' => 'sixer' ), wp_list_pluck( $objects, 'name' ) );

		// Do find.
		$find_args = array(
			'object_type' => 'pod',
			'type'        => 'custom',
			'order'       => 'DESC',
		);

		$objects = $this->pods_object_storage->find( $find_args );

		$this->assertCount( 2, $objects );
		$this->assertEquals( array( 'sixer' => 'sixer', 'fiver' => 'fiver' ), wp_list_pluck( $objects, 'name' ) );
	}

	/**
	 * @covers Post_Type::find
	 * @covers Collection::find
	 */
	public function test_find_orderby() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'find' ), 'Method find does not exist' );

		// Add test pods.
		$args = array(
			'object_type' => 'pod',
			'name'        => 'sixer',
			'label'       => 'Sixer',
			'type'        => 'custom',
			'fourohfour'  => 405,
		);

		$this->setup_pods_object( $args );

		$args2 = array(
			'object_type' => 'pod',
			'name'        => 'fiver',
			'label'       => 'Fiver',
			'type'        => 'custom',
			'fourohfour'  => 405,
		);

		$this->setup_pods_object( $args2 );

		// Do find.
		$find_args = array(
			'object_type' => 'pod',
			'type'        => 'custom',
			'orderby'     => 'title',
			'order'       => 'ASC',
		);

		$objects = $this->pods_object_storage->find( $find_args );

		$this->assertCount( 2, $objects );
		$this->assertEquals( array( 'fiver' => 'fiver', 'sixer' => 'sixer' ), wp_list_pluck( $objects, 'name' ) );

		// Do find.
		$find_args = array(
			'object_type' => 'pod',
			'type'        => 'custom',
			'orderby'     => 'title',
			'order'       => 'DESC',
		);

		$objects = $this->pods_object_storage->find( $find_args );

		$this->assertCount( 2, $objects );
		$this->assertEquals( array( 'sixer' => 'sixer', 'fiver' => 'fiver' ), wp_list_pluck( $objects, 'name' ) );
	}

	/**
	 * @covers Post_Type::find
	 * @covers Collection::find
	 */
	public function test_find_limit() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'find' ), 'Method find does not exist' );

		// Add test pods.
		$args = array(
			'object_type' => 'pod',
			'name'        => 'fiver',
			'label'       => 'Fiver',
			'type'        => 'custom',
			'fourohfour'  => 405,
		);

		$this->setup_pods_object( $args );

		$args2 = array(
			'object_type' => 'pod',
			'name'        => 'sixer',
			'label'       => 'Sixer',
			'type'        => 'custom',
			'fourohfour'  => 405,
		);

		$this->setup_pods_object( $args2 );

		$args = array(
			'object_type' => 'pod',
			'limit'       => 2,
		);

		$this->assertCount( 2, $this->pods_object_storage->find( $args ) );
	}

	/**
	 * @covers Post_Type::add
	 * @covers Post_Type::add_object
	 * @covers Collection::add
	 * @covers Collection::add_object
	 */
	public function test_add() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'add' ), 'Method add does not exist' );

		$args = array(
			'object_type' => 'field',
			'name'        => 'fiver',
			'parent'      => $this->pods_object_pod->get_id(),
			'group'       => $this->pods_object_group->get_id(),
			'fourohfour'  => 405,
		);

		$object = $this->setup_pods_object( $args );

		$id = $object->get_id();

		$post = get_post( $id );

		$this->assertInternalType( 'integer', $id );
		$this->assertInstanceOf( WP_Post::class, $post );
		$this->assertEquals( $id, $object->get_id() );
		$this->assertEquals( $object->get_label(), $post->post_title );
		$this->assertEquals( $object->get_name(), $post->post_name );
		$this->assertEquals( $object->get_parent(), $post->post_parent );
		$this->assertEquals( $object->get_parent_id(), $post->post_parent );
		$this->assertEquals( '_pods_field', $post->post_type );
		$this->assertEquals( 'post_type', $object->get_storage_type() );
		$this->assertEquals( $object->get_arg( 'group' ), get_post_meta( $id, 'group', true ) );
		$this->assertEquals( $object->get_arg( 'fourohfour' ), get_post_meta( $id, 'fourohfour', true ) );
		$this->assertEquals( $object->get_arg( 'custom1' ), get_post_meta( $id, 'custom1', true ) );
		$this->assertCount( 3, get_post_meta( $id ) );
	}

	/**
	 * @covers Post_Type::save
	 * @covers Post_Type::save_object
	 * @covers Collection::save
	 * @covers Collection::save_object
	 */
	public function test_save() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'save' ), 'Method save does not exist' );

		$args = array(
			'object_type' => 'field',
			'name'        => 'fiver',
			'parent'      => $this->pods_object_pod->get_id(),
			'group'       => $this->pods_object_group->get_id(),
			'fourohfour'  => 405,
		);

		$object = $this->setup_pods_object( $args );

		$object->set_arg( 'label', 'New label' );

		$id = $this->pods_object_storage->save( $object );

		$post = get_post( $id );

		$this->assertInternalType( 'integer', $id );
		$this->assertInstanceOf( WP_Post::class, $post );
		$this->assertEquals( $id, $object->get_id() );
		$this->assertEquals( $object->get_label(), $post->post_title );
		$this->assertEquals( $object->get_name(), $post->post_name );
		$this->assertEquals( $object->get_parent(), $post->post_parent );
		$this->assertEquals( $object->get_parent_id(), $post->post_parent );
		$this->assertEquals( '_pods_' . $object->get_object_type(), $post->post_type );
		$this->assertEquals( 'post_type', $object->get_storage_type() );
		$this->assertEquals( $object->get_arg( 'group' ), get_post_meta( $id, 'group', true ) );
		$this->assertEquals( $object->get_arg( 'fourohfour' ), get_post_meta( $id, 'fourohfour', true ) );
		$this->assertEquals( $object->get_arg( 'custom1' ), get_post_meta( $id, 'custom1', true ) );
		$this->assertCount( 3, get_post_meta( $id ) );
	}

	/**
	 * @covers Post_Type::get_args
	 * @covers Collection::get_args
	 */
	public function test_get_args() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'get_args' ), 'Method get_args does not exist' );

		$args = array(
			'object_type' => 'field',
			'name'        => 'fiver',
			'parent'      => $this->pods_object_pod->get_id(),
			'group'       => $this->pods_object_group->get_id(),
			'fourohfour'  => 405,
			'custom2'     => 'value2',
		);

		$object = $this->setup_pods_object( $args );

		$id = $object->get_id();

		// Set an empty field.
		update_post_meta( $id, 'custom3', '' );

		// Set a multiple value field.
		add_post_meta( $id, 'custom4', 'value1' );
		add_post_meta( $id, 'custom4', 'value2' );
		add_post_meta( $id, 'custom4', 'value3' );
		add_post_meta( $id, 'custom4', 'value4' );

		$args = $this->pods_object_storage->get_args( $object );

		$this->assertEquals( $object->get_arg( 'group' ), $args['group'] );
		$this->assertEquals( $object->get_arg( 'fourohfour' ), $args['fourohfour'] );
		$this->assertEquals( $object->get_arg( 'custom1' ), $args['custom1'] );
		$this->assertEquals( $object->get_arg( 'custom2' ), $args['custom2'] );
		$this->assertArrayNotHasKey( 'custom3', $args );
		$this->assertCount( 4, $args['custom4'] );
	}

	/**
	 * @covers Post_Type::save_args
	 * @covers Collection::save_args
	 */
	public function test_save_args() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'save_args' ), 'Method save_args does not exist' );

		$args = array(
			'object_type' => 'field',
			'name'        => 'fiver',
			'parent'      => $this->pods_object_pod->get_id(),
			'group'       => $this->pods_object_group->get_id(),
			'fourohfour'  => 405,
			'custom2'     => 'value2',
		);

		$object = $this->setup_pods_object( $args );

		$id = $object->get_id();

		$this->assertTrue( $this->pods_object_storage->save_args( $object ) );
		$this->assertEquals( $object->get_arg( 'group' ), get_post_meta( $id, 'group', true ) );
		$this->assertEquals( $object->get_arg( 'fourohfour' ), get_post_meta( $id, 'fourohfour', true ) );
		$this->assertEquals( $object->get_arg( 'custom1' ), get_post_meta( $id, 'custom1', true ) );
		$this->assertEquals( $object->get_arg( 'custom2' ), get_post_meta( $id, 'custom2', true ) );
		$this->assertCount( 4, get_post_meta( $id ) );
	}

	/**
	 * @covers Post_Type::duplicate
	 * @covers Post_Type::add
	 * @covers Post_Type::add_object
	 * @covers Collection::duplicate
	 * @covers Collection::add
	 * @covers Collection::add_object
	 */
	public function test_duplicate() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'duplicate' ), 'Method duplicate does not exist' );

		$args = array(
			'object_type' => 'field',
			'name'        => 'fiver',
			'parent'      => $this->pods_object_pod->get_id(),
			'group'       => $this->pods_object_group->get_id(),
			'fourohfour'  => 405,
		);

		$object = $this->setup_pods_object( $args );

		$id = $this->pods_object_storage->duplicate( $object );

		$post = get_post( $id );

		$duplicated_object = $this->pods_object_storage->to_object( $post );

		$this->assertInternalType( 'integer', $id );
		$this->assertInstanceOf( WP_Post::class, $post );
		$this->assertEquals( $id, $duplicated_object->get_id() );
		$this->assertEquals( $duplicated_object->get_label(), $post->post_title );
		$this->assertEquals( $duplicated_object->get_name(), $post->post_name );
		$this->assertEquals( $duplicated_object->get_parent(), $post->post_parent );
		$this->assertEquals( $duplicated_object->get_parent_id(), $post->post_parent );
		$this->assertEquals( '_pods_field', $post->post_type );
		$this->assertEquals( 'post_type', $duplicated_object->get_storage_type() );
		$this->assertEquals( $duplicated_object->get_arg( 'group' ), get_post_meta( $id, 'group', true ) );
		$this->assertEquals( $duplicated_object->get_arg( 'fourohfour' ), get_post_meta( $id, 'fourohfour', true ) );
		$this->assertEquals( $duplicated_object->get_arg( 'custom1' ), get_post_meta( $id, 'custom1', true ) );
	}

	/**
	 * @covers Post_Type::delete
	 * @covers Collection::delete
	 */
	public function test_delete() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'delete' ), 'Method delete does not exist' );

		$args = array(
			'object_type' => 'field',
			'name'        => 'fiver',
			'parent'      => $this->pods_object_pod->get_id(),
			'group'       => $this->pods_object_group->get_id(),
			'fourohfour'  => 405,
		);

		$object = $this->setup_pods_object( $args );

		$id = $object->get_id();

		$this->assertEquals( $id, $object->get_id() );
		$this->assertTrue( $this->pods_object_storage->delete( $object ) );
		$this->assertNull( $object->get_id() );
	}

	/**
	 * @covers Post_Type::reset
	 * @covers Collection::reset
	 */
	public function test_reset() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'reset' ), 'Method reset does not exist' );

		$args = array(
			'object_type' => 'field',
			'name'        => 'fiver',
			'parent'      => $this->pods_object_pod->get_id(),
			'group'       => $this->pods_object_group->get_id(),
			'fourohfour'  => 405,
		);

		$object = $this->setup_pods_object( $args );

		$this->assertFalse( $this->pods_object_storage->reset( $object ) );
	}

	/**
	 * @covers Post_Type::to_object
	 */
	public function test_to_object() {
		$this->assertTrue( method_exists( $this->pods_object_storage, 'to_object' ), 'Method to_object does not exist' );

		$post = get_post( $this->pods_object_field->get_id() );

		$to = $this->pods_object_storage->to_object( $post );

		$this->assertInstanceOf( Whatsit::class, $to );
		$this->assertEquals( $this->pods_object_field->get_object_type(), $to->get_object_type() );
		$this->assertEquals( $this->pods_object_field->get_id(), $to->get_id() );
		$this->assertEquals( $this->pods_object_field->get_name(), $to->get_name() );
		$this->assertEquals( $this->pods_object_field->get_parent(), $to->get_parent() );
		$this->assertEquals( $this->pods_object_field->get_group(), $to->get_group() );
	}

}
