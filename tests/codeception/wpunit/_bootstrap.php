<?php
// Here you can initialize variables that will be available to your tests

add_filter( 'pods_error_mode', static function() { return 'exception'; } );
add_filter( 'pods_api_cache', '__return_false' );
