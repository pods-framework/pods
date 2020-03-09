<?php

/**
 * Custom class for authenticating with the Promoter Connector.
 *
 * @since 4.9
 */
class Tribe__Promoter__Auth {

	/**
	 * @var Tribe__Promoter__Connector $connector
	 */
	private $connector;

	/**
	 * Tribe__Promoter__Auth constructor.
	 *
	 * @param Tribe__Promoter__Connector $connector Connector object.
	 *
	 * @since 4.9
	 */
	public function __construct( Tribe__Promoter__Connector $connector ) {
		$this->connector = $connector;
	}

	/**
	 * Add an update the KEY used for promoter during the connection.
	 *
	 * @since 4.9.12
	 *
	 * @param $secret_key
	 *
	 * @return string
	 */
	public function filter_promoter_secret_key( $secret_key ) {
		return empty( $secret_key ) ? $this->generate_secret_key() : $secret_key;
	}

	/**
	 * Authorize the request with the Promoter Connector.
	 *
	 * @return bool Whether the request was authorized successfully.
	 *
	 * @since 4.9
	 */
	public function authorize_with_connector() {
		$secret_key   = $this->generate_secret_key();
		$promoter_key = tribe_get_request_var( 'promoter_key' );
		$license_key  = tribe_get_request_var( 'license_key' );

		// send request to auth connector
		return $this->connector->authorize_with_connector( get_current_user_id(), $secret_key, $promoter_key, $license_key );
	}

	/**
	 * Grab the WP constant and store it as the auth key, if none exists or is it empty
	 * it creates a dynamic one.
	 *
	 * @since 4.9.12
	 *
	 * @return string The secret key.
	 *
	 * @since 4.9
	 */
	public function generate_secret_key() {
		$key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';

		if ( empty( $key ) ) {
			$key = $this->generate_key();
		}

		update_option( 'tribe_promoter_auth_key', $key );

		return $key;
	}

	/**
	 * Create a custom key to be usead as tribe_promoter_auth_key
	 *
	 * @since 4.9.12
	 *
	 * @return string
	 */
	private function generate_key() {
		$base = bin2hex( $this->get_random_byes() );
		$to_hash = sprintf( '%s%s%s', get_bloginfo( 'name' ),  get_bloginfo( 'url' ), uniqid() );
		return $base . hash( 'md5', $to_hash );
	}

	/**
	 * Add function to get a random set of bytes to be used as Token
	 *
	 * @since 4.9.12
	 *
	 * @param int $length
	 *
	 * @return string
	 */
	private function get_random_byes( $length = 16 ) {
		if ( function_exists( 'random_bytes' ) ) {
			try {
				return random_bytes( $length );
			} catch ( Exception $e ) {
				return uniqid();
			}
		}

		if ( function_exists( 'openssl_random_pseudo_bytes' ) ) {
			return openssl_random_pseudo_bytes( $length );
		}

		return uniqid();
	}
}
