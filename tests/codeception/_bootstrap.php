<?php

Codeception\Util\Autoload::addNamespace( 'Pods_Unit_Tests', __DIR__ . '/_support' );

if ( ! defined( 'PODS_WP_VERSION_MINIMUM' ) ) {
	define( 'PODS_WP_VERSION_MINIMUM', '6.1' );
}
