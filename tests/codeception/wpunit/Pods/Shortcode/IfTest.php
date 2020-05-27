<?php

namespace Pods_Unit_Tests\Pods\Shortcode;

use Pods_Unit_Tests\Pods_UnitTestCase;
use Pods;

/**
 * Class IfTest
 *
 * @group pods-shortcode
 * @group pods-shortcode-pods-if
 */
class IfTest extends Pods_UnitTestCase {

	/**
	 * @var string
	 */
	protected $pod_name = 'test_if';

	/**
	 * @var int
	 */
	protected $pod_id = 0;

	/**
	 * @var Pods
	 */
	protected $pod;

	/**
	 *
	 */
	public function setUp() {
		parent::setUp();

		add_shortcode( 'test_if_text', function ( $args, $content ) {
			return 'abc123';
		} );
		add_shortcode( 'test_if_recurse', function ( $args, $content ) {
			return do_shortcode( $content );
		} );

		$api = pods_api();

		$this->pod_id = $api->save_pod( array(
			'type' => 'post_type',
			'name' => $this->pod_name,
		) );

		$params = array(
			'pod_id' => $this->pod_id,
			'name'   => 'number1',
			'type'   => 'number',
		);

		$api->save_field( $params );

		$params = array(
			'pod_id' => $this->pod_id,
			'name'   => 'number2',
			'type'   => 'number',
		);

		$api->save_field( $params );

		$params = array(
			'pod_id'           => $this->pod_id,
			'name'             => 'related_field',
			'type'             => 'pick',
			'pick_object'      => 'post_type',
			'pick_val'         => $this->pod_name,
			'pick_format_type' => 'single',
		);

		$api->save_field( $params );

		$this->pod = pods( $this->pod_name );
	}

	/**
	 *
	 */
	public function tearDown() {
		if ( shortcode_exists( 'test_if_text' ) ) {
			remove_shortcode( 'test_if_text' );
		}
		if ( shortcode_exists( 'test_if_recurse' ) ) {
			remove_shortcode( 'test_if_recurse' );
		}

		$this->pod_id = null;
		$this->pod    = null;

		parent::tearDown();
	}

	/**
	 *
	 */
	public function test_psuedo_shortcodes() {
		// Make sure our pseudo shortcodes are working properly
		$this->assertEquals( 'abc123', do_shortcode( '[test_if_text]' ) );
		$this->assertEquals( 'abc123', do_shortcode( '[test_if_recurse][test_if_text][/test_if_recurse]' ) );
	}

