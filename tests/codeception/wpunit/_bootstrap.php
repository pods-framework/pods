<?php
// Here you can initialize variables that will be available to your tests

add_filter( 'pods_error_mode', static function() { return 'exception'; } );
add_filter( 'pods_api_cache', '__return_false' );

if ( ! defined( 'PODS_WP_VERSION_MINIMUM' ) ) {
	define( 'PODS_WP_VERSION_MINIMUM', '6.1' );
}
