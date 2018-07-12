<?php

namespace Pods_Unit_Tests\Shortcodes;

/**
 * Class Test_Before
 *
 * @package Pods_Unit_Tests
 * @group   pods_acceptance_tests
 * @group   pods-shortcodes
 * @group   pods-shortcodes-before
 */
class Test_Before extends \Pods_Unit_Tests\Pods_UnitTestCase {

	protected static $pod_name = 'test_before';

	protected static $pod_id;

	public static function wpSetUpBeforeClass() {

		self::$pod_id = pods_api()->save_pod(
			array(
				'storage' => 'meta',
				'type'    => 'post_type',
				'name'    => self::$pod_name,
			)
		);

		$params = array(
			'pod'    => self::$pod_name,
			'pod_id' => self::$pod_id,
			'name'   => 'number1',
			'type'   => 'number',
		);

		pods_api()->save_field( $params );

		$params = array(
			'pod'    => self::$pod_name,
			'pod_id' => self::$pod_id,
			'name'   => 'number2',
			'type'   => 'number',
		);

		pods_api()->save_field( $params );

	}

	public static function wpTearDownAfterClass() {

		pods_api()->delete_pod( array( 'id' => self::$pod_id ) );

	}

	public function test_before_simple() {

		$pod_name = self::$pod_name;

		$pod = pods( $pod_name );

		$pod->add(
			array(
				'post_status'   => 'publish',
				'name'          => 'main post',
				'number1'       => 123,
				'number2'       => 456,
			)
		);

		$pod->add(
			array(
				'post_status'   => 'publish',
				'name'          => 'secondary post',
				'number1'       => 321,
				'number2'       => 654,
			)
		);

		$content = '[before]Start[/before] {@number1}.';

		$shortcode_output = do_shortcode( "[pods pod='{$pod_name}' orderby='t.ID']{$content}[/pods]" );

		$this->assertEquals( 'Start 123. 321.', $shortcode_output );
	}

	public function test_after_magic_tags() {

		$pod_name = self::$pod_name;

		$pod = pods( $pod_name );

		$pod->add(
			array(
				'post_status'   => 'publish',
				'name'          => 'main post',
				'number1'       => 123,
				'number2'       => 456,
			)
		);

		$pod->add(
			array(
				'post_status'   => 'publish',
				'name'          => 'secondary post',
				'number1'       => 321,
				'number2'       => 654,
			)
		);

		$content = '[before]Total records: {@_total}[/before] {@number1}.';

		$shortcode_output = do_shortcode( "[pods pod='{$pod_name}' orderby='t.ID']{$content}[/pods]" );

		$this->assertEquals( 'Total Records: 2 123. 321.', $shortcode_output );
	}
}
