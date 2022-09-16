<?php

namespace Pods_Unit_Tests\Pods;

use Pods;
use Pods_Unit_Tests\Pods_UnitTestCase;
use Spatie\Snapshots\MatchesSnapshots;
use tad\WP\Snapshots\WPHtmlOutputDriver;

/**
 * Class MappingTest
 *
 * @todo    Add image_fields and avatar tests.
 *
 * @package Pods_Unit_Tests
 *
 * @group   pods-mapping
 * @group   pods-field
 */
class MappingTest extends Pods_UnitTestCase {
	use MatchesSnapshots;

	public static $db_reset_teardown = false;

	private $pod_id;
	private $pod_name = 'mappingtest';
	private $pod_label = 'Mapping Test';
	private $pod_type = 'post_type';
	private $pod_storage = 'meta';
	private $field_id;
	private $field_name = 'test_field';
	private $field_label = 'Test field';
	private $field_type = 'text';
	private $field_id2;
	private $field_name2 = 'another_test_field';
	private $field_label2 = 'Another test field';
	private $field_type2 = 'text';
	private $item_id;

	/**
	 * The pods system under test
	 *
	 * @var   \Pods
	 */
	private $pod;

	public function setUp() : void {
		parent::setUp();

		$params = [
			'name'    => $this->pod_name,
			'label'   => $this->pod_label,
			'type'    => $this->pod_type,
			'storage' => $this->pod_storage,
		];

		$api = pods_api();

		// Save Pod similar to PodsAdmin.
		$this->pod_id = $api->save_pod( $params );

		$field_params = [
			'pod_id' => $this->pod_id,
			'name'   => $this->field_name,
			'label'  => $this->field_label,
			'type'   => $this->field_type,
		];

		$this->field_id = $api->save_field( $field_params );

		$field_params = [
			'pod_id' => $this->pod_id,
			'name'   => $this->field_name2,
			'label'  => $this->field_label2,
			'type'   => $this->field_type2,
		];

		$this->field_id2 = $api->save_field( $field_params );

		$pod = pods( $this->pod_name );

		$this->item_id = $pod->add( [
			'post_title'       => 'Test title',
			'post_content'     => 'Test content',
			'post_status'      => 'publish',
			$this->field_name  => 'Test value',
			$this->field_name2 => 'Another test value',
		] );
	}

	public function tearDown() : void {
		$api = pods_api();

		// Delete all posts.
		$api->reset_pod( [ 'name' => $this->pod_name ] );

		// Delete the pod config.
		$api->delete_pod( [ 'name' => $this->pod_name ] );

		parent::tearDown();
	}

	/**
	 * @covers \Pods\Data\Map_Field_Values::map_value
	 */
	public function test_default() {
		$pod = pods( $this->pod_name, $this->item_id );

		$this->assertEquals( [], $pod->field( 'any_map' ) );

		$this->assertNull( pods_data_field( $this->pod_name, 'any_map' ) );
		$this->assertNull( pods_data_field( $pod, 'any_map' ) );
		$this->assertNull( pods_data_field( null, 'any_map' ) );
	}

	/**
	 * @covers \Pods\Data\Map_Field_Values::custom
	 */
	public function test_custom() {
		$callback = static function( $value, $field ) {
			if ( 'any_map' !== $field ) {
				return $value;
			}

			return 'my-custom-value';
		};

		add_filter( 'pods_data_map_field_values_custom', $callback, 10, 2 );

		$pod = pods( $this->pod_name, $this->item_id );

		$this->assertEquals( 'my-custom-value', $pod->field( 'any_map' ) );

		$this->assertEquals( 'my-custom-value', pods_data_field( $this->pod_name, 'any_map' ) );
		$this->assertEquals( 'my-custom-value', pods_data_field( $pod, 'any_map' ) );
		$this->assertEquals( 'my-custom-value', pods_data_field( null, 'any_map' ) );

		remove_filter( 'pods_data_map_field_values_custom', $callback );
	}

