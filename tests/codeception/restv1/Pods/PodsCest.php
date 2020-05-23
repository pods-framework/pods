<?php

namespace Pods_Unit_Tests\Pods;

use Codeception\Example;
use Pods_Unit_Tests\Testcases\REST\BaseRestCest;
use Restv1Tester;

class PodsCest extends BaseRestCest {

	public function _before( Restv1Tester $I ) {
		parent::_before( $I );

		$this->test_rest_url = $this->pods_rest_url . 'pods';
	}

	/**
	 * It should not have access to getting list of pods.
	 *
	 * @test
	 */
	public function should_not_have_access_to_getting_pods( Restv1Tester $I ) {
		$I->sendGET( $this->test_rest_url );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 401 );

		$I->seeResponseContainsJson( [
			'code' => 'rest_forbidden',
		] );
	}

	/**
	 * It should allow getting list of Pods.
	 *
	 * @test
	 */
	public function should_allow_getting_list_of_pods( Restv1Tester $I ) {
		$I->generate_nonce_for_role( 'administrator' );

		$I->sendGET( $this->test_rest_url );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$response = json_decode( $I->grabResponse(), true );

		$I->assertCount( 14, $response['pods'] );
	}

	public function provider_return_type() {
		yield 'full' => [
			'return_type' => 'full',
			'expected'    => [
				// Compare IDs here, assertion will handle that.
				67,
				113,
				104,
				85,
				40,
				31,
				22,
				76,
				122,
				4,
				13,
				58,
				49,
				94,
			],
		];

		yield 'names' => [
			'return_type' => 'names',
			'expected'    => [
				'category',
				'comment',
				'media',
				'nav_menu',
				'nav_menu_item',
				'page',
				'post',
				'post_tag',
				'test_act',
				'test_post_meta',
				'test_post_table',
				'test_tax_meta',
				'test_tax_table',
				'user',
			],
		];

		yield 'ids' => [
			'return_type' => 'ids',
			'expected'    => [
				67,
				113,
				104,
				85,
				40,
				31,
				22,
				76,
				122,
				4,
				13,
				58,
				49,
				94,
			],
		];

		yield 'count' => [
			'return_type' => 'count',
			'expected'    => 14,
		];
	}

	/**
	 * It should allow getting list of Pods with return type.
	 *
	 * @test
	 * @dataProvider provider_return_type
	 */
	public function should_allow_getting_list_of_pods_with_return_type( Restv1Tester $I, Example $example ) {
		$variation = $example->getIterator()->getArrayCopy();

		$I->generate_nonce_for_role( 'administrator' );

		$I->sendGET( $this->test_rest_url, [
			'return_type' => $variation['return_type'],
		] );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$response = json_decode( $I->grabResponse(), true );

		$pods = $response['pods'];

		// Compare IDs if doing full return.
		if ( 'full' === $variation['return_type'] ) {
			$pods = wp_list_pluck( $response['pods'], 'id' );
		}

		$I->assertEquals( $variation['expected'], $pods );
	}

	public function provider_types() {
		yield 'post_type' => [
			'types'    => 'post_type',
			'expected' => [
				4,
				13,
				22,
				31,
				40,
			],
		];

		yield 'taxonomy' => [
			'types'    => 'taxonomy',
			'expected' => [
				49,
				58,
				67,
				76,
				85,
			],
		];

		yield 'media' => [
			'types'    => 'media',
			'expected' => [
				104,
			],
		];

		yield 'user' => [
			'types'    => 'user',
			'expected' => [
				94,
			],
		];

		yield 'settings' => [
			'types'    => 'settings',
			'expected' => [],
		];

		yield 'pod' => [
			'types'    => 'pod',
			'expected' => [
				122,
			],
		];

		yield 'post_type + taxonomy' => [
			'types'    => [
				'post_type',
				'taxonomy',
			],
			'expected' => [
				4,
				13,
				22,
				31,
				40,
				49,
				58,
				67,
				76,
				85,
			],
		];

		yield 'post_type + taxonomy (string)' => [
			'types'    => 'post_type,taxonomy',
			'expected' => [
				4,
				13,
				22,
				31,
				40,
				49,
				58,
				67,
				76,
				85,
			],
		];

		yield '(empty)' => [
			'types'    => '',
			'expected' => [
				4,
				13,
				22,
				31,
				40,
				49,
				58,
				67,
				76,
				85,
				94,
				104,
				113,
				122,
			],
		];
	}

	/**
	 * It should allow getting list of Pods by types.
	 *
	 * @test
	 * @dataProvider provider_types
	 */
	public function should_allow_getting_list_of_pods_by_types( Restv1Tester $I, Example $example ) {
		$variation = $example->getIterator()->getArrayCopy();

		$I->generate_nonce_for_role( 'administrator' );

		$I->sendGET( $this->test_rest_url, [
			'types' => $variation['types'],
		] );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$response = json_decode( $I->grabResponse(), true );

		$pods = wp_list_pluck( $response['pods'], 'id' );

		sort( $pods );

		$I->assertEquals( $variation['expected'], $pods );
	}

	public function provider_ids() {
		yield 'one id array' => [
			'ids'      => [
				113,
			],
			'expected' => [
				113,
			],
		];

		yield 'one id string' => [
			'ids'      => '113',
			'expected' => [
				113,
			],
		];

		yield 'multi id array' => [
			'ids'      => [
				113,
				104,
			],
			'expected' => [
				104,
				113,
			],
		];

		yield 'multi id string' => [
			'ids'      => '113,104',
			'expected' => [
				104,
				113,
			],
		];

		yield '(empty)' => [
			'ids'      => '',
			'expected' => [
				4,
				13,
				22,
				31,
				40,
				49,
				58,
				67,
				76,
				85,
				94,
				104,
				113,
				122,
			],
		];
	}

	/**
	 * It should allow getting list of Pods by ids.
	 *
	 * @test
	 * @dataProvider provider_ids
	 */
	public function should_allow_getting_list_of_pods_by_ids( Restv1Tester $I, Example $example ) {
		$variation = $example->getIterator()->getArrayCopy();

		$I->generate_nonce_for_role( 'administrator' );

		$I->sendGET( $this->test_rest_url, [
			'ids' => $variation['ids'],
		] );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$response = json_decode( $I->grabResponse(), true );

		$pods = wp_list_pluck( $response['pods'], 'id' );

		sort( $pods );

		$I->assertEquals( $variation['expected'], $pods );
	}

	public function provider_args() {
		yield 'match' => [
			'args'  => [
				'pod_index' => 'name',
			],
			'expected' => [
				122,
			],
		];

		yield 'match multi args' => [
			'args'  => [
				'pod_index' => 'name',
				'storage'   => 'table',
			],
			'expected' => [
				122,
			],
		];

		yield 'no match' => [
			'args'  => [
				'pod_index' => 'something-else',
			],
			'expected' => [],
		];
	}

	/**
	 * It should allow getting list of Pods by args.
	 *
	 * @test
	 * @dataProvider provider_args
	 */
	public function should_allow_getting_list_of_pods_by_args( Restv1Tester $I, Example $example ) {
		$variation = $example->getIterator()->getArrayCopy();

		$I->generate_nonce_for_role( 'administrator' );

		$I->sendGET( $this->test_rest_url, [
			'args' => $variation['args'],
		] );

		$I->seeResponseIsJson();
		$I->seeResponseCodeIs( 200 );

		$response = json_decode( $I->grabResponse(), true );

		$pods = wp_list_pluck( $response['pods'], 'id' );

		sort( $pods );

		$I->assertEquals( $variation['expected'], $pods );
	}
}
