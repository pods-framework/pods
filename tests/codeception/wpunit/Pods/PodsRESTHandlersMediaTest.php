<?php

namespace Pods_Unit_Tests\Pods;

use PodsRESTFields;
use PodsRESTHandlers;
use Pods_Unit_Tests\Pods_UnitTestCase;
use WP_Post;
use WP_REST_Request;

/**
 * Regression test for Pods Framework issue #6420.
 *
 * A Pods custom field added to the Media (attachment) pod could not be
 * updated via the WP REST API. Reading worked, but the save path failed
 * silently, leaving the field empty and the read returning [false]. The
 * same field worked on a custom post type.
 *
 * Root cause was a lookup key mismatch in PodsRESTHandlers::save_handler():
 * register_rest_field( 'attachment', ... ) stores the field under the
 * 'attachment' key in $wp_rest_additional_fields, but save_handler was
 * keying by the pod name 'media' instead. The fix (commit 6a62c35b0) keys
 * the lookup by the WP post type ($type).
 *
 * This test directly invokes save_handler() on a media pod and asserts
 * the field is persisted. It would fail if the lookup regressed to
 * $pod_name, locking the fix.
 *
 * @group  pods-rest
 * @covers PodsRESTHandlers::save_handler
 */
class PodsRESTHandlersMediaTest extends Pods_UnitTestCase {

	/**
	 * @var int
	 */
	protected $media_pod_id = 0;

	/**
	 * @var int
	 */
	protected $media_group_id = 0;

	/**
	 * @var int
	 */
	protected $attachment_id = 0;

	/**
	 * @var \Pods\Whatsit\Pod
	 */
	protected $media_pod;

	public function setUp(): void {
		parent::setUp();

		$api = pods_api();

		// Extend the Media (attachment) pod. The pod name is 'media' and
		// the underlying WP post type is 'attachment'.
		$this->media_pod_id = $api->save_pod( [
			'type'                   => 'media',
			'storage'                => 'meta',
			'public'                 => 1,
			'supports_custom_fields' => 1,
			'rest_enable'            => 1,
			'name'                   => 'media',
		] );

		$this->media_group_id = $api->save_group( [
			'pod_id' => $this->media_pod_id,
			'name'   => 'media-checksum',
		] );

		// Field name mirrors the reporter's "checksum" example.
		$api->save_field( [
			'pod_id'     => $this->media_pod_id,
			'group_id'   => $this->media_group_id,
			'name'       => 'media_checksum',
			'label'      => 'Checksum',
			'type'       => 'text',
			'rest_read'  => 1,
			'rest_write' => 1,
		] );

		$this->media_pod = $api->load_pod( [
			'id' => $this->media_pod_id,
		] );

		// Create a real attachment so save_handler can resolve the WP_Post.
		$this->attachment_id = wp_insert_attachment( [
			'post_type'      => 'attachment',
			'post_mime_type' => 'image/png',
			'post_title'     => 'Test attachment',
			'post_status'    => 'inherit',
		] );
	}

	public function tearDown(): void {
		$this->media_pod_id   = 0;
		$this->media_group_id = 0;
		$this->attachment_id  = 0;
		$this->media_pod      = null;

		// Reset the current user between tests.
		global $current_user;
		$current_user = null;
		wp_set_current_user( 0 );

		parent::tearDown();
	}

	/**
	 * It should register the media pod field under the 'attachment' key
	 * in $wp_rest_additional_fields, not the pod name 'media'.
	 *
	 * @test
	 */
	public function should_register_field_under_attachment_key() {
		global $wp_rest_additional_fields;

		$this->register_media_pod_rest_field();

		$this->assertArrayHasKey( 'attachment', $wp_rest_additional_fields );
		$this->assertArrayHasKey( 'media_checksum', $wp_rest_additional_fields['attachment'] );

		// Must NOT be registered under the pod name 'media' (that would
		// indicate the regression from issue #6420).
		$this->assertArrayNotHasKey( 'media', $wp_rest_additional_fields );

		$field_args = $wp_rest_additional_fields['attachment']['media_checksum'];

		$this->assertTrue( ! empty( $field_args['pods_update'] ) );
		$this->assertNotEmpty( $field_args['get_callback'] );
	}

	/**
	 * It should persist a media pod field when save_handler() is invoked.
	 *
	 * Reproduces issue #6420: prior to the fix, save_handler keyed
	 * $wp_rest_additional_fields by $pod_name ('media') and never matched
	 * the field registered under 'attachment', so the value was never
	 * saved.
	 *
	 * @test
	 */
	public function should_save_field_on_media_pod_via_save_handler() {
		$this->register_media_pod_rest_field();

		// Build a request that includes the field value, the way a real
		// REST POST would after WP_REST_Server has parsed the body.
		$request = new WP_REST_Request( 'POST', '/wp/v2/media/' . $this->attachment_id );
		$request->set_param( 'media_checksum', '42' );

		$post = get_post( $this->attachment_id );

		PodsRESTHandlers::save_handler( $post, $request, false );

		$this->assertEquals( '42', get_post_meta( $this->attachment_id, 'media_checksum', true ) );
	}

	/**
	 * It should read the saved value back through get_handler(), matching
	 * the read path the reporter saw work correctly.
	 *
	 * @test
	 */
	public function should_read_field_via_get_handler_after_save() {
		$this->register_media_pod_rest_field();

		$request = new WP_REST_Request( 'GET', '/wp/v2/media/' . $this->attachment_id );
		$request->set_param( 'media_checksum', '42' );

		$post = get_post( $this->attachment_id );

		PodsRESTHandlers::save_handler( $post, $request, false );

		$value = PodsRESTHandlers::get_handler(
			$post->to_array(),
			'media_checksum',
			$request,
			'attachment'
		);

		$this->assertSame( '42', $value );
	}

	/**
	 * Reset any stale rest_api_init registrations from prior tests in
	 * this class, then instantiate PodsRESTFields for the media pod and
	 * fire rest_api_init so the field is registered against the
	 * 'attachment' object type.
	 *
	 * Each test calls this in isolation so a prior instance of
	 * PodsRESTFields (which caches a reference to its pod) cannot leak
	 * into the current test.
	 */
	private function register_media_pod_rest_field(): void {
		global $wp_rest_additional_fields;

		// Only the $wp_rest_additional_fields reset is needed to isolate
		// rest_api_init registrations across tests in this class; the
		// WPTestCase base rolls back $wp_filter between cases.
		$wp_rest_additional_fields = [];

		// Instantiating PodsRESTFields hooks add_fields() onto rest_api_init.
		new PodsRESTFields( $this->media_pod );

		do_action( 'rest_api_init' );
	}
}
