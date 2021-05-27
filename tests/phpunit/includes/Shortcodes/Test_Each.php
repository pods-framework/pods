<?php

namespace Pods_Unit_Tests\Shortcodes;

/**
 * Class Test_Each
 *
 * @package Pods_Unit_Tests
 * @group   pods_acceptance_tests
 * @group   pods-shortcodes
 * @group   pods-shortcodes-each
 */
class Test_Each extends \Pods_Unit_Tests\Pods_UnitTestCase {

	protected static $pod_name = 'test_each';

	protected static $pod_id;

	public static function wpSetUpBeforeClass() {

		add_shortcode(
			'test_each_recurse', function ( $args, $content ) {

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
			'name'             => 'images',
			'type'             => 'file',
			'file_format_type' => 'multi',
			'file_type'        => 'images',
		);

		pods_api()->save_field( $params );

		$params = array(
			'pod'              => self::$pod_name,
			'pod_id'           => self::$pod_id,
			'name'             => 'related_field',
			'type'             => 'pick',
			'pick_object'      => 'post_type',
			'pick_val'         => self::$pod_name,
			'pick_format_type' => 'multi',
		);

		pods_api()->save_field( $params );

	}

	public static function wpTearDownAfterClass() {

		if ( shortcode_exists( 'test_each_recurse' ) ) {
			remove_shortcode( 'test_each_recurse' );
		}
		pods_api()->delete_pod( array( 'id' => self::$pod_id ) );

	}

	public function test_psuedo_shortcodes() {

		// Make sure our pseudo shortcodes are working properly
		$this->assertEquals( 'abc123', do_shortcode( '[test_each_recurse]abc123[/test_each_recurse]' ) );
	}

	public function test_each_simple() {

		$pod_name = self::$pod_name;
		$sub_ids  = array();
		$pod      = pods( $pod_name );
		for ( $x = 1; $x <= 5; $x ++ ) {
			$sub_ids[] = $pod->add(
				array(
					'post_status' => 'publish',
					'name'        => $x,
					'number1'     => $x,
					'number2'     => $x * $x,
				)
			);
		}
		$main_id = $pod->add(
			array(
				'post_status'   => 'publish',
				'name'          => 'main post',
				'number1'       => 123,
				'number2'       => 456,
				'related_field' => $sub_ids,
			)
		);
		$content = base64_encode( '{@number1}_{@number2}' );
		$this->assertEquals( '1_12_43_94_165_25', do_shortcode( "[pod_sub_template pod='{$pod_name}' id='{$main_id}' field='related_field']{$content}[/pod_sub_template]" ) );

		/**
		 * Image tests.
		 */

		$image_ids = array();
		$image_ids[] = $this->factory()->attachment->create();
		$image_ids[] = $this->factory()->attachment->create();
		$image_ids[] = $this->factory()->attachment->create();

		$main_id = $pod->save(
			array(
				'ID'     => $main_id,
				'images' => $image_ids,
			)
		);

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

	public function test_each_with_nested_if() {

		$pod_name = self::$pod_name;
		$sub_ids  = array();
		$pod      = pods( $pod_name );
		for ( $x = 1; $x <= 5; $x ++ ) {
			$sub_ids[] = $pod->add(
				array(
					'post_status' => 'publish',
					'name'        => $x,
					'number1'     => $x,
					'number2'     => $x * $x,
				)
			);
		}
		$main_id = $pod->add(
			array(
				'post_status'   => 'publish',
				'name'          => 'main post',
				'number1'       => 123,
				'number2'       => 456,
				'related_field' => $sub_ids,
			)
		);
		$content = base64_encode( '[if number1]{@number1}_{@number2}[/if]' );
		$this->assertEquals( '1_12_43_94_165_25', do_shortcode( "[pod_sub_template pod='{$pod_name}' id='{$main_id}' field='related_field']{$content}[/pod_sub_template]" ) );
		// Testing [each] inside [if]
		$inner_content = base64_encode( '[if number1]{@number1}_{@number2}[/if]' );
		$content       = base64_encode( "[pod_sub_template pod='{$pod_name}' id='{$main_id}' field='related_field']{$inner_content}[/pod_sub_template]" );
		$this->assertEquals( '1_12_43_94_165_25', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$main_id}' field='related_field']{$content}[/pod_if_field]" ) );

		// Testing [each] inside [if] with [else]
		$inner_content = base64_encode( '[if number1]{@number1}_{@number2}[/if]' );
		$content       = base64_encode( "[pod_sub_template pod='{$pod_name}' id='{$main_id}' field='related_field']{$inner_content}[/pod_sub_template][else]No related field" );
		$this->assertEquals( '1_12_43_94_165_25', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$main_id}' field='related_field']{$content}[/pod_if_field]" ) );

		// Testing [each] inside [if] with [else] and no relationships
		$main_id       = $pod->add(
			array(
				'post_status' => 'publish',
				'name'        => 'post with no related fields',
				'number1'     => 123,
				'number2'     => 456,
			)
		);
		$inner_content = base64_encode( '[if number1]{@number1}_{@number2}[/if]' );
		$content       = base64_encode( "[pod_sub_template pod='{$pod_name}' id='{$main_id}' field='related_field']{$inner_content}[/pod_sub_template][else]No related field" );
		$this->assertEquals( 'No related field', do_shortcode( "[pod_if_field pod='{$pod_name}' id='{$main_id}' field='related_field']{$content}[/pod_if_field]" ) );
	}

	public function test_each_nested_in_external() {

		$pod_name = self::$pod_name;
		$sub_ids  = array();
		$pod      = pods( $pod_name );
		for ( $x = 1; $x <= 5; $x ++ ) {
			$sub_ids[] = $pod->add(
				array(
					'post_status' => 'publish',
					'name'        => $x,
					'number1'     => $x,
					'number2'     => $x * $x,
				)
			);
		}
		$main_id = $pod->add(
			array(
				'post_status'   => 'publish',
				'name'          => 'main post',
				'number1'       => 123,
				'number2'       => 456,
				'related_field' => $sub_ids,
			)
		);
		$content = base64_encode( '{@number1}_{@number2}' );
		$this->assertEquals( '1_12_43_94_165_25', do_shortcode( "[test_each_recurse][pod_sub_template pod='{$pod_name}' id='{$main_id}' field='related_field']{$content}[/pod_sub_template][/test_each_recurse]" ) );
	}

	/**
	 * Test traversal each statements.
	 * Almost similar to test_each_nested_in_external() but this traverses one level deeper.
	 */
	public function test_each_traversal() {

		$pod_name = self::$pod_name;
		$subsub_ids  = array();
		$pod      = pods( $pod_name );
		for ( $x = 1; $x <= 5; $x ++ ) {
			$subsub_ids[] = $pod->add(
				array(
					'post_status' => 'publish',
					'name'        => $x,
					'number1'     => $x,
					'number2'     => $x * $x,
				)
			);
		}
		$sub_id = $pod->add(
			array(
				'post_status'   => 'publish',
				'name'          => 'sub post',
				'number1'       => 123,
				'number2'       => 456,
				'related_field' => $subsub_ids,
			)
		);
		$main_id = $pod->add(
			array(
				'post_status'   => 'publish',
				'name'          => 'main post',
				'number1'       => 159,
				'number2'       => 753,
				'related_field' => $sub_id,
			)
		);
		$content = base64_encode( '{@number1}_{@number2}' );
		$this->assertEquals( '1_12_43_94_165_25', do_shortcode( "[test_each_recurse][pod_sub_template pod='{$pod_name}' id='{$main_id}' field='related_field.related_field']{$content}[/pod_sub_template][/test_each_recurse]" ) );
	}
}
