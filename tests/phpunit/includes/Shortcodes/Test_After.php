<?php

namespace Pods_Unit_Tests\Shortcodes;

/**
 * Class Test_After
 *
 * @package Pods_Unit_Tests
 * @group   pods_acceptance_tests
 * @group   pods-shortcodes
 * @group   pods-shortcodes-after
 */
class Test_After extends \Pods_Unit_Tests\Pods_UnitTestCase {

	protected static $pod_name = 'test_after';

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

	public function test_after_simple() {

		$pod_name = self::$pod_name;

		$pod = pods( $pod_name );

		$main_id = $pod->add(
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

		$content = '{@number1}. [after]Done[/after]';

		$shortcode = "[pods name='{$pod_name}' orderby='t.ID']{$content}[/pods]";

		$shortcode_output = apply_filters( 'the_content', $shortcode, $main_id );

		$this->assertEquals( '123. 321. Done', $shortcode_output );
	}

	public function test_after_magic_tags() {

		$pod_name = self::$pod_name;

		$pod = pods( $pod_name );

		$main_id = $pod->add(
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

		$content = '{@number1}. [after]Total records: {@_total}[/after]';

		$shortcode = "[pods name='{$pod_name}' orderby='t.ID']{$content}[/pods]";

		$shortcode_output = apply_filters( 'the_content', $shortcode, $main_id );

		$this->assertEquals( '123. 321. Total Records: 2', $shortcode_output );
	}
}
