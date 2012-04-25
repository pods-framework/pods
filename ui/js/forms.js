jQuery( document ).ready( function () {
    if ( jQuery( '#pods-form-ui-code3' ).data( 'width' ) ) {
        jQuery( '#pods-form-ui-code3' ).cleditor( {
                                                      width : jQuery( '#pods-form-ui-code3' ).data( 'width' )
                                                  } );
    }
    else {
        jQuery( '#pods-form-ui-code3' ).cleditor();
    }
    jQuery( '.chosen' ).chosen();

    // Click anywhere on a pods-boolean field and select its input element
    jQuery( '.pods-boolean' ).click( function ( evt ) {
        jQuery( evt.target ).children( 'input' ).click();
    } );
} );