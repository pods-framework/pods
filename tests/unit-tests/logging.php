<?php
namespace Pods_Unit_Tests;

/**
 * @group pods_logging
 */
class Tests_Logging extends Pods_UnitTestCase {
	protected $_object = null;

	public function setUp() {
		parent::setUp();

		$this->_object = new \Pods_Logging;
		$this->_object->register_post_type();
		$this->_object->register_taxonomy();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_post_type() {
		global $wp_post_types;
		$this->assertArrayHasKey( 'pods_log', $wp_post_types );
	}

	public function test_post_type_labels() {
		global $wp_post_types;
		$this->assertEquals( 'Logs', $wp_post_types['pods_log']->labels->name );
		$this->assertEquals( 'Logs', $wp_post_types['pods_log']->labels->singular_name );
		$this->assertEquals( 'Add New', $wp_post_types['pods_log']->labels->add_new );
		$this->assertEquals( 'Add New Post', $wp_post_types['pods_log']->labels->add_new_item );
		$this->assertEquals( 'Edit Post', $wp_post_types['pods_log']->labels->edit_item );
		$this->assertEquals( 'View Post', $wp_post_types['pods_log']->labels->view_item );
		$this->assertEquals( 'Search Posts', $wp_post_types['pods_log']->labels->search_items );
		$this->assertEquals( 'No posts found.', $wp_post_types['pods_log']->labels->not_found );
		$this->assertEquals( 'No posts found in Trash.', $wp_post_types['pods_log']->labels->not_found_in_trash );
		$this->assertEquals( 'Logs', $wp_post_types['pods_log']->labels->all_items );
		$this->assertEquals( 'Logs', $wp_post_types['pods_log']->labels->menu_name );
		$this->assertEquals( 'Logs', $wp_post_types['pods_log']->labels->name_admin_bar );
		$this->assertEquals( '', $wp_post_types['pods_log']->publicly_queryable );
		$this->assertEquals( 'post', $wp_post_types['pods_log']->capability_type );
		$this->assertEquals( 1, $wp_post_types['pods_log']->map_meta_cap );
		$this->assertEquals( '', $wp_post_types['pods_log']->rewrite );
		$this->assertEquals( '', $wp_post_types['pods_log']->has_archive );
		$this->assertEquals( 'Logs', $wp_post_types['pods_log']->label );
	}

	public function test_taxonomy_exist() {
		global $wp_taxonomies;
		$this->assertArrayHasKey( 'pods_log_type', $wp_taxonomies );
	}

	public function test_log_types() {
		$types = $this->_object->log_types();
		$this->assertEquals( 'sale', $types[0] );
		$this->assertEquals( 'file_download', $types[1] );
		$this->assertEquals( 'gateway_error', $types[2] );
		$this->assertEquals( 'api_request', $types[3] );
	}

	public function test_valid_log() {
		$this->assertTrue( $this->_object->valid_type( 'file_download' ) );
	}

	public function test_fake_log() {
		$this->assertFalse( $this->_object->valid_type( 'foo' ) );
	}

	public function test_add() {
		$this->assertNotNull( $this->_object->add() );
		$this->assertInternalType( 'integer', $this->_object->add() );
	}

	public function test_insert_log() {
		$this->assertNotNull( $this->_object->insert_log( array( 'log_type' => 'sale' ) ) );
		$this->assertInternalType( 'integer', $this->_object->insert_log( array( 'log_type' => 'sale' ) ) );
	}

	public function test_get_logs() {
		$log_id = $this->_object->insert_log( array( 'log_type' => 'sale', 'post_parent' => 1, 'post_title' => 'Test Log', 'post_content' => 'This is a test log inserted from PHPUnit' ) );
		$out = $this->_object->get_logs( 1, 'sale' );

		$this->assertObjectHasAttribute( 'ID', $out[0] );
		$this->assertObjectHasAttribute( 'post_author', $out[0] );
		$this->assertObjectHasAttribute( 'post_date', $out[0] );
		$this->assertObjectHasAttribute( 'post_date_gmt', $out[0] );
		$this->assertObjectHasAttribute( 'post_content', $out[0] );
		$this->assertObjectHasAttribute( 'post_title', $out[0] );
		$this->assertObjectHasAttribute( 'post_excerpt', $out[0] );
		$this->assertObjectHasAttribute( 'post_status', $out[0] );
		$this->assertObjectHasAttribute( 'comment_status', $out[0] );
		$this->assertObjectHasAttribute( 'ping_status', $out[0] );
		$this->assertObjectHasAttribute( 'post_password', $out[0] );
		$this->assertObjectHasAttribute( 'post_name', $out[0] );
		$this->assertObjectHasAttribute( 'to_ping', $out[0] );
		$this->assertObjectHasAttribute( 'pinged', $out[0] );
		$this->assertObjectHasAttribute( 'post_modified', $out[0] );
		$this->assertObjectHasAttribute( 'post_modified_gmt', $out[0] );
		$this->assertObjectHasAttribute( 'post_content_filtered', $out[0] );
		$this->assertObjectHasAttribute( 'post_parent', $out[0] );
		$this->assertObjectHasAttribute( 'guid', $out[0] );
		$this->assertObjectHasAttribute( 'menu_order', $out[0] );
		$this->assertObjectHasAttribute( 'post_type', $out[0] );
		$this->assertObjectHasAttribute( 'post_mime_type', $out[0] );
		$this->assertObjectHasAttribute( 'comment_count', $out[0] );
		$this->assertObjectHasAttribute( 'filter', $out[0] );

		$this->assertEquals( 'This is a test log inserted from PHPUnit', $out[0]->post_content );
		$this->assertEquals( 'Test Log', $out[0]->post_title );
		$this->assertEquals( 'pods_log', $out[0]->post_type );
	}

	public function test_get_connected_logs() {
		$log_id = $this->_object->insert_log( array( 'log_type' => 'sale', 'post_parent' => 1, 'post_title' => 'Test Log', 'post_content' => 'This is a test log inserted from PHPUnit' ) );
		$out = $this->_object->get_connected_logs( array( 'post_parent' => 1, 'log_type' => 'sale' ) );

		$this->assertObjectHasAttribute( 'ID', $out[0] );
		$this->assertObjectHasAttribute( 'post_author', $out[0] );
		$this->assertObjectHasAttribute( 'post_date', $out[0] );
		$this->assertObjectHasAttribute( 'post_date_gmt', $out[0] );
		$this->assertObjectHasAttribute( 'post_content', $out[0] );
		$this->assertObjectHasAttribute( 'post_title', $out[0] );
		$this->assertObjectHasAttribute( 'post_excerpt', $out[0] );
		$this->assertObjectHasAttribute( 'post_status', $out[0] );
		$this->assertObjectHasAttribute( 'comment_status', $out[0] );
		$this->assertObjectHasAttribute( 'ping_status', $out[0] );
		$this->assertObjectHasAttribute( 'post_password', $out[0] );
		$this->assertObjectHasAttribute( 'post_name', $out[0] );
		$this->assertObjectHasAttribute( 'to_ping', $out[0] );
		$this->assertObjectHasAttribute( 'pinged', $out[0] );
		$this->assertObjectHasAttribute( 'post_modified', $out[0] );
		$this->assertObjectHasAttribute( 'post_modified_gmt', $out[0] );
		$this->assertObjectHasAttribute( 'post_content_filtered', $out[0] );
		$this->assertObjectHasAttribute( 'post_parent', $out[0] );
		$this->assertObjectHasAttribute( 'guid', $out[0] );
		$this->assertObjectHasAttribute( 'menu_order', $out[0] );
		$this->assertObjectHasAttribute( 'post_type', $out[0] );
		$this->assertObjectHasAttribute( 'post_mime_type', $out[0] );
		$this->assertObjectHasAttribute( 'comment_count', $out[0] );
		$this->assertObjectHasAttribute( 'filter', $out[0] );

		$this->assertEquals( 'This is a test log inserted from PHPUnit', $out[0]->post_content );
		$this->assertEquals( 'Test Log', $out[0]->post_title );
		$this->assertEquals( 'pods_log', $out[0]->post_type );
	}

	public function test_get_log_count() {
		$this->_object->insert_log( array( 'log_type' => 'sale', 'post_parent' => 1, 'post_title' => 'Test Log', 'post_content' => 'This is a test log inserted from PHPUnit' ) );
		$this->_object->insert_log( array( 'log_type' => 'sale', 'post_parent' => 1, 'post_title' => 'Test Log', 'post_content' => 'This is a test log inserted from PHPUnit' ) );
		$this->_object->insert_log( array( 'log_type' => 'sale', 'post_parent' => 1, 'post_title' => 'Test Log', 'post_content' => 'This is a test log inserted from PHPUnit' ) );
		$this->_object->insert_log( array( 'log_type' => 'sale', 'post_parent' => 1, 'post_title' => 'Test Log', 'post_content' => 'This is a test log inserted from PHPUnit' ) );
		$this->_object->insert_log( array( 'log_type' => 'sale', 'post_parent' => 1, 'post_title' => 'Test Log', 'post_content' => 'This is a test log inserted from PHPUnit' ) );

		$this->assertInternalType( 'integer', $this->_object->get_log_count( 1, 'sale' ) );
		$this->assertEquals( 5, $this->_object->get_log_count( 1, 'sale' ) );
	}

	public function test_delete_logs() {
		$this->_object->insert_log( array( 'log_type' => 'sale', 'post_parent' => 1, 'post_title' => 'Test Log', 'post_content' => 'This is a test log inserted from PHPUnit' ) );
		$this->_object->insert_log( array( 'log_type' => 'sale', 'post_parent' => 1, 'post_title' => 'Test Log', 'post_content' => 'This is a test log inserted from PHPUnit' ) );
		$this->_object->insert_log( array( 'log_type' => 'sale', 'post_parent' => 1, 'post_title' => 'Test Log', 'post_content' => 'This is a test log inserted from PHPUnit' ) );
		$this->_object->insert_log( array( 'log_type' => 'sale', 'post_parent' => 1, 'post_title' => 'Test Log', 'post_content' => 'This is a test log inserted from PHPUnit' ) );
		$this->_object->insert_log( array( 'log_type' => 'sale', 'post_parent' => 1, 'post_title' => 'Test Log', 'post_content' => 'This is a test log inserted from PHPUnit' ) );

		$this->assertNull( $this->_object->delete_logs( 1 ) );
	}
}