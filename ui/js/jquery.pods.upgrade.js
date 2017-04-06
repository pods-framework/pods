/*@global PodsI18n */
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
                    'pod' : '',
                    'version' : $( '#pods-wizard-box' ).data( 'version' )
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
                            $row.removeClass( 'pods-wizard-table-active' ).addClass( 'pods-wizard-table-warning' );
                            $row.find( 'td span.pods-wizard-info' ).html( d.replace( '<e>', '' ).replace( '</e>', '' ) );
                            if ( window.console ) console.log( d.replace( '<e>', '' ).replace( '</e>', '' ) );

                            // Run next
                            return methods[ 'prepare' ]();
                        }
                    },
                    error : function () {
                        $row.removeClass( 'pods-wizard-table-active' ).addClass( 'pods-wizard-table-error' );
                        $row.find( 'td span.pods-wizard-info' ).text( PodsI18n.__( 'Unable to process request, please try again.' ) );
                    },
                    dataType : 'html'
                } );
            }
            else {
                $( '#pods-wizard-next' ).show();
            }
        },
        migrate : function ( postdata, $row ) {
            var pods_ajaxurl = $( '#pods-wizard-box' ).data( 'url' );

            if ( 'undefined' != typeof pods_ajaxurl )
                pods_ajaxurl = pods_ajaxurl.replace( /\?nojs\=1/, '?pods_ajax=1' );

            if ( 'undefined' != typeof ajaxurl && ('undefined' == typeof pods_ajaxurl || '' == pods_ajaxurl || '?pods_ajax=1' == pods_ajaxurl || document.location.href == pods_ajaxurl || document.location.href.replace( /\?nojs\=1/, '?pods_ajax=1' ) == pods_ajaxurl) )
                pods_ajaxurl = ajaxurl + '?pods_ajax=1';

            if ( 'undefined' != typeof postdata || $( '#pods-wizard-panel-3 table tbody tr.pods-wizard-table-pending' )[ 0 ] ) {
                if ( 'undefined' == typeof $row )
                    var $row = $( '#pods-wizard-panel-3 table tbody tr.pods-wizard-table-pending' ).first();

                if ( 'undefined' == typeof postdata ) {
                    $row.removeClass( 'pods-wizard-table-pending' ).addClass( 'pods-wizard-table-active' );

                    var postdata = {
                        'action' : $( '#pods-wizard-box' ).data( 'action' ),
                        'method' : $( '#pods-wizard-box' ).data( 'method' ),
                        '_wpnonce' : $( '#pods-wizard-box' ).data( '_wpnonce' ),
                        'step' : 'migrate',
                        'type' : $row.data( 'upgrade' ),
                        'pod' : '',
                        'version' : $( '#pods-wizard-box' ).data( 'version' )
                    };

                    if ( 'undefined' != typeof $row.data( 'pod' ) )
                        postdata[ 'pod' ] = $row.data( 'pod' );
                }

                $.ajax( {
                    type : 'POST',
                    url : pods_ajaxurl,
                    cache : false,
                    data : postdata,
                    success : function ( d ) {
                        if ( -1 == d.indexOf( '<e>' ) && '-1' != d ) {
                            if ( '-2' == d ) {
                                // Run next
                                return methods[ 'migrate' ]( postdata, $row );
                            }
                            else if ( '1' == d ) {
                                $row.removeClass( 'pods-wizard-table-active' ).addClass( 'pods-wizard-table-complete' );

                                // Run next
                                return methods[ 'migrate' ]();
                            }
                            else if ( ( d.length - 2 ) == d.indexOf( '-2' ) ) {
                                $row.removeClass( 'pods-wizard-table-active' ).addClass( 'pods-wizard-table-warning' );
                                $row.find( 'td span.pods-wizard-info' ).html( d.replace( '<e>', '' ).replace( '</e>', '' ) );
                                if ( window.console ) console.log( d.replace( '<e>', '' ).replace( '</e>', '' ) );

                                // Run next
                                return methods[ 'migrate' ]( postdata, $row );
                            }
                            else if ( ( d.length - 1 ) == d.indexOf( '1' ) ) {
                                $row.removeClass( 'pods-wizard-table-active' ).addClass( 'pods-wizard-table-warning' );
                                $row.find( 'td span.pods-wizard-info' ).html( d.replace( '<e>', '' ).replace( '</e>', '' ) );
																if ( window.console ) console.log( d.replace( '<e>', '' ).replace( '</e>', '' ) );

                                // Run next
                                return methods[ 'migrate' ]();
                            }
                            else {
                                $row.removeClass( 'pods-wizard-table-active' ).addClass( 'pods-wizard-table-error' );
                                $row.find( 'td span.pods-wizard-info' ).html( d.replace( '<e>', '' ).replace( '</e>', '' ) );
																if ( window.console ) console.log( d.replace( '<e>', '' ).replace( '</e>', '' ) );
                            }
                        }
                        else if ( -1 < d.indexOf( 'Database Error;' ) ) {
                            $row.removeClass( 'pods-wizard-table-active' ).addClass( 'pods-wizard-table-error' );
                            $row.find( 'td span.pods-wizard-info' ).html( d.replace( '<e>', '' ).replace( '</e>', '' ) );
														if ( window.console ) console.log( d.replace( '<e>', '' ).replace( '</e>', '' ) );
                        }
                        else {
                            $row.removeClass( 'pods-wizard-table-active' ).addClass( 'pods-wizard-table-warning' );
                            $row.find( 'td span.pods-wizard-info' ).html( d.replace( '<e>', '' ).replace( '</e>', '' ) );
														if ( window.console ) console.log( d.replace( '<e>', '' ).replace( '</e>', '' ) );

                            // Run next
                            return methods[ 'migrate' ]();
                        }
                    },
                    error : function () {
                        $row.removeClass( 'pods-wizard-table-active' ).addClass( 'pods-wizard-table-error' );
                        $row.find( 'td span.pods-wizard-info' ).text( PodsI18n.__( 'Unable to process request, please try again.' ) );
                    },
                    dataType : 'html'
                } );
            }
            else {
                $( '#pods-wizard-next' ).click().text( 'Start using Pods' ).addClass( 'finished' );
                $( '#pods-wizard-next' ).off( 'click' );
                $( '#pods-wizard-next' ).prop( 'href', 'admin.php?page=pods' );
                $( '#pods-wizard-next' ).show();
                $( '#pods-wizard-finished' ).show();
            }
        }
    };

    $.fn.PodsUpgrade = function ( method ) {
        return methods[ method ]();
        // go through tr by tr, run if/else checks
    };
})( jQuery );