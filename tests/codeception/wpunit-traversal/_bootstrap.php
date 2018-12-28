<?php
// Here you can initialize variables that will be available to your tests

add_filter( 'pods_error_mode', function() { return 'exception'; } );

$rebuild_data = filter_var( getenv( 'PODS_REBUILD_DATA' ), FILTER_VALIDATE_BOOLEAN );

if ( $rebuild_data ) {
	Pods_Unit_Tests\Pods_TraversalTestCase::_initialize();

	echo "\nData rebuilt, you can now export the updated SQL to tests/codeception_data/dump-pods-testcase.sql\n";
	die();
}

pods_require_component( 'table-storage' );
pods_require_component( 'advanced-relationships' );
pods_require_component( 'migrate-packages' );
pods_require_component( 'advanced-content-types' );

require_once PODS_DIR . '/components/Migrate-Packages/Migrate-Packages.php';
require_once PODS_DIR . '/components/Advanced-Content-Types.php';
require_once PODS_DIR . '/components/Table-Storage.php';

Pods_Unit_Tests\Pods_TraversalTestCase::_initialize();
