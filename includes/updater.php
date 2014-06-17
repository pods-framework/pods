<?php

// Prevent loading this file directly - Busted!
if ( !defined( 'ABSPATH' ) )
    die( '-1' );

/* // managewp.com GitHub Updater integration code, need to merge this in and clean up
// mwp_premium_update_notification filter
//
// Hook to this filter to provide the new version of your plugin if available
//
add_filter( 'mwp_premium_update_notification', 'myplugin_mwp_update_notification' );
if ( !function_exists( 'myplugin_mwp_update_notification' ) ) {
    function myplugin_mwp_update_notification( $premium_updates ) {

        if ( !function_exists( 'get_plugin_data' ) )
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        $myplugin = get_plugin_data( __FILE__ ); // or path to your main plugin file, we expect it to have standard header with plugin info
        $myplugin[ 'type' ] = 'plugin';

        // This is the only line you need to edit
        $myplugin[ 'new_version' ] = '1.4'; // edit your plugin's new version

        array_push( $premium_updates, $myplugin );
        return $premium_updates;
    }
}

// mwp_premium_perform_update filter
//
// Hook to this filter to return either the URL to the new version
// or your callback function which will perform the update when called
//
add_filter( 'mwp_premium_perform_update', 'myplugin_mwp_perform_update' );
if ( !function_exists( 'myplugin_mwp_perform_update' ) ) {
    function myplugin_mwp_perform_update( $update ) {

        if ( !function_exists( 'get_plugin_data' ) )
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        $my_addon = get_plugin_data( __FILE__ ); // or path to your main plugin file, we expect it to have standard header with plugin info

        // This is the only line you need to edit
        $my_addon[ 'url' ] = 'http://mysite.com/file.zip'; // provide URL to the archive file with the new version
        $my_addon[ 'callback' ] = 'my_update_callback'; // OR provide your own callback function for managing the update

        array_push( $update, $my_addon );

        return $update;
    }
}

// mwp_premium_update_check filter
//
// Hook to this filter to supply your function that handles the update check
//
add_filter( 'mwp_premium_update_check', 'myplugin_mwp_update_check' );
if ( !function_exists( 'myplugin_mwp_update_check' ) ) {
    function myplugin_mwp_update_check( $update ) {

        if ( !function_exists( 'get_plugin_data' ) )
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        $my_addon = get_plugin_data( __FILE__ ); // or path to your main plugin file, we expect it to have standard header with plugin info

        // This is the only line you need to edit
        $my_addon[ 'callback' ] = 'my_update_callback'; // provide your callback function which will check for updates when called

        array_push( $update, $my_addon );

        return $update;
    }
}*/

