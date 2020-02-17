<?php


abstract class Tribe__REST__Headers__Base_Header {

	/**
	 * @var Tribe__REST__Headers__Base_Interface
	 */
	protected $base;

	/**
	 * Tribe__REST__Headers__Base_Header constructor.
	 *
	 * @param Tribe__REST__Headers__Base_Interface $base
	 */
	public function __construct( Tribe__REST__Headers__Base_Interface $base ) {
		$this->base = $base;
	}
}
