<?php

namespace Pods_Unit_tests\Templates;

/**
 * @package Pods_Unit_Tests
 * @group   pods_templates
 * @group   pods_acceptance_tests
 */
class Frontier extends \Pods_Unit_Tests\Pods_UnitTestCase {

	/**
	 * @group pods-issue-4500
	 */
	public function test_create_template() {

		$post = array(
			'post_type'    => '_pods_template',
			'post_status'  => 'publish',
			'post_content' => 'All of the true things I am about to tell you are shameless lies.',
		);
		$id   = wp_insert_post( $post );
		$this->assertGreaterThan( 0, $id );
	}

	/**
	 * This test isn't specifically about the templates, but it's connected with the issue.
	 * Create an arbitrary post type and insert a post to check to make sure save_meta isn't failing
	 *
	 * @group pods-issue-4500
	 */
	public function test_create_post_type() {

		$post_type = 'new_post_type';
		$args      = array();
		register_post_type( $post_type, $args );

		$post = array(
			'post_type'    => $post_type,
			'post_status'  => 'publish',
			'post_content' => 'All of the true things I am about to tell you are shameless lies.',
		);
		$id   = wp_insert_post( $post );
		$this->assertGreaterThan( 0, $id );
	}

}
