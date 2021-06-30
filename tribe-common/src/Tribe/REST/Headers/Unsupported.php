<?php


class Tribe__REST__Headers__Unsupported extends Tribe__REST__Headers__Base_Header implements Tribe__REST__Headers__Headers_Interface {

	/**
	 * @var Tribe__REST__Main
	 */
	protected $main;

	/**
	 * Tribe__REST__Headers__Unsupported constructor.
	 *
	 * @param Tribe__REST__Headers__Base_Interface $base
	 * @param Tribe__REST__Main                    $main
	 */
	public function __construct( Tribe__REST__Headers__Base_Interface $base, Tribe__REST__Main $main ) {
		parent::__construct( $base );
		$this->main = $main;
	}

	/**
	 * Prints TEC REST API related meta on the site.
	 */
	public function add_header() {
		// no-op
	}

	/**
	 * Sends TEC REST API related headers.
	 */
	public function send_header() {
		if ( headers_sent() ) {
			return;
		}

		header( $this->base->get_api_version_header() . ': unsupported' );
	}
}
