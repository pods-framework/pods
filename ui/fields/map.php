<?php
wp_enqueue_script( 'googlemaps' );
wp_enqueue_style( 'pods-component-address-maps' );

$attributes = array();
$attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );
?>
<div id="<?php echo $attributes['id'] . '-map-canvas' ?>" class="pods-address-maps-map-canvas"></div>

<?php echo PodsForm::label( $name . '-map-lookup-address', 'Address to lookup:' ) ?>
<?php echo PodsForm::field( $name . '-map-lookup-address', '', 'text' ) ?>
<input type="button" name="<?php echo $attributes['id'] . '-map-lookup-button' ?>" id="<?php echo $attributes['id'] . '-map-lookup-button' ?>" value="Lookup Location from Address" />
<?php echo PodsForm::label( $name . '-map-lookup-lat', 'Latitude' ) ?>
<?php echo PodsForm::field( $name . '-map-lookup-lat', '', 'text', array( 'read_only' => 1 ) ) ?>

<?php echo PodsForm::label( $name . '-map-lookup-lon', 'Longitude' ) ?>
<?php echo PodsForm::field( $name . '-map-lookup-lon', '', 'text', array( 'read_only' => 1 ) ) ?>
<script type="text/javascript">
	jQuery( document ).ready( function ( $ ) {
		var mapDiv = document.getElementById( '<?php echo $attributes['id'] . '-map-canvas'?>' );
		var geocodeButton = $( '#<?php echo $attributes['id'] . '-map-lookup-button'  ?>' );
		var addressField = $( '#<?php echo $attributes['id'] . '-map-lookup-address'  ?>' );
		var latField = $( '#<?php echo $attributes['id'] . '-map-lookup-lat'  ?>' );
		var lngField = $( '#<?php echo $attributes['id'] . '-map-lookup-lon'  ?>' );

		var map = null;
		var geocoder = null;
		var marker = null;

		//------------------------------------------------------------------------
		// Initialze the map
		//
		(function () {

			var zoom = 10;
			var center = new google.maps.LatLng( 40.026, -82.936 );

			map = new google.maps.Map( mapDiv, {
				center : center, zoom : zoom, mapTypeId : google.maps.MapTypeId.ROADMAP
			} );

			geocoder = new google.maps.Geocoder();

		})();

		//------------------------------------------------------------------------
		// Geolocate from the address
		//
		geocodeButton.on( 'click', function ( event ) {

			event.preventDefault();

			var address = addressField.val();

			geocoder.geocode( {'address' : address}, function ( results, status ) {

				if ( status == google.maps.GeocoderStatus.OK ) {
					var location = results[0].geometry.location;

					// Center the map and set the lat/lng values
					map.setCenter( location );
					latField.val( location.lat() );
					lngField.val( location.lng() );

					// Set the marker options
					var markerOptions = {
						map : map, position : location, draggable : true
					};

					// Create a new marker, if needed, and set the event listeners
					if ( !marker ) {
						marker = new google.maps.Marker( markerOptions );
						google.maps.event.addListener( marker, 'drag', function () {
							latField.val( marker.getPosition().lat() );
							lngField.val( marker.getPosition().lng() );
						} );
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

		} ); // end button click event

	} ); // end document ready
</script>