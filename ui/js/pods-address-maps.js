(function ( $ ) {

	var methods = {};

	$.fn.PodsAddressMap = function ( method ) {

		if ( methods [method] ) {
			return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ) )
		}
		else {
			$.error( 'Method ' + method )
		}

	}

})( jQuery );