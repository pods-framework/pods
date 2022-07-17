<?php

namespace Pods\CLI\Commands;

/**
 * Class Field
 *
 * @since 2.8.0
 */
class Field extends Base {

	/**
	 * @var string
	 */
	protected $command = 'field';

	/**
	 * Setup endpoint object.
	 *
	 * @since 2.8.0
	 */
	public function __construct() {
		$this->endpoint_archive     = pods_container( 'pods.rest-v1.endpoints.fields' );
		$this->endpoint_single      = pods_container( 'pods.rest-v1.endpoints.field' );
		$this->endpoint_single_slug = pods_container( 'pods.rest-v1.endpoints.field-slug' );
	}
}
