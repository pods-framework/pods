<?php

namespace Pods_Unit_Tests\Pods;

use Pods_Unit_Tests\Testcases\REST\PodsRestCest;
use Restv1Tester;

class FieldCest extends PodsRestCest {

	public function _before( Restv1Tester $I ) {
		parent::_before( $I );

		$this->test_rest_url = $this->pods_rest_url . 'fields/%d';
	}

	/**
	 * It should not have access to getting list of field.
	 *
	 * @test
	 */
	public function should_not_have_access_to_getting_field( Restv1Tester $I ) {
		$I->sendGET( sprintf( $this->test_rest_url, $this->field_id ) );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 401 );

		$I->seeResponseContainsJson( [
			'code' => 'rest_forbidden',
		] );
	}

	/**
	 * It should allow getting field.
	 *
	 * @test
	 */
	public function should_allow_getting_field( Restv1Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$I->sendGET( sprintf( $this->test_rest_url, $this->field_id ) );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$response = json_decode( $I->grabResponse(), true );

		$I->assertEquals( 'my-field', $response['field']['name'] );
		$I->assertEquals( 'text', $response['field']['type'] );
		$I->assertEquals( $this->field_id, $response['field']['id'] );
	}

	/**
	 * It should allow updating field.
	 *
	 * @test
	 */
	public function should_allow_updating_field( Restv1Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$args = [
			'name'  => 'new_name',
			'label' => 'New label',
			'type'  => 'website',
			'args'  => [
				'test_update' => 1,
			],
		];

		$I->sendPOST( sprintf( $this->test_rest_url, $this->field_id ), $args );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$response = json_decode( $I->grabResponse(), true );

		$I->assertEquals( $args['name'], $response['field']['name'] );
		$I->assertEquals( $args['label'], $response['field']['label'] );
		$I->assertEquals( $args['type'], $response['field']['type'] );
		$I->assertEquals( $this->field_id, $response['field']['id'] );
		$I->assertArrayHasKey( 'test_update', $response['field'] );
		$I->assertEquals( $args['args']['test_update'], $response['field']['test_update'] );

		// Make another request to ensure it's returning uncached.
		$args = [
			'name'  => 'new_name2',
			'label' => 'New label2',
			'type'  => 'number',
			'args'  => [
				'test_update' => 2,
			],
		];

		$I->sendPOST( sprintf( $this->test_rest_url, $this->field_id ), $args );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$response = json_decode( $I->grabResponse(), true );

		$I->assertEquals( $args['name'], $response['field']['name'] );
		$I->assertEquals( $args['label'], $response['field']['label'] );
		$I->assertEquals( $args['type'], $response['field']['type'] );
		$I->assertEquals( $this->field_id, $response['field']['id'] );
		$I->assertArrayHasKey( 'test_update', $response['field'] );
		$I->assertEquals( $args['args']['test_update'], $response['field']['test_update'] );
	}

	/**
	 * It should allow deleting field.
	 *
	 * @test
	 */
	public function should_allow_deleting_field( Restv1Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$post_id = $I->havePostInDatabase( [
			'post_type'    => 'my-pod',
			'post_title'   => 'Test content',
			'post_content' => 'Test content',
		] );
		$I->havePostMetaInDatabase( $post_id, 'my-field', 'test meta' );

		$I->sendDELETE( sprintf( $this->test_rest_url, $this->field_id ) );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );
		$I->seeResponseContainsJson( [
			'status' => 'deleted',
		] );

		$I->seePostInDatabase( [ 'ID' => $post_id ] );
		$I->dontSeePostInDatabase( [ 'ID' => $this->field_id ] );

		// Content should still remain.
		$I->seePostMetaInDatabase( [ 'post_id' => $post_id, 'meta_key' => 'my-field' ] );
	}
}
