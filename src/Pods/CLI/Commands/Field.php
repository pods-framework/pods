<?php

namespace Pods\CLI\Commands;

/**
 * Class Field
 *
 * @since 2.8
 */
class Field extends Base {

	/**
	 * @var string
	 */
	protected $command = 'field';

	/**
	 * Setup endpoint object.
	 *
	 * @since 2.8
	 */
	public function __construct() {
		$this->endpoint = tribe( 'pods.rest-v1.endpoints.fields' );
	}
}
