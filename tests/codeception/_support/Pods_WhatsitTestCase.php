<?php

namespace Pods_Unit_Tests;

use Pods\Whatsit\Store;
use Pods\Whatsit\Storage;
use Pods\Whatsit;

/**
 * Class Pods_WhatsitTestCase
 */
class Pods_WhatsitTestCase extends Pods_UnitTestCase {

	/**
	 * @var Storage\Post_Type
	 */
	protected $pods_object_storage;

	/**
	 * @var array
	 */
	protected $pod_args;

	/**
	 * @var array
	 */
	protected $group_args;

	/**
	 * @var array
	 */
	protected $field_args;

	/**
	 * @var Whatsit\Pod
	 */
	protected $pods_object_pod;

	/**
	 * @var Whatsit\Group
	 */
	protected $pods_object_group;

	/**
	 * @var Whatsit\Field
	 */
	protected $pods_object_field;

	/**
	 * @var array
	 */
	protected $setup_objects = array();

	/**
	 * @var string
	 */
	protected $storage_type = 'post_type';

	public function setUp(): void {
		parent::setUp();

		$object_collection = Store::get_instance();

		$this->pods_object_storage = $object_collection->get_storage_object( $this->storage_type );

		// Setup pod.
		$pod_args = array(
			'object_type' => 'pod',
			'name'        => 'test-pod',
			'label'       => 'Test pod',
			'description' => 'Testing pod',
			'type'        => 'post_type',
			'storage'     => 'meta',
			'object'      => 'test-pod',
		);

		$this->pods_object_pod = $this->setup_pods_object( $pod_args, 'pod' );

		// Setup group.
		$group_args = array(
			'object_type' => 'group',
			'name'        => 'test-group',
			'label'       => 'Test group',
			'description' => 'Testing group',
			'parent'      => $this->pods_object_pod->get_id(),
			'type'        => 'metabox',
		);

		$this->pods_object_group = $this->setup_pods_object( $group_args, 'group' );

		// Setup field.
		$field_args = array(
			'object_type' => 'field',
			'name'        => 'test-field',
			'label'       => 'Test field',
			'description' => 'Testing field',
			'parent'      => $this->pods_object_pod->get_id(),
			'group'       => $this->pods_object_group->get_id(),
			'type'        => 'text',
		);

		$this->pods_object_field = $this->setup_pods_object( $field_args, 'field' );
	}

	public function tearDown(): void {
		unset( $this->pods_object_pod, $this->pods_object_group, $this->pods_object_field );

		$this->setup_objects = array();

		$this->pod_args   = array();
		$this->group_args = array();
		$this->field_args = array();

		unset( $this->pods_object_storage );

		parent::tearDown();
	}

	/**
	 * Setup and return a Whatsit.
	 *
	 * @param array  $args Object arguments.
	 * @param string $type Object type.
	 *
	 * @return Whatsit
	 */
	public function setup_pods_object( array $args = array(), $type = '' ) {
		$defaults = array(
			'object_type'  => 'pod',
			'object_storage_type' => 'post_type',
			'id'           => '',
			'name'         => 'test',
			'label'        => 'Test',
			'description'  => 'Testing',
			'parent'       => '',
			'group'        => '',
			'custom1'      => 'value1',
		);

		$args = array_merge( $defaults, $args );

		$args['custom1'] = $args['custom1'] . '-' . $args['name'];

		$object_collection = Store::get_instance();

		$class_name = $object_collection->get_object_type( $args['object_type'] );

		/** @var Whatsit $object */
		$object = new $class_name;
		$object->setup( $args );

		$id = $this->pods_object_storage->add( $object );

		$args['id'] = $id;

		if ( 'pod' === $type ) {
			$this->pod_args = $args;
		} elseif ( 'group' === $type ) {
			$this->group_args = $args;
		} elseif ( 'field' === $type ) {
			$this->field_args = $args;
		}

		$this->setup_objects[] =& $object;

		return $object;
	}

}
