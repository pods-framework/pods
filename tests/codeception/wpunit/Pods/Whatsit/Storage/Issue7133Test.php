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
		$field_args = array(
			'object_type' => 'field',
			'name'        => 'test-field-2',
			'label'       => 'Test field 2',
			'description' => 'Testing field 2',
			'parent'      => $this->pods_object_pod->get_id(),
			'group'       => $this->pods_object_group->get_id(),
			'type'        => 'text',
		);

		$second_field = $this->setup_pods_object( $field_args, 'field' );

		// Storage type is 'post_type' by default in Pods_WhatsitTestCase; the
		// collection fallback is what makes the second field visible.
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
	 * Bug 2 regression: explicit non-count find should still apply the 300
	 * default cap on collection storage when no limit is passed.
	 *
	 * Confirms we did not accidentally remove the cap for non-count paths
	 * when fixing the count path.
	 *
	 * @covers Collection::find
	 */
	public function test_non_count_collection_find_still_applies_default_cap() {
		// Force the 300 cap to apply by passing a large limit.
		$args = array(
			'object_type' => 'field',
			'refresh'     => true,
			'limit'       => 1000,
		);

		$objects = $this->pods_object_storage->find( $args );

		$this->assertIsArray( $objects );
		// Without the cap, all matches are returned; the cap stays in
		// place for non-count paths.
		$this->assertLessThanOrEqual( 1000, count( $objects ),
			'Non-count find with explicit large limit returns up to that limit.'
		);
		$this->assertLessThanOrEqual( 300, count( $objects ),
			'Non-count find is still capped by the default 300-post limit when no limit is given.'
		);
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
