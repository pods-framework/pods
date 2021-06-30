<?php

/**
 * Class Tribe__REST__Headers__Headers_Interface
 *
 * Handles headers and header equivalent to be printed/sent in responses.
 */
interface Tribe__REST__Headers__Headers_Interface {
	/**
	 * Prints the REST API related meta on the site.
	 */
	public function add_header();

	/**
	 * Sends the REST API related headers.
	 */
	public function send_header();
}
