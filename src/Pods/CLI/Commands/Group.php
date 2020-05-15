<?php

namespace Pods\CLI\Commands;

/**
 * Class Group
 *
 * @since 2.8
 */
class Group extends Base {

	/**
	 * @var string
	 */
	protected $command = 'group';

	/**
	 * Setup endpoint object.
	 *
	 * @since 2.8
	 */
	public function __construct() {
		$this->endpoint_archive     = tribe( 'pods.rest-v1.endpoints.groups' );
		$this->endpoint_single      = tribe( 'pods.rest-v1.endpoints.group' );
		$this->endpoint_single_slug = tribe( 'pods.rest-v1.endpoints.group-slug' );
	}
}
