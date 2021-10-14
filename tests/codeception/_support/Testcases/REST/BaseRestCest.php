<?php

namespace Pods_Unit_Tests\Testcases\REST;

use Restv1Tester;

class BaseRestCest {

	/**
	 * @var \tad\WPBrowser\Module\WPLoader\FactoryStore
	 */
	protected $factory;

	/**
	 * @var string
	 */
	protected $site_url;

	/**
	 * @var string
	 */
	protected $wp_rest_url;

	/**
	 * @var string
	 */
	protected $pods_rest_url;

	/**
	 * @var string
	 */
	protected $test_rest_url;

	/**
	 * @var string
	 */
	protected $name;

	public function _before( Restv1Tester $I ) {
		$this->factory       = $I->factory();
		$this->site_url      = $I->grabSiteUrl();
		$this->wp_rest_url   = $this->site_url . '/wp-json/wp/v2/';
		$this->pods_rest_url = $this->site_url . '/wp-json/pods/v1/';

		// Reset the user to visitor before each test.
		wp_set_current_user( 0 );
	}

	public function _after( Restv1Tester $I ) {
		// Do any other tear down here.
	}

	/**
	 * Set name for snapshot.
	 *
	 * @param string $name Method identifier for snapshot.
	 */
	protected function setName( $name ) {
		$this->name = $name;
	}

	/**
	 * Get name for snapshot.
	 *
	 * @return string Method identifier for snapshot.
	 */
	protected function getName() {
		return $this->name;
	}
}