<?php

namespace Pods_Unit_Tests;

/**
 * Class Pods_UnitTest_Factory_For_Field
 *
 * @package Pods_Unit_Tests
 */
class Pods_UnitTest_Factory_For_Field extends \WP_UnitTest_Factory_For_Thing {

	/**
	 * Pods_UnitTest_Factory_For_Field constructor.
	 *
	 * @param null $factory
	 */
	public function __construct( $factory = null ) {
		parent::__construct( $factory );
		$this->default_generation_definitions = array(
			'id'    => new \WP_UnitTest_Generator_Sequence( '%s' ),
			'name'  => new \WP_UnitTest_Generator_Sequence( 'test-field-%s' ),
			'label' => new \WP_UnitTest_Generator_Sequence( 'Test Field %s' ),
		);
	}

	/**
	 * @param $args
	 */
	public function create_object( $args ) {
		// not yet implemented
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
