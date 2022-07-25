<?php

namespace Pods\CLI\Commands;

/**
 * Class Pod
 *
 * @since 2.8.0
 */
class Pod extends Base {

	/**
	 * @var string
	 */
	protected $command = 'pod';

	/**
	 * Setup endpoint object.
	 *
	 * @since 2.8.0
	 */
	public function __construct() {
		$this->endpoint_archive     = pods_container( 'pods.rest-v1.endpoints.pods' );
		$this->endpoint_single      = pods_container( 'pods.rest-v1.endpoints.pod' );
		$this->endpoint_single_slug = pods_container( 'pods.rest-v1.endpoints.pod-slug' );
	}
}
