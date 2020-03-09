<?php

/**
 * Class Tribe__Promoter__View
 */
class Tribe__Promoter__View extends Tribe__Template {

	/**
	 * Tribe__Promoter__View constructor.
	 *
	 * @since 4.9
	 */
	public function __construct() {
		$this->set_template_origin( Tribe__Main::instance() );
		$this->set_template_folder( 'src/views/promoter' );
		$this->set_template_context_extract( true );
		$this->set_template_folder_lookup( true );
	}

	/**
	 * Add the rewrite rules and tags.
	 *
	 * @since 4.9
	 */
	public function add_rewrites() {
		add_rewrite_rule( 'tribe-promoter-auth/?$', 'index.php?tribe-promoter-auth-check=1', 'top' );
		add_rewrite_tag( '%tribe-promoter-auth-check%', '([^&]+)' );
	}

	/**
	 * Get the redirect URL for finishing onboarding
	 *
	 * @since 4.9.6
	 *
	 * @return string Redirect URL for completing onboarding.
	 */
	public function authorized_redirect_url() {
		$url = 'https://promoter.theeventscalendar.com/welcome/review';

		if ( defined( 'TRIBE_PROMOTER_AUTHORIZED_REDIRECT_URL' ) ) {
			$url = TRIBE_PROMOTER_AUTHORIZED_REDIRECT_URL;
		}

		/**
		 * The url for redirecting in order to complete onboarding
		 *
		 * @since 4.9.6
		 *
		 * @param string $url Redirect URL.
		 */
		return apply_filters( 'tribe_promoter_authorized_redirect_url', $url );
	}

	/**
	 * Display the auth check page when the correct permalink is loaded.
	 *
	 * @since 4.9
	 */
	public function display_auth_check_view() {
		global $wp_query;

		$promoter_key = tribe_get_request_var( 'promoter_key' );
		$license_key  = tribe_get_request_var( 'license_key' );

		if ( empty( $promoter_key ) || empty( $wp_query->query_vars['tribe-promoter-auth-check'] ) ) {
			return;
		}

		$is_admin   = is_user_logged_in() && current_user_can( 'manage_options' );
		$authorized = false;
		$auth_error = false;

		if ( $is_admin && ! empty( $_POST['promoter_authenticate'] ) ) {
			/** @var Tribe__Promoter__Auth $promoter_auth */
			$promoter_auth = tribe( 'promoter.auth' );
			$authorized    = $promoter_auth->authorize_with_connector();
			$auth_error    = ! $authorized;
		}

		if ( $authorized ) {
			wp_redirect( esc_url_raw( $this->authorized_redirect_url() ) );
		} else {
			$this->template( 'auth', [
				'authorized'   => $authorized,
				'auth_error'   => $auth_error,
				'logged_in'    => is_user_logged_in(),
				'admin'        => $is_admin,
				'promoter_key' => $promoter_key,
				'license_key'  => $license_key,
			], true );
		}

		tribe_exit();
	}

}
