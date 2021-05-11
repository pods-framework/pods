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
	 * @since 4.9
	 *
	 * @param Tribe__Promoter__Connector $connector Connector object.
	 * @return void
	 */
	public function __construct( Tribe__Promoter__Connector $connector ) {
		$this->connector = $connector;
	}

	/**
	 * Register the promoter auth key as part of the settings in order to make it available into the REST API.
	 *
	 * @since 4.12.6
	 *
	 * @return void
	 */
	public function register_setting() {
		register_setting(
			'options',
			'tribe_promoter_auth_key',
			[
				'type'              => 'string',
				'show_in_rest'      => true,
				'description'       => __( 'Promoter Key', 'tribe-common' ),
				'sanitize_callback' => 'sanitize_text_field',
			]
		);
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

		_deprecated_function( __METHOD__, '4.12.6' );

		return empty( $secret_key ) ? $this->generate_secret_key() : $secret_key;
	}

	/**
	 * Authorize the request with the Promoter Connector.
	 *
	 * @since 4.9
	 *
	 * @return bool Whether the request was authorized successfully.
	 */
	public function authorize_with_connector() {
		$secret_key   = $this->generate_secret_key();
		$promoter_key = tribe_get_request_var( 'promoter_key' );
		$license_key  = tribe_get_request_var( 'license_key' );

		// send request to auth connector
		$result = $this->connector->authorize_with_connector( get_current_user_id(), $secret_key, $promoter_key, $license_key );

		// If the secret was not stored correctly on Connector Application, remove it!
		if ( ! $result ) {
			delete_option( 'tribe_promoter_auth_key' );
		}

		return $result;
	}

	/**
	 * Grab the WP constant and store it as the auth key, if none exists or is it empty
	 * it creates a dynamic one.
	 *
	 * @since 4.9.12
	 *
	 * @since 4.9
	 *
	 * @return string The secret key.
	 */
	public function generate_secret_key() {

		$salt = wp_generate_password( 6 );

		if ( defined( 'AUTH_KEY' ) ) {
			$key = AUTH_KEY;
		} else {
			$key = wp_generate_password( 25 );
		}

		$key = sha1( $salt . get_current_blog_id() . $key . get_bloginfo( 'url' ) );

		update_option( 'tribe_promoter_auth_key', $key );

		return $key;
	}
}
