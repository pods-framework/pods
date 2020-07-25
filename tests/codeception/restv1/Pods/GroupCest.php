<?php

namespace Pods_Unit_Tests\Pods;

use Pods_Unit_Tests\Testcases\REST\BaseRestCest;
use Restv1Tester;

class GroupCest extends BaseRestCest {

	protected $pod_id;
	protected $group_id;
	protected $group_id2;
	protected $field_id;
	protected $field_id2;
	protected $field_id3;
	protected $field_id4;

	public function _before( Restv1Tester $I ) {
		parent::_before( $I );

		$this->test_rest_url = $this->pods_rest_url . 'groups/%d';

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
	 * It should not have access to getting list of groups.
	 *
	 * @test
	 */
	public function should_not_have_access_to_getting_groups( Restv1Tester $I ) {
		$I->sendGET( sprintf( $this->test_rest_url, $this->group_id ) );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 401 );

		$I->seeResponseContainsJson( [
			'code' => 'rest_forbidden',
		] );
	}

	/**
	 * It should allow getting group.
	 *
	 * @test
	 */
	public function should_allow_getting_group( Restv1Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$I->sendGET( sprintf( $this->test_rest_url, $this->group_id ) );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$response = json_decode( $I->grabResponse(), true );

		$I->assertEquals( 'my-group', $response['group']['name'] );
		$I->assertEquals( $this->group_id, $response['group']['id'] );
		$I->assertArrayNotHasKey( 'fields', $response['group'] );
	}

	/**
	 * It should allow getting group with fields.
	 *
	 * @test
	 */
	public function should_allow_getting_group_with_fields( Restv1Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$I->sendGET( sprintf( $this->test_rest_url, $this->group_id ), [
			'include_fields' => 1,
		] );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$response = json_decode( $I->grabResponse(), true );

		$I->assertEquals( 'my-group', $response['group']['name'] );
		$I->assertEquals( $this->group_id, $response['group']['id'] );
		$I->assertArrayHasKey( 'fields', $response['group'] );
		$I->assertCount( 2, $response['group']['fields'] );
		$I->assertEquals( $this->field_id, $response['group']['fields'][0]['id'] );
		$I->assertEquals( $this->field_id2, $response['group']['fields'][1]['id'] );
	}

	/**
	 * It should allow updating group.
	 *
	 * @test
	 */
	public function should_allow_updating_group( Restv1Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$args = [
			'name'  => 'new_name',
			'label' => 'New label',
			'args'  => [
				'test_update' => 1,
			],
		];

		$I->sendPOST( sprintf( $this->test_rest_url, $this->group_id ), $args );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$response = json_decode( $I->grabResponse(), true );

		$I->assertEquals( $args['name'], $response['group']['name'] );
		$I->assertEquals( $args['label'], $response['group']['label'] );
		$I->assertEquals( $this->group_id, $response['group']['id'] );
		$I->assertArrayHasKey( 'test_update', $response['group'] );
		$I->assertEquals( $args['args']['test_update'], $response['group']['test_update'] );
		$I->assertArrayNotHasKey( 'fields', $response['group'] );

		// Make another request to ensure it's returning uncached.
		$args = [
			'name'  => 'new_name2',
			'label' => 'New label2',
			'args'  => [
				'test_update' => 2,
			],
		];

		$I->sendPOST( sprintf( $this->test_rest_url, $this->group_id ), $args );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$response = json_decode( $I->grabResponse(), true );

		$I->assertEquals( $args['name'], $response['group']['name'] );
		$I->assertEquals( $args['label'], $response['group']['label'] );
		$I->assertEquals( $this->group_id, $response['group']['id'] );
		$I->assertArrayHasKey( 'test_update', $response['group'] );
		$I->assertEquals( $args['args']['test_update'], $response['group']['test_update'] );
		$I->assertArrayNotHasKey( 'fields', $response['group'] );
	}

	/**
	 * It should allow deleting group.
	 *
	 * @test
	 */
	public function should_allow_deleting_group( Restv1Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$post_id = $I->havePostInDatabase( [
			'post_type'    => 'my-pod',
			'post_title'   => 'Test content',
			'post_content' => 'Test content',
		] );
		$I->havePostMetaInDatabase( $post_id, 'my-field', 'test meta' );

		$I->sendDELETE( sprintf( $this->test_rest_url, $this->group_id ) );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );
		$I->seeResponseContainsJson( [
			'status' => 'deleted',
		] );

		$I->seePostInDatabase( [ 'ID' => $post_id ] );
		$I->dontSeePostInDatabase( [ 'ID' => $this->group_id ] );
		$I->dontSeePostInDatabase( [ 'ID' => $this->field_id ] );

		// Content should still remain.
		$I->seePostMetaInDatabase( [ 'post_id' => $post_id, 'meta_key' => 'my-field' ] );
	}
}
