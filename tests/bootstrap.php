<?php

ini_set('display_errors','on');
error_reporting(E_ALL);
define( 'PODS_PLUGIN_DIR', dirname( dirname( __FILE__ ) ) . '/'  );

require_once dirname( __FILE__ ) . '/../tmp/wordpress-tests/includes/functions.php';

function _install_and_load_pods() {
	require dirname( __FILE__ ) . '/includes/loader.php';
}
tests_add_filter( 'muplugins_loaded', '_install_and_load_pods' );

require dirname( __FILE__ ) . '/../tmp/wordpress-tests/includes/bootstrap.php';

require dirname( __FILE__ ) . '/framework/testcase.php';
