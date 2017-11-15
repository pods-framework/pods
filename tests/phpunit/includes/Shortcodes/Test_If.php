<?php

namespace Pods_Unit_Tests\Shortcodes;

/**
 * Class Test_If
 *
 * @package Pods_Unit_Tests
 * @group   pods_acceptance_tests
 * @group   pods-shortcodes
 * @group   pods-shortcodes-if
 */
class Test_If extends \Pods_Unit_Tests\Pods_UnitTestCase {

	protected static $pod_name = 'test_if';

	protected static $pod_id;

	public static function wpSetUpBeforeClass() {

		add_shortcode(
			'test_if_text', function ( $args, $content ) {

				return 'abc123';
			}
		);
		add_shortcode(
			'test_if_recurse', function ( $args, $content ) {

				return do_shortcode( $content );
			}
		);

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
		$params = array(
			'pod'              => self::$pod_name,
			'pod_id'           => self::$pod_id,
			'name'             => 'related_field',
			'type'             => 'pick',
			'pick_object'      => 'post_type',
			'pick_val'         => self::$pod_name,
			'pick_format_type' => 'single',
		);
		pods_api()->save_field( $params );

	}

	public static function wpTearDownAfterClass() {

		if ( shortcode_exists( 'test_if_text' ) ) {
			remove_shortcode( 'test_if_text' );
		}
		if ( shortcode_exists( 'test_if_recurse' ) ) {
			remove_shortcode( 'test_if_recurse' );
		}
		pods_api()->delete_pod( array( 'id' => self::$pod_id ) );

	}

	public function test_psuedo_shortcodes() {

		// Make sure our pseudo shortcodes are working properly
		$this->assertEquals( 'abc123', do_shortcode( '[test_if_text]' ) );
		$this->assertEquals( 'abc123', do_shortcode( '[test_if_recurse][test_if_text][/test_if_recurse]' ) );
	}

