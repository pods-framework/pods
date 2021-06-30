<?php


/**
 * Interface Tribe__REST__Headers__Base_Interface
 *
 * Provides basic information for the
 */
interface Tribe__REST__Headers__Base_Interface {
	/**
	 * Returns the header that the REST API will print on the page head to report
	 * its version.
	 *
	 * @return string
	 */
	public function get_api_version_header();

	/**
	 * Returns the header the REST API will print on the page head to report its root
	 * url.
	 *
	 * @return string
	 */
	public function get_api_root_header();

	/**
	 * Returns the `name` of the meta tag that will be printed on the page to indicate
	 * the REST API version.
	 *
	 * @return string
	 */
	public function get_api_version_meta_name();

	/**
	 * Returns the REST API URL.
	 *
	 * @return string
	 */
	public function get_rest_url();

	/**
	 * Returns the header the REST API will print on the page head to report its origin
	 * url. Normaly the home_url()

	 *
	 * @return string
	 */
	public function get_api_origin_header();
}
