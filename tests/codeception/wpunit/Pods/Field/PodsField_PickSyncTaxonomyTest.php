<?php

namespace Pods_Unit_Tests\Pods\Field;

use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * @group              pods-field
 * @coversDefaultClass PodsField_Pick
 */
class PodsField_PickSyncTaxonomyTest extends Pods_UnitTestCase {

	/**
	 * @var string
	 */
	protected $post_type = 'test_sync_tome';

	/**
	 * @var string
	 */
	protected $taxonomy = 'test_sync_shelf';

	/**
	 * @var int
	 */
	protected $term_id = 0;

	/**
	 * @var int
	 */
	protected $post_one = 0;

	/**
	 * @var int
	 */
	protected $post_two = 0;

	public function setUp(): void {
		parent::setUp();

		$api = pods_api();

		$taxonomy_pod_id = $api->save_pod( [
			'type'    => 'taxonomy',
			'storage' => 'meta',
			'name'    => $this->taxonomy,
		] );

		$post_type_pod_id = $api->save_pod( [
			'type'    => 'post_type',
			'storage' => 'meta',
			'name'    => $this->post_type,
		] );

		// The post type's field syncs the taxonomy, and the taxonomy's sister field holds one post.
		$shelves_field_id = $api->save_field( [
			'pod_id'             => $post_type_pod_id,
			'name'               => 'shelves',
			'type'               => 'pick',
			'pick_object'        => 'taxonomy',
			'pick_val'           => $this->taxonomy,
			'pick_format_type'   => 'multi',
			'pick_sync_taxonomy' => 1,
		] );

		$tomes_field_id = $api->save_field( [
			'pod_id'           => $taxonomy_pod_id,
			'name'             => 'tomes',
			'type'             => 'pick',
			'pick_object'      => 'post_type',
			'pick_val'         => $this->post_type,
			'pick_format_type' => 'single',
			'sister_id'        => $shelves_field_id,
		] );

		$api->save_field( [
			'id'        => $shelves_field_id,
			'pod_id'    => $post_type_pod_id,
			'name'      => 'shelves',
			'sister_id' => $tomes_field_id,
		] );

		register_post_type( $this->post_type );
		register_taxonomy( $this->taxonomy, $this->post_type );

		$term = wp_insert_term( 'Top Shelf', $this->taxonomy );

		$this->term_id = $term['term_id'];

		$this->post_one = wp_insert_post( [
			'post_title'  => 'Tome One',
			'post_type'   => $this->post_type,
			'post_status' => 'publish',
		] );

		$this->post_two = wp_insert_post( [
			'post_title'  => 'Tome Two',
			'post_type'   => $this->post_type,
			'post_status' => 'publish',
		] );
	}

	public function tearDown(): void {
		unregister_taxonomy( $this->taxonomy );
		unregister_post_type( $this->post_type );

		parent::tearDown();
	}

	/**
	 * Saving the post's own field syncs its terms.
	 *
	 * @covers ::save
	 */
	public function test_save_syncs_terms_of_saved_post() {
		pods( $this->post_type, $this->post_one )->save( 'shelves', [ $this->term_id ] );

		$this->assertEquals( [ $this->term_id ], $this->get_term_ids( $this->post_one ) );

		pods( $this->post_type, $this->post_one )->save( 'shelves', [] );

		$this->assertEquals( [], $this->get_term_ids( $this->post_one ) );
	}

	/**
	 * A post that loses the term from its field through the sister field's limit also loses the term.
	 *
	 * @covers ::save
	 */
	public function test_save_syncs_terms_of_post_removed_by_sister_limit() {
		pods( $this->post_type, $this->post_two )->save( 'shelves', [ $this->term_id ] );
		pods( $this->post_type, $this->post_one )->save( 'shelves', [ $this->term_id ] );

		$this->assertEquals( [], $this->get_field_ids( $this->post_two ) );
		$this->assertEquals( [], $this->get_term_ids( $this->post_two ) );

		$this->assertEquals( [ $this->term_id ], $this->get_field_ids( $this->post_one ) );
		$this->assertEquals( [ $this->term_id ], $this->get_term_ids( $this->post_one ) );
	}

	/**
	 * Saving the term's sister field syncs the terms of the posts added to and removed from it.
	 *
	 * @covers ::save
	 */
	public function test_save_syncs_terms_of_posts_changed_from_term() {
		pods( $this->post_type, $this->post_one )->save( 'shelves', [ $this->term_id ] );
		pods( $this->taxonomy, $this->term_id )->save( 'tomes', $this->post_two );

		$this->assertEquals( [], $this->get_field_ids( $this->post_one ) );
		$this->assertEquals( [], $this->get_term_ids( $this->post_one ) );

		$this->assertEquals( [ $this->term_id ], $this->get_field_ids( $this->post_two ) );
		$this->assertEquals( [ $this->term_id ], $this->get_term_ids( $this->post_two ) );

		$posts = get_posts( [
			'post_type' => $this->post_type,
			'fields'    => 'ids',
			'tax_query' => [
				[
					'taxonomy' => $this->taxonomy,
					'terms'    => [ $this->term_id ],
				],
			],
		] );

		$this->assertEquals( [ $this->post_two ], $posts );
	}

	/**
	 * Clearing the term's sister field syncs the terms of the post removed from it.
	 *
	 * @covers ::save
	 */
	public function test_save_syncs_terms_of_post_cleared_from_term() {
		pods( $this->post_type, $this->post_one )->save( 'shelves', [ $this->term_id ] );
		pods( $this->taxonomy, $this->term_id )->save( 'tomes', '' );

		$this->assertEquals( [], $this->get_field_ids( $this->post_one ) );
		$this->assertEquals( [], $this->get_term_ids( $this->post_one ) );
	}

	/**
	 * A post of another post type saved in the term's sister field does not get the term.
	 *
	 * @covers ::save
	 */
	public function test_save_does_not_sync_terms_of_other_post_type() {
		$page_id = wp_insert_post( [
			'post_title'  => 'Not A Tome',
			'post_type'   => 'page',
			'post_status' => 'publish',
		] );

		pods( $this->taxonomy, $this->term_id )->save( 'tomes', $page_id );

		$this->assertEquals( [], $this->get_term_ids( $page_id ) );
	}

	/**
	 * @param int $post_id The post ID.
	 *
	 * @return int[] The term IDs in the post's relationship field.
	 */
	private function get_field_ids( $post_id ) {
		return array_map( 'intval', get_post_meta( $post_id, 'shelves', false ) );
	}

	/**
	 * @param int $post_id The post ID.
	 *
	 * @return int[] The post's terms.
	 */
	private function get_term_ids( $post_id ) {
		return array_map( 'intval', wp_get_object_terms( $post_id, $this->taxonomy, [ 'fields' => 'ids' ] ) );
	}
}