	public function test_if_simple() {

		$pod_name = self::$pod_name;
		$id       = pods( $pod_name )->add(
			array(
				'name'    => __FUNCTION__ . '1',
				'number1' => 123,
				'number2' => 456,
			)
		);
		$content  = base64_encode( 'ABC' );
		$this->assertEquals( 'ABC', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number1']{$content}[/pod_if_field]" ) );
		$content = base64_encode( 'ABC[else]DEF' );
		$this->assertEquals( 'ABC', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number1']{$content}[/pod_if_field]" ) );
		$this->assertNotEquals( 'DEF', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number1']{$content}[/pod_if_field]" ) );

		$id      = pods( $pod_name )->add(
			array(
				'name'    => __FUNCTION__ . '2',
				'number1' => 456,
				'number2' => 0,
			)
		);
		$content = base64_encode( 'ABC' );
		$this->assertNotEquals( 'ABC', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number2']{$content}[/pod_if_field]" ) );
		$this->assertNotEquals( 'ABC', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='invalidfield']{$content}[/pod_if_field]" ) );
		$content = base64_encode( 'ABC[else]DEF' );
		$this->assertEquals( 'DEF', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number2']{$content}[/pod_if_field]" ) );
		$this->assertEquals( 'DEF', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='invalidfield']{$content}[/pod_if_field]" ) );
		$this->assertNotEquals( 'ABC', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number2']{$content}[/pod_if_field]" ) );
	}

	public function test_if_nested() {

		$pod_name      = self::$pod_name;
		$id            = pods( $pod_name )->add(
			array(
				'name'    => __FUNCTION__ . '1',
				'number1' => 123,
				'number2' => 456,
			)
		);
		$inner_content = base64_encode( 'XYZ' );
		$content       = base64_encode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number2']{$inner_content}[/pod_if_field]" );
		$this->assertEquals( 'XYZ', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number1']{$content}[/pod_if_field]" ) );

		$inner_content = base64_encode( 'XYZ' );
		$content       = base64_encode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number2']{$inner_content}[/pod_if_field]" );
		$this->assertEquals( 'XYZ', do_shortcode( "[test_if_recurse][pod_if_field pod='{$pod_name}' id='{$id}' field='number1']{$content}[/pod_if_field][/test_if_recurse]" ) );

		$this->markTestIncomplete( 'Nested shortcodes currently broken, test disabled until issue resolved' );
		$inner_content = base64_encode( '[test_if_recurse]XYZ[/test_if_recurse]' );
		$content       = base64_encode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number2']{$inner_content}[/pod_if_field]" );
		$this->assertEquals( 'XYZ', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number1']{$content}[/pod_if_field]" ) );

	}

	public function test_if_nested_external_shortcodes() {

		$this->markTestIncomplete( 'Nested shortcodes currently broken, test disabled until issue resolved' );

		$pod_name = self::$pod_name;
		$id       = pods( $pod_name )->add(
			array(
				'name'    => __FUNCTION__ . '1',
				'number1' => 123,
				'number2' => 456,
			)
		);
		$content  = base64_encode( '[test_if_text][else]INVALID' );
		$this->assertEquals( 'abc123', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number1']{$content}[/pod_if_field]" ) );
	}

	public function test_if_with_magic_tags() {

		$pod_name = self::$pod_name;
		$id       = pods( $pod_name )->add(
			array(
				'name'    => 'my post title',
				'number1' => 123,
				'number2' => 456,
			)
		);
		$content  = base64_encode( '{@post_title}' );
		$this->assertEquals( 'my post title', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number1']{$content}[/pod_if_field]" ) );
		$content = base64_encode( '{@number1}' );
		$this->assertEquals( '123', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number1']{$content}[/pod_if_field]" ) );

		$id      = pods( $pod_name )->add(
			array(
				'name'    => 'my post title',
				'number1' => 456,
				'number2' => 0,
			)
		);
		$content = base64_encode( '{@number2}[else]{@number1}' );
		$this->assertEquals( '456', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number2']{$content}[/pod_if_field]" ) );
	}

	public function test_if_in_html() {

		$pod_name = self::$pod_name;
		$id       = pods( $pod_name )->add(
			array(
				'name'    => 'my post title',
				'number1' => 123,
				'number2' => 456,
			)
		);
		$content  = base64_encode( '{@number1}[else]{@number2}' );
		// This isn't supposed to be perfect HTML, just good enough for the test
		$this->assertEquals( '<img src="123">', do_shortcode( "<img src=\"[pod_if_field pod='{$pod_name}' id='{$id}' field='number1']{$content}[/pod_if_field]\">" ) );
	}

	/**
	 * @group bug-4403
	 */
	public function test_if_related_field() {

		$pod_name = self::$pod_name;
		$id1      = pods( $pod_name )->add(
			array(
				'post_status' => 'publish',
				'name'        => 'first post title',
				'number1'     => 123,
				'number2'     => 456,
			)
		);
		$id2      = pods( $pod_name )->add(
			array(
				'post_status'   => 'publish',
				'name'          => 'second post title',
				'number1'       => 987,
				'number2'       => 654,
				'related_field' => $id1,
			)
		);

		// Not exactly related to the shortcode test but lets make sure we can at least retrieve the proper data
		$this->assertEquals( '123', pods( $pod_name, $id2 )->field( 'related_field.number1' ) );

		$content = base64_encode( '{@related_field.post_title}' );
		$this->assertEquals( 'first post title', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id2}' field='related_field']{$content}[/pod_if_field]" ) );

		$content = base64_encode( '<a href="{@related_field.permalink}">{@related_field.post_title}{/a>' );
		$this->assertEquals( '<a href="http://example.org/?test_if=first-post-title">first post title{/a>', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id2}' field='related_field']{$content}[/pod_if_field]" ) );

		$this->assertEquals( 'first post title', do_shortcode( "[pods name='{$pod_name}' id='{$id2}'][if related_field]{@related_field.post_title}[/if][/pods]" ) );

	}
}
