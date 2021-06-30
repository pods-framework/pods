jQuery( function() {

	var maxHeight = 0;
	jQuery( "div.tribe-addon .caption" ).each( function() {
		var h = jQuery( this ).height();
		maxHeight = h > maxHeight ? h : maxHeight;
	} );

	jQuery( "div.tribe-addon:not(.first) .caption" ).css( 'height', maxHeight );
} );