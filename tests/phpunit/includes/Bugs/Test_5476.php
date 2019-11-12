<?php

namespace Pods_Unit_tests\Bugs;

/**
 * @package Pods_Unit_Tests
 * @group   pods_acceptance_tests
 * @group   pods-issue-5476
 */
class Bug_5476 extends \Pods_Unit_Tests\Pods_UnitTestCase {

	public function test_import_page() {
		$import = '{"meta":{"version":"2.7.16-a-1","build":1569005171},"pods":{"320":{"id":320,"name":"page","label":"Page","description":"","type":"post_type","storage":"meta","object":"page","alias":"","fields":{"banana":{"id":321,"name":"banana","label":"Test","description":"","help":"","class":"","type":"wysiwyg","weight":0,"pick_object":"","pick_val":"","sister_id":"","required":"0","unique":"0","wysiwyg_editor":"tinymce","wysiwyg_media_buttons":"1","wysiwyg_oembed":"0","wysiwyg_wptexturize":"1","wysiwyg_convert_chars":"1","wysiwyg_wpautop":"1","wysiwyg_allow_shortcode":"0","pick_post_status":["publish"],"admin_only":"0","restrict_role":"0","restrict_capability":"0","hidden":"0","read_only":"0","roles_allowed":["administrator"],"rest_read":"0","rest_write":"0","rest_pick_response":"array","rest_pick_depth":"2","wysiwyg_repeatable":"0","wysiwyg_allowed_html_tags":""}},"show_in_menu":"1","pfat_enable":"0","pfat_run_outside_loop":"0","pfat_append_single":"append","pfat_filter_single":"the_content","pfat_append_archive":"append","pfat_filter_archive":"the_content","rest_enable":"0","rest_base":"page","read_all":"0","write_all":"0","built_in_taxonomies_story":"0","built_in_taxonomies_storytax":"0","built_in_taxonomies_publication":"0","built_in_taxonomies_publications":"0"}}}';

		$components = \PodsInit::$components;
		$components->load();
		$active = $components->activate_component('migrate-packages');
		$this->assertTrue( $active );

		// The WordPress test framework rolls back during tearDown, so we can safely delete the data without affecting other tests
		pods_api()->delete_pod( 'page' );

		$migrate = $components->components['migrate-packages']['object'];
		$result = $migrate->import( $import );
		$this->assertNotFalse( $result );

		$pod = pods( 'page' );
		$id = $pod->add( array( 'banana' => 'This failure is brought to you by migrate-packages' ) );
		$pod->fetch( $id );
		$value = $pod->display('banana');
		$this->assertEquals( 'This failure is brought to you by migrate-packages', $value );

	}
}
