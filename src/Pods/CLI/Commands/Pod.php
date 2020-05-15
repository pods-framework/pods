<?php

namespace Pods\CLI\Commands;

/**
 * Class Pod
 *
 * @since 2.8
 */
class Pod extends Base {

	/**
	 * @var string
	 */
	protected $command = 'pod';

	/**
	 * Setup endpoint object.
	 *
	 * @since 2.8
	 */
	public function __construct() {
		$this->endpoint_archive     = tribe( 'pods.rest-v1.endpoints.pods' );
		$this->endpoint_single      = tribe( 'pods.rest-v1.endpoints.pod' );
		$this->endpoint_single_slug = tribe( 'pods.rest-v1.endpoints.pod-slug' );
	}
}
