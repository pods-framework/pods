
if ( typeof PodsMaps == 'undefined' ) {
	var PodsMaps = {};
}

(function ( $ ) {

	// @todo PodsMaps is probably not needed anymore

	PodsMaps.ajaxData = {};
	PodsMaps.doingAjax = false;
	PodsMaps.ajaxResults = false;

	PodsMaps.mergeAddressFromInputs = function( fields ) {
		var address = [];
		if ( fields.line_1.length ) { address.push( fields.line_1.val() ); }
		if ( fields.line_2.length ) { address.push( fields.line_2.val() ); }
		if ( fields.city.length ) { address.push( fields.city.val() ); }
		if ( fields.postal_code.length ) { address.push( fields.postal_code.val() ); }
		if ( fields.region.length ) { address.push( fields.region.val() ); }
		if ( fields.country.length ) { address.push( fields.country.val() ); }
		return address.join(', ');
	};

	PodsMaps.geocode = function( data ) {
		PodsMaps.ajaxData.pods_maps_action = 'geocode';
		PodsMaps.ajaxData.pods_maps_data = data;
		return PodsMaps.doAjaxPost();
	};

	PodsMaps.geocodeAddressToLatLng = function( data ) {
		PodsMaps.ajaxData.pods_maps_action = 'geocode_address_to_latlng';
		PodsMaps.ajaxData.pods_maps_data = data;
		return PodsMaps.doAjaxPost();
	};

	PodsMaps.geocodeLatLngToAddress = function( data ) {
		PodsMaps.ajaxData.pods_maps_action = 'geocode_latlng_to_address';
		PodsMaps.ajaxData.pods_maps_data = data;
		return PodsMaps.doAjaxPost();
	};

	PodsMaps.doAjaxPost = function() {
		PodsMaps.ajaxData.action = 'pods_maps';
		PodsMaps.ajaxData._pods_maps_nonce = PodsMaps._nonce;
		PodsMaps.doingAjax = true;
		PodsMaps.ajaxResults = false;
		$.post(
			PodsMaps.ajaxurl,
			PodsMaps.ajaxData,
			function( response ) {
				PodsMaps.ajaxData = {};
				PodsMaps.doingAjax = false;
				if ( typeof response.data != 'undefined' ) {
					PodsMaps.ajaxResults = response.data;
					$(document).trigger('PodsMapsAjaxDone');
					return response.data;
				}
				return false;
			}
		);
	};

	var methods = {};

	$.fn.PodsMap = function ( method ) {

		if ( methods [method] ) {
			return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ) );
		}
		else {
			$.error( 'Method ' + method );
		}

	};

})( jQuery );