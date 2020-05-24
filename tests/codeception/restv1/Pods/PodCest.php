<?php

namespace Pods_Unit_Tests\Pods;

use Pods_Unit_Tests\Testcases\REST\BaseRestCest;
use Restv1Tester;

class PodCest extends BaseRestCest {

	protected $pod_id;
	protected $group_id;
	protected $group_id2;
	protected $field_id;
	protected $field_id2;
	protected $field_id3;
	protected $field_id4;

	public function _before( Restv1Tester $I ) {
		parent::_before( $I );

		$this->test_rest_url = $this->pods_rest_url . 'pods/%d';

		$api = pods_api();

		$this->pod_id = $api->save_pod( [
			'storage' => 'meta',
			'type'    => 'post_type',
			'name'    => 'my-pod',
		] );

		$this->group_id = $api->save_group( [
			'pod_id' => $this->pod_id,
			'name'   => 'my-group',
		] );

		$this->group_id2 = $api->save_group( [
			'pod_id' => $this->pod_id,
			'name'   => 'my-group2',
		] );

		$this->field_id = $api->save_field( [
			'pod_id'   => $this->pod_id,
			'group_id' => $this->group_id,
			'name'     => 'my-field',
			'type'     => 'text',
		] );

		$this->field_id2 = $api->save_field( [
			'pod_id'   => $this->pod_id,
			'group_id' => $this->group_id,
			'name'     => 'my-field2',
			'type'     => 'text',
		] );

		$this->field_id3 = $api->save_field( [
			'pod_id'   => $this->pod_id,
			'group_id' => $this->group_id2,
			'name'     => 'my-field3',
			'type'     => 'text',
		] );

		$this->field_id4 = $api->save_field( [
			'pod_id'   => $this->pod_id,
			'group_id' => $this->group_id2,
			'name'     => 'my-field4',
			'type'     => 'text',
		] );
	}

	public function _after( Restv1Tester $I ) {
		$this->pod_id    = null;
		$this->group_id  = null;
		$this->group_id2 = null;
		$this->field_id  = null;
		$this->field_id2 = null;
		$this->field_id3 = null;
		$this->field_id4 = null;

		parent::_after( $I );
	}

	/**
	 * It should not have access to getting list of pods.
	 *
	 * @test
	 */
	public function should_not_have_access_to_getting_pods( Restv1Tester $I ) {
		$I->sendGET( sprintf( $this->test_rest_url, $this->pod_id ) );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 401 );