	/**
	 * @covers \Pods\Data\Map_Field_Values::pod_info
	 */
	public function test_pod_info() {
		$pod = pods( $this->pod_name, $this->item_id );

		$this->assertEquals( $this->pod_name, $pod->field( '_pod' ) );

		$this->assertEquals( $this->pod_id, $pod->field( '_pod.id' ) );
		$this->assertEquals( $this->pod_id, $pod->field( '_pod.ID' ) );
		$this->assertEquals( $this->pod_name, $pod->field( '_pod.name' ) );
		$this->assertEquals( $this->pod_label, $pod->field( '_pod.label' ) );
		$this->assertEquals( $this->pod_type, $pod->field( '_pod.type' ) );
		$this->assertEquals( $this->pod_storage, $pod->field( '_pod.storage' ) );

		$this->assertEquals( '', $pod->field( '_pod.any_non_option' ) );

		$this->assertEquals( $this->pod_label, pods_data_field( $this->pod_name, '_pod.label' ) );
		$this->assertEquals( $this->pod_label, pods_data_field( $pod, '_pod.label' ) );
		$this->assertEquals( $this->pod_label, pods_data_field( $pod, '_pod.label' ) );
	}

	/**
	 * @covers \Pods\Data\Map_Field_Values::field_info
	 */
	public function test_field_info() {
		$pod = pods( $this->pod_name, $this->item_id );

		$this->assertEquals( $this->field_label, $pod->field( '_field.' . $this->field_name ) );

		$this->assertEquals( $this->field_id, $pod->field( '_field.' . $this->field_name . '.id' ) );
		$this->assertEquals( $this->field_id, $pod->field( '_field.' . $this->field_name . '.ID' ) );
		$this->assertEquals( $this->field_name, $pod->field( '_field.' . $this->field_name . '.name' ) );
		$this->assertEquals( $this->field_label, $pod->field( '_field.' . $this->field_name . '.label' ) );
		$this->assertEquals( $this->field_type, $pod->field( '_field.' . $this->field_name . '.type' ) );

		$this->assertEquals( '', $pod->field( '_field.' . $this->field_name . '.any_non_option' ) );

		$this->assertEquals( $this->field_label, pods_data_field( $this->pod_name, '_field.' . $this->field_name . '.label' ) );
		$this->assertEquals( $this->field_label, pods_data_field( $pod, '_field.' . $this->field_name . '.label' ) );
		$this->assertNull( pods_data_field( null, '_field.' . $this->field_name . '.label' ) );
	}

	/**
	 * @covers \Pods\Data\Map_Field_Values::display_fields
	 */
	public function test_display_fields_as_all_fields() {
		$pod = pods( $this->pod_name, $this->item_id );

		$current_wp_url = getenv( 'WP_URL' );
		$snapshot_url   = 'https://wp.localhost';

		$driver = new WPHtmlOutputDriver( $current_wp_url, $snapshot_url );

		$all_fields_ul = $pod->display( '_all_fields' );

		$this->assertContains( '</ul>', $all_fields_ul );
		$this->assertMatchesSnapshot( $all_fields_ul, $driver );

		$all_fields_ul2 = $pod->display( '_all_fields.ul' );

		$this->assertContains( '</ul>', $all_fields_ul2 );
		$this->assertMatchesSnapshot( $all_fields_ul2, $driver );

		$all_fields_div = $pod->display( '_all_fields.div' );

		$this->assertContains( '</div>', $all_fields_div );
		$this->assertMatchesSnapshot( $all_fields_div, $driver );

		$all_fields_p = $pod->display( '_all_fields.p' );

		$this->assertContains( '</p>', $all_fields_p );
		$this->assertMatchesSnapshot( $all_fields_p, $driver );

		$all_fields_table = $pod->display( '_all_fields.table' );

		$this->assertContains( '</table>', $all_fields_table );
		$this->assertMatchesSnapshot( $all_fields_table, $driver );

		$all_fields_ol = $pod->display( '_all_fields.ol' );

		$this->assertContains( '</ol>', $all_fields_ol );
		$this->assertMatchesSnapshot( $all_fields_ol, $driver );

		$all_fields_dl = $pod->display( '_all_fields.dl' );

		$this->assertContains( '</dl>', $all_fields_dl );
		$this->assertMatchesSnapshot( $all_fields_dl, $driver );

		$this->assertNull( pods_data_field( $this->pod_name, '_all_fields' ) );
		$this->assertContains( 'Another test value', pods_data_field( $pod, '_all_fields' ) );
		$this->assertContains( 'Test title', pods_data_field( $pod, '_all_fields.ul.' . $this->field_name ) ); // Ignored field name.
		$this->assertNull( pods_data_field( null, '_all_fields' ) );
	}