	/**
	 *
	 */
	public function test_if_simple() {
		$this->assertNotFalse( $this->pod );

		$pod_name = $this->pod_name;

		$id = $this->pod->add( array(
			'name'    => __FUNCTION__ . '1',
			'number1' => 123,
			'number2' => 456,
		) );

		$content = base64_encode( 'ABC' );

		$this->assertEquals( 'ABC', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number1']{$content}[/pod_if_field]" ) );

		$content = base64_encode( 'ABC[else]DEF' );

		$this->assertEquals( 'ABC', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number1']{$content}[/pod_if_field]" ) );
		$this->assertNotEquals( 'DEF', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number1']{$content}[/pod_if_field]" ) );

		$id = $this->pod->add( array(
			'name'    => __FUNCTION__ . '2',
			'number1' => 456,
			'number2' => 0,
		) );

		$content = base64_encode( 'ABC' );

		$this->assertNotEquals( 'ABC', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number2']{$content}[/pod_if_field]" ) );
		$this->assertNotEquals( 'ABC', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='invalidfield']{$content}[/pod_if_field]" ) );

		$content = base64_encode( 'ABC[else]DEF' );

		$this->assertEquals( 'DEF', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number2']{$content}[/pod_if_field]" ) );
		$this->assertEquals( 'DEF', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='invalidfield']{$content}[/pod_if_field]" ) );
		$this->assertNotEquals( 'ABC', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number2']{$content}[/pod_if_field]" ) );
	}

	/**
	 *
	 */
	public function test_if_nested() {
		$this->assertNotFalse( $this->pod );

		$pod_name = $this->pod_name;

		$id = $this->pod->add( array(
			'name'    => __FUNCTION__ . '1',
			'number1' => 123,
			'number2' => 456,
		) );

		$inner_content = base64_encode( 'XYZ' );
		$content       = base64_encode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number2']{$inner_content}[/pod_if_field]" );

		$this->assertEquals( 'XYZ', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number1']{$content}[/pod_if_field]" ) );

		$inner_content = base64_encode( 'XYZ' );
		$content       = base64_encode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number2']{$inner_content}[/pod_if_field]" );

		$this->assertEquals( 'XYZ', do_shortcode( "[test_if_recurse][pod_if_field pod='{$pod_name}' id='{$id}' field='number1']{$content}[/pod_if_field][/test_if_recurse]" ) );

		//$this->markTestSkipped( 'Nested shortcodes currently broken, test disabled until issue resolved' );

		return;

		$inner_content = base64_encode( '[test_if_recurse]XYZ[/test_if_recurse]' );
		$content       = base64_encode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number2']{$inner_content}[/pod_if_field]" );

		$this->assertEquals( 'XYZ', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number1']{$content}[/pod_if_field]" ) );
	}

	/**
	 *
	 */
	public function _test_if_nested_external_shortcodes() {
		$this->assertNotFalse( $this->pod );

		$this->markTestSkipped( 'Nested shortcodes currently broken, test disabled until issue resolved' );

		$pod_name = $this->pod_name;

		$id = $this->pod->add( array(
			'name'    => __FUNCTION__ . '1',
			'number1' => 123,
			'number2' => 456,
		) );

		$content = base64_encode( '[test_if_text][else]INVALID' );

		$this->assertEquals( 'abc123', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number1']{$content}[/pod_if_field]" ) );
	}

	/**
	 *
	 */
	public function test_if_with_magic_tags() {
		$this->assertNotFalse( $this->pod );

		$pod_name = $this->pod_name;

		$id = $this->pod->add( array(
			'name'    => 'my post title',
			'number1' => 123,
			'number2' => 456,
		) );

		$content = base64_encode( '{@post_title}' );
		$this->assertEquals( 'my post title', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number1']{$content}[/pod_if_field]" ) );

		$content = base64_encode( '{@number1}' );
		$this->assertEquals( '123', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number1']{$content}[/pod_if_field]" ) );

		$id = $this->pod->add( array(
			'name'    => 'my post title',
			'number1' => 456,
			'number2' => 0,
		) );

		$content = base64_encode( '{@number2}[else]{@number1}' );
		$this->assertEquals( '456', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id}' field='number2']{$content}[/pod_if_field]" ) );
	}

	/**
	 *
	 */
	public function test_if_in_html() {
		$this->assertNotFalse( $this->pod );

		$pod_name = $this->pod_name;

		$id      = $this->pod->add( array(
			'name'    => 'my post title',
			'number1' => 123,
			'number2' => 456,
		) );
		$content = base64_encode( '{@number1}[else]{@number2}' );
		// This isn't supposed to be perfect HTML, just good enough for the test
		$this->assertEquals( '<img src="123">', do_shortcode( "<img src=\"[pod_if_field pod='{$pod_name}' id='{$id}' field='number1']{$content}[/pod_if_field]\">" ) );
	}

	/**
	 * @group bug-4403
	 */
	public function test_if_related_field() {
		$this->assertNotFalse( $this->pod );

		$pod_name = $this->pod_name;

		$id1 = $this->pod->add( array(
			'post_status' => 'publish',
			'name'        => 'first post title',
			'number1'     => 123,
			'number2'     => 456,
		) );
		$id2 = $this->pod->add( array(
			'post_status'   => 'publish',
			'name'          => 'second post title',
			'number1'       => 987,
			'number2'       => 654,
			'related_field' => $id1,
		) );

		// Not exactly related to the shortcode test but lets make sure we can at least retrieve the proper data
		$this->assertEquals( '123', pods( $pod_name, $id2 )->field( 'related_field.number1' ) );

		$content = base64_encode( '{@related_field.post_title}' );
		$this->assertEquals( 'first post title', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id2}' field='related_field']{$content}[/pod_if_field]" ) );

		$content = base64_encode( '<a href="{@related_field.permalink}">{@related_field.post_title}</a>' );

		$site_url = site_url();

		$valid = array(
			'<a href="' . $site_url . '/first-post-title">first post title</a>',
			'<a href="' . $site_url . '/?test_if=first-post-title">first post title</a>',
			'<a href="' . $site_url . '/?p=' . $id1. '">first post title</a>',
		);

		$this->assertContains( do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$id2}' field='related_field']{$content}[/pod_if_field]" ), $valid );

		$this->assertEquals( 'first post title', do_shortcode( "[pods name='{$pod_name}' id='{$id2}'][if related_field]{@related_field.post_title}[/if][/pods]" ) );
	}
}
