<?php

namespace Pods_Unit_Tests;

use \WP_UnitTest_Factory_For_Thing;

/**
 * Class Pods_UnitTest_Factory
 *
 * @package Pods_Unit_Tests
 */
class Pods_UnitTest_Factory extends \WP_UnitTest_Factory {

	/**
	 * @var Pods_UnitTest_Factory_For_Pod
	 */
	public $pod;

	/**
	 * @var Pods_UnitTest_Factory_For_Field
	 */
	public $field;

	/**
	 * Pods_UnitTest_Factory constructor.
	 */
	public function __construct() {

		parent::__construct();

		$this->pod = new Pods_UnitTest_Factory_For_Pod( $this );
	}
}

/**
 * Class Pods_UnitTest_Factory_For_Pod
 *
 * @package Pods_Unit_Tests
 */
class Pods_UnitTest_Factory_For_Pod extends WP_UnitTest_Factory_For_Thing {

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

/**
 * Class Pods_UnitTest_Factory_For_Field
 *
 * @package Pods_Unit_Tests
 */
class Pods_UnitTest_Factory_For_Field extends WP_UnitTest_Factory_For_Thing {

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