	/**
	 * @covers \Pods\Data\Map_Field_Values::display_fields
	 */
	public function test_display_fields() {
		$pod = pods( $this->pod_name, $this->item_id );

		$current_wp_url = getenv( 'WP_URL' );
		$snapshot_url   = 'https://wp.localhost';

		$driver = new WPHtmlOutputDriver( $current_wp_url, $snapshot_url );

		$display_fields_ul = $pod->display( '_display_fields' );

		$this->assertContains( '</ul>', $display_fields_ul );
		$this->assertMatchesSnapshot( $display_fields_ul, $driver );

		$display_fields_ul_with_no_type_and_fields_are_ignored = $pod->display( '_display_fields.post_title' );

		$this->assertEquals( $display_fields_ul, $display_fields_ul_with_no_type_and_fields_are_ignored );

		$display_fields_ul2 = $pod->display( '_display_fields.ul' );

		$this->assertContains( '</ul>', $display_fields_ul2 );
		$this->assertMatchesSnapshot( $display_fields_ul2, $driver );

		$display_fields_ul2_with_included = $pod->display( '_display_fields.ul.' . $this->field_name );

		$this->assertContains( '</ul>', $display_fields_ul2_with_included );
		$this->assertContains( 'Test value', $display_fields_ul2_with_included );
		$this->assertNotContains( 'Test title', $display_fields_ul2_with_included );
		$this->assertNotContains( 'Another test value', $display_fields_ul2_with_included );

		$display_fields_ul2_with_excluded = $pod->display( '_display_fields.ul.exclude=' . $this->field_name );

		$this->assertContains( '</ul>', $display_fields_ul2_with_excluded );
		$this->assertNotContains( 'Test value', $display_fields_ul2_with_excluded );
		$this->assertContains( 'Test title', $display_fields_ul2_with_excluded );
		$this->assertContains( 'Another test value', $display_fields_ul2_with_excluded );

		$display_fields_div = $pod->display( '_display_fields.div' );

		$this->assertContains( '</div>', $display_fields_div );
		$this->assertMatchesSnapshot( $display_fields_div, $driver );

		$display_fields_div_with_included = $pod->display( '_display_fields.div.' . $this->field_name );

		$this->assertContains( '</div>', $display_fields_div_with_included );
		$this->assertContains( 'Test value', $display_fields_div_with_included );
		$this->assertNotContains( 'Test title', $display_fields_div_with_included );
		$this->assertNotContains( 'Another test value', $display_fields_div_with_included );

		$display_fields_div_with_excluded = $pod->display( '_display_fields.div.exclude=' . $this->field_name );

		$this->assertContains( '</div>', $display_fields_div_with_excluded );
		$this->assertNotContains( 'Test value', $display_fields_div_with_excluded );
		$this->assertContains( 'Test title', $display_fields_div_with_excluded );
		$this->assertContains( 'Another test value', $display_fields_div_with_excluded );

		$display_fields_p = $pod->display( '_display_fields.p' );

		$this->assertContains( '</p>', $display_fields_p );
		$this->assertMatchesSnapshot( $display_fields_p, $driver );

		$display_fields_p_with_included = $pod->display( '_display_fields.p.' . $this->field_name );

		$this->assertContains( '</p>', $display_fields_p_with_included );
		$this->assertContains( 'Test value', $display_fields_p_with_included );
		$this->assertNotContains( 'Test title', $display_fields_p_with_included );
		$this->assertNotContains( 'Another test value', $display_fields_p_with_included );

		$display_fields_p_with_excluded = $pod->display( '_display_fields.p.exclude=' . $this->field_name );

		$this->assertContains( '</p>', $display_fields_p_with_excluded );
		$this->assertNotContains( 'Test value', $display_fields_p_with_excluded );
		$this->assertContains( 'Test title', $display_fields_p_with_excluded );
		$this->assertContains( 'Another test value', $display_fields_p_with_excluded );

		$display_fields_table = $pod->display( '_display_fields.table' );

		$this->assertContains( '</table>', $display_fields_table );
		$this->assertMatchesSnapshot( $display_fields_table, $driver );

		$display_fields_table_with_included = $pod->display( '_display_fields.table.' . $this->field_name );

		$this->assertContains( '</table>', $display_fields_table_with_included );
		$this->assertContains( 'Test value', $display_fields_table_with_included );
		$this->assertNotContains( 'Test title', $display_fields_table_with_included );
		$this->assertNotContains( 'Another test value', $display_fields_table_with_included );

		$display_fields_table_with_excluded = $pod->display( '_display_fields.table.exclude=' . $this->field_name );

		$this->assertContains( '</table>', $display_fields_table_with_excluded );
		$this->assertNotContains( 'Test value', $display_fields_table_with_excluded );
		$this->assertContains( 'Test title', $display_fields_table_with_excluded );
		$this->assertContains( 'Another test value', $display_fields_table_with_excluded );

		$display_fields_ol = $pod->display( '_display_fields.ol' );

		$this->assertContains( '</ol>', $display_fields_ol );
		$this->assertMatchesSnapshot( $display_fields_ol, $driver );

		$display_fields_ol_with_included = $pod->display( '_display_fields.ol.' . $this->field_name );

		$this->assertContains( '</ol>', $display_fields_ol_with_included );
		$this->assertContains( 'Test value', $display_fields_ol_with_included );
		$this->assertNotContains( 'Test title', $display_fields_ol_with_included );
		$this->assertNotContains( 'Another test value', $display_fields_ol_with_included );

		$display_fields_ol_with_excluded = $pod->display( '_display_fields.ol.exclude=' . $this->field_name );

		$this->assertContains( '</ol>', $display_fields_ol_with_excluded );
		$this->assertNotContains( 'Test value', $display_fields_ol_with_excluded );
		$this->assertContains( 'Test title', $display_fields_ol_with_excluded );
		$this->assertContains( 'Another test value', $display_fields_ol_with_excluded );

		$display_fields_dl = $pod->display( '_display_fields.dl' );

		$this->assertContains( '</dl>', $display_fields_dl );
		$this->assertMatchesSnapshot( $display_fields_dl, $driver );

		$display_fields_dl_with_included = $pod->display( '_display_fields.dl.' . $this->field_name );

		$this->assertContains( '</dl>', $display_fields_dl_with_included );
		$this->assertContains( 'Test value', $display_fields_dl_with_included );
		$this->assertNotContains( 'Test title', $display_fields_dl_with_included );
		$this->assertNotContains( 'Another test value', $display_fields_dl_with_included );

		$display_fields_dl_with_excluded = $pod->display( '_display_fields.dl.exclude=' . $this->field_name );

		$this->assertContains( '</dl>', $display_fields_dl_with_excluded );
		$this->assertNotContains( 'Test value', $display_fields_dl_with_excluded );
		$this->assertContains( 'Test title', $display_fields_dl_with_excluded );
		$this->assertContains( 'Another test value', $display_fields_dl_with_excluded );

		$this->assertNull( pods_data_field( $this->pod_name, '_display_fields' ) );
		$this->assertContains( 'Another test value', pods_data_field( $pod, '_display_fields' ) );
		$this->assertContains( 'Test title', pods_data_field( $pod, '_display_fields.ul.post_title' ) );
		$this->assertNull( pods_data_field( null, '_display_fields' ) );
	}

