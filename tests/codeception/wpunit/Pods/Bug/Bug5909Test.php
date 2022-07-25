<?php

namespace Pods_Unit_Tests\Bugs;

/**
 * @package Pods_Unit_Tests
 * @group   pods_acceptance_tests
 * @group   pods-issue-5254
 */
class Bug_5909Test extends \Pods_Unit_Tests\Pods_UnitTestCase {

	/*
	 * Tests for Pod options and Pod fiels options to be stored properly.
	 * Origin of this test was because it occured that options where stored sanitized (with slashes).
	 */
	public function test_pod_sanitize_post_request() {

		$where = "post_title LIKE '%test%'";

		$_field_json = file_get_contents( codecept_data_dir( 'packages/test-pod-sanitize-post-request.json' ) );
		$_field_json = json_decode( $_field_json, true );

		$_field_json['pick_where'] = $where;

		$params = [
			'name'   => 'sanitize5909',
			'fields' => [
				$_field_json,
			],
		];

		$api = pods_api();

		// Save Pod similar to PodsAdmin.
		$api->save_pod( $params );

		// Fetch the stored data.
		$pod = pods( $params['name'] );

		$rel_field = $pod->fields( $_field_json['name'] );

		$this->assertNotEmpty( $rel_field );
		$this->assertEquals( $where, $rel_field['pick_where'] );
		$this->assertEquals( $where, $rel_field['options']['pick_where'] );
	}
}
