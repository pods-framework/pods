<?php
/**
 * Bootstrap the plugin unit testing environment.
 *
 * @package    WordPress
 * @subpackage Fields API
 */

// Support for:
// 1. `WP_DEVELOP_DIR` environment variable
// 2. Plugin installed inside of WordPress.org developer checkout
// 3. Tests checked out to /tmp
if ( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
	$test_root = getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit';
} elseif ( file_exists( '../../../../tests/phpunit/includes/bootstrap.php' ) ) {
	$test_root = '../../../../tests/phpunit';
} elseif ( file_exists( '/tmp/wordpress-tests-lib/includes/bootstrap.php' ) ) {
	$test_root = '/tmp/wordpress-tests-lib';
}

require $test_root . '/includes/functions.php';

// Config
define( 'PODS_SESSION_AUTO_START', false );
define( 'PODS_TEST_PLUGIN_FILE', $_SERVER['PWD'] . '/init.php' );
define( 'PODS_TEST_PLUGIN_DIR', $_SERVER['PWD'] );
define( 'PODS_TEST_PLUGIN', $_SERVER['PWD'] . '/init.php' );
define( 'PODS_TEST_DIR', $_SERVER['PWD'] . '/tests/phpunit' );

// Error reporting
error_reporting( E_ALL & ~E_DEPRECATED & ~E_STRICT );

function _manually_load_plugin() {

	add_filter( 'pods_allow_deprecated', '__return_true' );
	add_filter( 'pods_error_die', '__return_false' );
	add_filter( 'pods_error_exception', '__return_false' );

	// Disable e-mails
	add_filter( 'send_password_change_email', '__return_false' );
	add_filter( 'send_email_change_email', '__return_false' );

	require PODS_TEST_PLUGIN_FILE;
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $test_root . '/includes/bootstrap.php';

echo "Installing Pods...\n";

activate_plugin( PODS_TEST_PLUGIN );

require PODS_TEST_DIR . '/includes/factory.php';
require PODS_TEST_DIR . '/includes/testcase.php';

global $current_user;

$current_user = new WP_User( 1 );
$current_user->set_role( 'administrator' );

$classLoader = require PODS_TEST_PLUGIN_DIR . '/vendor/autoload.php';
$classLoader->addPsr4( 'Pods_Unit_Tests\\', dirname( __FILE__ ) . '/includes', true );

