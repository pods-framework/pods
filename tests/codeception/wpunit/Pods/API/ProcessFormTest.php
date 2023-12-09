<?php

namespace Pods_Unit_Tests\Pods\API;

use Exception;
use Pods_Unit_Tests\Pods_UnitTestCase;
use Pods;
use PodsAPI;

/**
 * @group  pods
 * @covers PodsAPI
 */
class ProcessFormTest extends Pods_UnitTestCase {

	/**
	 * @var PodsAPI
	 */
	protected $api;

	/**
	 * @var string
	 */
	protected $pod;

	/**
	 * @var int
	 */
	protected $pod_id;

	/**
	 * @var string
	 */
	protected $group;

	/**
	 * @var int
	 */
	protected $group_id;

	/**
	 * @var string
	 */
	protected $field;

	/**
	 * @var int
	 */
	protected $field_id;

	/**
	 * @var string
	 */
	protected $field2;

	/**
	 * @var int
	 */
	protected $field2_id;

	/**
	 * @var string
	 */
	protected $field_hidden;

	/**
	 * @var int
	 */
	protected $field_hidden_id;

	/**
	 * @var string
	 */
	protected $field_conditional_show;

	/**
	 * @var int
	 */
	protected $field_conditional_show_id;

	/**
	 * @var string
	 */
	protected $field_conditional_hide;

	/**
	 * @var int
	 */
	protected $field_conditional_hide_id;

	public function setUp(): void {
		parent::setUp();

		$this->api = pods_api();

		$this->populate();
	}

	/**
	 *
	 */
	public function tearDown(): void {
		parent::tearDown();

		if ( $this->pod ) {
			$this->api->delete_pod( [ 'name' => $this->pod ] );
		}

		$this->api = null;
	}

	public function populate() {
		$this->pod    = 'test_groups_pod';
		$this->pod_id = $this->api->save_pod( [
			'name'            => $this->pod,
			'type'            => 'post_type',
			'label'           => 'Test pod for groups',
		] );

		$this->group    = 'test_group';
		$this->group_id = $this->api->save_group( [
			'pod_id'          => $this->pod_id,
			'name'            => $this->group,
			'label'           => 'Test group',
		] );

		$this->field    = 'test_field';
		$this->field_id = $this->api->save_field( [
			'pod_id'          => $this->pod_id,
			'group_id'        => $this->group_id,
			'name'            => $this->field,
			'label'           => 'Test field',
		] );

		$this->field2    = 'test_field2';
		$this->field2_id = $this->api->save_field( [
			'pod_id'          => $this->pod_id,
			'group_id'        => $this->group_id,
			'name'            => $this->field2,
			'label'           => 'Test field 2',
		] );

		$this->field_hidden    = 'test_hidden_field';
		$this->field_hidden_id = $this->api->save_field( [
			'pod_id'          => $this->pod_id,
			'group_id'        => $this->group_id,
			'name'            => $this->field_hidden,
			'label'           => 'Test hidden field',
		] );

		$this->field_conditional_show    = 'test_conditional_show_field';
		$this->field_conditional_show_id = $this->api->save_field( [
			'pod_id'                   => $this->pod_id,
			'group_id'                 => $this->group_id,
			'name'                     => $this->field_conditional_show,
			'label'                    => 'Test conditional show field',
			'enable_conditional_logic' => 1,
			'conditional_logic'        => [
				'action' => 'show',
				'logic'  => 'any',
				'rules'  => [
					[
						'field'   => $this->field,
						'compare' => '=',
						'value'   => '12345',
					],
				],
			],
		] );

		$this->field_conditional_hide    = 'test_conditional_hide_field';
		$this->field_conditional_hide_id = $this->api->save_field( [
			'pod_id'                   => $this->pod_id,
			'group_id'                 => $this->group_id,
			'name'                     => $this->field_conditional_hide,
			'label'                    => 'Test conditional hide field',
			'enable_conditional_logic' => 1,
			'conditional_logic'        => [
				'action' => 'hide',
				'logic'  => 'any',
				'rules'  => [
					[
						'field'   => $this->field,
						'compare' => '=',
						'value'   => '12345',
					],
				],
			],
		] );
	}

