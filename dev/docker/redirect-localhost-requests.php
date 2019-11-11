<?php
/**
 * Redirects internal WordPress calls to `http://localhost:port` removing the port to work around
 * the fact that WordPress will not be able to reach its own address at `http://locahost:port`.
 */
add_action( 'pre_http_request', function ( $result, $r, $url ) {
	$http = _wp_http_get_object();
	$port = parse_url( $url, PHP_URL_PORT );

	if ( $port ) {
		$url = str_replace( ":{$port}", '', $url );
	} else {
		return $result;
	}

	return $http->request( $url, $r );
}, 10, 3 );