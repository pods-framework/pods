<?php

namespace Pods_Unit_Tests\Pods;

use Pods;
use Pods\Whatsit\Pod;
use Pods_Unit_Tests\Pods_UnitTestCase;
use PodsRESTHandlers;

/**
 * @group  pods-rest
 * @covers PodsRESTHandlers
 */
class PodsRESTHandlersTest extends Pods_UnitTestCase {

	/**
	 * @var string
	 */
	protected $pod_name = 'test_pods_rest';

	/**
	 * @var int
	 */
	protected $pod_id = 0;

	/**
	 * @var int
	 */
	protected $pod_item_id = 0;

	/**
	 * @var Pod
	 */
	protected $pod;

	/**
	 * @var string
	 */
	protected $full_read_pod_name = 'test_pods_rest_read';

	/**
	 * @var int
	 */
	protected $full_read_pod_id = 0;

	/**
	 * @var int
	 */
	protected $full_read_pod_item_id = 0;

	/**
	 * @var Pod
	 */
	protected $full_read_pod;

	/**
	 * @var string
	 */
	protected $full_write_pod_name = 'test_pods_rest_write';

	/**
	 * @var int
	 */
	protected $full_write_pod_id = 0;

	/**
	 * @var int
	 */
	protected $full_write_pod_item_id = 0;

	/**
	 * @var Pod
	 */
	protected $full_write_pod;

	public function setUp(): void {
		parent::setUp();

		$api = pods_api();

		/////////////////////////
		// Basic pod
		/////////////////////////

		$this->pod_id = $api->save_pod( [
			'type'                   => 'post_type',
			'storage'                => 'meta',
			'public'                 => 1,
			'supports_custom_fields' => 1,
			'rest_enable'            => 1,
			'name'                   => $this->pod_name,
		] );

		$this->save_test_fields( $this->pod_id );
		$this->pod_item_id = $this->save_test_item( $this->pod_name );

		$this->pod = $api->load_pod( [
			'id' => $this->pod_id,
		] );

		/////////////////////////
		// Full REST read pod
		/////////////////////////

		$this->full_read_pod_id = $api->save_pod( [
			'type'                   => 'post_type',
			'storage'                => 'meta',
			'public'                 => 1,
			'supports_custom_fields' => 1,
			'rest_enable'            => 1,
			'name'                   => $this->full_read_pod_name,
		] );

		$this->save_test_fields( $this->full_read_pod_id );
		$this->full_read_pod_item_id = $this->save_test_item( $this->full_read_pod_name );

		$this->full_read_pod = $api->load_pod( [
			'id' => $this->full_read_pod_id,
		] );

		/////////////////////////
		// Full REST write pod
		/////////////////////////

		$this->full_write_pod_id = $api->save_pod( [
			'type'                   => 'post_type',
			'storage'                => 'meta',
			'public'                 => 1,
			'supports_custom_fields' => 1,
			'rest_enable'            => 1,
			'name'                   => $this->full_write_pod_name,
		] );

		$this->save_test_fields( $this->full_write_pod_id );
		$this->full_write_pod_item_id = $this->save_test_item( $this->full_write_pod_name );

		$this->full_write_pod = $api->load_pod( [
			'id' => $this->full_write_pod_id,
		] );
	}

	protected function save_test_fields( $pod_id ) {
		$api = pods_api();

		$api->save_field( [
			'pod_id' => $pod_id,
			'name'   => 'non_rest_number',
			'type'   => 'number',
		] );

		$api->save_field( [
			'pod_id'    => $pod_id,
			'name'      => 'read_rest_number',
			'type'      => 'number',
			'rest_read' => 1,
		] );

		$api->save_field( [
			'pod_id'           => $pod_id,
			'name'             => 'read_access_rest_number',
			'type'             => 'number',
			'rest_read'        => 1,
			'rest_read_access' => 1,
		] );

		$api->save_field( [
			'pod_id'     => $pod_id,
			'name'       => 'write_rest_number',
			'type'       => 'number',
			'rest_write' => 1,
		] );
	}

	protected function save_test_item( $pod_name ) {
		$api = pods_api();

		$api->save_pod_item( [
			'pod'                     => $pod_name,
			'post_title'              => 'Test item for ' . $pod_name,
			'post_status'             => 'publish',
			'non_rest_number'         => 111,
			'read_rest_number'        => 222,
			'read_access_rest_number' => 333,
			'write_rest_number'       => 444,
		] );
	}

	public function tearDown(): void {
		$this->pod_item_id            = null;
		$this->pod_id                 = null;
		$this->pod                    = null;
		$this->full_read_pod_item_id  = null;
		$this->full_read_pod_id       = null;
		$this->full_read_pod          = null;
		$this->full_write_pod_item_id = null;
		$this->full_write_pod_id      = null;
		$this->full_write_pod         = null;

		// Reset current user.
		global $current_user;

		$current_user = null;

		wp_set_current_user( 0 );

		parent::tearDown();
	}

