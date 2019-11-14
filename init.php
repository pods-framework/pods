<?php
/*
Plugin Name: Pods - Custom Content Types and Fields
Plugin URI: https://pods.io/
Description: Pods is a framework for creating, managing, and deploying customized content types and fields
Version: 2.7.16.2
Author: Pods Framework Team
Author URI: https://pods.io/about/
Text Domain: pods
GitHub Plugin URI: https://github.com/pods-framework/pods

Copyright 2009-2019  Pods Foundation, Inc  (email : contact@podsfoundation.org)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * @package Pods\Global
 */

// Prevent conflicts with Pods 1.x
if ( defined( 'PODS_VERSION' ) || defined( 'PODS_DIR' ) ) {
	add_action( 'init', 'pods_deactivate_pods_1_x' );
	add_action( 'init', 'pods_deactivate_pods_ui' );
} else {
	// Current version
	define( 'PODS_VERSION', '2.7.16.2' );

	// Version tracking between DB updates themselves
	define( 'PODS_DB_VERSION', '2.3.5' );

	// This should always be -2 versions behind the latest WP release
	// To be updated each Major x.x Pods release
	if ( ! defined( 'PODS_WP_VERSION_MINIMUM' ) ) {
		define( 'PODS_WP_VERSION_MINIMUM', '4.5' );
	}

	// This should match minimum WP requirements or usage (90%+)
	// Found at: https://wordpress.org/about/stats/
	if ( ! defined( 'PODS_PHP_VERSION_MINIMUM' ) ) {
		define( 'PODS_PHP_VERSION_MINIMUM', '5.3' );
	}

	// This should match minimum WP requirements or usage (90%+)
	// Found at: https://wordpress.org/about/stats/
	// Using 5.1 for now, many RedHat servers aren't EOL yet and they backport security releases
	if ( ! defined( 'PODS_MYSQL_VERSION_MINIMUM' ) ) {
		define( 'PODS_MYSQL_VERSION_MINIMUM', '5.1' );
	}

	define( 'PODS_SLUG', plugin_basename( __FILE__ ) );
	define( 'PODS_URL', plugin_dir_url( __FILE__ ) );
	define( 'PODS_DIR', plugin_dir_path( __FILE__ ) );

	// Prevent conflicts with old Pods UI plugin
	if ( function_exists( 'pods_ui_manage' ) ) {
		add_action( 'init', 'pods_deactivate_pods_ui' );
	} else {
		global $pods, $pods_init, $pods_form;

		require_once PODS_DIR . 'includes/classes.php';
		require_once PODS_DIR . 'includes/data.php';
		require_once PODS_DIR . 'includes/general.php';

		if ( ! defined( 'PODS_MEDIA' ) || PODS_MEDIA ) {
			require_once PODS_DIR . 'includes/media.php';
		}

		if ( ! defined( 'SHORTINIT' ) || ! SHORTINIT ) {
			if ( pods_allow_deprecated() ) {
				require_once PODS_DIR . 'deprecated/deprecated.php';
			}

			if ( false !== pods_compatibility_check() ) {
				$pods_form = pods_form();

				if ( ! is_network_admin() ) {
					$pods_init = pods_init();
				}
			}
		}
	}
}

/**
 * Deactivate Pods 1.x or other Pods plugins
 */
function pods_deactivate_pods_1_x() {

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
 * Deactivate Pods UI plugin
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
