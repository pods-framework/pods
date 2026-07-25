<?php

namespace Pods_Unit_Tests\Pods;

use Pods_Unit_Tests\Pods_UnitTestCase;
use WP_Query;

/**
 * @group pods-wp-query-table-meta
 * @group pods-config-required
 * @covers \Pods\Theme\WP_Query_Integration::rewrite_meta_query_clauses
 * @covers \Pods\Theme\WP_Query_Integration::maybe_collect_table_meta_query
 */
class WP_QueryMetaQueryTest extends Pods_UnitTestCase {

	/**
	 * @var int
	 */
	protected $pod_id = 0;

	/**
	 * @var string
	 */
	protected $pod_name = '';

	public function setUp(): void {
		parent::setUp();

		$this->pod_name = 'wp_query_tbl_' . wp_generate_password( 6, false, false );

		$api = pods_api();

		$this->pod_id = $api->save_pod( [
			'type'    => 'post_type',
			'name'    => $this->pod_name,
			'storage' => 'table',
		] );

		$api->save_field( [
			'pod_id' => $this->pod_id,
			'name'   => 'subtitle',
			'type'   => 'text',
		] );

		$api->save_field( [
			'pod_id'           => $this->pod_id,
			'name'             => 'rating',
			'type'             => 'number',
			'number_format'    => '9999.99',
		] );

		$api->save_field( [
			'pod_id'           => $this->pod_id,
			'name'             => 'related',
			'type'             => 'pick',
			'pick_object'      => 'post_type',
			'pick_val'         => $this->pod_name,
			'pick_format_type' => 'single',
		] );

		register_post_type( $this->pod_name, [
			'public'  => true,
			'label'   => $this->pod_name,
			'rewrite' => false,
		] );
	}

	public function tearDown(): void {
		unregister_post_type( $this->pod_name );

		pods_api()->delete_pod( [ 'id' => $this->pod_id ] );
		$this->pod_id   = 0;
		$this->pod_name = '';

		parent::tearDown();
	}

	/**
	 * Smoke check: meta_query by table-stored field returns the same IDs as `pods()`.
	 */
	public function test_meta_query_by_table_field_returns_same_ids_as_pods_helper() {
		$ids = $this->make_items( [
			[ 'subtitle' => 'Alpha', 'rating' => 5 ],
			[ 'subtitle' => 'Beta',  'rating' => 7 ],
			[ 'subtitle' => 'Gamma', 'rating' => 9 ],
		] );

		$pod_ids = pods( $this->pod_name, [
			'where' => [ 'subtitle' => 'Beta' ],
		] )->ids();

		sort( $pod_ids );
		$expected = [ $ids[1] ];

		$query = new WP_Query( [
			'post_type'  => $this->pod_name,
			'meta_query' => [
				[ 'key' => 'subtitle', 'value' => 'Beta' ],
			],
			'fields'     => 'ids',
		] );

		$this->assertSame( $expected, array_values( array_map( 'intval', $query->posts ) ) );
	}

	/**
	 * The `pods_` / `_pods_` prefix on a meta_query key should still resolve to the table field.
	 */
	public function test_pods_prefixed_meta_key_resolves_to_table_field() {
		$ids = $this->make_items( [
			[ 'subtitle' => 'Foo' ],
			[ 'subtitle' => 'Bar' ],
		] );

		$query = new WP_Query( [
			'post_type'  => $this->pod_name,
			'meta_query' => [
				[ 'key' => '_pods_subtitle', 'value' => 'Foo' ],
			],
			'fields'     => 'ids',
		] );

		$this->assertSame( [ $ids[0] ], array_values( array_map( 'intval', $query->posts ) ) );
	}

	/**
	 * Mixed query — pod field + unrelated WP meta — pod field is JOINed, foreign key stays on postmeta.
	 */
	public function test_mixed_query_pod_field_and_foreign_meta() {
		$ids = $this->make_items( [
			[ 'subtitle' => 'A', 'foreign' => 'needle' ],
			[ 'subtitle' => 'B', 'foreign' => 'haystack' ],
			[ 'subtitle' => 'C', 'foreign' => 'needle' ],
		] );

		$query = new WP_Query( [
			'post_type'  => $this->pod_name,
			'meta_query' => [
				'relation' => 'AND',
				[ 'key' => 'subtitle', 'value' => 'A' ],
				[ 'key' => 'foreign',  'value' => 'needle' ],
			],
			'fields'     => 'ids',
		] );

		$this->assertSame( [ $ids[0] ], array_values( array_map( 'intval', $query->posts ) ) );
	}

	/**
	 * Relationship field by ID should drive the JOIN through podsrel and return matching rows.
	 */
	public function test_relationship_field_meta_query_uses_podsrel_join() {
		$ids = $this->make_items( [
			[ 'subtitle' => 'Parent One' ],
			[ 'subtitle' => 'Parent Two' ],
		] );

		// Make a child that points back to the first parent.
		$child = wp_insert_post( [
			'post_type'  => $this->pod_name,
			'post_title' => 'Child',
			'post_status' => 'publish',
		] );

		pods( $this->pod_name, $child )->save( 'related', $ids[0] );

		$query = new WP_Query( [
			'post_type'  => $this->pod_name,
			'meta_query' => [
				[ 'key' => 'related', 'value' => (string) $ids[0] ],
			],
			'fields'     => 'ids',
		] );

		$matches = array_values( array_map( 'intval', $query->posts ) );
		sort( $matches );

		$this->assertSame( [ (int) $child ], $matches );
	}

	/**
	 * `suppress_filters = true` should bypass our hooks and return no JOIN rewriting.
	 */
	public function test_suppress_filters_short_circuits_rewrite() {
		$ids = $this->make_items( [
			[ 'subtitle' => 'Solo' ],
		] );

		// Pre-condition: subtitle is only stored in the pod table, so an unfiltered
		// WP_Query should miss it and return 0 rows.
		$query = new WP_Query( [
			'post_type'       => $this->pod_name,
			'meta_query'      => [
				[ 'key' => 'subtitle', 'value' => 'Solo' ],
			],
			'suppress_filters' => true,
			'fields'          => 'ids',
		] );

		$this->assertSame( [], array_values( array_map( 'intval', $query->posts ) ) );
	}

	/**
	 * A meta_query without our keys against a table-based pod CPT must not add any JOINs.
	 */
	public function test_no_table_meta_keys_means_no_join_added() {
		$ids = $this->make_items( [
			[ 'subtitle' => 'X' ],
		] );

		$query = new WP_Query( [
			'post_type'  => $this->pod_name,
			'meta_query' => [
				[ 'key' => 'foreign', 'value' => 'haystack' ],
			],
			'fields'     => 'ids',
		] );

		$this->assertSame( 'DISTINCT', $query->distinct_query, 'DISTINCT should not be set when no table-field joins added' );
		$this->assertSame( [], array_values( array_map( 'intval', $query->posts ) ) );
	}

	/**
	 * Build a batch of pod items and return their post IDs in input order.
	 *
	 * @param array $items Field key/value pairs per item.
	 *
	 * @return int[] Post IDs.
	 */
	protected function make_items( array $items ) {
		$ids = [];

		foreach ( $items as $row ) {
			$id = wp_insert_post( [
				'post_type'  => $this->pod_name,
				'post_title' => 'Item ' . wp_generate_password( 4, false, false ),
				'post_status' => 'publish',
			] );

			$pod = pods( $this->pod_name, $id );

			foreach ( $row as $field => $value ) {
				$pod->save( $field, $value );
			}

			$ids[] = (int) $id;
		}

		return $ids;
	}
}
