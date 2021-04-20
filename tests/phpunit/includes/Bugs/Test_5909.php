<?php

namespace Pods_Unit_tests\Bugs;

/**
 * @package Pods_Unit_Tests
 * @group   pods_acceptance_tests
 * @group   pods-issue-5254
 */
class Bug_5909 extends \Pods_Unit_Tests\Pods_UnitTestCase {

	/*
	 * Tests for Pod options and Pod fiels options to be stored properly.
	 * Origin of this test was because it occured that options where stored sanitized (with slashes).
	 */
	public function test_pod_sanitize_post_request() {

		$where = "post_title LIKE '%test%'";

		$_field_json = '{"label":"Rel","name":"rel","description":"","type":"pick","pick_object":"post_type-post","pick_custom":"","pick_table":"","sister_id":"","required":0,"pick_format_type":"single","pick_format_single":"dropdown","pick_format_multi":"checkbox","pick_display_format_multi":"default","pick_display_format_separator":", ","pick_allow_add_new":"1","pick_taggable":0,"pick_show_icon":"1","pick_show_edit_link":"1","pick_show_view_link":"1","pick_select_text":"","pick_limit":"0","pick_table_id":"","pick_table_index":"","pick_display":"","pick_user_role":[],"pick_where":"'.$where.'","pick_orderby":"","pick_groupby":"","pick_post_status":["publish"],"class":"","default_value":"","default_value_parameter":"","admin_only":0,"restrict_role":0,"restrict_capability":0,"hidden":0,"read_only":0,"roles_allowed":["administrator"],"capability_allowed":"","rest_read":0,"rest_write":0,"rest_pick_response":"array","rest_pick_depth":"2"}';

		$params = array();

		// See PodsAdmin::admin_ajax()
		$params['fields'][0] = (array) @json_decode( $_field_json, true );

		$params['name'] = 'sanitize5909';

		$api = pods_api();

		// Save Pod similar to PodsAdmin.
		$api->save_pod( $params );

		// Fetch the stored data.
		$pod = pods( 'sanitize5909' );

		$this->assertEquals( $where, $pod->fields['rel']['options']['pick_where'] );
	}
}
