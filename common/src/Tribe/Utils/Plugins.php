<?php
defined( 'WPINC' ) || die; // Do not load directly.

/**
 * Plugin utilities
 */
class Tribe__Utils__Plugins {

	/**
	 * Gets plugin data from the plugin file header
	 *
	 * @see get_plugin_data() for WP Admin only function this is similar to.
	 *
	 * @param string $plugin_file Absolute path to plugin file containing header.
	 *
	 * @return array Plugin data; keys match capitalized file header declarations.
	 */
	public static function get_plugin_data( $plugin_file ) {
		$headers = array(
			'Name'           => 'Plugin Name',
			'PluginURI'      => 'Plugin URI',
			'Version'        => 'Version',
			'ExtensionClass' => 'Extension Class',
			'ExtensionFile'  => 'Extension File',
			'Description'    => 'Description',
			'Author'         => 'Author',
			'AuthorURI'      => 'Author URI',
			'TextDomain'     => 'Text Domain',
			'DomainPath'     => 'Domain Path',
			'Network'        => 'Network',
		);

		/**
		 * Filter which header keys passed to get_file_data().
		 *
		 * @see get_file_data()
		 *
		 * @param array  $headers     The headers.
		 * @param string $plugin_file The plugin file path.
		 */
		$headers = apply_filters( 'tribe_get_plugin_data_headers', $headers, $plugin_file );
		$file_data = get_file_data( $plugin_file, $headers, 'plugin' );

		/**
		 * Filter the parsed plugin header data.
		 *
		 * @param array  $file_data   Output from get_file_data().
		 * @param string $plugin_file The plugin file path.
		 * @param array  $headers     The headers.
		 */
		return apply_filters( 'tribe_get_plugin_data', $file_data, $plugin_file, $headers );
	}

	/**
	 * Get list of active plugins with a given prefix in the plugin folder path.
	 *
	 * @param string|array $prefix Prefixes you want to retrieve.
	 *
	 * @return array List of plugins with prefix in path.
	 */
	public static function get_plugins_with_prefix( $prefix ) {
		$full_list = wp_get_active_and_valid_plugins();

		if ( is_multisite() ) {
			$full_list = array_merge( $full_list, wp_get_active_network_plugins() );
		}

		$filtered_list = array();

		foreach ( $full_list as $plugin ) {
			$base = plugin_basename( $plugin );

			if ( 0 === Tribe__Utils__Array::strpos( $base, $prefix ) ) {
				$filtered_list[] = $plugin;
			}
		}

		return $filtered_list;
	}
}
