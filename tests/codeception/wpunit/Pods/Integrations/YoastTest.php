<?php

namespace Pods_Unit_Tests\Integrations;

/**
 * @package Pods_Unit_Tests
 * @group   pods_acceptance_tests
 * @group   pods-issue-7288
 */
class YoastTest extends \Pods_Unit_Tests\Pods_UnitTestCase {

	public function test_add_post_type_supports_no_yoast_returns_unchanged(): void {
		$yoast      = new \Pods\Integrations\Yoast();
		$supports   = [
			'supports_title' => [
				'name'  => 'supports_title',
				'label' => 'Title',
				'type'  => 'boolean',
			],
		];

		$this->assertSame( $supports, $yoast->add_post_type_supports( $supports ) );
	}

	public function test_add_post_type_supports_with_yoast_adds_toggle(): void {
		if ( ! defined( 'WPSEO_VERSION' ) ) {
			define( 'WPSEO_VERSION', '20.0' );
		}

		$yoast    = new \Pods\Integrations\Yoast();
		$supports = $yoast->add_post_type_supports( [] );

		$this->assertArrayHasKey( 'supports_yoast_seo', $supports );
		$this->assertSame( 'supports_yoast_seo', $supports['supports_yoast_seo']['name'] );
		$this->assertSame( 'boolean', $supports['supports_yoast_seo']['type'] );
		$this->assertNotEmpty( $supports['supports_yoast_seo']['label'] );
	}

	public function test_add_post_type_supports_preserves_existing_supports(): void {
		if ( ! defined( 'WPSEO_VERSION' ) ) {
			define( 'WPSEO_VERSION', '20.0' );
		}

		$yoast    = new \Pods\Integrations\Yoast();
		$supports = [
			'supports_title' => [
				'name'  => 'supports_title',
				'label' => 'Title',
				'type'  => 'boolean',
			],
		];

		$result = $yoast->add_post_type_supports( $supports );

		$this->assertArrayHasKey( 'supports_title', $result );
		$this->assertArrayHasKey( 'supports_yoast_seo', $result );
	}
}
