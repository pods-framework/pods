<?php


class Tribe__REST__System {

	/**
	 * Whether the WP installation supports WP REST API or not.
	 *
	 * @return bool
	 */
	public function supports_wp_rest_api() {
		return function_exists( 'get_rest_url' );
	}
}
