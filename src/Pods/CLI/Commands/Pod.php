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
		$this->endpoint = tribe( 'pods.rest-v1.endpoints.pods' );
	}
}
