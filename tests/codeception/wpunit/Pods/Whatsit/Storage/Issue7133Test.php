<?php

namespace Pods_Unit_Tests\Pods\Whatsit\Storage;

use Pods_Unit_Tests\Pods_WhatsitTestCase;
use Pods\Whatsit\Storage\Post_Type;

/**
 * Regression tests for pods-framework/pods issue #7133.
 *
 * - Bug 1: pods.json import with old_name values blocks field import.
 * - Bug 2: file-storage pod count_fields() returns 1 per pod instead of N.
 *
 * @group  pods-whatsit
 * @group  pods-whatsit-storage
 * @group  pods-issue-7133
 * @covers Post_Type::find
 * @covers Collection::find
 */
class Issue7133Test extends Pods_WhatsitTestCase {

	public function tearDown(): void {
		$this->pods_object_storage->fallback_mode( true );

		parent::tearDown();
	}

	/**
	 * Bug 2 regression: count of file-storage objects should return all
	 * matching objects, not just the WP_Query found_posts + 1 from the
	 * collection fallback.
	 *
	 * Before the fix, the count-mode limit=1 leaked into the collection
	 * fallback find, slicing the result to 1 per parent.
	 *
	 * @covers Post_Type::find
	 * @covers Collection::find
	 */
	public function test_count_fields_returns_all_collection_matches() {
		// Register a second field on the same pod/group.
		// Pods_WhatsitTestCase defaults object_storage_type to 'post_type'. Leaving it
		// there would let WP_Query find this field directly, so the collection fallback
		// -- the path the limit=1 leak actually broke -- would never be exercised and the
		// test would pass with the fix reverted. Register it in collection storage so it
		// is only reachable through the fallback.
		$field_args = array(
			'object_type'         => 'field',
			'object_storage_type' => 'collection',
			'name'                => 'test-field-2',
			'label'               => 'Test field 2',
			'description'         => 'Testing field 2',
			'parent'              => $this->pods_object_pod->get_id(),
			'group'               => $this->pods_object_group->get_id(),
			'type'                => 'text',
		);

		$second_field = $this->setup_pods_object( $field_args, 'field' );

		$this->pods_object_storage->fallback_mode( true );

		$args = array(
			'object_type' => 'field',
			'parent'      => $this->pods_object_pod->get_id(),
			'parent_name' => $this->pods_object_pod->get_name(),
			'refresh'     => true,
			'count'       => true,
		);

		$count = $this->pods_object_storage->find( $args );

		$this->assertEquals(
			2,
			count( $count ),
			'count_fields() should return both fields (was capped at 1 by the limit=1 leak before the fix).'
		);

		unset( $second_field );
	}

	/**
	 * Bug 2 regression: the limit must still constrain non-count finds.
	 *
	 * The fix skips the limit only in count mode. This asserts the two modes really do
	 * differ, which is the actual invariant -- the previous version of this test passed
	 * an explicit limit (bypassing the default it claimed to check) and then asserted a
	 * bound the fixture could never exceed, so it held regardless of the fix.
	 *
	 * @covers Collection::find
	 */
	public function test_limit_constrains_non_count_find_but_not_count_find() {
		$field_args = array(
			'object_type'         => 'field',
			'object_storage_type' => 'collection',
			'name'                => 'test-field-limit',
			'label'               => 'Test field limit',
			'description'         => 'Testing field limit',
			'parent'              => $this->pods_object_pod->get_id(),
			'group'               => $this->pods_object_group->get_id(),
			'type'                => 'text',
		);

		$extra_field = $this->setup_pods_object( $field_args, 'field' );

		$this->pods_object_storage->fallback_mode( true );

		$base_args = array(
			'object_type' => 'field',
			'parent'      => $this->pods_object_pod->get_id(),
			'parent_name' => $this->pods_object_pod->get_name(),
			'refresh'     => true,
		);

		// Non-count mode honours the limit.
		$limited = $this->pods_object_storage->find( array_merge( $base_args, array( 'limit' => 1 ) ) );

		$this->assertLessThanOrEqual(
			1,
			count( $limited ),
			'A non-count find must still respect an explicit limit.'
		);

		// Count mode ignores it, which is the whole point of the fix.
		$counted = $this->pods_object_storage->find( array_merge( $base_args, array( 'count' => true ) ) );

		$this->assertGreaterThan(
			1,
			count( $counted ),
			'Count mode must not be capped by the internal limit=1 it sets for itself.'
		);

		unset( $extra_field );
	}

	/**
	 * Bug 2 regression: empty result with count=true returns 0, not 1
	 * (the old behavior returned at least 1 from the collection fallback).
	 *
	 * @covers Post_Type::find
	 * @covers Collection::find
	 */
	public function test_count_empty_parent_returns_zero() {
		$this->pods_object_storage->fallback_mode( true );

		$args = array(
			'object_type' => 'field',
			'parent'      => 'pod/does-not-exist',
			'refresh'     => true,
			'count'       => true,
		);

		$count = $this->pods_object_storage->find( $args );

		$this->assertSame(
			array(),
			$count,
			'Empty result on count should be an empty array (post_type count + 0 collection matches).'
		);
	}
}
