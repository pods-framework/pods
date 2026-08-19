<?php

namespace Pods_Unit_Tests\Functions;

use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * Magic tags used in pick_where / pick_having are evaluated with a Pods object so
 * that item tags like {@id} resolve. Ambient-context tags such as {@get.foo} must
 * still bypass the Pod: Pods::process_magic_tags() only falls through to the
 * general resolver when PODS_SHORTCODE_ALLOW_EVALUATE_TAGS is enabled, and that
 * fallback drops the sanitize flag, which would place raw request data into a SQL
 * clause.
 *
 * @group  pods-magic-tags
 * @group  pods-issue-7406
 * @covers ::pods_tag_is_context_scoped
 * @covers ::pods_evaluate_tags_in_context
 */
class EvaluateTagContextTest extends Pods_UnitTestCase {

	public function tearDown(): void {
		unset( $_GET['evil'], $_GET['foo'] );

		parent::tearDown();
	}

	/**
	 * @return array[]
	 */
	public function provider_context_scoped() {
		return [
			'get'            => [ 'get.foo' ],
			'post var'       => [ 'post.foo' ],
			'request'        => [ 'request.foo' ],
			'cookie'         => [ 'cookie.foo' ],
			'server'         => [ 'server.HTTP_HOST' ],
			'user'           => [ 'user.ID' ],
			'option'         => [ 'option.blogname' ],
			'constant'       => [ 'constant.ABSPATH' ],
			'site-url alone' => [ 'site-url' ],
			'prefix alone'   => [ 'prefix' ],
			'with helper'    => [ 'get.foo,some_helper' ],
			'braced'         => [ '{@get.foo}' ],
		];
	}

	/**
	 * @dataProvider provider_context_scoped
	 *
	 * @param string $tag The magic tag.
	 */
	public function test_context_scoped_tags_are_detected( $tag ) {
		$this->assertTrue( pods_tag_is_context_scoped( $tag ) );
	}

	/**
	 * @return array[]
	 */
	public function provider_item_scoped() {
		return [
			'bare id'          => [ 'id' ],
			'bare post_title'  => [ 'post_title' ],
			'bare name'        => [ 'name' ],
			'unknown prefix'   => [ 'something.else' ],
			'traversal'        => [ 'author.display_name' ],
			'empty'            => [ '' ],
		];
	}

	/**
	 * @dataProvider provider_item_scoped
	 *
	 * @param string $tag The magic tag.
	 */
	public function test_item_scoped_tags_are_not_context_scoped( $tag ) {
		$this->assertFalse( pods_tag_is_context_scoped( $tag ) );
	}

	/**
	 * A real field on the Pod must win over the ambient context, so existing
	 * traversal keeps working.
	 */
	public function test_real_pod_field_takes_precedence_over_context_prefix() {
		$api = pods_api();

		$pod_id = $api->save_pod( [
			'type' => 'post_type',
			'name' => 'ctx_tag_pod',
		] );

		$api->save_field( [
			'pod_id' => $pod_id,
			'name'   => 'user',
			'type'   => 'text',
		] );

		$pod = pods( 'ctx_tag_pod' );

		$this->assertFalse( pods_tag_is_context_scoped( 'user.something', $pod ) );

		// Without the Pod, the same tag is ambient.
		$this->assertTrue( pods_tag_is_context_scoped( 'user.something' ) );
	}

	/**
	 * The regression this guards: a request value routed through a Pod-scoped
	 * evaluation must still be sanitized rather than emitted raw.
	 */
	public function test_request_values_are_sanitized_when_evaluated_with_a_pod() {
		$_GET['evil'] = "1' OR '1'='1";

		$api = pods_api();

		$pod_id = $api->save_pod( [
			'type' => 'post_type',
			'name' => 'ctx_tag_pod_2',
		] );

		$api->save_field( [
			'pod_id' => $pod_id,
			'name'   => 'some_field',
			'type'   => 'text',
		] );

		$pod = pods( 'ctx_tag_pod_2' );

		$result = pods_evaluate_tags_in_context(
			'`t`.`id` = {@get.evil}',
			[
				'sanitize' => true,
				'pod'      => $pod,
			]
		);

		$this->assertStringNotContainsString( "OR '1'='1", $result );
		$this->assertStringNotContainsString( "1' OR", $result );
	}

	/**
	 * Ambient tags must resolve even though a Pod was supplied.
	 */
	public function test_context_tag_still_resolves_when_a_pod_is_supplied() {
		$_GET['foo'] = 'bar';

		$api = pods_api();

		$pod_id = $api->save_pod( [
			'type' => 'post_type',
			'name' => 'ctx_tag_pod_3',
		] );

		$api->save_field( [
			'pod_id' => $pod_id,
			'name'   => 'some_field',
			'type'   => 'text',
		] );

		$pod = pods( 'ctx_tag_pod_3' );

		$result = pods_evaluate_tags_in_context(
			'value = {@get.foo}',
			[
				'sanitize' => true,
				'pod'      => $pod,
			]
		);

		$this->assertSame( 'value = bar', $result );
	}
}
