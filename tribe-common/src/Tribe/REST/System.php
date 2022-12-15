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

	/**
	 * Determines if we are coming from a REST API request.
	 *
	 * @since 5.0.0
	 *
	 * @return bool
	 */
	public static function is_rest_api() {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			// Probably a CLI request
			return false;
		}

		$rest_prefix         = trailingslashit( rest_get_url_prefix() );
		$is_rest_api_request = strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) !== false;

		return $is_rest_api_request;
	}
}
