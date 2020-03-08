<?php
/**
 * Template Factory
 *
 * The parent class for managing the view methods in core and addons
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( class_exists( 'Tribe__Template_Factory' ) ) {
	return;
}

class Tribe__Template_Factory {

	/**
	 * Array of asset packages needed for this template
	 *
	 * @deprecated 4.7.18
	 *
	 * @var array
	 **/
	protected $asset_packages = array();

	/**
	 * Static variable that holds array of vendor script handles, for adding to later deps.
	 *
	 * @deprecated 4.7.18
	 *
	 * @static
	 * @var array
	 */
	protected static $vendor_scripts = array();

	/**
	 * Constant that holds the ajax hook suffix for the view
	 *
	 * @deprecated 4.7.18
	 *
	 * @static
	 * @var string
	 */
	const AJAX_HOOK = '';

	/**
	 * Run include packages, set up hooks
	 *
	 * @deprecated 4.7.18
	 *
	 * @return void
	 **/
	public function __construct() {
		$this->asset_packages();
	}

	/**
	 * Manage the asset packages defined for this template
	 *
	 * @deprecated 4.7.18
	 *
	 * @return void
	 **/
	protected function asset_packages() {
		foreach ( $this->asset_packages as $asset_package ) {
			$this->asset_package( $asset_package );
		}
	}

	/**
	 * Handles an asset package request.
	 *
	 * @deprecated 4.7.18
	 *
	 * @param string              $name          The asset name in the `hyphen-separated-format`
	 * @param array               $deps          An array of dependency handles
	 * @param string              $vendor_url    URL to vendor scripts and styles dir
	 * @param string              $prefix        MT script and style prefix
	 * @param Tribe__Main         $tec           An instance of the main plugin class
	 */
	protected static function handle_asset_package_request( $name, $deps, $vendor_url, $prefix, $tec ) {

		$asset = self::get_asset_factory_instance( $name );

		self::prepare_asset_package_request( $asset, $name, $deps, $vendor_url, $prefix, $tec );
	}

	/**
	 * initializes asset package request
	 *
	 * @deprecated 4.7.18
	 *
	 * @param object              $asset         The Tribe__*Asset object
	 * @param string              $name          The asset name in the `hyphen-separated-format`
	 * @param array               $deps          An array of dependency handles
	 * @param string              $vendor_url    URL to vendor scripts and styles dir
	 * @param string              $prefix        MT script and style prefix
	 * @param Tribe__Main         $common        An instance of the main plugin class
	 */
	protected static function prepare_asset_package_request( $asset, $name, $deps, $vendor_url, $prefix, $common ) {
		if ( ! $asset ) {
			do_action( $prefix . '-' . $name );

			return;
		}

		$asset->set_name( $name );
		$asset->set_deps( $deps );
		$asset->set_vendor_url( $vendor_url );
		$asset->set_prefix( $prefix );
		$asset->set_tec( $common );

		$asset->handle();
	}

	/**
	 * Retrieves the appropriate asset factory instance
	 *
	 * @deprecated 4.7.18
	 *
	 */
	protected static function get_asset_factory_instance( $name ) {
		$asset = Tribe__Asset__Factory::instance()->make_for_name( $name );
		return $asset;
	}

	/**
	 *
	 * @deprecated 4.7.18
	 *
	 * @param string $script_handle A registered script handle.
	 */
	public static function add_vendor_script( $script_handle ) {
		if ( in_array( $script_handle, self::$vendor_scripts ) ) {
			return;
		}
		self::$vendor_scripts[] = $script_handle;
	}

	/**
	 * @return string[] An array of registered vendor script handles.
	 */
	public static function get_vendor_scripts() {
		return self::$vendor_scripts;
	}

	/**
	 * Asset calls for vendor packages
	 *
	 * @deprecated 4.7.18
	 *
	 * @param string $name
	 * @param array  $deps Dependents
	 */
	public static function asset_package( $name, $deps = array() ) {

		$common = Tribe__Main::instance();
		$prefix = 'tribe-events';

		// setup plugin resources & 3rd party vendor urls
		$vendor_url = trailingslashit( $common->plugin_url ) . 'vendor/';

		self::handle_asset_package_request( $name, $deps, $vendor_url, $prefix, $common );
	}

	/**
	 * Returns the path to a minified version of a js or css file, if it exists.
	 * If the file does not exist, returns false.
	 *
	 * @deprecated 4.7.18
	 *
	 * @param string $url                 The path or URL to the un-minified file.
	 * @param bool   $default_to_original Whether to just return original path if min version not found.
	 *
	 * @return string|false The path/url to minified version or false, if file not found.
	 */
	public static function getMinFile( $url, $default_to_original = false ) {
		if ( ! defined( 'SCRIPT_DEBUG' ) || SCRIPT_DEBUG === false ) {
			if ( substr( $url, - 3, 3 ) == '.js' ) {
				$url_new = substr_replace( $url, '.min', - 3, 0 );
			}
			if ( substr( $url, - 4, 4 ) == '.css' ) {
				$url_new = substr_replace( $url, '.min', - 4, 0 );
			}
		}

		if ( isset( $url_new ) && file_exists( str_replace( content_url(), WP_CONTENT_DIR, $url_new ) ) ) {
			return $url_new;
		} elseif ( $default_to_original ) {
			return $url;
		} else {
			return false;
		}
	}

	/**
	 * Playing ping-pong with WooCommerce. They keep changing their script.
	 *
	 * @deprecated 4.7.18
	 *
	 * @see https://github.com/woothemes/woocommerce/issues/3623
	 */
	public static function get_placeholder_handle() {
		$placeholder_handle = 'jquery-placeholder';

		global $woocommerce;
		if (
			class_exists( 'Woocommerce' ) &&
			version_compare( $woocommerce->version, '2.0.11', '>=' ) &&
			version_compare( $woocommerce->version, '2.0.13', '<=' )
		) {
			$placeholder_handle = 'tribe-placeholder';
		}

		return $placeholder_handle;
	}
}
