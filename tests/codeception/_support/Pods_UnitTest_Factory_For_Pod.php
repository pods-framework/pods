<?php

namespace Pods_Unit_Tests;

/**
 * Class Pods_UnitTest_Factory_For_Pod
 *
 * @package Pods_Unit_Tests
 */
class Pods_UnitTest_Factory_For_Pod extends \WP_UnitTest_Factory_For_Thing {

	/**
	 * Pods_UnitTest_Factory_For_Pod constructor.
	 *
	 * @param null $factory
	 */
	public function __construct( $factory = null ) {
		parent::__construct( $factory );
		$this->default_generation_definitions = array(
			'id'    => new \WP_UnitTest_Generator_Sequence( '%s' ),
			'name'  => new \WP_UnitTest_Generator_Sequence( 'test-pod-%s' ),
			'label' => new \WP_UnitTest_Generator_Sequence( 'Test Pod %s' ),
		);
	}

	/**
	 * @param $args
	 *
	 * @return int
	 */
	public function create_object( $args ) {
		return pods_api()->save_pod( $args );
	}

	/**
	 * @param $post_id
	 * @param $fields
	 */
	public function update_object( $post_id, $fields ) {
		// not yet implemented
	}

	/**
	 * @param $post_id
	 */
	public function get_object_by_id( $post_id ) {
		// not yet implemented
	}
}
