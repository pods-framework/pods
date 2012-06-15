<?php
/*
Plugin Name: Pods Development Framework
Plugin URI: http://podsframework.org/
Description: Create / Manage / Develop / Extend content types: Posts, Pages, Custom Post Types, Categories, Tags, Custom Taxonomy, Comments, Users, Custom Content Types, and Custom Tables
Version: 2.0.0 Alpha 14
Author: The Pods Framework Team
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
define( 'PODS_VERSION', '2.0.0-a-14' );

if ( !defined( 'PODS_WP_VERSION_MINIMUM' ) )
    define( 'PODS_WP_VERSION_MINIMUM', '3.4' );
if ( !defined( 'PODS_PHP_VERSION_MINIMUM' ) )
    define( 'PODS_PHP_VERSION_MINIMUM', '5.2.4' );
if ( !defined( 'PODS_MYSQL_VERSION_MINIMUM' ) )
    define( 'PODS_MYSQL_VERSION_MINIMUM', '5.0' );

define( 'PODS_SLUG', plugin_basename( __FILE__ ) );
define( 'PODS_URL', plugin_dir_url( __FILE__ ) );
define( 'PODS_DIR', plugin_dir_path( __FILE__ ) );
if ( !defined( 'WP_INCLUDES_URL' ) )
    define( 'WP_INCLUDES_URL', includes_url() );

require_once( PODS_DIR . 'functions.php' );

if ( is_admin() ) { // note the use of is_admin() to double check that this is happening in the admin
    // GitHub Plugin Updater
    // https://github.com/jkudish/WordPress-GitHub-Plugin-Updater
    require_once( PODS_DIR . 'updater.php' );

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
        'sslverify' => true, // whether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
        'requires' => '3.4', // which version of WordPress does your plugin require?
        'tested' => '3.4', // which version of WordPress is your plugin tested up to?
        'version' => PODS_VERSION
    );
    new WPGitHubUpdater( $config );
}

global $pods, $pods_init, $pods_admin, $pod_page_exists;
if ( false !== pods_compatible() && ( !defined( 'SHORTINIT' ) || !SHORTINIT ) ) {
    require_once( PODS_DIR . 'deprecated/deprecated.php' );

    $pods_init = pods_init();
}