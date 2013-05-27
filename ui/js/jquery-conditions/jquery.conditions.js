( function ( $ ) {
    var methods = {
        init : function ( args ) {

        }
    };

    $.fn.conditions = function ( method ) {
        if ( methods[ method ] )
            return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ) );
        else if ( typeof method === 'object' )
            return methods[ 'init' ]( method );

        $.error( 'Method ' + method + ' does not exist on jQuery.conditions' );

        return false;
    };
} )( jQuery );