	protected function create_base_form_params( Pods $pod, array $submittable_fields, bool $duplicate = false ): array {
		$path = '/my-page-with-form/';

		if ( ! isset( $submittable_fields[0] ) ) {
			$submittable_fields = array_keys( $submittable_fields );
		}

		$form_fields = implode( ',', $submittable_fields );

		$uri_hash   = wp_create_nonce( 'pods_uri_' . $path );
		$field_hash = wp_create_nonce( 'pods_fields_' . $form_fields );

		if ( is_user_logged_in() ) {
			$uid = 'user_' . get_current_user_id();
		} else {
			$uid = pods_session_id();
		}

		$item_id = $duplicate ? 0 : $pod->id();

		$nonce = wp_create_nonce( 'pods_form_' . $pod->pod . '_' . $uid . '_' . $item_id . '_' . $uri_hash . '_' . $field_hash );

		return [
			'_pods_nonce'    => $nonce,
			'_pods_pod'      => $this->pod,
			'_pods_id'       => (string) $item_id, // This comes through as a string during submit.
			'_pods_uri'      => $uri_hash,
			'_pods_form'     => $form_fields,
			'_pods_location' => $path,
		];
	}

	public function test_process_form_with_no_values() {
		$this->expectExceptionMessage( 'Invalid submission' );
		$this->expectException( Exception::class );

		$this->api->process_form( [] );
	}

	public function test_process_form_with_no_nonce() {
		/** @var Pods $pod */
		$pod = pods( $this->pod );

		$params = $this->create_base_form_params( $pod, [
			$this->field,
		] );

		$params['_pods_nonce'] = '';

		$this->expectExceptionMessage( 'Invalid submission' );
		$this->expectException( Exception::class );

		$this->api->process_form( $params );
	}

	public function test_process_form_with_no_pod() {
		/** @var Pods $pod */
		$pod = pods( $this->pod );

		$params = $this->create_base_form_params( $pod, [
			$this->field,
		] );

		$params['_pods_pod'] = '';

		$this->expectExceptionMessage( 'Invalid submission' );
		$this->expectException( Exception::class );

		$this->api->process_form( $params );
	}

	public function test_process_form_with_no_uri() {
		/** @var Pods $pod */
		$pod = pods( $this->pod );

		$params = $this->create_base_form_params( $pod, [
			$this->field,
		] );

		$params['_pods_uri'] = '';

		$this->expectExceptionMessage( 'Invalid submission' );
		$this->expectException( Exception::class );

		$this->api->process_form( $params );
	}

	public function test_process_form_with_no_form() {
		/** @var Pods $pod */
		$pod = pods( $this->pod );

		$params = $this->create_base_form_params( $pod, [
			$this->field,
		] );

		$params['_pods_form'] = '';

		$this->expectExceptionMessage( 'Invalid submission' );
		$this->expectException( Exception::class );

		$this->api->process_form( $params );
	}

	public function test_process_form_with_invalid_nonce() {
		wp_set_current_user( 1 );

		/** @var Pods $pod */
		$pod = pods( $this->pod );

		$params = $this->create_base_form_params( $pod, [
			$this->field,
			$this->field2,
			$this->field_hidden,
			$this->field_conditional_show,
			$this->field_conditional_hide,
		] );

		$params['_pods_nonce'] = 'this_nonce_is_invalid';

		$this->expectExceptionMessage( 'Access denied, please refresh and try again.' );
		$this->expectException( Exception::class );

		$this->api->process_form( $params );
	}

	public function test_process_form_with_wrong_pod() {
		wp_set_current_user( 1 );

		/** @var Pods $pod */
		$pod = pods( $this->pod );

		$params = $this->create_base_form_params( $pod, [
			$this->field,
			$this->field2,
			$this->field_hidden,
			$this->field_conditional_show,
			$this->field_conditional_hide,
		] );

		$params['_pods_pod'] = 'different_pod';

		$this->expectExceptionMessage( 'Access denied, please refresh and try again.' );
		$this->expectException( Exception::class );

		$this->api->process_form( $params );
	}

	public function test_process_form_with_wrong_id() {
		wp_set_current_user( 1 );

		/** @var Pods $pod */
		$pod = pods( $this->pod );

		$params = $this->create_base_form_params( $pod, [
			$this->field,
			$this->field2,
			$this->field_hidden,
			$this->field_conditional_show,
			$this->field_conditional_hide,
		] );

		$params['_pods_id'] = '1234';

		$this->expectExceptionMessage( 'Access denied, please refresh and try again.' );
		$this->expectException( Exception::class );

		$this->api->process_form( $params );
	}

