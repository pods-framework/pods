// General panel scripts .
function pods_templates_randomUUID() {
	var s = [], itoh = '0123456789ABCDEF';
	for ( var i = 0; i < 6; i++ ) {
		s[i] = Math.floor( Math.random() * 0x10 );
	}
	return s.join( '' );
}

var pods_templates_field_callbacks = [];
jQuery( function ( $ ) {
	// add row
	$( 'body' ).on( 'click', '.pods_templates-add-group-row', function () {
		var clicked = $( this ), rowid = pods_templates_randomUUID(),
			template = $( '#' + clicked.data( 'rowtemplate' ) ).html().replace( /{{id}}/g, rowid );
		if ( clicked.data( 'field' ) ) {
			var ref = clicked.data( 'field' ).split( '-' );
			template = template.replace( /\_\_i\_\_/g, ref[ref.length - 2] );
		}
		//console.log(clicked.parent().parent().find('.groupitems').last());
		template = template.replace( /\_\_count\_\_/g, clicked.parent().parent().find( '.groupitems' ).length );
		clicked.parent().before( template );

		for ( var callback in pods_templates_field_callbacks ) {
			if ( typeof window[pods_templates_field_callbacks[callback]] === 'function' ) {
				window[pods_templates_field_callbacks[callback]]();
			}
		}

	} );
	$( 'body' ).on( 'click', '.pods_templates-removeRow', function () {
		$( this ).next().remove();
		$( this ).remove();
		////console.log(this);
	} );
	// tabs
	$( 'body' ).on( 'click', '.pods_templates-metabox-config-nav li a, .pods_templates-shortcode-config-nav li a, .pods_templates-settings-config-nav li a, .pods_templates-widget-config-nav li a', function () {
		$( this ).parent().parent().find( '.current' ).removeClass( 'current' );
		$( this ).parent().parent().parent().parent().find( '.group' ).hide();
		$( '' + $( this ).attr( 'href' ) + '' ).show();
		$( this ).parent().addClass( 'current' );
		if ( $( this ).data( 'tabset' ).length ) {
			$( '#' + $( this ).data( 'tabset' ) ).val( $( this ).data( 'tabkey' ) );
		}
		return false;
	} );

	// initcallbacks
	setInterval( function () {
		$( '.pods_templates-init-callback' ).each( function ( k, v ) {
			var callback = $( this );
			if ( typeof window[callback.data( 'init' )] === 'function' ) {
				window[callback.data( 'init' )]();
				callback.removeClass( 'pods_templates-init-callback' );
			}
		} );
	}, 100 );
} );
