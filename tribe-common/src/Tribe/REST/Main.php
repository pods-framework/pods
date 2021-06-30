<?php


/**
 * Class Tribe__REST__Main
 *
 * The main entry point for a The Events Calendar REST API implementation.
 *
 * This class should not contain business logic and merely set up and start the REST API support.
 */
abstract class Tribe__REST__Main {

	/**
	 * The Events Calendar REST APIs URL namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'tribe';

	/**
	 * Returns the namespace of The Events Calendar REST APIs.
	 *
	 * @return string
	 */
	public function get_namespace() {
		return $this->namespace;
	}

	/**
	 * Returns the REST API URL prefix.
	 *
	 * @return string The REST API URL prefix.
	 */
	public function get_url_prefix() {
		$use_builtin = $this->use_builtin();

		if ( $use_builtin ) {
			$prefix = rest_get_url_prefix();
		} else {
			$prefix = apply_filters( 'rest_url_prefix', 'wp-json' );
		}

		$default_tec_prefix = $this->namespace . '/' . trim( $this->url_prefix(), '/' );
		$prefix = rtrim( $prefix, '/' ) . '/' . trim( $default_tec_prefix, '/' );

		/**
		 * Filters the TEC REST API URL prefix
		 *
		 * @param string $prefix             The complete URL prefix.
		 * @param string $default_tec_prefix The default URL prefix appended to the REST URL by The Events Calendar.
		 */
		return apply_filters( 'tribe_events_rest_url_prefix', $prefix, $default_tec_prefix );
	}

	/**
	 * Retrieves the URL to a TEC REST endpoint on a site.
	 *
	 * Note: The returned URL is NOT escaped.
	 *
	 * @global WP_Rewrite $wp_rewrite
	 *
	 * @param string      $path    Optional. TEC REST route. Default '/'.
	 * @param string      $scheme  Optional. Sanitization scheme. Default 'rest'.
	 * @param int         $blog_id Optional. Blog ID. Default of null returns URL for current blog.
	 *
	 * @return string Full URL to the endpoint.
	 */
	public function get_url( $path = '/', $scheme = 'rest', $blog_id = null ) {
		if ( empty( $path ) ) {
			$path = '/';
		}

		$tec_path = '/' . trim( $this->namespace, '/' ) . $this->url_prefix() . '/' . ltrim( $path, '/' );

		if ( $this->use_builtin() ) {
			$url = get_rest_url( $blog_id, $tec_path, $scheme );
		} else {
			if ( ( is_multisite() && get_blog_option( $blog_id, 'permalink_structure' ) ) || get_option( 'permalink_structure' ) ) {
				global $wp_rewrite;

				if ( $wp_rewrite->using_index_permalinks() ) {
					$url = get_home_url( $blog_id, $wp_rewrite->index . '/' . self::get_url_prefix(), $scheme );
				} else {
					$url = get_home_url( $blog_id, self::get_url_prefix(), $scheme );
				}

				$url .= '/' . ltrim( $path, '/' );
			} else {
				$url = get_home_url( $blog_id, 'index.php', $scheme );

				$url = add_query_arg( 'rest_route', $tec_path, $url );
			}

			if ( is_ssl() ) {
				// If the current host is the same as the REST URL host, force the REST URL scheme to HTTPS.
				if ( $_SERVER['SERVER_NAME'] === parse_url( get_home_url( $blog_id ), PHP_URL_HOST ) ) {
					$url = set_url_scheme( $url, 'https' );
				}
			}
		}

		/**
		 * Filters The Events Calendar REST URL.
		 *
		 * @param string $url     TEC REST URL.
		 * @param string $path    REST route.
		 * @param int    $blog_id Blog ID.
		 * @param string $scheme  Sanitization scheme.
		 */
		return apply_filters( 'tribe_rest_url', $url, $path, $blog_id, $scheme );
	}

	/**
	 * Whether built-in WP REST API functions and functionalities should/can be used or not.
	 *
	 * @return bool
	 */
	protected function use_builtin() {
		/**
		 * Filters whether builtin WordPress REST API functions should be used or not if available.
		 */
		$use_builtin = apply_filters( 'tribe_events_rest_use_builtin', true );

		return $use_builtin && function_exists( 'get_rest_url' );
	}

	/**
	 * Returns the REST API URL prefix that will be appended to the namespace.
	 *
	 * The prefix should be in the `/some/path` format.
	 *
	 * @return string
	 */
	abstract protected function url_prefix();

	/**
	 * Returns the string indicating the REST API version.
	 *
	 * @return string
	 */
	abstract public function get_version();

	/**
	 * Returns the URL where the API users will find the API documentation.
	 *
	 * @return string
	 */
	abstract public function get_reference_url();
}