	/**
	 * @covers \Pods\Data\Map_Field_Values::context_info
	 */
	public function test_context_info() {
		$pod = pods( $this->pod_name, $this->item_id );

		// Test $_GET.
		$_GET['some-value'] = '123';

		$this->assertEquals( $_GET['some-value'], $pod->field( '_context.get.some-value' ) );

		// Test $_POST.
		$_POST['some-value2'] = '456';

		$this->assertEquals( $_POST['some-value2'], $pod->field( '_context.post.some-value2' ) );

		// Test removal of HTML in value.
		$_POST['some-value3'] = '<a href="https://gosomewhere.com">Go somewhere</a>';

		$this->assertEquals( 'Go somewhere', $pod->field( '_context.post.some-value3' ) );

		// Test HTML in raw value.
		$_POST['some-value3'] = '<a href="https://gosomewhere.com">Go somewhere</a>';

		$this->assertEquals( $_POST['some-value3'], $pod->field( '_context.post.some-value3.raw' ) );

		// Test prefix.
		global $wpdb;

		$this->assertEquals( $wpdb->prefix, $pod->field( '_context.prefix' ) );

		$this->assertEquals( $_POST['some-value3'], pods_data_field( $this->pod_name, '_context.post.some-value3.raw' ) );
		$this->assertEquals( $_POST['some-value3'], pods_data_field( $pod, '_context.post.some-value3.raw' ) );
		$this->assertEquals( $_POST['some-value3'], pods_data_field( null, '_context.post.some-value3.raw' ) );
	}

