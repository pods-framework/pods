<?php

namespace Pods\CLI\Commands;

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * Class Group
 *
 * @since 2.8.0
 */
class Group extends Base {

	/**
	 * @var string
	 */
	protected $command = 'group';

	/**
	 * Setup endpoint object.
	 *
	 * @since 2.8.0
	 */
	public function __construct() {
		$this->endpoint_archive     = pods_container( 'pods.rest-v1.endpoints.groups' );
		$this->endpoint_single      = pods_container( 'pods.rest-v1.endpoints.group' );
		$this->endpoint_single_slug = pods_container( 'pods.rest-v1.endpoints.group-slug' );
	}
}
