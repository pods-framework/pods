/**
 * Provides live preview facilities for the event date format fields, akin
 * to (and using the same ajax mechanism as) the date format preview in WP's
 * general settings screen.
 */
jQuery( document ).ready( function( $ ) {
	// Whenever the input field for a date format changes, update the matching
	// live preview area
	$( ".live-date-preview" ).siblings( "input" ).change( function() {
		var $format_field  = $( this );
		var new_format     = $format_field.val();
		var $preview_field = $format_field.siblings( ".live-date-preview" );

		/**
		 * Update the preview field when we get our response back from WP.
		 */
		var show_update = function( preview_text ) {
			preview_text = $( "<div/>" ).html( preview_text ).text(); // Escaping!
			$preview_field.html( preview_text );
		}

		// Before making the request, show the spinner (this should naturally be "wiped"
		// when the response is rendered)
		$preview_field.append( "<span class='spinner'></span>" );
		$preview_field.find( ".spinner" ).css( "visibility", "visible" );

		var request = {
			action: "date_format",
			date:   new_format
		}

		$.post( ajaxurl, request, show_update, "text" );
	} );
} );