<?php
/**
 * Provides functions to seek and interact with files.
 *
 * @since 5.0.0
 */

if ( ! function_exists( 'tec_is_file_from_plugins' ) ) {
	/**
	 * Checks if a file is from one of the specified plugins.
	 *
	 * @since 5.0.0
	 *
	 * @param string $file            The path of the file to check.
	 * @param string ...$plugin_files A set of plugin main files to check, e.g. `the-events-calendar.php`.
	 *
	 * @return bool Whether the file is from one of the specified plugins.
	 */
	function tec_is_file_from_plugins( string $file, string ...$plugin_files ): bool {
		static $wp_active_and_valid_plugins = null;

		if ( empty( $wp_active_and_valid_plugins ) ) {
			// The list is expensive to generate, so we cache it.
			$wp_active_and_valid_plugins = wp_get_active_and_valid_plugins();
		}

		$plugin_dirs = array_map(
			'dirname',
			array_filter( $wp_active_and_valid_plugins, static function ( string $plugin ) use ( $plugin_files ): bool {
				return in_array( basename( $plugin ), $plugin_files, true );
			} )
		);

		foreach ( $plugin_dirs as $plugin_dir ) {
			if ( strpos( $file, $plugin_dir ) !== false ) {
				return true;
			}
		}

		return false;
	}
}
