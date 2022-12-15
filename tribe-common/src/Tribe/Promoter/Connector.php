<?php

/**
 * Custom class for communicating with the Promoter Auth Connector the class is created
 * early in the process and many functions that come from utils are not available during the
 * execution of the methods from this class
 *
 * @since 4.9
 */
class Tribe__Promoter__Connector {

	/**
	 * Whether the user request is currently authorized by Promoter.
	 *
	 * @since 4.9.4
	 *
	 * @var bool
	 */
	public $authorized = false;

	/**
	 * Get the base URL for interacting with the connector.
	 *
	 * @return string Base URL for interacting with the connector.
	 *
	 * @since 4.9
	 */
	public function base_url() {
		if ( defined( 'TRIBE_PROMOTER_AUTH_CONNECTOR_URL' ) ) {
			return TRIBE_PROMOTER_AUTH_CONNECTOR_URL;
		}

		return 'https://us-central1-promoter-auth-connector.cloudfunctions.net/promoterConnector/';
	}

	/**
	 * Authorize Promoter to communicate with this site.
	 *
	 * @param string $user_id      Promoter user ID.
	 * @param string $secret_key   Promoter secret key.
	 * @param string $promoter_key Promoter key (not license related).
	 * @param string $license_key  Promoter license key.
	 *
	 * @return bool Whether connector was authorized.
	 *
	 * @since 4.9
	 */
	public function authorize_with_connector( $user_id, $secret_key, $promoter_key, $license_key ) {
		$url = $this->base_url() . 'connect';

		$payload = [
			'clientSecret' => $secret_key,
			'licenseKey'   => $license_key,
			'userId'       => $user_id,
		];

		$token = \Firebase\JWT\JWT::encode( $payload, $promoter_key, 'HS256' );

		$response = $this->make_call( $url, [
			'body'      => [ 'token' => $token ],
			'sslverify' => false,
		] );

		return (bool) $response;
	}

	/**
	 * Authenticate the current request user with the Auth Connector
	 *
	 * @param string $user_id User ID.
	 *
	 * @return bool|string User ID or if promoter is authorized then it return true like a valid user.
	 *
	 * @since 4.9
	 */
	public function authenticate_user_with_connector( $user_id ) {
		$this->authorized = false;

		// If user is already authenticated no need to move forward (wp-admin) and others.
		if ( ! empty( $user_id ) ) {
			$this->authorized = true;
			return $user_id;
		}

		$token = $this->get_token();

		if ( empty( $token ) ) {
			return $user_id;
		}

		$url = $this->base_url() . 'connect/auth';

		$response = $this->make_call( $url, [
			'body'      => [ 'token' => $token ],
			'sslverify' => false,
		] );

		if ( ! $response ) {
			return $user_id;
		}

		$this->authorized = true;

		return $response;
	}

	/**
	 * Get the token either from a request or a header
	 *
	 * @since 4.9.20
	 *
	 * @return mixed
	 */
	protected function get_token() {
		$request_token = $this->get_token_from_request();

		return ( $request_token )
			? sanitize_text_field( $request_token )
			: $this->get_token_from_headers();
	}

	/**
	 * Get the token from a Request variable if present, otherwise fallback to `null`
	 *
	 * @since 4.9.20
	 *
	 * @return mixed
	 */
	protected function get_token_from_request() {
		// Used in favor of tribe_get_request_var as at this point tribe_get_request_var is not defined.
		return \Tribe__Utils__Array::get_in_any(
			[ $_GET, $_POST, $_REQUEST ],
			'tribe_promoter_auth_token'
		);
	}

	/**
	 * Get the token directly from a Bearer Authentication Header, for hosts that
	 * does not support large Query strings
	 *
	 * @since 4.9.20
	 *
	 * @return mixed
	 */
	protected function get_token_from_headers() {
		$headers = [
			'HTTP_AUTHORIZATION',
			'REDIRECT_HTTP_AUTHORIZATION',
		];

		foreach ( $headers as $header ) {
			if ( empty( $_SERVER[ $header ] ) ) {
				continue;
			}

			list( $token ) = sscanf( $_SERVER[ $header ], 'Bearer %s' );

			if ( $token ) {
				return sanitize_text_field( $token );
			}
		}
	}

	/**
	 * Notify the Promoter app of changes within this system.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @since 4.9
	 */
	public function notify_promoter_of_changes( $post_id ) {
		$post_type = get_post_type( $post_id );

		if ( ! in_array( $post_type, [ 'tribe_events', 'tribe_tickets' ], true ) ) {
			return;
		}

		$secret_key  = $this->get_secret_key();

		if ( empty( $secret_key ) ) {
			return;
		}

		/** @var Tribe__Promoter__PUE $promoter_pue */
		$promoter_pue = tribe( 'promoter.pue' );
		$license_info = $promoter_pue->get_license_info();

		if ( ! $license_info ) {
			return;
		}

		$license_key = $license_info['key'];

		$payload = [
			'licenseKey' => $license_key,
			'sourceId' => $post_id instanceof WP_Post ? $post_id->ID : $post_id,
		];

		$token = \Firebase\JWT\JWT::encode( $payload, $secret_key, 'HS256' );

		$url = $this->base_url() . 'connect/notify';

		$args = [
			'body'      => [ 'token' => $token ],
			'sslverify' => false,
		];

		$this->make_call( $url, $args );
	}

	/**
	 * Get the value for the option `tribe_promoter_auth_key`
	 *
	 * @since 4.9.12
	 *
	 * @return mixed
	 */
	public function get_secret_key() {
		$secret_key = get_option( 'tribe_promoter_auth_key' );

		/**
		 * @since 4.9.12
		 *
		 * @param string $secret_key
		 */
		return apply_filters( 'tribe_promoter_secret_key', $secret_key );
	}

	/**
	 * Make the call to the remote endpoint.
	 *
	 * @since 4.9
	 *
	 * @param array  $args Data to send.
	 *
	 * @param string $url  URL to send data to.
	 *
	 * @return string|false The response body or false if not successful.
	 *
	 */
	public function make_call( $url, $args ) {
		$response = wp_remote_post( $url, wp_parse_args( $args,  [ 'timeout' => 30 ] ) );
		$code     = wp_remote_retrieve_response_code( $response );
		$body     = wp_remote_retrieve_body( $response );

		if ( $code > 299 || is_wp_error( $response ) ) {
			do_action(
				'tribe_log',
				'debug',
				__METHOD__,
				[
					'url'           => $url,
					'args'          => $args,
					'response'      => $response,
					'response_code' => $code,
				]
			);

			return false;
		}

		return $body;
	}

	/**
	 * Check whether the user request is currently authorized by Promoter.
	 *
	 * @since 4.9.4
	 *
	 * @return bool Whether the user request is currently authorized by Promoter.
	 */
	public function is_user_authorized() {
		return $this->authorized;
	}
}
