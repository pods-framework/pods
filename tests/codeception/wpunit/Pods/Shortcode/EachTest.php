<?php

namespace Pods_Unit_Tests\Pods\Shortcode;

use Pods;
use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * Class EachTest
 *
 * @group pods-shortcode
 * @group pods-shortcode-pods-each
 */
class EachTest extends Pods_UnitTestCase {

	/**
	 * @var string
	 */
	protected $pod_name = 'test_each';

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
	public function setUp() : void {
		parent::setUp();

		add_shortcode( 'test_each_recurse', function ( $args, $content ) {
			return do_shortcode( $content );
		} );

		$api = pods_api();

		$this->pod_id = $api->save_pod( [
			'type' => 'post_type',
			'name' => $this->pod_name,
		] );

		$params = [
			'pod_id' => $this->pod_id,
			'name'   => 'number1',
			'type'   => 'number',
		];

		$api->save_field( $params );

		$params = [
			'pod_id' => $this->pod_id,
			'name'   => 'number2',
			'type'   => 'number',
		];

		$api->save_field( $params );

		$params = [
			'pod_id'           => $this->pod_id,
			'name'             => 'related_field',
			'type'             => 'pick',
			'pick_object'      => 'post_type',
			'pick_val'         => $this->pod_name,
			'pick_format_type' => 'multi',
		];

		$api->save_field( $params );

		$this->pod = pods( $this->pod_name );
	}

	/**
	 *
	 */
	public function tearDown() : void {
		if ( shortcode_exists( 'test_each_recurse' ) ) {
			remove_shortcode( 'test_each_recurse' );
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
		$this->assertEquals( 'abc123', do_shortcode( '[test_each_recurse]abc123[/test_each_recurse]' ) );
	}

	/**
	 *
	 */
	public function test_each_simple() {
		$this->assertNotFalse( $this->pod );

		$pod_name = $this->pod_name;

		$sub_ids = [];

		for ( $x = 1; $x <= 5; $x ++ ) {
			$sub_ids[] = $this->pod->add( [
				'post_status' => 'publish',
				'post_title'  => __FUNCTION__ . ': sub post ' . $x,
				'number1'     => $x,
				'number2'     => $x * $x,
			] );
		}

		$main_id = $this->pod->add( [
			'post_status'   => 'publish',
			'post_title'    => __FUNCTION__ . ': main post',
			'number1'       => 123,
			'number2'       => 456,
			'related_field' => $sub_ids,
		] );

		$content = base64_encode( '/{@number1}_{@number2}/' );

		$this->assertEquals( '/1_1//2_4//3_9//4_16//5_25/', do_shortcode( "[pod_sub_template pod='{$pod_name}' id='{$main_id}' field='related_field']{$content}[/pod_sub_template]" ) );

		/**
		 * Image tests.
		 */

		$image_ids   = [];
		$image_ids[] = $this->factory()->attachment->create();
		$image_ids[] = $this->factory()->attachment->create();
		$image_ids[] = $this->factory()->attachment->create();

		$main_id = $this->pod->save( [
				'ID'     => $main_id,
				'images' => $image_ids,
			] );

		$content = base64_encode( '{@_src}' );
		$compare = '';
		foreach ( $image_ids as $img ) {
			$compare .= pods_image_url( $img, 'medium' );
		}

		// Make sure the media Pod exists.
		// @todo Validate when there is not media Pod active. Requires refactor of caching.
		$this->assertTrue( pods( 'media' )->valid() );

		// Should return all image links.
		$this->assertEquals( $compare, do_shortcode( "[pod_sub_template pod='{$pod_name}' id='{$main_id}' field='images']{$content}[/pod_sub_template]" ) );

		// Use media object for Pod related fields.
		$content = base64_encode( '{@title}' );
		$compare = '';
		foreach ( $image_ids as $img ) {
			$compare .= get_the_title( $img );
		}

		// Should still return all image links.
		$this->assertEquals( $compare, do_shortcode( "[pod_sub_template pod='{$pod_name}' id='{$main_id}' field='images']{$content}[/pod_sub_template]" ) );
	}

	/**
	 *
	 */
	public function test_each_with_nested_if() {
		$this->assertNotFalse( $this->pod );

		$pod_name = $this->pod_name;

		$sub_ids = [];

		for ( $x = 1; $x <= 5; $x ++ ) {
			$sub_ids[] = $this->pod->add( [
				'post_status' => 'publish',
				'name'        => $x,
				'number1'     => $x,
				'number2'     => $x * $x,
			] );
		}

		$main_id = $this->pod->add( [
			'post_status'   => 'publish',
			'name'          => 'main post',
			'number1'       => 123,
			'number2'       => 456,
			'related_field' => $sub_ids,
		] );

		$content = base64_encode( '[if number1]/{@number1}_{@number2}/[/if]' );

		$this->assertEquals( '/1_1//2_4//3_9//4_16//5_25/', do_shortcode( "[pod_sub_template pod='{$pod_name}' id='{$main_id}' field='related_field']{$content}[/pod_sub_template]" ) );

		// Testing [each] inside [if]
		$inner_content = base64_encode( '[if number1]/{@number1}_{@number2}/[/if]' );
		$content       = base64_encode( "[pod_sub_template pod='{$pod_name}' id='{$main_id}' field='related_field']{$inner_content}[/pod_sub_template]" );

		$this->assertEquals( '/1_1//2_4//3_9//4_16//5_25/', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$main_id}' field='related_field']{$content}[/pod_if_field]" ) );

		// Testing [each] inside [if] with [else]
		$inner_content = base64_encode( '[if number1]/{@number1}_{@number2}/[/if]' );
		$content       = base64_encode( "[pod_sub_template pod='{$pod_name}' id='{$main_id}' field='related_field']{$inner_content}[/pod_sub_template][else]No related field" );

		$this->assertEquals( '/1_1//2_4//3_9//4_16//5_25/', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$main_id}' field='related_field']{$content}[/pod_if_field]" ) );

		// Testing [each] inside [if] with [else] and no relationships
		$main_id = $this->pod->add( [
			'post_status' => 'publish',
			'name'        => 'post with no related fields',
			'number1'     => 123,
			'number2'     => 456,
		] );

		$inner_content = base64_encode( '[if number1]/{@number1}_{@number2}/[/if]' );
		$content       = base64_encode( "[pod_sub_template pod='{$pod_name}' id='{$main_id}' field='related_field']{$inner_content}[/pod_sub_template][else]No related field" );

		$this->assertEquals( 'No related field', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$main_id}' field='related_field']{$content}[/pod_if_field]" ) );
	}

	/**
	 *
	 */
	public function test_each_nested_in_external() {
		$this->assertNotFalse( $this->pod );

		$pod_name = $this->pod_name;

		$sub_ids = [];

		for ( $x = 1; $x <= 5; $x ++ ) {
			$sub_ids[] = $this->pod->add( [
				'post_status' => 'publish',
				'name'        => $x,
				'number1'     => $x,
				'number2'     => $x * $x,
			] );
		}

		$main_id = $this->pod->add( [
			'post_status'   => 'publish',
			'name'          => 'main post',
			'number1'       => 123,
			'number2'       => 456,
			'related_field' => $sub_ids,
		] );

		$content = base64_encode( '/{@number1}_{@number2}/' );

		$this->assertEquals( '/1_1//2_4//3_9//4_16//5_25/', do_shortcode( "[test_each_recurse][pod_sub_template pod='{$pod_name}' id='{$main_id}' field='related_field']{$content}[/pod_sub_template][/test_each_recurse]" ) );
	}
}
