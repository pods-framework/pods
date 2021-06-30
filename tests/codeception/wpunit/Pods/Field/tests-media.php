<?php

namespace Pods_Unit_Tests;

/**
 * @group pods_acceptance_tests
 */
class Test_Media extends Pods_UnitTestCase {

	/** @var array */
	public static $images = array();

	public function setUp() {
		self::$images[] = $this->factory()->attachment->create();
		self::$images[] = $this->factory()->attachment->create();
		self::$images[] = $this->factory()->attachment->create();
		//parent::setUp();
	}

	public function tearDown() {
		//parent::tearDown();
	}

	public function test_pods_image() {
		// Simple fetch.
		$this->assertNotEmpty( pods_image( self::$images[0] ) );

		// Default.
		$this->assertNotEmpty( pods_image( null, 'thumbnail', self::$images[0] ) );

		// Default (make sure it does not loop).
		$this->assertEmpty( pods_image( null, 'thumbnail', 123456789 ) );
	}

	public function test_pods_image_url() {
		// Simple fetch.
		$this->assertNotEmpty( pods_image_url( self::$images[0] ) );

		// Default.
		$this->assertNotEmpty( pods_image_url( null, 'thumbnail', self::$images[0] ) );

		// Default (make sure it does not loop).
		$this->assertEmpty( pods_image_url( null, 'thumbnail', 123456789 ) );
	}

}
