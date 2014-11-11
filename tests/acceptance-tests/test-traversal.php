<?php
namespace Pods_Unit_Tests;
	use Mockery;
	use Pods;

	require PODS_PLUGIN_DIR . '/components/Migrate-Packages/Migrate-Packages.php';
	require PODS_PLUGIN_DIR . '/classes/Pods.php';
	require PODS_PLUGIN_DIR . '/classes/fields/pick.php';

class Test_Traversal extends Pods_UnitTestCase
{
	private $ids = array();

	public function setUp() {
		// create two pods
		$related_json = '{"meta":{"version":"2.4.4","build":1415197722},"pods":{"pods_test_related":{"name":"pods_test_related","label":"Test Related","description":"","type":"post_type","storage":"meta","object":"","alias":"","fields":{"related_field":{"name":"related_field","label":"Related Field","description":"","help":"","class":"","type":"text","weight":0,"pick_object":"","pick_val":"","sister_id":"","required":"0","text_allow_shortcode":"0","text_allow_html":"0","text_allowed_html_tags":"strong em a ul ol li b i","text_max_length":"255","admin_only":"0","restrict_role":"0","restrict_capability":"0","hidden":"0","read_only":"0","roles_allowed":["administrator"],"unique":"0","text_repeatable":"0"}},"show_in_menu":"1","label_singular":"Related","public":"1","show_ui":"1","supports_title":"1","supports_editor":"1","publicly_queryable":"1","capability_type":"post","capability_type_custom":"master","capability_type_extra":"1","rewrite":"1","rewrite_with_front":"1","rewrite_pages":"1","query_var":"1","can_export":"1","default_status":"draft","menu_position":"0","show_in_nav_menus":"1","show_in_admin_bar":"1"}}}';
		\Pods_Migrate_Packages::import( $related_json, true );

		$master_json = '{"meta":{"version":"2.4.4","build":1415197696},"pods":{"pods_test_master":{"name":"pods_test_master","label":"Test Master","description":"","type":"post_type","storage":"meta","object":"","alias":"","fields":{"foo_relation":{"name":"foo_relation","label":"Foo Relation","description":"","help":"","class":"","type":"pick","weight":0,"pick_object":"post_type","pick_val":"pods_test_related","sister_id":"","required":"0","pick_format_type":"single","pick_format_single":"dropdown","pick_format_multi":"checkbox","pick_taggable":"0","pick_limit":"0","pick_allow_html":"0","pick_user_role":[],"admin_only":"0","restrict_role":"0","restrict_capability":"0","hidden":"0","read_only":"0","roles_allowed":["administrator"],"unique":"0","pick_select_text":"","pick_table_id":"","pick_table_index":"","pick_display":"","pick_where":"","pick_orderby":"","pick_groupby":""}},"show_in_menu":"1","label_singular":"Master","public":"1","show_ui":"1","supports_title":"1","supports_editor":"1","publicly_queryable":"1","capability_type":"post","capability_type_custom":"master","capability_type_extra":"1","rewrite":"1","rewrite_with_front":"1","rewrite_pages":"1","query_var":"1","can_export":"1","default_status":"draft","menu_position":"0","show_in_nav_menus":"1","show_in_admin_bar":"1"}}}';
		\Pods_Migrate_Packages::import( $master_json, true );

		// add items
		$related = pods( 'pods_test_related' );

		$related_item = array(
			'post_title' => 'Related Record',
			'post_author' => 1,
			'post_status' => 'publish',

		    'related_field' => 'Related field value'
		);

		$this->ids[ 'pods_test_related' ] = array(
			$related->add( $related_item )
		);

		$master = pods( 'pods_test_master' );

		$master_item = array(
			'post_title' => 'Master Record',
			'post_author' => 1,
			'post_status' => 'publish',

		    'foo_relation' => $this->ids[ 'pods_test_related' ][ 0 ]
		);

		$this->ids[ 'pods_test_master' ] = array(
			$master->add( $master_item )
		);
	}

	public function test_set_up()
	{
		$pod_master = pods( 'pods_test_master', $this->ids[ 'pods_test_master' ][ 0 ] );
		$pod_related = pods( 'pods_test_related', $this->ids[ 'pods_test_related' ][ 0 ] );

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
		$params[ 'limit' ] = 1;
		$params[ 'where' ] = 'foo_relation.post_title = "Related Record"';

		$pod = pods( 'pods_test_master', $params, true );

		$this->assertEquals( 1, $pod->total() );
		$this->assertEquals( 1, $pod->total_found() );

		$this->assertNotEmpty( $pod->fetch() );

		$this->assertEquals( $this->ids[ 'pods_test_master' ][ 0 ], $pod->id() );
		$this->assertEquals( $this->ids[ 'pods_test_master' ][ 0 ], $pod->field( 'ID' ) );
		$this->assertEquals( 'Master Record', $pod->field( 'post_title' ) );
		$this->assertEquals( 'Master Record', $pod->display( 'post_title' ) );

		$this->assertEquals( 'Related Record', $pod->field( 'foo_relation.post_title' ) );
		$this->assertEquals( 'Related Record', $pod->display( 'foo_relation.post_title' ) );
		$this->assertEquals( 'Related Record', get_post_meta( $pod->id(), 'foo_relation.post_title', true ) );

		$this->assertEquals( $this->ids[ 'pods_test_related' ][ 0 ], $pod->field( 'foo_relation.ID' ) );
		$this->assertEquals( $this->ids[ 'pods_test_related' ][ 0 ], $pod->display( 'foo_relation.ID' ) );
		$this->assertEquals( $this->ids[ 'pods_test_related' ][ 0 ], get_post_meta( $pod->id(), 'foo_relation.ID', true ) );

		$this->assertEquals( 'Related field value', $pod->field( 'foo_relation.related_field' ) );
		$this->assertEquals( 'Related field value', $pod->display( 'foo_relation.related_field' ) );
		$this->assertEquals( 'Related field value', get_post_meta( $pod->id(), 'foo_relation.related_field', true ) );
	}
}
