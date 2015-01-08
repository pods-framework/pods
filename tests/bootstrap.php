<?php
// Config
define( 'PODS_SESSION_AUTO_START', false );
define( 'PODS_TEST_PLUGIN_FILE', dirname( dirname( __FILE__ ) ) . '/init.php' );
define( 'PODS_TEST_PLUGIN_DIR', dirname( dirname( __FILE__ ) ) );
define( 'PODS_TEST_PLUGIN', basename( dirname( dirname( __FILE__ ) ) ) . '/init.php' );
define( 'PODS_TEST_DIR', dirname( __FILE__ ) );

// Error reporting
error_reporting( E_ALL & ~E_DEPRECATED & ~E_STRICT );


$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['SERVER_NAME'] = '';
$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

$_tests_dir = getenv('WP_TESTS_DIR');
if ( !$_tests_dir ) $_tests_dir = '/tmp/wordpress-tests-lib';

require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	require PODS_TEST_PLUGIN_FILE;
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';

echo "Installing Pods...\n";

activate_plugin( PODS_TEST_PLUGIN );

require PODS_TEST_DIR . '/includes/factory.php';
require PODS_TEST_DIR . '/includes/testcase.php';

global $current_user;

$current_user = new WP_User(1);
$current_user->set_role('administrator');