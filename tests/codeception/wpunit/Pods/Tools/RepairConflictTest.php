<?php

namespace Pods_Unit_Tests\Pods\Tools;

use Pods\Tools\Repair;
use Pods_Unit_Tests\Pods_UnitTestCase;

/**
 * Tests that the Repair tool stops instead of repairing the wrong things when another plugin
 * is altering the queries it relies on.
 *
 * @group  pods-tools
 * @group  pods-tools-repair
 * @covers \Pods\Tools\Repair
 */
class RepairConflictTest extends Pods_UnitTestCase {

	/**
	 * @var \Pods\Whatsit\Pod
	 */
	protected $pod;

	/**
	 * @var int
	 */
	protected $group_id;

	/**
	 * @var int[]
	 */
	protected $field_ids = [];

	/**
	 * The filters registered by a test, to remove during tear down.
	 *
	 * @var array[]
	 */
	protected $registered_filters = [];

	public function setUp(): void {
		parent::setUp();

		$api = pods_api();

		$pod_id = $api->save_pod( [
			'name'    => 'repair_conflict_pod',
			'label'   => 'Repair Conflict Pod',
			'type'    => 'post_type',
			'storage' => 'meta',
		] );

		$this->pod = $api->load_pod( [ 'id' => $pod_id ] );

		$this->group_id = $api->save_group( [
			'pod'   => $this->pod,
			'name'  => 'details',
			'label' => 'Details',
		] );

		foreach ( [ 'first_field', 'second_field' ] as $field_name ) {
			$this->field_ids[ $field_name ] = $api->save_field( [
				'pod'      => $this->pod,
				'group_id' => $this->group_id,
				'name'     => $field_name,
				'label'    => $field_name,
				'type'     => 'text',
			] );
		}

		$this->flush_pods_caches();

		$this->pod = $api->load_pod( [ 'id' => $pod_id ] );
	}

	public function tearDown(): void {
		foreach ( $this->registered_filters as $filter ) {
			remove_filter( $filter[0], $filter[1], $filter[2] );
		}

		$this->registered_filters = [];

		parent::tearDown();
	}

	/**
	 * Register a filter that simulates another plugin and remove it again during tear down.
	 *
	 * @param string   $hook     The hook name.
	 * @param callable $callback The callback.
	 * @param int      $priority The priority.
	 * @param int      $accepted The number of accepted arguments.
	 */
	protected function add_conflicting_filter( $hook, $callback, $priority = 10, $accepted = 1 ) {
		add_filter( $hook, $callback, $priority, $accepted );

		$this->registered_filters[] = [ $hook, $callback, $priority ];
	}

	/**
	 * Flush every layer of caching so the next lookup really runs the query again.
	 */
	protected function flush_pods_caches() {
		pods_api()->cache_flush_pods();
		pods_static_cache_clear();

		self::flush_cache();
	}

	/**
	 * Get the `type` meta of a field straight from the database.
	 *
	 * @param int $field_id The field ID.
	 *
	 * @return string The field type stored in the database.
	 */
	protected function get_field_type_from_db( $field_id ) {
		global $wpdb;

		return (string) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT `meta_value` FROM `{$wpdb->postmeta}` WHERE `post_id` = %d AND `meta_key` = 'type'",
				$field_id
			)
		);
	}

	/**
	 * The tool should run normally when no other plugin is interfering.
	 */
	public function test_repair_runs_without_conflicts() {
		$tool = pods_container( Repair::class );

		$results = $tool->repair_groups_and_fields_for_pod( $this->pod, 'full' );

		$this->assertArrayNotHasKey( 'conflicts', $results, 'A conflict was reported without another plugin interfering.' );
		$this->assertFalse( $tool->has_conflicts() );
	}

	/**
	 * A plugin that hides groups from queries would make the tool create a duplicate group.
	 */
	public function test_repair_stops_when_a_plugin_hides_groups() {
		$this->flush_pods_caches();

		// Simulate a plugin that filters out all `_pods_group` results.
		$this->add_conflicting_filter( 'posts_pre_query', static function ( $posts, $query ) {
			if ( '_pods_group' === $query->get( 'post_type' ) ) {
				return [];
			}

			return $posts;
		}, 10, 2 );

		$tool    = pods_container( Repair::class );
		$results = $tool->repair_groups_and_fields_for_pod( $this->pod, 'full' );

		$this->assertTrue( $tool->has_conflicts(), 'The tool did not detect the hidden groups.' );
		$this->assertNotEmpty( $results['conflicts'] );
		$this->assertStringContainsString( 'another plugin is conflicting', $results['message_html'] );

		// No second group should have been created for the Pod.
		global $wpdb;

		$total_groups = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM `{$wpdb->posts}` WHERE `post_type` = '_pods_group' AND `post_parent` = %d",
				$this->pod->get_id()
			)
		);

		$this->assertSame( 1, $total_groups, 'The tool created a duplicate group despite the conflict.' );
	}

	/**
	 * A plugin that breaks meta queries would make the tool reset every field to the "text" type.
	 */
	public function test_repair_stops_when_a_plugin_breaks_meta_queries() {
		$api = pods_api();

		// Give one field a type that really is invalid so there is something legitimate to repair.
		update_post_meta( $this->field_ids['first_field'], 'type', 'not_a_real_field_type' );

		$this->flush_pods_caches();

		// Simulate a plugin that drops meta queries, which makes every field look broken.
		$this->add_conflicting_filter( 'pods_whatsit_storage_post_type_find_args', static function ( $post_args ) {
			unset( $post_args['meta_query'] );

			return $post_args;
		} );

		$tool = pods_container( Repair::class );
		$pod  = $api->load_pod( [ 'id' => $this->pod->get_id() ] );

		$results = $tool->repair_groups_and_fields_for_pod( $pod, 'full' );

		$this->assertTrue( $tool->has_conflicts(), 'The tool did not detect the broken meta query.' );
		$this->assertNotEmpty( $results['conflicts'] );

		// The valid field must not have been rewritten.
		$this->assertSame( 'text', $this->get_field_type_from_db( $this->field_ids['second_field'] ) );

		// The invalid field must be left alone too, the tool stops rather than doing a partial repair.
		$this->assertSame( 'not_a_real_field_type', $this->get_field_type_from_db( $this->field_ids['first_field'] ) );
	}

	/**
	 * Preview mode reports the conflict as well, so the conflict is found before a real run.
	 */
	public function test_preview_mode_reports_conflicts() {
		$this->flush_pods_caches();

		$this->add_conflicting_filter( 'posts_pre_query', static function ( $posts, $query ) {
			if ( '_pods_field' === $query->get( 'post_type' ) ) {
				return [];
			}

			return $posts;
		}, 10, 2 );

		$tool    = pods_container( Repair::class );
		$results = $tool->repair_groups_and_fields_for_pod( $this->pod, 'preview' );

		$this->assertTrue( $tool->has_conflicts(), 'Preview mode did not detect the hidden fields.' );
		$this->assertNotEmpty( $results['conflicts'] );
	}

}
