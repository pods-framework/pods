<?php
/**
 * @package Pods
 * @category Tests
 */
namespace Pods_Unit_Tests;
	use \WP_UnitTest_Factory_For_Thing;

class Pods_UnitTest_Factory extends \WP_UnitTest_Factory {
	/**
	 * @var Pods_UnitTest_Factory_For_Pod
	 */
	public $pod;

	/**
	 * @var Pods_UnitTest_Factory_For_Field
	 */
	public $field;

	public function __construct() {
		parent::__construct();

		$this->pod = new Pods_UnitTest_Factory_For_Pod( $this );
	}
}

class Pods_UnitTest_Factory_For_Pod extends WP_UnitTest_Factory_For_Thing {
	public function __construct( $factory = null ) {
		parent::__construct( $factory );
		$this->default_generation_definitions = array(
			'id'         => new \WP_UnitTest_Generator_Sequence( '%s' ),
			'name'       => new \WP_UnitTest_Generator_Sequence( 'test-pod-%s' ),
			'label'      => new \WP_UnitTest_Generator_Sequence( 'Test Pod %s' ),
		);
	}

	public function create_object( $args ) {
		return pods_api()->save_pod( $args );
	}

	public function update_object( $post_id, $fields ) {
		// not yet implemented
	}

	public function get_object_by_id( $post_id ) {
		// not yet implemented
	}
}

class Pods_UnitTest_Factory_For_Field extends WP_UnitTest_Factory_For_Thing {
	public function __construct( $factory = null ) {
		parent::__construct( $factory );
		$this->default_generation_definitions = array(
			'id'         => new \WP_UnitTest_Generator_Sequence( '%s' ),
			'name'       => new \WP_UnitTest_Generator_Sequence( 'test-field-%s' ),
			'label'      => new \WP_UnitTest_Generator_Sequence( 'Test Field %s' ),
		);
	}

	public function create_object( $args ) {
		// not yet implemented
	}

	public function update_object( $post_id, $fields ) {
		// not yet implemented
	}

	public function get_object_by_id( $post_id ) {
		// not yet implemented
	}
}
