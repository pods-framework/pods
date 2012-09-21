<?php
/*
Plugin Name: Pods Framework
Plugin URI: http://podsframework.org/
Description: Create / Manage / Develop / Extend content types: Posts, Pages, Media, Custom Post Types, Categories, Tags, Custom Taxonomy, Comments, Users, Custom Content Types, and Custom Tables
Version: 2.0.0 RC 1
Author: Pods Framework Team
Author URI: http://podsframework.org/about/

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
    define( 'PODS_VERSION', '2.0.0-rc-1' );

    if ( !defined( 'PODS_WP_VERSION_MINIMUM' ) )
        define( 'PODS_WP_VERSION_MINIMUM', '3.4' );
    if ( !defined( 'PODS_PHP_VERSION_MINIMUM' ) )
        define( 'PODS_PHP_VERSION_MINIMUM', '5.2.4' );
    if ( !defined( 'PODS_MYSQL_VERSION_MINIMUM' ) )
        define( 'PODS_MYSQL_VERSION_MINIMUM', '5.0' );

    define( 'PODS_SLUG', plugin_basename( __FILE__ ) );
    define( 'PODS_URL', plugin_dir_url( __FILE__ ) );
    define( 'PODS_DIR', plugin_dir_path( __FILE__ ) );

    require_once( PODS_DIR . 'functions.php' );

    $update = admin_url( 'update.php' );
    $update = str_replace( get_bloginfo( 'wpurl' ), '', $update );

    $update_network = network_admin_url( 'update.php' );
    $update_network = str_replace( get_bloginfo( 'wpurl' ), '', $update_network );

    if ( is_admin() &&
         ( isset( $_GET[ 'pods_force_refresh' ] ) ||
           ( 'update-selected' == pods_var( 'action' ) &&
             ( false !== strpos( $_SERVER[ 'REQUEST_URI' ], $update ) ||
               false !== strpos( $_SERVER[ 'REQUEST_URI' ], $update_network ) ) ) ) ) {
        // GitHub Plugin Updater
        // https://github.com/jkudish/WordPress-GitHub-Plugin-Updater
        require_once( PODS_DIR . 'updater.php' );

        $version = PODS_VERSION;

        if ( isset( $_GET[ 'pods_force_refresh' ] ) )
            $version = '0.1';

        if ( 'update-selected' == pods_var( 'action' ) && ( false !== strpos( $_SERVER[ 'REQUEST_URI' ], $update ) || false !== strpos( $_SERVER[ 'REQUEST_URI' ], $update_network ) ) )
            $version = '0.1';

        $user = 'pods-framework';
        $repo = 'pods';
        $branch = '2.0';
        $config = array(
            'slug' => PODS_SLUG, // this is the slug of your plugin
            'proper_folder_name' => dirname( PODS_SLUG ), // this is the name of the folder your plugin lives in
            'api_url' => 'https://api.github.com/repos/' . $user . '/' . $repo, // the github API url of your github repo
            'raw_url' => 'https://raw.github.com/' . $user . '/' . $repo . '/' . $branch, // the github raw url of your github repo
            'github_url' => 'https://github.com/' . $user . '/' . $repo, // the github url of your github repo
            'zip_url' => 'https://github.com/' . $user . '/' . $repo . '/zipball/' . $branch, // the zip url of the github repo
            'sslverify' => false, // whether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
            'requires' => '3.4', // which version of WordPress does your plugin require?
            'tested' => '3.4.1', // which version of WordPress is your plugin tested up to?
            'version' => $version
        );

        new WPGitHubUpdater( $config );
    }

    global $pods, $pods_init;

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
