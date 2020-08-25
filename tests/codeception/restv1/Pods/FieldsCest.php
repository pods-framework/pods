<?php

namespace Pods_Unit_Tests\Pods;

use Pods_Unit_Tests\Testcases\REST\PodsRestCest;
use Restv1Tester;

class FieldsCest extends PodsRestCest {

	public function _before( Restv1Tester $I ) {
		parent::_before( $I );

		$this->test_rest_url = $this->pods_rest_url . 'fields';
	}

	/**
	 * It should not have access to getting list of fields.
	 *
	 * @test
	 */
	public function should_not_have_access_to_getting_fields( Restv1Tester $I ) {
		$I->sendGET( $this->test_rest_url );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 401 );

		$I->seeResponseContainsJson( [
			'code' => 'rest_forbidden',
		] );
	}

	/**
	 * It should allow getting all fields.
	 *
	 * @test
	 */
	public function should_allow_getting_all_fields( Restv1Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$I->sendGET( $this->test_rest_url );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$response = json_decode( $I->grabResponse(), true );

		$I->assertArrayHasKey( 'fields', $response );
		$I->assertCount( 16, $response['fields'] );
		$I->assertContains( 'my-field', wp_list_pluck( $response['fields'], 'name' ) );
		$I->assertContains( $this->field_id, wp_list_pluck( $response['fields'], 'id' ) );
	}

	/**
	 * It should allow getting bi-directional fields.
	 *
	 * @test
	 */
	public function should_allow_getting_bi_directional_fields( Restv1Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$api = pods_api();

		// Create a new pod.
		$pod_id2 = $api->save_pod( [
			'storage' => 'meta',
			'type'    => 'post_type',
			'name'    => 'my-pod2',
		] );

		// Create a new relationship field to our current pod.
		$related_field_id = $api->save_field( [
			'pod_id'      => $pod_id2,
			'name'        => 'my-related-field',
			'type'        => 'pick',
			'pick_object' => 'post_type',
			'pick_val'    => 'my-pod',
		] );

		// Get fields related to our current pod, on the new pod.
		$I->sendGET( $this->test_rest_url, [
			'types' => 'pick',
			'pod'  => 'my-pod2',
			'args' => [
				'pick_object' => 'post_type',
				'pick_val'    => 'my-pod',
			],
		] );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$response = json_decode( $I->grabResponse(), true );

		$I->assertArrayHasKey( 'fields', $response );
		$I->assertCount( 1, $response['fields'] );
		$I->assertEquals( 'my-related-field', $response['fields'][0]['name'] );
		$I->assertEquals( 'pick', $response['fields'][0]['type'] );
		$I->assertEquals( $related_field_id, $response['fields'][0]['id'] );
	}
}
