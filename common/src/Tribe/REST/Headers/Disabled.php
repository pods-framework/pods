<?php


class Tribe__REST__Headers__Disabled extends Tribe__REST__Headers__Base_Header implements Tribe__REST__Headers__Headers_Interface {

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

		header( $this->base->get_api_version_header() . ': disabled' );
	}
}
