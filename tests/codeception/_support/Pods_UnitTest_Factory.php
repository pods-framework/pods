<?php

namespace Pods_Unit_Tests;

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
