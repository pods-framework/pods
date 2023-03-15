<?php
/**
 * Pods - Custom Content Types and Fields
 *
 * @package   Pods
 * @author    Pods Framework Team
 * @copyright 2023 Pods Foundation, Inc
 * @license   GPL v2 or later
 *
 * Plugin Name:       Pods - Custom Content Types and Fields
 * Plugin URI:        https://pods.io/
 * Description:       Pods is a framework for creating, managing, and deploying customized content types and fields
 * Version:           2.9.13
 * Author:            Pods Framework Team
 * Author URI:        https://pods.io/about/
 * Text Domain:       pods
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.7
 * Requires PHP:      5.6
 * GitHub Plugin URI: https://github.com/pods-framework/pods
 * Primary Branch:    main
 */

/*
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 */

if ( defined( 'PODS_VERSION' ) || defined( 'PODS_DIR' ) ) {
	// Prevent conflicts with Pods 1.x and Pods UI plugins.
	add_action( 'init', 'pods_deactivate_pods_duplicate' );
	add_action( 'init', 'pods_deactivate_pods_ui' );
} else {
	// Current version.
	define( 'PODS_VERSION', '2.9.13' );

	// Current database version, this is the last version the database changed.
	define( 'PODS_DB_VERSION', '2.3.5' );

	/**
	 * We aim to keep this as recent as possible to avoid ongoing React/Gutenberg compatibility problems.
	 *
	 * This should always be -2 versions behind the latest WP release. Example: 5.5 if 5.7 is current.
	 *
	 * To be updated each Major x.x Pods release.
	 */
	if ( ! defined( 'PODS_WP_VERSION_MINIMUM' ) ) {
		define( 'PODS_WP_VERSION_MINIMUM', '5.7' );
	}

	/**
	 * This should match minimum WP requirements or usage of 90%+.
	 *
	 * Found at: https://wordpress.org/about/stats/
	 *
	 * Next planned minimum PHP version: 7.2 (to match WooCommerce and others pushing WP forward).
	 */
	if ( ! defined( 'PODS_PHP_VERSION_MINIMUM' ) ) {
		define( 'PODS_PHP_VERSION_MINIMUM', '5.6' );
	}

	/**
	 * This should match minimum WP requirements or usage of 90%+.
	 *
	 * Found at: https://wordpress.org/about/stats/
	 */
	if ( ! defined( 'PODS_MYSQL_VERSION_MINIMUM' ) ) {
		define( 'PODS_MYSQL_VERSION_MINIMUM', '5.5' );
	}

	define( 'PODS_FILE', __FILE__ );
	define( 'PODS_SLUG', plugin_basename( __FILE__ ) );
	define( 'PODS_URL', plugin_dir_url( __FILE__ ) );
	define( 'PODS_DIR', plugin_dir_path( __FILE__ ) );

	// Prevent conflicts with old Pods UI plugin
	if ( function_exists( 'pods_ui_manage' ) ) {
		add_action( 'init', 'pods_deactivate_pods_ui' );
	} else {
		global $pods, $pods_init, $pods_form;

		// Init custom autoloader.
		require_once PODS_DIR . 'classes/PodsInit.php';

		spl_autoload_register( array( 'PodsInit', 'autoload_class' ) );

		// Include global functions.
		require_once PODS_DIR . 'includes/classes.php';
		require_once PODS_DIR . 'includes/data.php';
		require_once PODS_DIR . 'includes/forms.php';
		require_once PODS_DIR . 'includes/general.php';

		// Maybe include media functions.
		if ( ! defined( 'PODS_MEDIA' ) || PODS_MEDIA ) {
			require_once PODS_DIR . 'includes/media.php';
		}

		// Maybe run full init.
		if ( ! defined( 'SHORTINIT' ) || ! SHORTINIT ) {
			// Maybe include deprecated classes / functions.
			if ( pods_allow_deprecated() ) {
				require_once PODS_DIR . 'deprecated/deprecated.php';
			}

			// Check if minimum required versions are met.
			if ( false !== pods_compatibility_check() ) {
				$pods_form = pods_form();

				// If not on network admin, run full init.
				if ( ! is_network_admin() ) {
					$pods_init = pods_init();
				}
			}
		}
	}
}

/**
 * Deactivate this version of Pods if Pods is already included.
 *
 * @since 2.8.0
 */
function pods_deactivate_pods_duplicate() {
	if ( defined( 'PODS_VERSION' ) && defined( 'PODS_DIR' ) && file_exists( untrailingslashit( PODS_DIR ) . '/init.php' ) ) {
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		deactivate_plugins( realpath( untrailingslashit( PODS_DIR ) . '/init.php' ) );

		if ( ! headers_sent() && ( ! function_exists( 'pods_ui_manage' ) && ! file_exists( WP_CONTENT_DIR . 'plugins/pods-ui/pods-ui.php' ) ) ) {
			wp_redirect( $_SERVER['REQUEST_URI'] );
			die();
		}
	}
}

/**
 * Deactivate Pods UI plugin if already included.
 *
 * @since 2.0.0
 */
function pods_deactivate_pods_ui() {
	if ( function_exists( 'pods_ui_manage' ) && file_exists( WP_CONTENT_DIR . 'plugins/pods-ui/pods-ui.php' ) ) {
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		deactivate_plugins( realpath( WP_CONTENT_DIR . 'plugins/pods-ui/pods-ui.php' ) );

		if ( ! headers_sent() ) {
			wp_redirect( $_SERVER['REQUEST_URI'] );
			die();
		}
	}
}
