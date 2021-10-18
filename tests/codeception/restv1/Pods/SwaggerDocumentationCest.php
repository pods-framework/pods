<?php

namespace Pods_Unit_Tests\Pods;

use Pods_Unit_Tests\Testcases\REST\BaseRestCest;
use Restv1Tester;

class SwaggerDocumentationCest extends BaseRestCest {

	public function _before( Restv1Tester $I ) {
		parent::_before( $I );

		$this->test_rest_url = $this->pods_rest_url . 'doc';
	}

	/**
	 * @test
	 * it should return a JSON array containing headers in Swagger format
	 */
	public function it_should_load_site_url( Restv1Tester $I ) {
		$I->sendGET( $this->site_url );

		$I->seeResponseCodeIs( 200 );
	}

	/**
	 * @test
	 * it should return a JSON array containing headers in Swagger format
	 */
	public function it_should_load_wp_rest_url( Restv1Tester $I ) {
		$I->sendGET( $this->wp_rest_url );

		$I->seeResponseCodeIs( 200 );
	}

	/**
	 * @test
	 * it should expose a Swagger documentation endpoint
	 */
	public function it_should_expose_a_swagger_documentation_endpoint( Restv1Tester $I ) {
		$I->sendGET( $this->test_rest_url );

		$I->seeResponseCodeIs( 200 );
	}

	/**
	 * @test
	 * it should return a JSON array containing headers in Swagger format
	 */
	public function it_should_return_a_json_array_containing_headers_in_swagger_format( Restv1Tester $I ) {
		$I->sendGET( $this->test_rest_url );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();

		$response = json_decode( $I->grabResponse(), true );

		$I->assertArrayHasKey( 'openapi', $response );
		$I->assertArrayHasKey( 'info', $response );
		$I->assertArrayHasKey( 'servers', $response );
		$I->assertArrayHasKey( 'paths', $response );
		$I->assertArrayHasKey( 'components', $response );
	}

	/**
	 * @test
	 * it should return the correct information
	 */
	public function it_should_return_the_correct_information( Restv1Tester $I ) {
		$I->sendGET( $this->test_rest_url );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();

		$response = json_decode( $I->grabResponse(), true );

		$I->assertArrayHasKey( 'info', $response );

		$info = $response['info'];

		$I->assertArrayHasKey( 'version', $info );
		$I->assertArrayHasKey( 'title', $info );
		$I->assertArrayHasKey( 'description', $info );
	}

	/**
	 * @test
	 * it should return the site URL as host
	 */
	public function it_should_return_the_site_url_as_host( Restv1Tester $I ) {
		$I->sendGET( $this->test_rest_url );

		$I->seeResponseCodeIs( 200 );
		$I->seeResponseIsJson();

		$response = json_decode( $I->grabResponse(), true );

		$I->assertArrayHasKey( 'url', $response['servers'][0] );
	}
}
