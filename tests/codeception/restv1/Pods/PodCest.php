<?php

namespace Pods_Unit_Tests\Pods;

use Pods_Unit_Tests\Testcases\REST\BaseRestCest;
use Restv1Tester;

class PodCest extends BaseRestCest {

	protected $pod_id;

	public function _before( Restv1Tester $I ) {
		parent::_before( $I );

		$this->test_rest_url = $this->pods_rest_url . 'pods/%d';

		$api = pods_api();

		$this->pod_id = $api->save_pod( [
			'storage' => 'meta',
			'type'    => 'post_type',
			'name'    => 'my-pod',
		] );
	}

	public function _after( Restv1Tester $I ) {
		$api = pods_api();

		$api->delete_pod( [ 'id' => $this->pod_id ] );

		$this->pod_id = null;

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
	}
}