	public function test_get_pods_object() {
		$pods_object = PodsRESTHandlers::get_pods_object( $this->pod_name, $this->pod_item_id );

		$this->assertInstanceOf( Pods::class, $pods_object );
		$this->assertEquals( $this->pod_name, $pods_object->pod );
		$this->assertEquals( $this->pod_item_id, $pods_object->id );
	}

	public function test_get_pods_object_with_non_pod() {
		$pods_object = PodsRESTHandlers::get_pods_object( 'non_existent_pod', $this->pod_item_id );

		$this->assertFalse( $pods_object );
	}

	public function test_get_pods_object_with_non_item() {
		$pods_object = PodsRESTHandlers::get_pods_object( $this->pod_name, 0 );

		$this->assertFalse( $pods_object );
	}

	public function test_get_handler() {
		$value = PodsRESTHandlers::get_handler( $object, $field_name, $request, $object_type );

		$this->assertFalse( $value );
	}

	public function test_register() {
		global $wp_rest_additional_fields;

		$sut = $this->sut();

		$sut->register( $this->pod->get_field( 'read_rest_number' ) );

		$this->assertTrue( isset( $wp_rest_additional_fields[ $this->pod_name ]['read_rest_number'] ) );
	}

	public function test_register_without_rest() {
		global $wp_rest_additional_fields;

		$sut = $this->sut();

		$sut->register( $this->pod->get_field( 'non_rest_number' ) );

		$this->assertFalse( isset( $wp_rest_additional_fields[ $this->pod_name ]['non_rest_number'] ) );
	}

	public function test_field_allowed_to_extend_read() {
		$this->assertTrue(
			PodsRESTFields::field_allowed_to_extend(
				$this->pod->get_field( 'read_rest_number' ),
				$this->pod,
				'read'
			)
		);
	}

	public function test_field_allowed_to_extend_read_requires_access_while_logged_out() {
		$this->assertFalse(
			PodsRESTFields::field_allowed_to_extend(
				$this->pod->get_field( 'read_access_rest_number' ),
				$this->pod,
				'read'
			)
		);
	}

	public function test_field_allowed_to_extend_read_requires_access_with_logged_in_user() {
		wp_set_current_user( 1 );

		$this->assertTrue(
			PodsRESTFields::field_allowed_to_extend(
				$this->pod->get_field( 'read_access_rest_number' ),
				$this->pod,
				'read'
			)
		);
	}

	public function test_field_allowed_to_extend_read_with_field_without_rest() {
		$this->assertFalse(
			PodsRESTFields::field_allowed_to_extend(
				$this->pod->get_field( 'non_rest_number' ),
				$this->pod,
				'read'
			)
		);

		$this->assertFalse(
			PodsRESTFields::field_allowed_to_extend(
				$this->pod->get_field( 'write_rest_number' ),
				$this->pod,
				'read'
			)
		);
	}

	public function test_field_allowed_to_extend_write() {
		$this->assertTrue(
			PodsRESTFields::field_allowed_to_extend(
				$this->pod->get_field( 'write_rest_number' ),
				$this->pod,
				'write'
			)
		);
	}

	public function test_field_allowed_to_extend_write_with_field_without_rest() {
		$this->assertFalse(
			PodsRESTFields::field_allowed_to_extend(
				$this->pod->get_field( 'non_rest_number' ),
				$this->pod,
				'write'
			)
		);

		$this->assertFalse(
			PodsRESTFields::field_allowed_to_extend(
				$this->pod->get_field( 'read_rest_number' ),
				$this->pod,
				'write'
			)
		);
	}

	public function test_field_allowed_to_extend_with_full_rest_read() {
		$this->assertTrue(
			PodsRESTFields::field_allowed_to_extend(
				$this->full_read_pod->get_field( 'read_rest_number' ),
				$this->full_read_pod,
				'read'
			)
		);
	}

	public function test_field_allowed_to_extend_with_full_rest_read_with_field_without_rest() {
		$this->assertFalse(
			PodsRESTFields::field_allowed_to_extend(
				$this->full_read_pod->get_field( 'non_rest_number' ),
				$this->full_read_pod,
				'read'
			)
		);

		$this->assertFalse(
			PodsRESTFields::field_allowed_to_extend(
				$this->full_read_pod->get_field( 'write_rest_number' ),
				$this->full_read_pod,
				'read'
			)
		);
	}

	public function test_field_allowed_to_extend_with_full_rest_write() {
		$this->assertTrue(
			PodsRESTFields::field_allowed_to_extend(
				$this->full_write_pod->get_field( 'write_rest_number' ),
				$this->full_write_pod,
				'write'
			)
		);
	}

	public function test_field_allowed_to_extend_with_full_rest_write_with_field_without_rest() {
		$this->assertFalse(
			PodsRESTFields::field_allowed_to_extend(
				$this->full_write_pod->get_field( 'non_rest_number' ),
				$this->full_write_pod,
				'write'
			)
		);

		$this->assertFalse(
			PodsRESTFields::field_allowed_to_extend(
				$this->full_write_pod->get_field( 'read_rest_number' ),
				$this->full_write_pod,
				'write'
			)
		);
	}

	private function sut(): PodsRESTHandlers {
		return new PodsRESTHandlers();
	}

}
