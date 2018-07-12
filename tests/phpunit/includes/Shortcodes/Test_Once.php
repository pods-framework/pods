<?php

namespace Pods_Unit_Tests\Shortcodes;

/**
 * Class Test_Once
 *
 * @package Pods_Unit_Tests
 * @group   pods_acceptance_tests
 * @group   pods-shortcodes
 * @group   pods-shortcodes-once
 */
class Test_Once extends \Pods_Unit_Tests\Pods_UnitTestCase {

	protected static $pod_name = 'test_once';

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

	public function test_once_simple() {

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

		$content = '{@number1}.[once]HI![/once]';

		$shortcode_output = do_shortcode( "[pods name='{$pod_name}' orderby='t.ID']{$content}[/pods]" );

		$this->assertEquals( '123.HI!321.', $shortcode_output );
	}

	public function test_once_magic_tags() {

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

		$content = '{@number1}.[once]{@number2}.[/once]';

		$shortcode_output = do_shortcode( "[pods name='{$pod_name}' orderby='t.ID']{$content}[/pods]" );

		$this->assertEquals( '123.456.321.', $shortcode_output );
	}
}
