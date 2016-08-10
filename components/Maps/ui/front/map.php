<?php
wp_enqueue_script( 'googlemaps' );
wp_enqueue_style( 'pods-component-address-maps' );

$attributes = array();
$attributes = PodsForm::merge_attributes( $attributes, $name, '', $options );

$address = '';
if ( isset( $value['address'] ) ) {
	$address = implode( ' ', $value['address'] );
}
?>
<div id="<?php echo $attributes['id'] . '-map-canvas' ?>" class="pods-address-maps-map-canvas"></div>

<script type="text/javascript">
	document.addEventListener( "DOMContentLoaded", function() { 
		jQuery( document ).ready( function ( $ ) {
			var mapDiv = document.getElementById( '<?php echo $attributes['id'] . '-map-canvas'?>' );
			var address = '<?php echo $address; ?>';

			var map = null;
			var geocoder = null;
			var marker = null;

			//------------------------------------------------------------------------
			// Initialze the map
			//
			//(function () {

				var zoom = 10;
				var center = new google.maps.LatLng( 40.026, -82.936 );

				map = new google.maps.Map( mapDiv, {
					center : center, zoom : zoom, mapTypeId : google.maps.MapTypeId.ROADMAP
				} );

				geocoder = new google.maps.Geocoder();

				//------------------------------------------------------------------------
				// Geolocate from the address
				//
				geocoder.geocode( {'address' : address}, function ( results, status ) {

					if ( status == google.maps.GeocoderStatus.OK ) {
						var latField, lngField;
						var location = results[0].geometry.location;

						// Center the map and set the lat/lng values
						map.setCenter( location );

						// Set the marker options
						var markerOptions = {
							map : map, position : location, draggable : true
						};

						// Create a new marker, if needed, and set the event listeners
						if ( !marker ) {
							marker = new google.maps.Marker( markerOptions );
						}
						// Marker is already set, just update its options
						else {
							marker.setOptions( markerOptions );
						}
					}
					// Geocode failure
					else {
						alert( "Geocode was not successful for the following reason: " + status );
					}

				} ); // end geocode

			//})();

		} ); // end document ready
	}, false );
</script>