if ( !class_exists( 'WPGitHubUpdater' ) ) :

    /**
     * @version 1.3
     * @author Joachim Kudish <info@jkudish.com>
     * @link http://jkudish.com
     * @package GithubUpdater
     * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
     * @copyright Copyright (c) 2011, Joachim Kudish
     *
     * GNU General Public License, Free Software Foundation
     * <http://creativecommons.org/licenses/GPL/2.0/>
     *
     * This program is free software; you can redistribute it and/or modify
     * it under the terms of the GNU General Public License as published by
     * the Free Software Foundation; either version 2 of the License, or
     * (at your option) any later version.
     *
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
     * GNU General Public License for more details.
     *
     * You should have received a copy of the GNU General Public License
     * along with this program; if not, write to the Free Software
     * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
     */
    class WPGitHubUpdater {

        /**
         * Class Constructor
         *
         * @since 1.0
         *
         * @param array $config configuration
         *
         * @return void
         */
        public function __construct ( $config = array() ) {

            global $wp_version;

            $defaults = array(
                'slug' => plugin_basename( __FILE__ ),
                'proper_folder_name' => dirname( plugin_basename( __FILE__ ) ),
                'api_url' => 'https://api.github.com/repos/jkudish/WordPress-GitHub-Plugin-Updater',
                'raw_url' => 'https://raw.github.com/jkudish/WordPress-GitHub-Plugin-Updater/master',
                'github_url' => 'https://github.com/jkudish/WordPress-GitHub-Plugin-Updater',
                'zip_url' => 'https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/zipball/master',
                'sslverify' => true,
                'requires' => $wp_version,
                'tested' => $wp_version,
            );

            $this->config = wp_parse_args( $config, $defaults );

            $this->set_defaults();

            if ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || ( defined( 'WP_GITHUB_FORCE_UPDATE' ) || WP_GITHUB_FORCE_UPDATE ) )
                add_action( 'init', array( $this, 'delete_transients' ) );

            add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'api_check' ) );

            // Hook into the plugin details screen
            add_filter( 'plugins_api', array( $this, 'get_plugin_info' ), 10, 3 );
            add_filter( 'upgrader_post_install', array( $this, 'upgrader_post_install' ), 10, 3 );

            // set timeout
            add_filter( 'http_request_timeout', array( $this, 'http_request_timeout' ) );

            // set sslverify for zip download
            add_filter( 'http_request_args', array( $this, 'http_request_sslverify' ), 10, 2 );
        }

        /**
         * Set defaults
         *
         * @since 1.2
         * @return void
         */
        public function set_defaults () {

            if ( !isset( $this->config[ 'new_version' ] ) )
                $this->config[ 'new_version' ] = $this->get_new_version();

            if ( !isset( $this->config[ 'last_updated' ] ) )
                $this->config[ 'last_updated' ] = $this->get_date();

            if ( !isset( $this->config[ 'description' ] ) )
                $this->config[ 'description' ] = $this->get_description();

            $plugin_data = $this->get_plugin_data();
            if ( !isset( $this->config[ 'plugin_name' ] ) )
                $this->config[ 'plugin_name' ] = $plugin_data[ 'Name' ];

            if ( !isset( $this->config[ 'version' ] ) )
                $this->config[ 'version' ] = $plugin_data[ 'Version' ];

            if ( !isset( $this->config[ 'author' ] ) )
                $this->config[ 'author' ] = $plugin_data[ 'Author' ];

            if ( !isset( $this->config[ 'homepage' ] ) )
                $this->config[ 'homepage' ] = $plugin_data[ 'PluginURI' ];
        }

        /**
         * Callback fn for the http_request_timeout filter
         *
         * @since 1.0
         * @return int timeout value
         */
        public function http_request_timeout () {
            return 2;
        }

        /**
         * Callback fn for the http_request_args filter
         *
         * @param $args
         * @param $url
         *
         * @return mixed
         */
        public function http_request_sslverify ( $args, $url ) {
            if ( $this->config[ 'zip_url' ] == $url )
                $args[ 'sslverify' ] = $this->config[ 'sslverify' ];

            return $args;
        }

        /**
         * Delete transients (runs when WP_DEBUG is on)
         * For testing purposes the site transient will be reset on each page load
         *
         * @since 1.0
         * @return void
         */
        public function delete_transients () {
            delete_site_transient( 'update_plugins' );
            delete_site_transient( $this->config[ 'slug' ] . '_new_version' );
            delete_site_transient( $this->config[ 'slug' ] . '_github_data' );
            delete_site_transient( $this->config[ 'slug' ] . '_changelog' );
        }

        /**
         * Get New Version from github
         *
         * @since 1.0
         * @return int $version the version number
         */
        public function get_new_version () {
            $version = get_site_transient( $this->config[ 'slug' ] . '_new_version' );

            if ( !isset( $version ) || !$version || '' == $version ) {

                $raw_response = wp_remote_get(
                    trailingslashit( $this->config[ 'raw_url' ] ) . 'readme.txt',
                    array(
                        'sslverify' => $this->config[ 'sslverify' ],
                    )
                );

                $__version = false;

                if ( !is_wp_error( $raw_response ) ) {
                    $__version = explode( '~Current Version:', $raw_response[ 'body' ] );

                    if ( !isset( $__version[ '1' ] ) )
                        $__version = false;
                }

                if ( !$__version || is_wp_error( $raw_response ) ) {
                    $raw_response = wp_remote_get(
                        trailingslashit( $this->config[ 'raw_url' ] ) . 'README.md',
                        array(
                            'sslverify' => $this->config[ 'sslverify' ],
                        )
                    );

                    if ( is_wp_error( $raw_response ) )
                        return false;

                    $__version = explode( '~Current Version:', $raw_response[ 'body' ] );
                }

                if ( !isset( $__version[ '1' ] ) )
                    return false;

                $_version = explode( '~', $__version[ '1' ] );

                $version = trim( $_version[ 0 ] );

                // refresh every 6 hours
                set_site_transient( $this->config[ 'slug' ] . '_new_version', $version, 60 * 60 * 6 );
            }

            return $version;
        }

        /**
         * Get GitHub Data from the specified repository
         *
         * @since 1.0
         * @return array $github_data the data
         */
        public function get_github_data () {
            $github_data = get_site_transient( $this->config[ 'slug' ] . '_github_data' );

            if ( !isset( $github_data ) || !$github_data || '' == $github_data ) {
                $github_data = wp_remote_get(
                    $this->config[ 'api_url' ],
                    array(
                        'sslverify' => $this->config[ 'sslverify' ],
                    )
                );

                if ( is_wp_error( $github_data ) )
                    return false;

                $github_data = json_decode( $github_data[ 'body' ] );

                // refresh every 6 hours
                set_site_transient( $this->config[ 'slug' ] . '_github_data', $github_data, 60 * 60 * 6 );
            }

            return $github_data;
        }

        /**
         * Get update date
         *
         * @since 1.0
         * @return string $date the date
         */
        public function get_date () {
            $_date = $this->get_github_data();
            return ( !empty( $_date->updated_at ) ) ? date( 'Y-m-d', strtotime( $_date->updated_at ) ) : false;
        }

        /**
         * Get plugin description
         *
         * @since 1.0
         * @return string $description the description
         */
        public function get_description () {
            $_description = $this->get_github_data();
            return ( !empty( $_description->description ) ) ? $_description->description : false;
        }

        /**
         * Get Plugin data
         *
         * @since 1.0
         * @return object $data the data
         */
        public function get_plugin_data () {
            include_once( ABSPATH . '/wp-admin/includes/plugin.php' );
            $data = get_plugin_data( WP_PLUGIN_DIR . '/' . $this->config[ 'slug' ] );
            return $data;
        }

        /**
         * Hook into the plugin update check and connect to github
         *
         * @since 1.0
         *
         * @param object $transient the plugin data transient
         *
         * @return object $transient updated plugin data transient
         */
        public function api_check ( $transient ) {

            // Check if the transient contains the 'checked' information
            // If not, just return its value without hacking it
            if ( empty( $transient->checked ) )
                return $transient;

            // check the version and decide if it's new
            $update = version_compare( $this->config[ 'new_version' ], $this->config[ 'version' ] );

            if ( 1 === $update ) {
                $response = new stdClass;
                $response->new_version = $this->config[ 'new_version' ];
                $response->slug = $this->config[ 'proper_folder_name' ];
                $response->url = $this->config[ 'github_url' ];
                $response->package = $this->config[ 'zip_url' ];

                // If response is false, don't alter the transient
                if ( false !== $response )
                    $transient->response[ $this->config[ 'slug' ] ] = $response;
            }

            return $transient;
        }

        /**
         * Get Plugin info
         *
         * @since 1.0
         *
         * @param bool $false always false
         * @param string $action the API function being performed
         * @param object $args plugin arguments
         *
         * @return object $response the plugin info
         */
        public function get_plugin_info ( $false, $action, $response ) {

            // Check if this call API is for the right plugin
            if ( !is_object( $response ) || !isset( $response->slug ) || $response->slug != $this->config[ 'slug' ] )
                return false;

            $response->slug = $this->config[ 'slug' ];
            $response->plugin_name = $this->config[ 'plugin_name' ];
            $response->version = $this->config[ 'new_version' ];
            $response->author = $this->config[ 'author' ];
            $response->homepage = $this->config[ 'homepage' ];
            $response->requires = $this->config[ 'requires' ];
            $response->tested = $this->config[ 'tested' ];
            $response->downloaded = 0;
            $response->last_updated = $this->config[ 'last_updated' ];
            $response->sections = array( 'description' => $this->config[ 'description' ] );
            $response->download_link = $this->config[ 'zip_url' ];

            return $response;
        }

        /**
         * Upgrader/Updater
         * Move & activate the plugin, echo the update message
         *
         * @since 1.0
         *
         * @param boolean $true always true
         * @param mixed $hook_extra not used
         * @param array $result the result of the move
         *
         * @return array $result the result of the move
         */
        public function upgrader_post_install ( $true, $hook_extra, $result ) {

            global $wp_filesystem;

            // Move & Activate
            $proper_destination = WP_PLUGIN_DIR . '/' . $this->config[ 'proper_folder_name' ];
            $wp_filesystem->move( $result[ 'destination' ], $proper_destination );
            $result[ 'destination' ] = $proper_destination;
            $activate = activate_plugin( WP_PLUGIN_DIR . '/' . $this->config[ 'slug' ] );

            // Output the update message
            $fail = __( 'The plugin has been updated, but could not be reactivated. Please reactivate it manually.', 'pods' );
            $success = __( 'Plugin reactivated successfully.', 'pods' );
            echo is_wp_error( $activate ) ? $fail : $success;
            return $result;

        }

    }

endif; // endif class exists
