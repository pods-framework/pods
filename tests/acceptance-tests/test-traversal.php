<?php
namespace Pods_Unit_Tests;
	use Mockery;
	use Pods;

	require PODS_PLUGIN_DIR . '/components/Migrate-Packages/Migrate-Packages.php';
	require PODS_PLUGIN_DIR . '/classes/Pods.php';
	require PODS_PLUGIN_DIR . '/classes/fields/pick.php';

class Test_Traversal extends Pods_UnitTestCase
{
	public function setUp() {
		// create two pods
		$related_json = '{"meta":{"version":"2.4.4","build":1415197722},"pods":{"4":{"id":4,"name":"related","label":"Relateds","description":"","type":"post_type","storage":"meta","object":"","alias":"","fields":{"related_field":{"id":5,"name":"related_field","label":"Related Field","description":"","help":"","class":"","type":"text","weight":0,"pick_object":"","pick_val":"","sister_id":"","required":"0","text_allow_shortcode":"0","text_allow_html":"0","text_allowed_html_tags":"strong em a ul ol li b i","text_max_length":"255","admin_only":"0","restrict_role":"0","restrict_capability":"0","hidden":"0","read_only":"0","roles_allowed":["administrator"],"unique":"0","text_repeatable":"0"}},"show_in_menu":"1","label_singular":"Related","public":"1","show_ui":"1","supports_title":"1","supports_editor":"1","publicly_queryable":"1","capability_type":"post","capability_type_custom":"master","capability_type_extra":"1","rewrite":"1","rewrite_with_front":"1","rewrite_pages":"1","query_var":"1","can_export":"1","default_status":"draft","menu_position":"0","show_in_nav_menus":"1","show_in_admin_bar":"1"}}}';
		\Pods_Migrate_Packages::import( $related_json, true );

		$master_json = '{"meta":{"version":"2.4.4","build":1415197696},"pods":{"6":{"id":6,"name":"master","label":"Master","description":"","type":"post_type","storage":"meta","object":"","alias":"","fields":{"foo_relation":{"id":7,"name":"foo_relation","label":"Foo Relation","description":"","help":"","class":"","type":"pick","weight":0,"pick_object":"post_type","pick_val":"related","sister_id":"","required":"0","pick_format_type":"single","pick_format_single":"dropdown","pick_format_multi":"checkbox","pick_taggable":"0","pick_limit":"0","pick_allow_html":"0","pick_user_role":[],"admin_only":"0","restrict_role":"0","restrict_capability":"0","hidden":"0","read_only":"0","roles_allowed":["administrator"],"unique":"0","pick_select_text":"","pick_table_id":"","pick_table_index":"","pick_display":"","pick_where":"","pick_orderby":"","pick_groupby":""}},"show_in_menu":"1","label_singular":"Master","public":"1","show_ui":"1","supports_title":"1","supports_editor":"1","publicly_queryable":"1","capability_type":"post","capability_type_custom":"master","capability_type_extra":"1","rewrite":"1","rewrite_with_front":"1","rewrite_pages":"1","query_var":"1","can_export":"1","default_status":"draft","menu_position":"0","show_in_nav_menus":"1","show_in_admin_bar":"1"}}}';
		\Pods_Migrate_Packages::import( $master_json, true );

		// add items
		$related_item_json = '{"19":{"ID":19,"post_author":"1","post_date":"2014-11-05 18:25:03","post_date_gmt":"2014-11-05 18:25:03","post_content":"","post_title":"Related Record","post_excerpt":"","post_status":"publish","comment_status":"closed","ping_status":"closed","post_password":"","post_name":"related-record","to_ping":"","pinged":"","post_modified":"2014-11-05 18:25:03","post_modified_gmt":"2014-11-05 18:25:03","post_content_filtered":"","post_parent":0,"guid":"http:\/\/local.wordpress.dev\/?post_type=related&p=19","menu_order":0,"post_type":"related","post_mime_type":"","comment_count":"0"}}';
		$api = pods_api( 'related' );
		$api->display_errors = false;
		$api->import( json_decode( $related_item_json, true ), true );

		$master_item_json = '{"20":{"related":{"19":{"post_title":"Related Record","post_content":"","post_excerpt":"","post_author":"1","post_date":"2014-11-05 18:25:03","post_date_gmt":"2014-11-05 18:25:03","post_status":"publish","comment_status":"closed","ping_status":"closed","post_password":"","post_name":"related-record","to_ping":"","pinged":"","post_modified":"2014-11-05 18:25:03","post_modified_gmt":"2014-11-05 18:25:03","post_content_filtered":"","post_parent":0,"guid":"http:\/\/local.wordpress.dev\/?post_type=related&p=19","menu_order":0,"post_type":"related","post_mime_type":"","comment_count":"0"}},"post_title":"Master Record","post_content":"","post_excerpt":"","post_author":"1","post_date":"2014-11-05 18:25:36","post_date_gmt":"2014-11-05 18:25:36","post_status":"publish","comment_status":"closed","ping_status":"closed","post_password":"","post_name":"master-record","to_ping":"","pinged":"","post_modified":"2014-11-05 18:25:36","post_modified_gmt":"2014-11-05 18:25:36","post_content_filtered":"","post_parent":[],"guid":"http:\/\/local.wordpress.dev\/?post_type=master&p=20","menu_order":0,"post_type":"master","post_mime_type":"","comment_count":"0","ID":20}}';
		$api->pod = 'master';
		$api->import( json_decode( $master_item_json, true ), true );
	}

	public function test_set_up()
	{
		$pod_master = pods( 'master', 18 );
		$pod_related = pods( 'related', 8 );

		$this->assertTrue( is_object( $pod_master ) );
		$this->assertTrue( is_object( $pod_related ) );

		$this->assertInstanceOf( 'Pods', $pod_master );
		$this->assertInstanceOf( 'Pods', $pod_related );
	}

	/**
	 * Meta CPT -> related Meta CPT -> post_title
	 */
	public function test_cptm_relcptm_post_title()
	{
		$params = array();
		$params['where'] = 'related.post_title = "Related Record"';
		$pod = pods( 'master', $params, true );

		$this->assertEquals( $pod->field('post_title'), "Master Record" );
		$this->assertEquals( $pod->display( 'post_title' ), 'Master Record' );
		//$this->assertEqual( current( $pod->field( 'rel_m_cpt.post_title' ) ), 'Related Record' );
		//$this->assertEqual( $pod->display( 'rel_m_cpt.post_title' ), 'Related Record' );
		//$this->assertEqual( get_post_meta( $pod->id(), 'rel_m_cpt.post_title', true ), 'Related Record' );
	}
}
