<?php
/*
Plugin Name: Pods - Custom Content Types and Fields
Plugin URI: http://pods.io/
Description: Pods is a framework for creating, managing, and deploying customized content types and fields
Version: 2.3.18
Author: Pods Framework Team
Author URI: http://pods.io/about/
Text Domain: pods
Domain Path: /languages/

Copyright 2009-2013  Pods Foundation, Inc  (email : contact@podsfoundation.org)

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
}
else {
    // Current version
    define( 'PODS_VERSION', '2.3.18' );

    // Version tracking between DB updates themselves
    define( 'PODS_DB_VERSION', '2.3.5' );

    if ( !defined( 'PODS_GITHUB_UPDATE' ) ) {
        define( 'PODS_GITHUB_UPDATE', false );
	}

    if ( !defined( 'PODS_GITHUB_BRANCH' ) ) {
        define( 'PODS_GITHUB_BRANCH', '2.x' );
	}

    if ( !defined( 'PODS_WP_VERSION_MINIMUM' ) ) {
        define( 'PODS_WP_VERSION_MINIMUM', '3.4' );
	}

    if ( !defined( 'PODS_PHP_VERSION_MINIMUM' ) ) {
        define( 'PODS_PHP_VERSION_MINIMUM', '5.2.4' );
	}

    if ( !defined( 'PODS_MYSQL_VERSION_MINIMUM' ) ) {
        define( 'PODS_MYSQL_VERSION_MINIMUM', '5.0' );
	}

    define( 'PODS_SLUG', plugin_basename( __FILE__ ) );
    define( 'PODS_URL', plugin_dir_url( __FILE__ ) );
    define( 'PODS_DIR', plugin_dir_path( __FILE__ ) );

    // Prevent conflicts with old Pods UI plugin
    if ( function_exists( 'pods_ui_manage' ) )
        add_action( 'init', 'pods_deactivate_pods_ui' );
    else {
        global $pods, $pods_init, $pods_form;

        require_once( PODS_DIR . 'includes/classes.php' );
        require_once( PODS_DIR . 'includes/data.php' );
        require_once( PODS_DIR . 'includes/general.php' );

        if ( !defined( 'PODS_MEDIA' ) || PODS_MEDIA )
            require_once( PODS_DIR . 'includes/media.php' );

        // @todo Allow user to opt-in to future betas easily
        if ( PODS_GITHUB_UPDATE ) {
            $update = admin_url( 'update.php' );
            $update = str_replace( get_site_url(), '', $update );

            $update_network = network_admin_url( 'update.php' );
            $update_network = str_replace( get_site_url(), '', $update_network );

            if ( is_admin() &&
                 ( isset( $_GET[ 'pods_force_refresh' ] ) ||
                   ( 'update-selected' == pods_var( 'action' ) &&
                     ( false !== strpos( $_SERVER[ 'REQUEST_URI' ], $update ) ||
                       false !== strpos( $_SERVER[ 'REQUEST_URI' ], $update_network ) ) ) ) ) {

                // Configuration
                $user = 'pods-framework';
                $repo = 'pods';
                $branch = PODS_GITHUB_BRANCH;

                // GitHub Plugin Updater
                // https://github.com/jkudish/WordPress-GitHub-Plugin-Updater
                require_once( PODS_DIR . 'includes/updater.php' );

                $version = PODS_VERSION;

                if ( isset( $_GET[ 'pods_force_refresh' ] ) )
                    $version = '0.1';

                if ( 'update-selected' == pods_var( 'action' ) && ( false !== strpos( $_SERVER[ 'REQUEST_URI' ], $update ) || false !== strpos( $_SERVER[ 'REQUEST_URI' ], $update_network ) ) )
                    $version = '0.1';

                $config = array(
                    'slug' => PODS_SLUG, // this is the slug of your plugin
                    'proper_folder_name' => dirname( PODS_SLUG ), // this is the name of the folder your plugin lives in
                    'api_url' => 'https://api.github.com/repos/' . $user . '/' . $repo, // the github API url of your github repo
                    'raw_url' => 'https://raw.github.com/' . $user . '/' . $repo . '/' . $branch, // the github raw url of your github repo
                    'github_url' => 'https://github.com/' . $user . '/' . $repo, // the github url of your github repo
                    'zip_url' => 'https://github.com/' . $user . '/' . $repo . '/zipball/' . $branch, // the zip url of the github repo
                    'sslverify' => false, // whether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
                    'requires' => '3.4', // which version of WordPress does your plugin require?
                    'tested' => '3.6', // which version of WordPress is your plugin tested up to?
                    'version' => $version
                );

                new WPGitHubUpdater( $config );
            }
        }

        if ( !defined( 'SHORTINIT' ) || !SHORTINIT ) {
            if ( pods_allow_deprecated() ) {
                require_once( PODS_DIR . 'deprecated/deprecated.php' );
			}

            if ( false !== pods_compatibility_check() ) {
                $pods_form = pods_form();

                $pods_init = pods_init();
            }
        }
    }
}

/**
 * Deactivate Pods 1.x or other Pods plugins
 */
function pods_deactivate_pods_1_x () {
    if ( defined( 'PODS_VERSION' ) && defined( 'PODS_DIR' ) && file_exists( untrailingslashit( PODS_DIR ) . '/init.php' ) ) {
        if ( !function_exists( 'deactivate_plugins' ) )
            include_once ABSPATH . 'wp-admin/includes/plugin.php';

        deactivate_plugins( realpath( untrailingslashit( PODS_DIR ) . '/init.php' ) );

        if ( !headers_sent() && ( !function_exists( 'pods_ui_manage' ) && !file_exists( WP_CONTENT_DIR . 'plugins/pods-ui/pods-ui.php' ) ) ) {
            wp_redirect( $_SERVER[ 'REQUEST_URI' ] );
            die();
        }
    }
}

/**
 * Deactivate Pods UI plugin
 */
function pods_deactivate_pods_ui () {
    if ( function_exists( 'pods_ui_manage' ) && file_exists( WP_CONTENT_DIR . 'plugins/pods-ui/pods-ui.php' ) ) {
        if ( !function_exists( 'deactivate_plugins' ) )
            include_once ABSPATH . 'wp-admin/includes/plugin.php';

        deactivate_plugins( realpath( WP_CONTENT_DIR . 'plugins/pods-ui/pods-ui.php' ) );

        if ( !headers_sent() ) {
            wp_redirect( $_SERVER[ 'REQUEST_URI' ] );
            die();
        }
    }
}