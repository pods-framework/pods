<?php
/**
 * Pods - Custom Content Types and Fields
 *
 * @package   Pods
 * @author    Pods Framework Team
 * @copyright 2026 Pods Foundation, Inc
 * @license   GPL v2 or later
 *
 * Plugin Name:       Pods - Custom Content Types and Fields
 * Plugin URI:        https://pods.io/
 * Description:       Pods is a framework for creating, managing, and deploying customized content types and fields
 * Version:           3.3.7
 * Author:            Pods Framework Team
 * Author URI:        https://pods.io/about/
 * Text Domain:       pods
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.3
 * Requires PHP:      7.2
 * GitHub Plugin URI: https://github.com/pods-framework/pods
 * Primary Branch:    main
 * Plugin ID:         did:plc:e3rm6t7cspgpzaf47kn3nnsl
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

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if ( defined( 'PODS_VERSION' ) || defined( 'PODS_DIR' ) ) {
	// Prevent conflicts with Pods 1.x and Pods UI plugins.
	add_action( 'init', 'pods_deactivate_pods_duplicate' );
	add_action( 'init', 'pods_deactivate_pods_ui' );
} else {
	// Current version.
	define( 'PODS_VERSION', '3.3.7' );

	// Current database version, this is the last version the database changed.
	define( 'PODS_DB_VERSION', '2.3.5' );

	/**
	 * We aim to keep this as recent as possible to avoid ongoing React/Gutenberg compatibility problems.
	 *
	 * This should always be -2 versions behind the latest WP release. Example: 5.5 if 5.7 is current.
	 *
	 * To be updated each Major x.x Pods release.
	 *
	 * Next planned minimum WP version: 6.6
	 */
	if ( ! defined( 'PODS_WP_VERSION_MINIMUM' ) ) {
		$pods_wp_version_minimum = getenv( 'PODS_WP_VERSION_MINIMUM' ) ?: '6.3';
		define( 'PODS_WP_VERSION_MINIMUM', $pods_wp_version_minimum );
	}

	/**
	 * This should match minimum WP requirements or usage of 90%+.
	 *
	 * Found at: https://wordpress.org/about/stats/
	 *
	 * Next planned minimum PHP version: 7.3
	 */
	if ( ! defined( 'PODS_PHP_VERSION_MINIMUM' ) ) {
		define( 'PODS_PHP_VERSION_MINIMUM', '7.2' );
	}

	/**
	 * This should match minimum WP requirements or usage of 90%+.
	 *
	 * Found at: https://wordpress.org/about/stats/
	 *
	 *  Next planned minimum MySQL version: 5.6
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
		// If there was an install/update failure and the sub directories do not exist. Bail to avoid fatal errors.
		if (
			! file_exists( PODS_DIR . 'classes/PodsInit.php' )
			|| ! file_exists( PODS_DIR . 'vendor/vendor-prefixed/autoload.php' )
		) {
			return;
		}

		global $pods, $pods_init, $pods_form;

		// Init custom autoloader.
		require_once PODS_DIR . 'classes/PodsInit.php';

		spl_autoload_register( array( 'PodsInit', 'autoload_class' ) );

		require_once PODS_DIR . 'vendor/vendor-prefixed/autoload.php';

		// Include global functions.
		require_once PODS_DIR . 'includes/access.php';
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
			wp_safe_redirect( add_query_arg( [ 'refresh' => 1 ] ) );
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
			wp_safe_redirect( add_query_arg( [ 'refresh' => 1 ] ) );
			die();
		}
	}
}

add_filter( 'wp_plugin_check_ignore_files', static function( $ignored_files ) {
	$pods_dev_files = [
		'ui/js/dfv/pods-dfv.min.js.map',
		'ui/js/codemirror/lib/codemirror.js',
		'.babelrc',
		'.distignore',
		'.DS_Store',
		'.editorconfig',
		'.env',
		'.env.example',
		'.env.testing.slic',
		'.eslintignore',
		'.eslintrc.json',
		'.gitattributes',
		'.gitignore',
		'.jshintrc',
		'.nvmrc',
		'.phpcs.compat.xml',
		'.phpcs.xml',
		'.phpstorm.meta.php',
		'.scrutinizer.yml',
		'.travis.yml',
		'babel.config.js',
		'CODE_OF_CONDUCT.md',
		'codeception.dist.yml',
		'codeception.example.yml',
		'codeception.slic.yml',
		'CODEOWNERS',
		'composer.json',
		'composer.lock',
		'Gruntfile.js',
		'jest.config.json',
		'jest.config.js',
		'jest-setup-wordpress-globals.js',
		'package.json',
		'package-lock.json',
		'phpcs.xml',
		'phpcs.xml.dist',
		'phpstan.neon',
		'phpunit.xml.dist',
		'README.md',
		'rollup.config.js',
		'slic.json',
		'TESTS.md',
		'webpack.common.js',
		'webpack.dev.js',
		'webpack.prod.js',
	];

	return array_merge( $ignored_files, $pods_dev_files );
} );

add_filter( 'wp_plugin_check_ignore_directories', static function( $ignored_dirs ) {
	$pods_dev_dirs = [
		'.git',
		'.github',
		'.wordpress-org',
		'bin',
		'dev',
		'docs',
		'github',
		'tests',
		'workspace',
		'ui/js/blocks/src',
		'ui/js/dfv/src',
		'ui/styles/src',
	];

	return array_merge( $ignored_dirs, $pods_dev_dirs );
} );
