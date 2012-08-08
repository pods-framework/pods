(function ( $ ) {
    var methods = {
        prepare : function () {
            var pods_ajaxurl = $( '#pods-wizard-box' ).data( 'url' );

            if ( 'undefined' != typeof pods_ajaxurl )
                pods_ajaxurl = pods_ajaxurl.replace( /\?nojs\=1/, '?pods_ajax=1' );

            if ( 'undefined' != typeof ajaxurl && ('undefined' == typeof pods_ajaxurl || '' == pods_ajaxurl || '?pods_ajax=1' == pods_ajaxurl || document.location.href == pods_ajaxurl || document.location.href.replace( /\?nojs\=1/, '?pods_ajax=1' ) == pods_ajaxurl) )
                pods_ajaxurl = ajaxurl + '?pods_ajax=1';

            if ( $( '#pods-wizard-panel-2 table tbody tr.pods-wizard-table-pending' )[ 0 ] ) {
                var $row = $( '#pods-wizard-panel-2 table tbody tr.pods-wizard-table-pending' ).first();

                $row.removeClass( 'pods-wizard-table-pending' ).addClass( 'pods-wizard-table-active' );

                var postdata = {
                    'action' : $( '#pods-wizard-box' ).data( 'action' ),
                    'method' : $( '#pods-wizard-box' ).data( 'method' ),
                    '_wpnonce' : $( '#pods-wizard-box' ).data( '_wpnonce' ),
                    'step' : 'prepare',
                    'type' : $row.data( 'upgrade' ),
                    'pod' : ''
                };

                if ( 'undefined' != typeof $row.data( 'pod' ) )
                    postdata[ 'pod' ] = $row.data( 'pod' );

                $.ajax( {
                    type : 'POST',
                    url : pods_ajaxurl,
                    cache : false,
                    data : postdata,
                    success : function ( d ) {
                        if ( -1 == d.indexOf( '<e>' ) && -1 != d ) {
                            $row.find( 'td.pods-wizard-count' ).text( d );
                            $row.removeClass( 'pods-wizard-table-active' ).addClass( 'pods-wizard-table-complete' );

                            if ( 'undefined' == typeof $row.data( 'pod' ) )
                                $( '#pods-wizard-panel-3 table tbody tr[data-upgrade="' + $row.data( 'upgrade' ) + '"] td.pods-wizard-count' ).text( d );
                            else
                                $( '#pods-wizard-panel-3 table tbody tr[data-pod="' + $row.data( 'pod' ) + '"] td.pods-wizard-count' ).text( d );

                            // Run next
                            return methods[ 'prepare' ]();
                        }
                        else {
                            $row.removeClass( 'pods-wizard-table-active' ).addClass( 'pods-wizard-table-error' );
                            $row.find( 'td span.pods-wizard-info' ).html( d.replace( '<e>', '' ).replace( '</e>', '' ) );
                            console.log( d.replace( '<e>', '' ).replace( '</e>', '' ) );

                            // Run next
                            return methods[ 'prepare' ]();
                        }
                    },
                    error : function () {
                        $row.removeClass( 'pods-wizard-table-active' ).addClass( 'pods-wizard-table-error pods-wizard-table-failure' );
                        $row.find( 'td span.pods-wizard-info' ).text( 'Unable to process request, please try again.' );
                    },
                    dataType : 'html'
                } );
            }
            else {
                jQuery( '#pods-wizard-next' ).show();
            }
        },
        migrate : function () {
            var pods_ajaxurl = $( '#pods-wizard-box' ).data( 'url' );

            if ( 'undefined' != typeof pods_ajaxurl )
                pods_ajaxurl = pods_ajaxurl.replace( /\?nojs\=1/, '?pods_ajax=1' );

            if ( 'undefined' != typeof ajaxurl && ('undefined' == typeof pods_ajaxurl || '' == pods_ajaxurl || '?pods_ajax=1' == pods_ajaxurl || document.location.href == pods_ajaxurl || document.location.href.replace( /\?nojs\=1/, '?pods_ajax=1' ) == pods_ajaxurl) )
                pods_ajaxurl = ajaxurl + '?pods_ajax=1';

            if ( $( '#pods-wizard-panel-3 table tbody tr.pods-wizard-table-pending' )[ 0 ] ) {
                var $row = $( '#pods-wizard-panel-3 table tbody tr.pods-wizard-table-pending' ).first();

                $row.removeClass( 'pods-wizard-table-pending' ).addClass( 'pods-wizard-table-active' );

                var postdata = {
                    'action' : $( '#pods-wizard-box' ).data( 'action' ),
                    'method' : $( '#pods-wizard-box' ).data( 'method' ),
                    '_wpnonce' : $( '#pods-wizard-box' ).data( '_wpnonce' ),
                    'step' : 'migratexx',
                    'type' : $row.data( 'upgrade' ),
                    'pod' : ''
                };

                if ( 'undefined' != typeof $row.data( 'pod' ) )
                    postdata[ 'pod' ] = $row.data( 'pod' );

                $.ajax( {
                    type : 'POST',
                    url : pods_ajaxurl,
                    cache : false,
                    data : postdata,
                    success : function ( d ) {
                        if ( -1 == d.indexOf( '<e>' ) && -1 != d ) {
                            $row.removeClass( 'pods-wizard-table-active' ).addClass( 'pods-wizard-table-complete' );

                            // Run next
                            return methods[ 'migrate' ]();
                        }
                        else {
                            $row.removeClass( 'pods-wizard-table-active' ).addClass( 'pods-wizard-table-error' );
                            $row.find( 'td span.pods-wizard-info' ).html( d.replace( '<e>', '' ).replace( '</e>', '' ) );
                            console.log( d.replace( '<e>', '' ).replace( '</e>', '' ) );

                            // Run next
                            return methods[ 'migrate' ]();
                        }
                    },
                    error : function () {
                        $row.removeClass( 'pods-wizard-table-active' ).addClass( 'pods-wizard-table-error pods-wizard-table-failure' );
                        $row.find( 'td span.pods-wizard-info' ).text( 'Unable to process request, please try again.' );
                    },
                    dataType : 'html'
                } );
            }
            else {
                jQuery( '#pods-wizard-next' ).click();
            }
        }
    };

    $.fn.PodsUpgrade = function ( method ) {
        return methods[ method ]();
        // go through tr by tr, run if/else checks
    };
})( jQuery );