	/**
	 * @covers \Pods\Data\Map_Field_Values::calculation
	 */
	public function test_calculation() {
		$pod = pods( $this->pod_name );

		// Add 4 more tests so we have 5 total.
		$pod->add( [
			'post_title'   => 'Test title 2',
			'post_name'    => 'test-title-2',
			'post_content' => 'Test content',
			'post_status'  => 'publish',
		] );

		$pod->add( [
			'post_title'   => 'Test title 3',
			'post_name'    => 'test-title-3',
			'post_content' => 'Test content',
			'post_status'  => 'publish',
		] );

		$pod->add( [
			'post_title'   => 'Test title 4',
			'post_name'    => 'test-title-4',
			'post_content' => 'Test content',
			'post_status'  => 'publish',
		] );

		$pod->add( [
			'post_title'   => 'Test title 5',
			'post_name'    => 'test-title-5',
			'post_content' => 'Test content',
			'post_status'  => 'publish',
		] );

		$pod->find( [
			// Limit to 2 and expect 3 pages.
			'limit' => 2,
		] );

		// Defaults prior to loop.
		$zebra    = true;
		$position = 0;

		$this->assertEquals( 1, $pod->zebra() );
		$this->assertEquals( $position, $pod->position() );
		$this->assertEquals( 2, $pod->total() );
		$this->assertEquals( 5, $pod->total_found() );
		$this->assertEquals( 3, $pod->total_pages() );

		while ( $pod->fetch() ) {
			$position ++;
			$zebra = ! $zebra;

			$this->assertEquals( (int) $zebra, $pod->field( '_zebra' ) );
			$this->assertEquals( $position, $pod->field( '_position' ) );
			$this->assertEquals( 2, $pod->field( '_total' ) );
			$this->assertEquals( 5, $pod->field( '_total_found' ) );
			$this->assertEquals( 3, $pod->field( '_total_pages' ) );

			$this->assertEquals( 0, pods_data_field( $this->pod_name, '_position' ) );
			$this->assertEquals( $position, pods_data_field( $pod, '_position' ) );
			$this->assertNull( pods_data_field( null, '_position' ) );
		}
	}

