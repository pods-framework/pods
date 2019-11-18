<?php

namespace Pods\REST\V1;

use Tribe__REST__Main as REST__Main;

/**
 * Class Main
 *
 * The main entry point for the REST API.
 *
 * This class should not contain business logic and merely set up and start the REST API support.
 *
 * @since 2.8
 */
class Main extends REST__Main {

	/**
	 * REST API URL prefix.
	 *
	 * This prefix is appended to the REST API URL ones.
	 *
	 * @var string
	 */
	protected $url_prefix = '/pods/v1';

	/**
	 * @var array
	 */
	protected $registered_endpoints = [];

	/**
	 * Returns the semantic version for REST API
	 *
	 * @since 2.8
	 *
	 * @return string
	 */
	public function get_semantic_version() {
		return '1.0.0';
	}

	/**
	 * Returns the string indicating the REST API version.
	 *
	 * @since 2.8
	 *
	 * @return string
	 */
	public function get_version() {
		return 'v1';
	}

	/**
	 * Returns the REST API URL prefix that will be appended to the namespace.
	 *
	 * The prefix should be in the `/some/path` format.
	 *
	 * @since 2.8
	 *
	 * @return string
	 */
	protected function url_prefix() {
		return $this->url_prefix;
	}

}
