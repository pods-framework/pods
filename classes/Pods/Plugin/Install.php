<?php
/**
 * Plugin Installer
 *
 * Adapted from example code by Otto
 *
 * @see http://ottopress.com/2012/themeplugin-dependencies/
 *
 * @package Pods
 */

class Pods_Plugin_Install {

	/**
	 * The plugin's slug.
	 *
	 * @var string
	 *
	 * @since 3.0.0
	 */
	var $slug;

	/**
	 * The plugin's URI.
	 *
	 * @var string
	 *
	 * @since 3.0.0
	 */
	var $uri;

	/**
	 * Will hold the list of installed plugins and their info.
	 *
	 * @var array
	 *
	 * @since 3.0.0
	 */
	private $plugins;

	/**
	 * Will hold the URIs of installed plugins.
	 *
	 * @var array
	 *
	 * @since 3.0.0
	 */
	private $uris;

	/**
	 * Class Construct
	 *
	 * @param string $slug Slug of plugin to install.
	 * @param string $uri URI (WordPress.org page) of plugin to install.
	 *
	 * @since 3.0.0
	 */
	function __construct( $slug, $uri ) {
		$this->slug = $slug;
		$this->uri = $uri;
		$this->plugins = get_plugins();
		$this->uris = wp_list_pluck( $this->plugins, 'PluginURI');
	}


	/**
	 * Check if plugin is already installed.
	 *
	 * @return bool  True if installed, false if not
	 *
	 * @since 3.0.0
	 */
	function check() {

		return in_array( $this->uri, $this->uris );

	}

	/**
	 * Check if plugin is installed and activated.
	 *
	 * @return bool True if installed and activated, false if not
	 *
	 * @since 3.0.0
	 */
	function check_active() {
		$plugin_file = $this->get_plugin_file();
		if ( $plugin_file) {

			return is_plugin_active( $plugin_file );

		}

	}

	/**
	 * Get activation link for an installed plugin.
	 *
	 * @return string|bool A link to activate the plugin or false if plugin isn't installed.
	 *
	 * @since 3.0.0
	 */
	function activate_link() {
		$plugin_file = $this->get_plugin_file();

		if ( $plugin_file ) {
			return wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . $plugin_file ), 'activate-plugin_' . $plugin_file );

		}

	}

	/**
	 * Get install link for plugin.
	 *
	 * @return bool|string A nonced installation link for the plugin
	 *
	 * @since 3.0.0
	 */
	function install_link() {
		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		$info = plugins_api('plugin_information', array('slug' => $this->slug ));

		if ( is_wp_error( $info ) ) {
			return false; // plugin not available from wordpress.org
		}

		return wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $this->slug), 'install-plugin_' . $this->slug);

	}

	/**
	 * Get plugin base file.
	 *
	 * @return bool|string The base file of the plugin, or false if not installed.
	 *
	 * @since 3.0.0
	 */
	function get_plugin_file() {
		$plugin_file = false;
		$plugins = get_plugins();
		$uri = $this->uri;
		foreach ( $plugins as $file => $plugin ) {
			if ( $uri === $plugin[ 'PluginURI'] ) {
				$plugin_file = $file;
				break;
			}
		}

		return $plugin_file;

	}

} 
