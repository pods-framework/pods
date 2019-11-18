/**
 * Notice Dismiss structure
 */
( function( $ ) {
	// Add / Update a key-value pair in the URL query parameters
	function update_query_string(uri, key, value) {
	    // remove the hash part before operating on the uri
	    var i = uri.indexOf( '#' );
	    var hash = i === -1 ? ''  : uri.substr(i);
	         uri = i === -1 ? uri : uri.substr(0, i);

	    var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
	    var separator = uri.indexOf('?') !== -1 ? "&" : "?";
	    if (uri.match(re)) {
	        uri = uri.replace(re, '$1' + key + "=" + value + '$2');
	    } else {
	        uri = uri + separator + key + "=" + value;
	    }
	    return uri + hash;  // finally append the hash as well
	}

	$( document ).ready( function() {
		$( '.tribe-dismiss-notice.is-dismissible' ).on( 'click', '.notice-dismiss', function() {
			var dismiss_ajaxurl = update_query_string( ajaxurl, 'tribe-dismiss-notice', $( this ).parents( '.tribe-dismiss-notice' ).data( 'ref' ) );

			$.ajax( dismiss_ajaxurl, {
				dataType: 'json',
				method: 'POST',
				data: {
					action: 'tribe_notice_dismiss'
				}
			} );
		} );
	} );
}( jQuery ) );