	/**
	 * @covers \Pods\Data\Map_Field_Values::image_fields
	 */
	public function test_image_fields_post_thumbnail() {
		$image_path = codecept_data_dir( 'images/zoltar.jpg' );

		$attachment_id = $this->factory()->attachment->create_upload_object( $image_path, $this->item_id );

		set_post_thumbnail( $this->item_id, $attachment_id );

		$pod = pods( $this->pod_name, $this->item_id );

		$this->assertStringStartsWith( '<img width="150" height="150" src="', $pod->field( 'post_thumbnail' ) );
		$this->assertStringStartsWith( '<img width="200" height="300" src="', $pod->field( 'post_thumbnail.medium' ) );
		$this->assertStringStartsWith( '<img width="123" height="123" src="', $pod->field( 'post_thumbnail.123x123' ) );

		$this->assertContains( '-150x150.jpg', $pod->field( 'post_thumbnail_url' ) );
		$this->assertContains( '-200x300.jpg', $pod->field( 'post_thumbnail_url.medium' ) );
		$this->assertContains( '-123x123.jpg', $pod->field( 'post_thumbnail_url.123x123' ) );

		$this->assertContains( '-150x150.jpg', $pod->field( 'post_thumbnail_src' ) );
		$this->assertContains( '-200x300.jpg', $pod->field( 'post_thumbnail_src.medium' ) );
		$this->assertContains( '-123x123.jpg', $pod->field( 'post_thumbnail_src.123x123' ) );

		$this->assertNull( pods_data_field( $this->pod_name, 'post_thumbnail_src.123x123' ) );
		$this->assertContains( '-123x123.jpg', pods_data_field( $pod, 'post_thumbnail_src.123x123' ) );
		$this->assertNull( pods_data_field( null, 'post_thumbnail_src.123x123' ) );
	}

	/**
	 * @covers \Pods\Data\Map_Field_Values::image_fields
	 */
	public function test_image_fields_image_attachment() {
		$image_path = codecept_data_dir( 'images/zoltar.jpg' );

		$attachment_id = $this->factory()->attachment->create_upload_object( $image_path, $this->item_id );

		set_post_thumbnail( $this->item_id, $attachment_id );

		$pod = pods( $this->pod_name, $this->item_id );

		$this->assertEquals( [], $pod->field( 'image_attachment' ) );

		$this->assertStringStartsWith( '<img width="150" height="150" src="', $pod->field( 'image_attachment.' . $attachment_id ) );
		$this->assertStringStartsWith( '<img width="200" height="300" src="', $pod->field( 'image_attachment.' . $attachment_id . '.medium' ) );
		$this->assertStringStartsWith( '<img width="123" height="123" src="', $pod->field( 'image_attachment.' . $attachment_id . '.123x123' ) );

		$this->assertContains( '-150x150.jpg', $pod->field( 'image_attachment_url.' . $attachment_id ) );
		$this->assertContains( '-200x300.jpg', $pod->field( 'image_attachment_url.' . $attachment_id . '.medium' ) );
		$this->assertContains( '-123x123.jpg', $pod->field( 'image_attachment_url.' . $attachment_id . '.123x123' ) );

		$this->assertContains( '-150x150.jpg', $pod->field( 'image_attachment_src.' . $attachment_id ) );
		$this->assertContains( '-200x300.jpg', $pod->field( 'image_attachment_src.' . $attachment_id . '.medium' ) );
		$this->assertContains( '-123x123.jpg', $pod->field( 'image_attachment_src.' . $attachment_id . '.123x123' ) );

		$this->assertContains( '-123x123.jpg', $pod->field( 'image_attachment_src.' . $attachment_id . '.123x123' ) );
		$this->assertContains( '-123x123.jpg', $pod->field( 'image_attachment_src.' . $attachment_id . '.123x123' ) );
		$this->assertContains( '-123x123.jpg', $pod->field( 'image_attachment_src.' . $attachment_id . '.123x123' ) );

		$this->assertNull( pods_data_field( $this->pod_name, 'image_attachment_src.' . $attachment_id . '.123x123' ) );
		$this->assertContains( '-123x123.jpg', pods_data_field( $pod, 'image_attachment_src.' . $attachment_id . '.123x123' ) );
		$this->assertNull( pods_data_field( null, 'image_attachment_src.' . $attachment_id . '.123x123' ) );
	}

}
