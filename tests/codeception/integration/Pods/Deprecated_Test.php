<?php

/**
 * Test that things are deprecated properly
 */
class Deprecated_Test extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		require_once PODS_DIR . 'deprecated/deprecated.php';
	}

	public function deprecated_classes() {
		return array(
			array( 'Pod' ),
			array( 'PodAPI' ),
		);
	}

	/**
	 * Test if a class exists was deprecated exists but is deprecated.
	 *
	 * @dataProvider deprecated_classes
	 */
	public function test_deprecated_class( $class ) {
		$this->assertTrue( class_exists( $class ), 'Class "' . $class . '" does not exist.' );

		$object = new $class;

		$this->expectDeprecated();
	}
}
