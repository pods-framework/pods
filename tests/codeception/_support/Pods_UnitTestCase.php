<?php

namespace Pods_Unit_Tests;

use Pods\Whatsit\Store;
use Pods\Whatsit\Storage;
use Pods;
use PodsMeta;

/**
 * Class Pods_UnitTestCase
 */
class Pods_UnitTestCase extends \Codeception\TestCase\WPTestCase {

	/**
	 * @var array
	 */
	public static $pods = array();

	/**
	 * @var bool
	 */
	public static $db_reset_teardown = true;

	/**
	 *
	 */
	public function tearDown() {
		if ( static::$db_reset_teardown ) {
			parent::tearDown();

			PodsMeta::$post_types             = array();
			PodsMeta::$taxonomies             = array();
			PodsMeta::$media                  = array();
			PodsMeta::$comment                = array();
			PodsMeta::$user                   = array();
			PodsMeta::$advanced_content_types = array();
			PodsMeta::$settings               = array();

			$object_collection = Store::get_instance();
			$object_collection->delete_objects( true );

			pods_api()->cache_flush_pods();
		}

		self::$pods = array();
	}

	/**
	 * @param $class
	 * @param $property
	 *
	 * @return mixed
	 */
	public function getReflectionPropertyValue( $class, $property ) {
		try {
			$reflection = new \ReflectionProperty( $class, $property );
			$reflection->setAccessible( true );

			return $reflection->getValue( $class );
		} catch ( \ReflectionException $exception ) {
			return null;
		}
	}

	/**
	 * @param $class
	 * @param $property
	 * @param $value
	 */
	public function setReflectionPropertyValue( $class, $property, $value ) {
		try {
			$reflection = new \ReflectionProperty( $class, $property );
			$reflection->setAccessible( true );

			$reflection->setValue( $class, $value );
		} catch ( \ReflectionException $exception ) {
			// Do nothing.
		}
	}

	/**
	 * @param $class
	 * @param $method
	 *
	 * @return mixed
	 */
	public function reflectionMethodInvoke( $class, $method ) {
		try {
			$reflection = new \ReflectionMethod( $class, $method );
			$reflection->setAccessible( true );

			return $reflection->invoke( $class );
		} catch ( \ReflectionException $exception ) {
			return null;
		}
	}

	/**
	 * @param $class
	 * @param $method
	 * @param $args
	 *
	 * @return mixed
	 */
	public function reflectionMethodInvokeArgs( $class, $method, $args ) {
		try {
			$reflection = new \ReflectionMethod( $class, $method );
			$reflection->setAccessible( true );

			return $reflection->invokeArgs( $class, $args );
		} catch ( \ReflectionException $exception ) {
			return null;
		}
	}

	/**
	 * Get/create pod from store.
	 *
	 * @param string $pod
	 *
	 * @return Pods
	 */
	public static function get_pod( $pod ) {
		if ( ! isset( self::$pods[ $pod ] ) ) {
			self::$pods[ $pod ] = pods( $pod, null, false );
		}

		return self::$pods[ $pod ];
	}
}
