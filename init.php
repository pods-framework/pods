<?php
/*
Plugin Name: Pods - Custom Content Types and Fields
Plugin URI: http://podsframework.org/
Description: Pods is a framework for creating, managing, and deploying customized content types and fields
Version: 2.0.0
Author: Pods Framework Team
Author URI: http://podsframework.org/about/
Text Domain: pods
Domain Path: /languages/

Copyright 2009-2012  The Pods Framework Team  (email : contact@podsframework.org)

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
if ( !defined( 'PODS_VERSION' ) && !defined( 'PODS_DIR' ) ) {
    define( 'PODS_VERSION', '2.0.0' );

    if ( !defined( 'PODS_WP_VERSION_MINIMUM' ) )
        define( 'PODS_WP_VERSION_MINIMUM', '3.4' );
    if ( !defined( 'PODS_PHP_VERSION_MINIMUM' ) )
        define( 'PODS_PHP_VERSION_MINIMUM', '5.2.4' );
    if ( !defined( 'PODS_MYSQL_VERSION_MINIMUM' ) )
        define( 'PODS_MYSQL_VERSION_MINIMUM', '5.0' );

    define( 'PODS_SLUG', plugin_basename( __FILE__ ) );
    define( 'PODS_URL', plugin_dir_url( __FILE__ ) );
    define( 'PODS_DIR', plugin_dir_path( __FILE__ ) );

    global $pods, $pods_init;

    require_once( PODS_DIR . 'functions.php' );

    if ( false !== pods_compatible() && ( !defined( 'SHORTINIT' ) || !SHORTINIT ) ) {
        if ( !defined( 'PODS_DEPRECATED' ) || PODS_DEPRECATED )
            require_once( PODS_DIR . 'deprecated/deprecated.php' );

        $pods_init = pods_init();
    }
}
else {
    function pods_deactivate_1_x () {
        if ( defined( 'PODS_VERSION' ) && defined( 'PODS_DIR' ) && file_exists( untrailingslashit( PODS_DIR ) . '/init.php' ) ) {
            if ( !function_exists( 'deactivate_plugins' ) )
                include_once ABSPATH . '/wp-admin/includes/plugin.php';

            deactivate_plugins( untrailingslashit( PODS_DIR ) . '/init.php' );

            // next refresh will load 2.0
            return;
        }
    }

    add_action( 'init', 'pods_deactivate_1_x' );
}

/**
 * Load the plugin textdomain.
 */
function pods_textdomain () {
	load_plugin_textdomain( 'pods', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'pods_textdomain' );