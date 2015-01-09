(function ( $ ) {
    var methods = {
        prepare : function () {
            var $wizard_box = $( '#pods-wizard-box' ),
                pods_ajaxurl = $wizard_box.data( 'url' );

            if ( 'undefined' != typeof pods_ajaxurl && '' != pods_ajaxurl )
                pods_ajaxurl = pods_ajaxurl.replace( /\?nojs\=1/, '?pods_ajax=1' );

            if ( 'undefined' != typeof ajaxurl && ( 'undefined' == typeof pods_ajaxurl || '' == pods_ajaxurl || '?pods_ajax=1' == pods_ajaxurl || document.location.href == pods_ajaxurl || document.location.href.replace( /\?nojs\=1/, '?pods_ajax=1' ) == pods_ajaxurl ) )
                pods_ajaxurl = ajaxurl + '?pods_ajax=1';

            var $table_pending = $( '#pods-wizard-panel-2 table tbody tr.pods-wizard-table-pending' );

            if ( $table_pending[ 0 ] ) {
                var $row = $table_pending.first();

                $row.removeClass( 'pods-wizard-table-pending' ).addClass( 'pods-wizard-table-active' );

                var postdata = {
                    'action' : $wizard_box.data( 'action' ),
                    'method' : $wizard_box.data( 'method' ),
                    '_wpnonce' : $wizard_box.data( '_wpnonce' ),
                    'migration' : $wizard_box.data( 'migration' ),
                    'step' : 'prepare',
                    'type' : $row.data( 'migrate' ),
                    'object' : ''
                };

                if ( 'undefined' != typeof $row.data( 'object' ) )
                    postdata[ 'object' ] = $row.data( 'object' );

                var $step_3 = $( '#pods-wizard-panel-3 table tbody' ),
                    $table = $row.parent();

                $.ajax( {
                    type : 'POST',
                    url : pods_ajaxurl,
                    cache : false,
                    data : postdata,
                    success : function ( d ) {
                        var message = d.replace( '<e>', '' ).replace( '</e>', '' );

                        if ( -1 == d.indexOf( '<e>' ) && -1 != d ) {
                            $row.find( 'td.pods-wizard-count' ).text( d );
                            $row.removeClass( 'pods-wizard-table-active' ).addClass( 'pods-wizard-table-complete' );

                            if ( 'undefined' != typeof $row.data( 'object' ) ) {
                                $table.find( 'tr[data-migrate="' + $row.data( 'migrate' ) + '"][data-object="' + $row.data( 'object' ) + '"] td.pods-wizard-count' ).text( d );
                                $step_3.find( 'tr[data-migrate="' + $row.data( 'migrate' ) + '"][data-object="' + $row.data( 'object' ) + '"] td.pods-wizard-count' ).text( d );
                            }
                            else {
                                $table.find( 'tr[data-migrate="' + $row.data( 'migrate' ) + '"] td.pods-wizard-count' ).text( d );
                                $step_3.find( 'tr[data-migrate="' + $row.data( 'migrate' ) + '"] td.pods-wizard-count' ).text( d );
                            }
                        }
                        else {
                            $row.removeClass( 'pods-wizard-table-active' ).addClass( 'pods-wizard-table-warning' );
                            $row.find( 'td span.pods-wizard-info' ).html( message );

                            if ( window.console ) console.log( message );
                        }

                        // Run next
                        return methods[ 'prepare' ]();
                    },
                    error : function () {
                        $row.removeClass( 'pods-wizard-table-active' ).addClass( 'pods-wizard-table-error' );
                        $row.find( 'td span.pods-wizard-info' ).text( 'Unable to process request, please try again.' );
                    },
                    dataType : 'html'
                } );
            }
            else {
                $( '#pods-wizard-next' ).show();
            }
        },
        migrate : function ( postdata, $row ) {
            var $wizard_box = $( '#pods-wizard-box' ),
                pods_ajaxurl = $wizard_box.data( 'url' );

            if ( 'undefined' != typeof pods_ajaxurl )
                pods_ajaxurl = pods_ajaxurl.replace( /\?nojs\=1/, '?pods_ajax=1' );

            if ( 'undefined' != typeof ajaxurl && ('undefined' == typeof pods_ajaxurl || '' == pods_ajaxurl || '?pods_ajax=1' == pods_ajaxurl || document.location.href == pods_ajaxurl || document.location.href.replace( /\?nojs\=1/, '?pods_ajax=1' ) == pods_ajaxurl) )
                pods_ajaxurl = ajaxurl + '?pods_ajax=1';

            var $table_pending = $( '#pods-wizard-panel-3 table tbody tr.pods-wizard-table-pending' );

            if ( 'undefined' != typeof postdata || $table_pending[ 0 ] ) {
                if ( 'undefined' == typeof $row )
                    $row = $table_pending.first();

                if ( 'undefined' == typeof postdata ) {
                    $row.removeClass( 'pods-wizard-table-pending' ).addClass( 'pods-wizard-table-active' );

                    postdata = {
                        'action' : $wizard_box.data( 'action' ),
                        'method' : $wizard_box.data( 'method' ),
                        '_wpnonce' : $wizard_box.data( '_wpnonce' ),
                        'migration' : $wizard_box.data( 'migration' ),
                        'step' : 'migrate',
                        'type' : $row.data( 'migrate' ),
                        'object' : ''
                    };

                    if ( 'undefined' != typeof $row.data( 'object' ) )
                        postdata[ 'object' ] = $row.data( 'object' );
                }

                $.ajax( {
                    type : 'POST',
                    url : pods_ajaxurl,
                    cache : false,
                    data : postdata,
                    success : function ( d ) {
                        var message = d.replace( '<e>', '' ).replace( '</e>', '' );

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
                                $row.find( 'td span.pods-wizard-info' ).html( message );

                                if ( window.console ) console.log( message );

                                // Run next
                                return methods[ 'migrate' ]( postdata, $row );
                            }
                            else if ( ( d.length - 1 ) == d.indexOf( '1' ) ) {
                                $row.removeClass( 'pods-wizard-table-active' ).addClass( 'pods-wizard-table-warning' );
                                $row.find( 'td span.pods-wizard-info' ).html( message );

                                if ( window.console ) console.log( message );

                                // Run next
                                return methods[ 'migrate' ]();
                            }
                            else {
                                $row.removeClass( 'pods-wizard-table-active' ).addClass( 'pods-wizard-table-error' );
                                $row.find( 'td span.pods-wizard-info' ).html( message );

                                if ( window.console ) console.log( message );
                            }
                        }
                        else if ( -1 < d.indexOf( 'Database Error;' ) ) {
                            $row.removeClass( 'pods-wizard-table-active' ).addClass( 'pods-wizard-table-error' );
                            $row.find( 'td span.pods-wizard-info' ).html( message );

                            if ( window.console ) console.log( message );
                        }
                        else {
                            $row.removeClass( 'pods-wizard-table-active' ).addClass( 'pods-wizard-table-warning' );
                            $row.find( 'td span.pods-wizard-info' ).html( message );

                            if ( window.console ) console.log( message );

                            // Run next
                            return methods[ 'migrate' ]();
                        }

                        return true;
                    },
                    error : function () {
                        $row.removeClass( 'pods-wizard-table-active' ).addClass( 'pods-wizard-table-error' );
                        $row.find( 'td span.pods-wizard-info' ).text( 'Unable to process request, please try again.' );
                    },
                    dataType : 'html'
                } );
            }
            else {
                var $wizard_next = $( '#pods-wizard-next' );
                $wizard_next.click().text( 'Start using Pods' ).addClass( 'finished' );
                $wizard_next.off( 'click' );
                $wizard_next.prop( 'href', 'admin.php?page=pods' );
                $wizard_next.show();
                $( '#pods-wizard-finished' ).show();
            }
        }
    };

    $.fn.PodsMigrate = function ( method ) {
        return methods[ method ]();
        // go through tr by tr, run if/else checks
    };
})( jQuery );