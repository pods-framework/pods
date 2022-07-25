<?php

namespace Pods_Unit_Tests\Bugs;

/**
 * @package Pods_Unit_Tests
 * @group   pods_acceptance_tests
 * @group   pods-issue-3451
 */
class Bug3451Test extends \Pods_Unit_Tests\Pods_UnitTestCase {

	private $pod_name = 'test3451';
	private $draft_post = [
		'ID'           => 0,
		'post_title'   => 'Test 3451 Draft post',
		'post_content' => 'Content here',
		'post_status'  => 'draft',
		'post_date'    => '2021-01-01 01:01:01',
	];
	private $private_post = [
		'ID'           => 0,
		'post_title'   => 'Test 3451 Private post',
		'post_content' => 'Content here',
		'post_status'  => 'private',
		'post_date'    => '2021-01-01 01:01:01',
	];
	private $public_post = [
		'ID'           => 0,
		'post_title'   => 'Test 3451 Public post',
		'post_content' => 'Content here',
		'post_status'  => 'publish',
		'post_date'    => '2021-01-01 01:01:01',
	];
	private $user_id = 0;

	public function setUp() {
		parent::setUp();

		$params = [
			'name'    => $this->pod_name,
			'type'    => 'post_type',
			'storage' => 'meta',
		];

		$api = pods_api();

		// Save Pod similar to PodsAdmin.
		$pod_id = $api->save_pod( $params );

		$field_params = [
			'pod_id'      => $pod_id,
			'name'        => $this->pod_name . '_related',
			'type'        => 'pick',
			'pick_object' => 'post_type',
			'pick_val'    => $this->pod_name,
		];

		$api->save_field( $field_params );

		$pod = pods( $this->pod_name );

		$this->user_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );

		$this->draft_post['post_author']   = $this->user_id;
		$this->private_post['post_author'] = $this->user_id;
		$this->public_post['post_author']  = $this->user_id;

		$this->draft_post['ID']   = $pod->add( $this->draft_post );
		$this->private_post['ID'] = $pod->add( $this->private_post );
		$this->public_post['ID']  = $pod->add( $this->public_post );
	}

	public function tearDown() : void {
		$api = pods_api();

		// Delete all posts.
		$api->reset_pod( [ 'name' => $this->pod_name ] );

		// Delete the pod config.
		$api->delete_pod( [ 'name' => $this->pod_name ] );

		// Reset current user.
		global $current_user;

		$current_user = null;

		wp_set_current_user( 0 );

		parent::tearDown();
	}

	/*
	 * Test that private posts show in find() for user.
	 */
	public function test_private_posts_show_in_find_for_user() {
		$this->markTestIncomplete( 'This test is for an enhancement that is not ready yet' );

		wp_set_current_user( $this->user_id );

		$pod = pods( $this->pod_name );

		$params = [
			'pagination' => false,
			'search'     => false,
			'limit'      => 5,
		];

		$pod->find( $params );

		codecept_debug( $pod->sql );

		$data = $pod->data();

		$titles = wp_list_pluck( $data, 'post_title' );

		$this->assertNotContains( $this->draft_post['post_title'], $titles );
		$this->assertContains( $this->private_post['post_title'], $titles );
		$this->assertContains( $this->public_post['post_title'], $titles );
	}

	/*
	 * Test that private posts show in find() with WHERE post_status.
	 */
	public function test_private_posts_show_in_find_with_where_post_status() {
		wp_set_current_user( $this->user_id );

		$pod = pods( $this->pod_name );

		$params = [
			'pagination' => false,
			'search'     => false,
			'limit'      => 5,
			'where'      => 't.post_status = "publish" OR ( t.post_status = "private" AND t.post_author = ' . get_current_user_id() . ' )',
		];

		$pod->find( $params );

		$data = $pod->data();

		$titles = wp_list_pluck( $data, 'post_title' );

		$this->assertNotContains( $this->draft_post['post_title'], $titles );
		$this->assertContains( $this->private_post['post_title'], $titles );
		$this->assertContains( $this->public_post['post_title'], $titles );
	}

	/*
	 * Test that private posts do not show in find() if no access.
	 */
	public function test_private_posts_do_not_show_in_find_if_no_access() {
		$pod = pods( $this->pod_name );

		$params = [
			'pagination' => false,
			'search'     => false,
			'limit'      => 5,
		];

		$pod->find( $params );

		$data = $pod->data();

		$titles = wp_list_pluck( $data, 'post_title' );

		$this->assertNotContains( $this->draft_post['post_title'], $titles );
		$this->assertNotContains( $this->private_post['post_title'], $titles );
		$this->assertContains( $this->public_post['post_title'], $titles );
	}

	/*
	 * Test that private post shows in fetch().
	 */
	public function test_private_post_shows_in_fetch() {
		wp_set_current_user( $this->user_id );

		$pod = pods( $this->pod_name );

		$this->assertNotEmpty( $pod->fetch( $this->draft_post['ID'] ) );
		$this->assertNotEmpty( $pod->fetch( $this->private_post['ID'] ) );
		$this->assertNotEmpty( $pod->fetch( $this->public_post['ID'] ) );
	}

	/*
	 * Test that private posts show in relationship lists for user.
	 */
	public function test_private_posts_show_in_relationship_lists_for_user() {
		// @todo Work on this.
	}

	/*
	 * Test that private posts do not show in relationship lists if no access.
	 */
	public function test_private_posts_do_not_show_in_relationship_lists_if_no_access() {
		// @todo Work on this.
	}
}
