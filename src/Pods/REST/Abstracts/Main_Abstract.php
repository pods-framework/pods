<?php

namespace Pods\REST\Abstracts;

use WP_Rewrite;

/**
 * Main abstract.
 *
 * @credit The Events Calendar team - https://github.com/the-events-calendar/tribe-common
 *
 * @since 3.0
 */
abstract class Main_Abstract {

	/**
	 * The REST APIs URL namespace.
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	protected $namespace = 'pods';

	/**
	 * Returns the namespace of REST APIs.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_namespace() {
		return $this->namespace;
	}

	/**
	 * Returns the REST API URL prefix.
	 *
	 * @since 3.0
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

		$default_pods_prefix = $this->namespace . '/' . trim( $this->url_prefix(), '/' );
		$prefix = rtrim( $prefix, '/' ) . '/' . trim( $default_pods_prefix, '/' );

		/**
		 * Allow filtering the REST API URL prefix.
		 *
		 * @since 3.0
		 *
		 * @param string $prefix             The complete URL prefix.
		 * @param string $default_pods_prefix The default URL prefix appended to the REST URL.
		 */
		return apply_filters( 'pods_rest_url_prefix', $prefix, $default_pods_prefix );
	}

	/**
	 * Retrieves the URL to a REST endpoint on a site.
	 *
	 * Note: The returned URL is NOT escaped.
	 *
	 * @since 3.0
	 *
	 * @global WP_Rewrite $wp_rewrite
	 *
	 * @param string      $path    Optional. The REST route. Default '/'.
	 * @param string      $scheme  Optional. The sanitization scheme. Default 'rest'.
	 * @param int         $blog_id Optional. The blog ID. Default of null returns URL for current blog.
	 *
	 * @return string Full URL to the endpoint.
	 */
	public function get_url( $path = '/', $scheme = 'rest', $blog_id = null ) {
		if ( empty( $path ) ) {
			$path = '/';
		}

		$pods_path = '/' . trim( $this->namespace, '/' ) . $this->url_prefix() . '/' . ltrim( $path, '/' );

		if ( $this->use_builtin() ) {
			$url = get_rest_url( $blog_id, $pods_path, $scheme );
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

				$url = add_query_arg( 'rest_route', $pods_path, $url );
			}

			if ( is_ssl() ) {
				// If the current host is the same as the REST URL host, force the REST URL scheme to HTTPS.
				if ( $_SERVER['SERVER_NAME'] === parse_url( get_home_url( $blog_id ), PHP_URL_HOST ) ) {
					$url = set_url_scheme( $url, 'https' );
				}
			}
		}

		/**
		 * Allow filtering the REST URL.
		 *
		 * @since 3.0
		 *
		 * @param string $url     The REST URL.
		 * @param string $path    The REST route.
		 * @param int    $blog_id The blog ID.
		 * @param string $scheme  The sanitization scheme.
		 */
		return apply_filters( 'pods_rest_url', $url, $path, $blog_id, $scheme );
	}

	/**
	 * Whether built-in WP REST API functions and functionalities should/can be used or not.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	protected function use_builtin() {
		/**
		 * Allow filtering whether builtin WordPress REST API functions should be used or not.
		 *
		 * @since 3.0
		 *
		 * @param bool $use_builtin Whether builtin WordPress REST API functions should be used or not.
		 */
		$use_builtin = apply_filters( 'pods_rest_use_builtin', true );

		return $use_builtin && function_exists( 'get_rest_url' );
	}

	/**
	 * Returns the REST API URL prefix that will be appended to the namespace.
	 *
	 * The prefix should be in the `/some/path` format.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	abstract protected function url_prefix();

	/**
	 * Returns the string indicating the REST API version.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	abstract public function get_version();

	/**
	 * Returns the URL where the API users will find the API documentation.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	abstract public function get_reference_url();
}
