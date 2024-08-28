<?php

namespace Pods_Unit_Tests\Pods;

use Pods\Whatsit\Pod;
use Pods_Unit_Tests\Pods_UnitTestCase;
use PodsRESTFields;

/**
 * @group  pods-rest
 * @covers PodsRESTFields
 */
class PodsRESTFieldsTest extends Pods_UnitTestCase {

	/**
	 * @var string
	 */
	protected $pod_name = 'test_pods_rest';

	/**
	 * @var int
	 */
	protected $pod_id = 0;

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

	public function tearDown(): void {
		$this->pod_id            = null;
		$this->pod               = null;
		$this->full_read_pod_id  = null;
		$this->full_read_pod     = null;
		$this->full_write_pod_id = null;
		$this->full_write_pod    = null;

		pods_static_cache_clear();

		// Reset current user.
		global $current_user;

		$current_user = null;

		wp_set_current_user( 0 );

		parent::tearDown();
	}

	public function test_get_pod() {
		$sut = $this->sut();

		$this->assertEquals($this->pod, $sut->get_pod());
	}

	public function test_set_pod() {
		$test_pod = new Pod( [
			'name'        => 'test_basic',
			'type'        => 'post_type',
			'rest_enable' => 1,
		] );

		$sut = $this->sut();

		$sut->set_pod( $test_pod );

		$this->assertEquals($test_pod, $sut->get_pod());
	}

	public function test_set_pod_for_unsupported_type() {
		$test_pod = new Pod( [
			'name'        => 'test_basic',
			'type'        => 'unsupported',
			'rest_enable' => 1,
		] );

		$sut = $this->sut();

		$sut->set_pod( $test_pod );

		$this->assertNull( $sut->get_pod() );
	}

	public function test_set_pod_for_non_rest_pod() {
		$test_pod = new Pod( [
			'name' => 'test_basic',
			'type' => 'post_type',
		] );

		$sut = $this->sut();

		$sut->set_pod( $test_pod );

		$this->assertNull( $sut->get_pod() );
	}

	public function test_add_fields() {
		$sut = $this->sut();

		$sut->add_fields();

		$this->assertEquals( 10, has_filter( 'rest_insert_' . $this->pod_name, [ 'PodsRESTHandlers', 'save_handler' ] ) );
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

	private function sut( Pod $pod = null ): PodsRESTFields {
		return new PodsRESTFields( $pod ?? $this->pod );
	}

}