	public function test_process_form_with_wrong_uri() {
		wp_set_current_user( 1 );

		/** @var Pods $pod */
		$pod = pods( $this->pod );

		$params = $this->create_base_form_params( $pod, [
			$this->field,
			$this->field2,
			$this->field_hidden,
			$this->field_conditional_show,
			$this->field_conditional_hide,
		] );

		$params['_pods_uri'] = 'wrong_uri_hash';

		$this->expectExceptionMessage( 'Access denied, please refresh and try again.' );
		$this->expectException( Exception::class );

		$this->api->process_form( $params );
	}

	public function test_process_form_with_wrong_form() {
		wp_set_current_user( 1 );

		/** @var Pods $pod */
		$pod = pods( $this->pod );

		$params = $this->create_base_form_params( $pod, [
			$this->field,
			$this->field2,
			$this->field_hidden,
			$this->field_conditional_show,
			$this->field_conditional_hide,
		] );

		$params['_pods_form'] = 'wrong,list,of,fields';

		$this->expectExceptionMessage( 'Access denied, please refresh and try again.' );
		$this->expectException( Exception::class );

		$this->api->process_form( $params );
	}

	public function test_process_form_with_different_location() {
		wp_set_current_user( 1 );

		/** @var Pods $pod */
		$pod = pods( $this->pod );

		$params = $this->create_base_form_params( $pod, [
			$this->field,
			$this->field2,
			$this->field_hidden,
			$this->field_conditional_show,
			$this->field_conditional_hide,
		] );

		$params['_pods_location'] = '/a-different-page/';

		$id = $this->api->process_form( $params );

		$this->assertIsInt( $id );
		$this->assertGreaterThan( 0, $id );
	}

	public function test_process_form_with_different_user() {
		wp_set_current_user( 1 );

		/** @var Pods $pod */
		$pod = pods( $this->pod );

		$params = $this->create_base_form_params( $pod, [
			$this->field,
		] );

		// Set some data that will be saved.
		$params[ 'pods_field_' . $this->field ] = 'some value for the field: ' . $this->field;

		// Overwrite the user ID that was used to create the nonce.
		$GLOBALS['current_user']->ID = 2;

		$this->expectExceptionMessage( 'Access denied, please refresh and try again.' );
		$this->expectException( Exception::class );

		$this->api->process_form( $params );
	}

	public function test_process_form_returns_valid_id() {
		wp_set_current_user( 1 );

		/** @var Pods $pod */
		$pod = pods( $this->pod );

		$params = $this->create_base_form_params( $pod, [
			$this->field,
			$this->field2,
			$this->field_hidden,
			$this->field_conditional_show,
			$this->field_conditional_hide,
		] );

		// Set some data that will be saved.
		$params[ 'pods_field_' . $this->field ]                  = 'some value for the field: ' . $this->field;
		$params[ 'pods_field_' . $this->field2 ]                 = 'some value for the field: ' . $this->field2;
		$params[ 'pods_field_' . $this->field_hidden ]           = 'some value for the field: ' . $this->field_hidden;
		$params[ 'pods_field_' . $this->field_conditional_show ] = 'some value for the field: ' . $this->field_conditional_show;
		$params[ 'pods_field_' . $this->field_conditional_hide ] = 'some value for the field: ' . $this->field_conditional_hide;

		$id = $this->api->process_form( $params );

		$this->assertIsInt( $id );
		$this->assertGreaterThan( 0, $id );

		$pod->fetch( $id );

		$this->assertEquals( $params[ 'pods_field_' . $this->field ], $pod->field( $this->field ) );
		$this->assertEquals( $params[ 'pods_field_' . $this->field2 ], $pod->field( $this->field2 ) );
		$this->assertEquals( $params[ 'pods_field_' . $this->field_hidden ], $pod->field( $this->field_hidden ) );
		$this->assertEquals( '', $pod->field( $this->field_conditional_show ) ); // This field would be hidden and won't save.
		$this->assertEquals( $params[ 'pods_field_' . $this->field_conditional_hide ], $pod->field( $this->field_conditional_hide ) );
	}
}
