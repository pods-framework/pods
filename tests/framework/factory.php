<?php
namespace Pods_Unit_Tests;

class Pods_UnitTest_Factory extends \WP_UnitTest_Factory {
	/**
	 * @var Pods_UnitTest_Factory_For_Download
	 */
	public $download;

	/**
	 * @var Pods_UnitTest_Factory_For_Discount
	 */
	public $discount;

	/**
	 * @var Pods_UnitTest_Factory_For_Payment
	 */
	public $payment;

	public function __construct() {
		parent::__construct();

		$this->download = new Pods_UnitTest_Factory_For_Download( $this );
		$this->discount = new Pods_UnitTest_Factory_For_Discount( $this );
		$this->payment = new Pods_UnitTest_Factory_For_Payment( $this );
	}
}

class Pods_UnitTest_Factory_For_Download extends \WP_UnitTest_Factory_For_Thing {
	public function __construct( $factory = null ) {
		parent::__construct( $factory );
		$this->default_generation_definitions = array(
			'post_status' => 'publish',
			'post_title' => new \WP_UnitTest_Generator_Sequence( 'Download title %s' ),
			'post_content' => new \WP_UnitTest_Generator_Sequence( 'Download content %s' ),
			'post_excerpt' => new \WP_UnitTest_Generator_Sequence( 'Download excerpt %s' ),
			'post_type' => 'download'
		);
	}

	public function create_object( $args ) {
		return wp_insert_post( $args );
	}

	public function update_object( $post_id, $fields ) {
		$fields['ID'] = $post_id;
		return wp_update_post( $fields );
	}

	public function get_object_by_id( $post_id ) {
		return get_post( $post_id );
	}
}

class Pods_UnitTest_Factory_For_Discount extends \WP_UnitTest_Factory_For_Thing {
	public function __construct( $factory = null ) {
		parent::__construct( $factory );
	}

	public function create_object( $args ) {
		return wp_insert_post( $args );
	}

	public function update_object( $post_id, $fields ) {
		$fields['ID'] = $post_id;
		return wp_update_post( $fields );
	}

	public function get_object_by_id( $post_id ) {
		return get_post( $post_id );
	}
}

class Pods_UnitTest_Factory_For_Payment extends \WP_UnitTest_Factory_For_Thing {
	public function __construct( $factory = null ) {
		parent::__construct( $factory );
	}

	public function create_object( $args ) {
		return wp_insert_post( $args );
	}

	public function update_object( $post_id, $fields ) {
		$fields['ID'] = $post_id;
		return wp_update_post( $fields );
	}

	public function get_object_by_id( $post_id ) {
		return get_post( $post_id );
	}
}