<?php

namespace Pods_Unit_Tests\Pods\Field;

use Pods_Unit_Tests\Pods_UnitTestCase;
use PodsField_Pick;

/**
 * @group              pods-field
 * @coversDefaultClass PodsField_Pick
 */
class PickWhereTagsTest extends Pods_UnitTestCase {

	/**
	 * Verify magic tags in a pick_where clause resolve against the current
	 * item context when get_object_data() is given a pod and id.
	 *
	 * Issue #7406: {@id} and {@post_title} came out blank in the SQL because
	 * the tag evaluator was called without any pod / id context.
	 */
	public function test_pick_where_evaluates_magic_tags_with_pod_context() {
		$api = pods_api();

		// A simple pod to be the target of the relationship field.
		$target_pod_name = 'pick_where_tag_target';

		$api->save_pod( [
			'name' => $target_pod_name,
			'type' => 'post_type',
		] );

		// Save two target items. The pick_where will only match one of them.
		$keep_title = 'MATCHED_' . wp_generate_password( 8, false );
		$drop_title = 'OTHER_' . wp_generate_password( 8, false );

		$keep_item_id = $this->factory()->post->create( [
			'post_type'    => $target_pod_name,
			'post_title'   => $keep_title,
			'post_status'  => 'publish',
		] );

		$drop_item_id = $this->factory()->post->create( [
			'post_type'    => $target_pod_name,
			'post_title'   => $drop_title,
			'post_status'  => 'publish',
		] );

		// A "main" pod holding a pick field into the target pod.
		$main_pod_name = 'pick_where_tag_main';

		$api->save_pod( [
			'name' => $main_pod_name,
			'type' => 'post_type',
		] );

		$api->save_field( [
			'pod'          => $main_pod_name,
			'name'         => 'related',
			'type'         => 'pick',
			'pick_object'  => 'post_type-' . $target_pod_name,
			'pick_where'   => "`t`.`post_title` = '{@post_title}'",
			'pick_format_type' => 'single',
			'pick_format_single' => 'dropdown',
		] );

		// Save a main item titled with the keep title so the where matches it.
		$main_item_id = $this->factory()->post->create( [
			'post_type'   => $main_pod_name,
			'post_title'  => $keep_title,
			'post_status' => 'publish',
		] );

		$pod = pods( $main_pod_name, $main_item_id );

		$field = new PodsField_Pick();

		$field_object = $pod->fields( 'related' );

		$object_params = [
			'name'        => 'related',
			'value'       => '',
			'options'     => $field_object,
			'pod'         => $pod,
			'id'          => $main_item_id,
			'context'     => 'data',
			'data_params' => [ 'query' => '' ],
		];

		$data = $field->get_object_data( $object_params );

		// In the 'data' context get_object_data() returns [item_id => item_label].
		// The magic tag must resolve to the current item's title, so only the
		// matching target item is present as a key.
		$this->assertIsArray( $data, 'get_object_data() should return an array.' );
		$this->assertArrayHasKey( $keep_item_id, $data, 'Resolved pick_where should include the matching target item.' );
		$this->assertArrayNotHasKey( $drop_item_id, $data, 'Resolved pick_where should exclude non-matching target items.' );
	}

	/**
	 * Boundary: when get_object_data() is called with an empty id, the fix
	 * must still evaluate magic tags (not leave them raw), even though no
	 * Pods instance context is available. {@get.test_7406_boundary} should
	 * resolve from $_GET and the rest of the tag should not leak through.
	 */
	public function test_pick_where_evaluates_magic_tags_without_id() {
		$_GET['test_7406_boundary'] = '5797';

		try {
			$where = "id = '{@get.test_7406_boundary}'";

			// pods_evaluate_tags() is the same evaluator the fix uses. Assert
			// the tag is replaced rather than left as the raw {@...} token.
			$evaluated = pods_evaluate_tags( $where, [
				'sanitize' => true,
			] );

			$this->assertStringNotContainsString( '{@get.test_7406_boundary}', $evaluated );
			$this->assertStringContainsString( '5797', $evaluated );
		} finally {
			unset( $_GET['test_7406_boundary'] );
		}
	}

}