		$I->seeResponseContainsJson( [
			'code' => 'rest_forbidden',
		] );
	}

	/**
	 * It should allow getting Pod.
	 *
	 * @test
	 */
	public function should_allow_getting_pod( Restv1Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$I->sendGET( sprintf( $this->test_rest_url, $this->pod_id ) );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$response = json_decode( $I->grabResponse(), true );

		$I->assertEquals( 'meta', $response['pod']['storage'] );
		$I->assertEquals( 'post_type', $response['pod']['type'] );
		$I->assertEquals( 'my-pod', $response['pod']['name'] );
		$I->assertArrayNotHasKey( 'groups', $response['pod'] );
		$I->assertArrayNotHasKey( 'fields', $response['pod'] );
	}

	/**
	 * It should allow getting Pod with groups.
	 *
	 * @test
	 */
	public function should_allow_getting_pod_with_groups( Restv1Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$I->sendGET( sprintf( $this->test_rest_url, $this->pod_id ), [
			'include_groups' => 1,
		] );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$response = json_decode( $I->grabResponse(), true );

		$I->assertEquals( 'meta', $response['pod']['storage'] );
		$I->assertEquals( 'post_type', $response['pod']['type'] );
		$I->assertEquals( 'my-pod', $response['pod']['name'] );
		$I->assertArrayHasKey( 'groups', $response['pod'] );
		$I->assertEquals( $this->group_id, $response['pod']['groups'][0]['id'] );
		$I->assertArrayNotHasKey( 'fields', $response['pod']['groups'][0] );
	}

	/**
	 * It should allow getting Pod with fields.
	 *
	 * @test
	 */
	public function should_allow_getting_pod_with_fields( Restv1Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$I->sendGET( sprintf( $this->test_rest_url, $this->pod_id ), [
			'include_fields' => 1,
		] );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$response = json_decode( $I->grabResponse(), true );

		$I->assertEquals( 'meta', $response['pod']['storage'] );
		$I->assertEquals( 'post_type', $response['pod']['type'] );
		$I->assertEquals( 'my-pod', $response['pod']['name'] );
		$I->assertArrayNotHasKey( 'groups', $response['pod'] );
		$I->assertArrayHasKey( 'fields', $response['pod'] );
		$I->assertEquals( $this->field_id, $response['pod']['fields'][0]['id'] );
	}

	/**
	 * It should allow getting Pod with group fields.
	 *
	 * @test
	 */
	public function should_allow_getting_pod_with_group_fields( Restv1Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$I->sendGET( sprintf( $this->test_rest_url, $this->pod_id ), [
			'include_groups'       => 1,
			'include_group_fields' => 1,
		] );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$response = json_decode( $I->grabResponse(), true );

		$I->assertEquals( 'meta', $response['pod']['storage'] );
		$I->assertEquals( 'post_type', $response['pod']['type'] );
		$I->assertEquals( 'my-pod', $response['pod']['name'] );
		$I->assertArrayHasKey( 'groups', $response['pod'] );
		$I->assertEquals( $this->group_id, $response['pod']['groups'][0]['id'] );
		$I->assertArrayHasKey( 'fields', $response['pod']['groups'][0] );
		$I->assertEquals( $this->field_id, $response['pod']['groups'][0]['fields'][0]['id'] );
		$I->assertArrayNotHasKey( 'fields', $response['pod'] );
	}

	/**
	 * It should allow getting Pod with groups and fields.
	 *
	 * @test
	 */
	public function should_allow_getting_pod_with_groups_and_fields( Restv1Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$I->sendGET( sprintf( $this->test_rest_url, $this->pod_id ), [
			'include_groups' => 1,
			'include_fields' => 1,
		] );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$response = json_decode( $I->grabResponse(), true );

		$I->assertEquals( 'meta', $response['pod']['storage'] );
		$I->assertEquals( 'post_type', $response['pod']['type'] );
		$I->assertEquals( 'my-pod', $response['pod']['name'] );
		$I->assertArrayHasKey( 'groups', $response['pod'] );
		$I->assertEquals( $this->group_id, $response['pod']['groups'][0]['id'] );
		$I->assertArrayNotHasKey( 'fields', $response['pod']['groups'][0] );
		$I->assertArrayHasKey( 'fields', $response['pod'] );
		$I->assertEquals( $this->field_id, $response['pod']['fields'][0]['id'] );
	}

	/**
	 * It should allow updating Pod.
	 *
	 * @test
	 */
	public function should_allow_updating_pod( Restv1Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$args = [
			'name'  => 'new_name',
			'label' => 'New label',
			'args'  => [
				'test_update' => 1,
			],
		];

		$I->sendPOST( sprintf( $this->test_rest_url, $this->pod_id ), $args );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$response = json_decode( $I->grabResponse(), true );

		$I->assertEquals( $args['name'], $response['pod']['name'] );
		$I->assertEquals( $args['label'], $response['pod']['label'] );
		$I->assertArrayHasKey( 'test_update', $response['pod'] );
		$I->assertEquals( $args['args']['test_update'], $response['pod']['test_update'] );
		$I->assertArrayNotHasKey( 'groups', $response['pod'] );
		$I->assertArrayNotHasKey( 'fields', $response['pod'] );

		// Make another request to ensure it's returning uncached.
		$args = [
			'name'  => 'new_name2',
			'label' => 'New label2',
			'args'  => [
				'test_update' => 2,
			],
		];

		$I->sendPOST( sprintf( $this->test_rest_url, $this->pod_id ), $args );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$response = json_decode( $I->grabResponse(), true );

		$I->assertEquals( $args['name'], $response['pod']['name'] );
		$I->assertEquals( $args['label'], $response['pod']['label'] );
		$I->assertArrayHasKey( 'test_update', $response['pod'] );
		$I->assertEquals( $args['args']['test_update'], $response['pod']['test_update'] );
		$I->assertArrayNotHasKey( 'groups', $response['pod'] );
		$I->assertArrayNotHasKey( 'fields', $response['pod'] );
	}

	/**
	 * It should allow updating Pod.
	 *
	 * @test
	 */
	public function should_allow_updating_pod_with_groups_and_fields_order( Restv1Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$args = [
			'name'  => 'new_name',
			'label' => 'New label',
			'args'  => [
				'test_update' => 1,
			],
			'order' => [
				'groups' => [
					$this->group_id => [
						$this->field_id2,
						$this->field_id,
					],
					$this->group_id2 => [
						$this->field_id4,
						$this->field_id3,
					],
				],
			],
		];

		$I->sendPOST( sprintf( $this->test_rest_url, $this->pod_id ), $args );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$response = json_decode( $I->grabResponse(), true );

		$I->assertEquals( $args['name'], $response['pod']['name'] );
		$I->assertEquals( $args['label'], $response['pod']['label'] );
		$I->assertArrayHasKey( 'test_update', $response['pod'] );
		$I->assertEquals( $args['args']['test_update'], $response['pod']['test_update'] );
		$I->assertArrayNotHasKey( 'groups', $response['pod'] );
		$I->assertArrayNotHasKey( 'fields', $response['pod'] );
	}
}
