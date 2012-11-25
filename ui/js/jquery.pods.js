(function ( $ ) {
    var pods_changed = false,
        pods_form_field_names = [],
        methods = {
            validate : function () {
                var $containers = $( 'form.pods-submittable, .pods-validation' ),
                    form_fields = 'input.pods-validate, select.pods-validate, textarea.pods-validate';

                // handle required
                $containers.on( 'change keyup blur', form_fields.replace( ',', '.pods-validate-required,' ) + '.pods-validate-required', function () {
                    var $el = $( this );

                    if ( !$el.is( ':visible' ) )
                        return;

                    var label = '';

                    if ( 'undefined' != typeof $el.data( 'label' ) )
                        label = $el.data( 'label' );
                    else if ( 0 < $el.parent().find( 'label' ).length )
                        label = $el.parent().find( 'label' ).html().trim();
                    else
                        label = $el.prop( 'name' ).trim().replace( '_', ' ' );

                    // TinyMCE support
                    if ( 'object' == typeof tinyMCE && -1 < $el.prop( 'class' ).indexOf( 'pods-ui-field-tinymce' ) )
                        tinyMCE.triggerSave();

                    var valid_field = true;

                    if ( $el.is( 'input[type=checkbox]' ) ) {
                        if ( !$el.is( ':checked' ) && !$el.hasClass( 'pods-form-ui-field-type-pick' ) )
                            valid_field = false;
                    }
                    else if ( '' == $el.val() )
                        valid_field = false;

                    if ( !valid_field && ( !$el.is( 'input[type=checkbox]' ) || !$el.hasClass( 'pods-form-ui-field-type-pick' ) ) ) {
                        if ( -1 == pods_form_field_names.indexOf( $el.prop( 'name' ) ) ) {
                            $el.parent().append( '<div class="pods-validate-error-message">' + label.replace( /(<([^>]+)>)/ig, '' ) + ' is required.</div>' );
                            $el.addClass( 'pods-validate-error' );

                            pods_form_field_names.push( $el.prop( 'name' ) );
                        }
                    }
                    else {
                        $el.parent().find( '.pods-validate-error-message' ).remove();
                        $el.removeClass( 'pods-validate-error' );

                        if ( 0 < pods_form_field_names.indexOf( $el.prop( 'name' ) ) )
                            pods_form_field_names.splice( pods_form_field_names.indexOf( $el.prop( 'name' ) ), 1 );
                    }
                } );
            },
            submit : function () {
                var $submitbutton;

                // Handle submit of form and translate to AJAX
                $( 'form.pods-submittable' ).on( 'submit', function ( e ) {
                    var $submittable = $( this );

                    pods_changed = false;

                    e.preventDefault();

                    pods_ajaxurl = $submittable.attr( 'action' );
                    pods_ajaxurl = pods_ajaxurl.replace( /\?nojs\=1/, '?pods_ajax=1' );

                    if ( 'undefined' != typeof ajaxurl && ('' == pods_ajaxurl || '?pods_ajax=1' == pods_ajaxurl || document.location.href == pods_ajaxurl || document.location.href.replace( /\?nojs\=1/, '?pods_ajax=1' ) == pods_ajaxurl) )
                        pods_ajaxurl = ajaxurl + '?pods_ajax=1';

                    if ( 'undefined' != typeof ajaxurl && $submittable.hasClass( 'pods-submittable-ajax' ) )
                        pods_ajaxurl = ajaxurl + '?pods_ajax=1';

                    var postdata = {};
                    var field_data = {};

                    var valid_form = true;

                    var field_id = 0,
                        field_index = 0;

                    // See if we have any instances of tinyMCE and save them
                    if( 'undefined' != typeof tinyMCE )
                        tinyMCE.triggerSave();

                    $submittable.find( '.pods-submittable-fields' ).find( 'input, select, textarea' ).each( function () {
                        var $el = $( this );
                        var field_name = $el.prop( 'name' );

                        if ( '' != field_name && 0 != field_name.indexOf( 'field_data[' ) ) {
                            var val = $el.val();

                            if ( $el.is( 'input[type=checkbox]' ) && !$el.is( ':checked' ) )
                                val = 0;
                            else if ( $el.is( 'input[type=radio]' ) && !$el.is( ':checked' ) )
                                return true; // This input is not checked, continue the loop

                            if ( $el.is( ':visible' ) && $el.hasClass( 'pods-validate pods-validate-required' ) && ( '' == $el.val() ) ) {
                                $el.trigger( 'change' );

                                if ( false !== valid_form )
                                    $el.focus();

                                valid_form = false;
                            }

                            postdata[ field_name ] = val;
                        }
                    } );

                    if ( false === valid_form ) {
                        $submittable.addClass( 'invalid-form' );

                        // re-enable the submit button
                        $( $submittable ).find( 'input[type=submit], button[type=submit]' ).each( function () {
                            var $submitbutton = $( this );

                            $submitbutton.css( 'cursor', 'pointer' );
                            $submitbutton.prop( 'disabled', false );
                            $submitbutton.parent().find( '.waiting' ).fadeOut();
                        } );

                        pods_form_field_names = [];

                        return false;
                    }
                    else
                        $submittable.removeClass( 'invalid-form' );

                    pods_ajaxurl = pods_ajaxurl + '&action=' + postdata.action;

                    $submitbutton = $submittable.find( 'input[type=submit], button[type=submit]' );

                    $.ajax( {
                        type : 'POST',
                        dataType : 'html',
                        url : pods_ajaxurl,
                        cache : false,
                        data : postdata,
                        success : function ( d ) {
                            if ( -1 == d.indexOf( '<e>' ) && -1 == d.indexOf('</e>') && -1 != d ) {
                                if ( 'undefined' != typeof pods_admin_submit_callback )
                                    pods_admin_submit_callback( d );
                                else if ( 'undefined' != typeof $submittable.data( 'location' ) )
                                    document.location.href = $submittable.data( 'location' ).replace( 'X_ID_X', parseInt( d ) );
                                else
                                    document.location.reload( true );
                            }
                            else if ( 'undefined' != typeof $submittable.data( 'error-location' ) )
                                document.location.href = $submittable.data( 'error-location' );
                            else {
                                var err_msg = d.replace( '<e>', '' ).replace( '</e>', '' );
                                if ( 'undefined' != typeof pods_admin_submit_error_callback )
                                    pods_admin_submit_error_callback( err_msg );

                                $submitbutton.css( 'cursor', 'pointer' );
                                $submitbutton.prop( 'disabled', false );
                                $submitbutton.parent().find( '.waiting' ).fadeOut();

                                if ( $( '#pods-wizard-next' )[ 0 ] ) {
                                    $( '#pods-wizard-next' ).css( 'cursor', 'pointer' );
                                    $( '#pods-wizard-next' ).prop( 'disabled', false );
                                    $( '#pods-wizard-next' ).text( $( '#pods-wizard-next' ).data( 'next' ) );
                                }

                                alert( 'Error: ' + err_msg );
                                console.log( err_msg );
                            }
                        },
                        error : function () {
                           $submitbutton.css( 'cursor', 'pointer' );
                           $submitbutton.prop( 'disabled', false );
                           $submitbutton.parent().find( '.waiting' ).fadeOut();

                            if ( $( '#pods-wizard-next' )[ 0 ] ) {
                                $( '#pods-wizard-next' ).css( 'cursor', 'pointer' );
                                $( '#pods-wizard-next' ).prop( 'disabled', false );
                                $( '#pods-wizard-next' ).text( $( '#pods-wizard-next' ).data( 'next' ) );
                            }

                            alert( 'Unable to process request, please try again.' );
                        }
                    } );

                } )// Handle submit button and show waiting image
                .on( 'click', 'input[type=submit], button[type=submit]', function ( e ) {
                    pods_changed = false;

                    e.preventDefault();

                    $( 'div#message' ).slideUp( 'fast', function() {
                        $( this ).remove();
                     } );

                    var $submitbutton = $( this );
                    $submitbutton.css( 'cursor', 'default' );
                    $submitbutton.prop( 'disabled', true );
                    $submitbutton.parent().find( '.waiting' ).fadeIn();

                    $( this ).closest( 'form.pods-submittable' ).trigger( 'submit' );
                } );

                // Handle submit via link and translate to AJAX
                $( 'form.pods-submittable a.pods-submit' ).on( 'click', function ( e ) {
                    var $submitbutton = $( this );

                    e.preventDefault();

                    pods_ajaxurl = $submitbutton.data( 'ajaxurl' );

                    if ( 'undefined' != typeof pods_ajaxurl )
                        pods_ajaxurl = pods_ajaxurl.replace( /\?nojs\=1/, '?pods_ajax=1' );
                    else if ( 'undefined' != typeof ajaxurl && ('undefined' == typeof pods_ajaxurl || '' == pods_ajaxurl || '?pods_ajax=1' == pods_ajaxurl || document.location.href == pods_ajaxurl || document.location.href.replace( /\?nojs\=1/, '?pods_ajax=1' ) == pods_ajaxurl) )
                        pods_ajaxurl = ajaxurl + '?pods_ajax=1';

                    var postdata = $submitbutton.data();

                    if ( 'undefined' != typeof $submitbutton.data( 'confirm' ) && !confirm( $submitbutton.data( 'confirm' ) ) )
                        return false;

                    $( 'div#message' ).slideUp( 'fast', function () {
                        $( this ).remove();
                    } );

                    pods_changed = false;

                    pods_ajaxurl = pods_ajaxurl + '&action=' + postdata.action;

                    $.ajax( {
                        type : 'POST',
                        dataType : 'html',
                        url : pods_ajaxurl,
                        cache : false,
                        data : postdata,
                        success : function ( d ) {
                            if ( -1 == d.indexOf( '<e>' ) && -1 != d ) {
                                if ( 'undefined' != typeof pods_admin_submit_callback )
                                    pods_admin_submit_callback( d );
                                else if ( 'undefined' != typeof $submitbutton.data( 'location' ) )
                                    document.location.href = $submitbutton.data( 'location' ).replace( 'X_ID_X', parseInt( d ) );
                                else
                                    document.location.reload( true );
                            }
                            else if ( 'undefined' != typeof $submitbutton.data( 'error-location' ) )
                                document.location.href = $submitbutton.data( 'error-location' );
                            else {
                                var err_msg = d.replace( '<e>', '' ).replace( '</e>', '' );
                                if ( 'undefined' != typeof pods_admin_submit_error_callback )
                                    pods_admin_submit_error_callback( err_msg );

                                $submitbutton.css( 'cursor', 'pointer' );
                                $submitbutton.prop( 'disabled', false );
                                $submitbutton.parent().find( '.waiting' ).fadeOut();

                                alert( 'Error: ' + err_msg );
                                console.log( err_msg );
                            }
                        },
                        error : function () {
                           $submitbutton.css( 'cursor', 'pointer' );
                           $submitbutton.prop( 'disabled', false );
                           $submitbutton.parent().find( '.waiting' ).fadeOut();

                            alert( 'Unable to process request, please try again.' );
                        }
                    } );
                } );
            },
            sluggable : function () {
                // Setup selector
                var $sluggable = $( '.pods-sluggable' ),
                    last_slug = null;

                if ( 0 !== $sluggable.length ) {
                    // Hold onto slug in-case change is cancelled
                    if ( $sluggable.find( '.pods-slug-edit input[type=text]' )[ 0 ] ) {
                        last_slug = $sluggable.find( '.pods-slug-edit input[type=text]' ).val();

                        last_slug = last_slug.replace( /<(?:.)*?>/g, '' ).replace( /([^0-9a-zA-Z ])/g, '' );

                        $( '.pods-slugged-lower:not(.pods-slugged[data-sluggable])' ).html( last_slug.toLowerCase() );
                        $( '.pods-slugged:not(.pods-slugged[data-sluggable])' ).html( last_slug.charAt( 0 ).toUpperCase() + last_slug.slice( 1 ) );
                    }

                    // Handle click to edit
                    $sluggable.on( 'click', '.pods-slug em, .pods-slug input[type=button]', function () {
                        $( this ).css( 'cursor', 'default' );
                        $( this ).prop( 'disabled', true );

                        $( this ).closest( '.pods-sluggable' ).find( '.pods-slug, .pods-slug-edit' ).toggle();
                        $( this ).closest( '.pods-sluggable' ).find( '.pods-slug-edit input[type=text]' ).focus();

                        $( this ).css( 'cursor', 'pointer' );
                        $( this ).prop( 'disabled', false );
                    } );

                    // Handle slug save
                    $sluggable.on( 'click', '.pods-slug-edit input[type=button]', function () {
                        $( this ).css( 'cursor', 'default' );
                        $( this ).prop( 'disabled', true );

                        last_slug = $( this ).parent().find( 'input[type=text]' ).val();

                        last_slug = last_slug.replace( /<(?:.)*?>/g, '' ).replace( /([^0-9a-zA-Z ])/g, '' );

                        $( this ).closest( '.pods-sluggable' ).find( '.pods-slug em' ).html( last_slug );
                        $( '.pods-slugged-lower:not(.pods-slugged[data-sluggable])' ).html( last_slug.toLowerCase() );
                        $( ".pods-slugged:not(.pods-slugged[data-sluggable])" ).html( last_slug.charAt( 0 ).toUpperCase() + last_slug.slice( 1 ) );
                        $( this ).closest( '.pods-sluggable' ).find( '.pods-slug, .pods-slug-edit' ).toggle();

                        $( this ).css( 'cursor', 'pointer' );
                        $( this ).prop( 'disabled', false );
                    } );

                    // Handle cancel slug edit
                    $sluggable.on( 'click', '.pods-slug-edit a.cancel', function ( e ) {
                        $( this ).css( 'cursor', 'default' );
                        $( this ).prop( 'disabled', true );

                        $( this ).parent().find( 'input[type=text]' ).val( last_slug );
                        $( this ).closest( '.pods-sluggable' ).find( '.pods-slug, .pods-slug-edit' ).toggle();

                        $( this ).css( 'cursor', 'pointer' );
                        $( this ).prop( 'disabled', false );

                        e.preventDefault();
                    } );
                    $sluggable.find( '.pods-slug-edit' ).hide();
                }

                var sluggables = [];

                $( '.pods-slugged[data-sluggable], .pods-slugged-lower[data-sluggable]' ).each( function () {
                    if ( -1 == sluggables.indexOf( $( this ).data( 'sluggable' ) ) )
                        sluggables.push( $( this ).data( 'sluggable' ) );
                } );

                for ( var i in sluggables ) {
                    var sluggable = sluggables[ i ];

                    methods[ 'sluggable_single' ]( sluggable );
                }
            },
            sluggable_single : function ( sluggable ) {
                var $slug = $( 'input[name="' + sluggable.replace( '[', '\\[' ).replace( ']', '\\]' ) + '"]' );

                if ( $slug[ 0 ] ) {
                    $( 'form' ).on( 'change', 'input[name="' + sluggable.replace( '[', '\\[' ).replace( ']', '\\]' ) + '"]', function () {
                        if ( 0 < $( this ).val().length ) {
                            var slug = $( this ).val();

                            slug = slug.replace( /<(?:.)*?>/g, '' ).replace( /([^0-9a-zA-Z ])/g, '' );

                            // update fields
                           $( 'input.pods-slugged[data-sluggable="' + $( this ).prop( 'name' ).replace( '[', '\\[' ).replace( ']', '\\]' ) + '"]' ).each( function () {
                                if ( '' == $( this ).val() ) {
                                    $( this ).val( slug.charAt( 0 ).toUpperCase() + slug.slice( 1 ) );
                                    $( this ).trigger( 'change' );
                                }
                            } );
                           $( 'input.pods-slugged-lower[data-sluggable="' + $( this ).prop( 'name' ).replace( '[', '\\[' ).replace( ']', '\\]' ) + '"]' ).each( function () {
                                if ( '' == $( this ).val() ) {
                                    $( this ).val( slug.toLowerCase() );
                                    $( this ).trigger( 'change' );
                                }
                            } );

                            // update elements and trigger change
                           $( '.pods-slugged-lower[data-sluggable="' + $( this ).prop( 'name' ).replace( '[', '\\[' ).replace( ']', '\\]' ) + '"]:not(input)' )
                                .html( slug.toLowerCase() )
                                .trigger( 'change' );

                            // trigger change
                           $( '.pods-slugged[data-sluggable="' + $( this ).prop( 'name' ).replace( '[', '\\[' ).replace( ']', '\\]' ) + '"]:not(input)' )
                                .html( slug.charAt( 0 ).toUpperCase() + slug.slice( 1 ) )
                                .trigger( 'change' );
                        }
                    } );

                    if ( 0 < $slug.val().length ) {
                        $slug.trigger( 'change' );
                    }
                }
            },
            tabbed : function () {
                $( '.pods-admin' ).on( 'click', '.pods-tabs .pods-tab a', function ( e ) {
                    $( this ).css( 'cursor', 'default' );
                    $( this ).prop( 'disabled', true );

                    var tab_class = '.pods-tabbed';

                    if ( 'undefined' != typeof $( this ).closest( '.pods-tabs' ).data( 'tabbed' ) )
                        tab_class = $( this ).closest( '.pods-tabs' ).data( 'tabbed' );

                    var $tabbed = $( this ).closest( tab_class );
                    var tab_hash = this.hash;

                    if ( $tabbed.find( '.pods-tabs .pods-tab a[data-tabs]' )[ 0 ] ) {
                        $tabbed.find( '.pods-tabs .pods-tab a[data-tabs]' ).each( function () {
                            var tabs = $( this ).data( 'tabs' ),
                                this_tab_hash = this.hash;

                            if ( tab_hash != this_tab_hash )
                                $tabbed.find( tabs ).hide();
                            else
                                $tabbed.find( tabs ).show();
                        } );
                    }
                    else {
                        $tabbed.find( '.pods-tab-group .pods-tab' ).not( tab_hash ).slideUp( 'fast', function () {
                            $tabbed.find( '.pods-tab-group .pods-tab' ).filter( tab_hash ).each( function () {
                                $this = $( this );
                                $( '.pods-dependent-toggle', $this ).trigger( 'change' );
                                $this.slideDown( 'fast' );
                            } );
                        } );
                    }

                    $tabbed.find( '.pods-tabs .pods-tab a' ).removeClass( 'selected' );

                    $( this ).addClass( 'selected' );

                    $( this ).css( 'cursor', 'pointer' );
                    $( this ).prop( 'disabled', false );

                    e.preventDefault();
                } );

                $( '.pods-tabbed' ).each( function () {
                    $( 'ul.pods-tabs .pods-tab:first a', this ).addClass( 'selected' );
                    $( '.pods-tab-group .pods-tab:first' ).each( function () {
                        $( '.pods-dependent-toggle', this ).trigger( 'change' );
                        $( this ).show();
                    } )
                } );
            },
            wizard : function () {
                var methods = {
                    setFinished : function () {
                        $( '#pods-wizard-next' ).text( $( '#pods-wizard-next' ).data('finished' ) );
                    },
                    setProgress : function () {
                        $( '#pods-wizard-next' ).text( $( '#pods-wizard-next' ).data( 'next ' ) );
                    },
                    stepBackward : function () {
                        $( '#pods-wizard-next' ).css( 'cursor', 'pointer' );
                        $( '#pods-wizard-next' ).prop( 'disabled', false );
                        $( '#pods-wizard-next' ).text( $( '#pods-wizard-next' ).data( 'next' ) );

                        // Step toolbar menu state forwards
                        $( 'li.pods-wizard-menu-current' )
                            .removeClass( 'pods-wizard-menu-current pods-wizard-menu-complete' )
                            .prev( 'li' )
                            .removeClass( 'pods-wizard-menu-complete' )
                            .addClass( 'pods-wizard-menu-current' );

                        // Get current step #
                        var step = false;

                        if ( $( 'li.pods-wizard-menu-current[data-step]' )[ 0 ] )
                            step = $( 'li.pods-wizard-menu-current' ).data( 'step' );

                        // Show start over button
                        if ( 1 == step )
                           $( '#pods-wizard-start' ).hide();
                        else
                           $( '#pods-wizard-start' ).show();

                        // Check if last step
                        if ( $( 'div.pods-wizard-panel:visible' ).prev( 'div.pods-wizard-panel' ).length ) {
                            // Show next panel
                           $( 'div.pods-wizard-panel:visible' )
                                .hide()
                                .prev()
                                .show();
                        }

                        window.location.hash = '';
                    },
                    stepForward : function () {
                        // Show action bar for second panel if hidden
                        $( 'div.pods-wizard-hide-first' )
                            .removeClass( 'pods-wizard-hide-first' )
                            // Remember that first panel should hide action bar
                            .data( 'hide', 1 );

                        // Step toolbar menu state forwards
                        $( 'li.pods-wizard-menu-current' )
                            .removeClass( 'pods-wizard-menu-current' )
                            .addClass( 'pods-wizard-menu-complete' )
                            .next( 'li' )
                            .addClass( 'pods-wizard-menu-current' );

                        // Get current step #
                        var step = false;

                        if ( $( 'li.pods-wizard-menu-current[data-step]' )[ 0 ] )
                            step = $( 'li.pods-wizard-menu-current' ).data( 'step' );

                        // Show start over button
                        $( '#pods-wizard-start' ).show();

                        // Allow for override
                        var check = true;

                        // Check if last step
                        if ( $( 'div.pods-wizard-panel:visible' ).next( 'div.pods-wizard-panel' ).length ) {
                            // Show next panel
                            $( 'div.pods-wizard-panel:visible' )
                                .hide()
                                .next()
                                .show();

                            // Allow for override
                            if ( 'undefined' != typeof pods_admin_wizard_callback )
                                check = pods_admin_wizard_callback( step );

                            if ( false === check )
                                return check;

                            window.location.hash = '';
                        }
                        else if ( $( '#pods-wizard-box' ).closest( 'form' )[ 0 ] ) {
                            $( '#pods-wizard-next' ).css( 'cursor', 'default' );
                            $( '#pods-wizard-next' ).prop( 'disabled', true );
                            $( '#pods-wizard-next' ).text( $( '#pods-wizard-next' ).data( 'processing' ) );

                            $( '#pods-wizard-box' ).closest( 'form' ).submit();

                            if ( $( '#pods-wizard-box' ).closest( 'form' ).hasClass( 'invalid-form' ) ) {
                                $( '#pods-wizard-next' ).css( 'cursor', 'pointer' );
                                $( '#pods-wizard-next' ).prop( 'disabled', false );
                                $( '#pods-wizard-next' ).text( $( '#pods-wizard-next' ).data( 'next' ) );

                                // Step toolbar menu state forwards
                                $( 'li.pods-wizard-menu-complete:last' )
                                    .removeClass( 'pods-wizard-menu-complete' )
                                    .addClass( 'pods-wizard-menu-current' )
                            }
                        }
                        else {
                            // Allow for override
                            if ( 'undefined' != typeof pods_admin_wizard_callback )
                                check = pods_admin_wizard_callback( step );

                            if ( false === check )
                                return check;

                            methods.setFinished();

                            window.location.hash = '';
                        }
                    },
                    startOver : function () {
                        // Reset next button text
                        methods.setProgress();

                        // If first panel and action bar is supposed to be hidden, hide it.
                        var $box = $( '#pods-wizard-box' );
                        if ( $box.data( 'hide' ) )
                           $box.addClass( 'pods-wizard-hide-first' );

                        // Revert to first current menu item
                        $( '#pods-wizard-heading ul li' )
                            .removeClass()
                            .first()
                            .addClass( 'pods-wizard-menu-current' );

                        // Revert to first panel
                        $( 'div.pods-wizard-panel' )
                            .hide()
                            .first()
                            .show();

                        // Hide start over button
                        $( '.pods-wizard-option-selected' ).removeClass();
                        $( '#pods-wizard-start' ).hide();
                        $( 'div.pods-wizard-option-cont' ).hide();
                        $( '#pods-wizard-choices' ).fadeIn( 'fast' );

                        if ( 'undefined' != typeof pods_admin_wizard_startover_callback )
                            pods_admin_wizard_startover_callback( $( this ) );

                        window.location.hash = '';
                    }
                }

                // Next button event binding
                $( '#pods-wizard-next' ).on( 'click', function ( e ) {
                    e.preventDefault();

                    methods.stepForward();
                } );

                // Start over button event binding
                $( '#pods-wizard-start' )
                    .hide()
                    .on( 'click', function ( e ) {
                            e.preventDefault();
                            methods.startOver();
                         } );

                // Upgrade choice button event binding
                $( '.pods-choice-button' ).on( 'click', function ( e ) {
                    e.preventDefault();

                    var target = $( this ).attr( 'href' );
                    $( '#pods-wizard-choices' ).slideUp( 'fast' );
                    $( target ).slideDown( 'fast' );
                } );

                // Create/extend option event binding
                $( '.pods-wizard-option a' ).on( 'click', function ( e ) {
                    e.preventDefault();

                    $( '.pods-wizard-option-content' ).hide();

                    var target = $( this ).attr( 'href' );

                    $( target ).show();
                    $( '.pods-wizard-option-content-' + target.replace( '#pods-wizard-', '' ) ).show();

                    if ( 'undefined' != typeof pods_admin_option_select_callback )
                        pods_admin_option_select_callback( $( this ) );

                    methods.stepForward();
                } );

                // Initial step panel setup
                $( '.pods-wizard .pods-wizard-step' ).hide();
                $( '.pods-wizard .pods-wizard-step:first' ).show();
            },
            dependency : function ( init ) {
                // Hide all dependents
                $( '.pods-dependency .pods-depends-on, .pods-dependency .pods-excludes-on, .pods-dependency .pods-wildcard-on' ).hide();

                // Handle dependent toggle
                $( '.pods-admin' ).on( 'change', '.pods-dependent-toggle', function ( e ) {
                    var $el = $( this );
                    var $current = $el.closest( '.pods-dependency' );
                    var $field = $el;

                    var dependent_flag = '.pods-depends-on-' + $el.data( 'name-clean' );
                    var dependent_specific = dependent_flag + '-' + $el.val();

                    $current.find( dependent_flag ).each( function () {
                        var $dependent_el = $( this );

                        if ( $dependent_el.parent().is( ':visible' ) ) {
                            if ( $field.is( 'input[type=checkbox]' ) && $field.is( ':checked' ) ) {
                                if ( $dependent_el.is( 'tr' ) )
                                    $dependent_el.show().addClass( 'pods-dependent-visible' );
                                else
                                    $dependent_el.slideDown().addClass( 'pods-dependent-visible' );

                                $dependent_el.find( '.pods-dependency .pods-depends-on' ).hide();
                                $dependent_el.find( '.pods-dependency .pods-excludes-on' ).hide();

                                $dependent_el.find( '.pods-dependency .pods-dependent-toggle' ).each( function () {
                                    $( this ).trigger( 'change' );
                                } );
                            }
                            else if ( $dependent_el.is( dependent_specific ) ) {
                                if ( $dependent_el.is( 'tr' ) )
                                    $dependent_el.show().addClass( 'pods-dependent-visible' );
                                else
                                    $dependent_el.slideDown().addClass( 'pods-dependent-visible' );

                                $dependent_el.find( '.pods-dependency .pods-depends-on' ).hide();
                                $dependent_el.find( '.pods-dependency .pods-excludes-on' ).hide();

                                $dependent_el.find( '.pods-dependency .pods-dependent-toggle' ).each( function () {
                                    $( this ).trigger( 'change' );
                                } );
                            }
                            else {
                                if ( $dependent_el.is( 'tr' ) )
                                    $dependent_el.hide().removeClass( 'pods-dependent-visible' );
                                else
                                    $dependent_el.slideUp().removeClass( 'pods-dependent-visible' );
                            }
                        }
                        else {
                            if ( $field.is( 'input[type=checkbox]' ) && $field.is( ':checked' ) ) {
                                $dependent_el.show().addClass( 'pods-dependent-visible' );
                                $dependent_el.find( '.pods-dependency .pods-depends-on' ).hide();
                                $dependent_el.find( '.pods-dependency .pods-excludes-on' ).hide();

                                $dependent_el.find( '.pods-dependency .pods-dependent-toggle' ).each( function () {
                                    $( this ).trigger( 'change' );
                                } );
                            }
                            else if ( $dependent_el.is( dependent_specific ) ) {
                                $dependent_el.show().addClass( 'pods-dependent-visible' );
                                $dependent_el.find( '.pods-dependency .pods-depends-on' ).hide();
                                $dependent_el.find( '.pods-dependency .pods-excludes-on' ).hide();

                                $dependent_el.find( '.pods-dependency .pods-dependent-toggle' ).each( function () {
                                    $( this ).trigger( 'change' );
                                } );
                            }
                            else
                                $dependent_el.hide().removeClass( 'pods-dependent-visible' );
                        }
                    } );

                    var exclude_flag = '.pods-excludes-on-' + $el.data( 'name-clean' );
                    var exclude_specific = exclude_flag + '-' + $el.val();

                    $current.find( exclude_flag ).each( function () {
                        var $dependent_el = $( this );

                        if ( $dependent_el.parent().is( ':visible' ) ) {
                            if ( $field.is( 'input[type=checkbox]' ) && $field.is( ':checked' ) ) {
                                if ( $dependent_el.is( 'tr' ) )
                                    $dependent_el.hide().removeClass( 'pods-dependent-visible' );
                                else
                                    $dependent_el.slideUp().removeClass( 'pods-dependent-visible' );
                            }
                            else if ( $dependent_el.is( exclude_specific ) ) {
                                if ( $dependent_el.is( 'tr' ) )
                                    $dependent_el.hide().removeClass( 'pods-dependent-visible' );
                                else
                                    $dependent_el.slideUp().removeClass( 'pods-dependent-visible' );
                            }
                            else {
                                if ( $dependent_el.is( 'tr' ) )
                                    $dependent_el.show().addClass( 'pods-dependent-visible' );
                                else
                                    $dependent_el.slideDown().addClass( 'pods-dependent-visible' );

                                $dependent_el.find( '.pods-dependency .pods-depends-on' ).hide();
                                $dependent_el.find( '.pods-dependency .pods-excludes-on' ).hide();

                                $dependent_el.find( '.pods-dependency .pods-dependent-toggle' ).each( function () {
                                    $( this ).trigger( 'change' );
                                } );
                            }
                        }
                        else {
                            if ( $field.is( 'input[type=checkbox]' ) && $field.is( ':checked' ) )
                                $dependent_el.hide().removeClass( 'pods-dependent-visible' );
                            else if ( $dependent_el.is( exclude_specific ) )
                                $dependent_el.hide().removeClass( 'pods-dependent-visible' );
                            else {
                                $dependent_el.show().addClass( 'pods-dependent-visible' );
                                $dependent_el.find( '.pods-dependency .pods-depends-on' ).hide();
                                $dependent_el.find( '.pods-dependency .pods-excludes-on' ).hide();

                                $dependent_el.find( '.pods-dependency .pods-dependent-toggle' ).each( function () {
                                    $( this ).trigger( 'change' );
                                } );
                            }
                        }
                    } );

                    var wildcard_flag = '.pods-wildcard-on-' + $el.data( 'name-clean' );
                    var wildcard_value = $el.val();

                    $current.find( wildcard_flag ).each( function () {
                        var $dependent_el = $( this );
                        var wildcard = $dependent_el.data( 'wildcard' );

                        if ( $dependent_el.parent().is( ':visible' ) ) {
                            if ( null !== wildcard_value.match( wildcard ) ) {
                                if ( $dependent_el.is( 'tr' ) )
                                    $dependent_el.show().addClass( 'pods-dependent-visible' );
                                else
                                    $dependent_el.slideDown().addClass( 'pods-dependent-visible' );

                                $dependent_el.find( '.pods-dependency .pods-depends-on' ).hide();
                                $dependent_el.find( '.pods-dependency .pods-excludes-on' ).hide();
                                $dependent_el.find( '.pods-dependency .pods-wildcard-on' ).hide();

                                $dependent_el.find( '.pods-dependency .pods-dependent-toggle' ).each( function () {
                                    $( this ).trigger( 'change' );
                                } );

                                if ( $dependent_el.is( '[data-dependency-trigger]' ) ) {
                                    var dependency_trigger = $dependent_el.data( 'dependency-trigger' );

                                    dependency_trigger = window[ dependency_trigger ];

                                    dependency_trigger( $dependent_el );
                                }
                            }
                            else {
                                if ( $dependent_el.is( 'tr' ) )
                                    $dependent_el.hide().removeClass( 'pods-dependent-visible' );
                                else
                                    $dependent_el.slideUp().removeClass( 'pods-dependent-visible' );
                            }
                        }
                        else {
                            if ( null !== wildcard_value.match( wildcard ) ) {
                                $dependent_el.show().addClass( 'pods-dependent-visible' );
                                $dependent_el.find( '.pods-dependency .pods-depends-on' ).hide();
                                $dependent_el.find( '.pods-dependency .pods-excludes-on' ).hide();
                                $dependent_el.find( '.pods-dependency .pods-wildcard-on' ).hide();

                                $dependent_el.find( '.pods-dependency .pods-dependent-toggle' ).each( function () {
                                    $( this ).trigger( 'change' );
                                } );

                                if ( $dependent_el.is( '[data-dependency-trigger]' ) ) {
                                    var dependency_trigger = $dependent_el.data( 'dependency-trigger' );

                                    dependency_trigger = window[ dependency_trigger ];

                                    dependency_trigger( $dependent_el );
                                }
                            }
                            else
                                $dependent_el.hide().removeClass( 'pods-dependent-visible' );
                        }
                    } );
                } );

                if ( 'undefined' != typeof init && init ) {
                    $( '.pods-dependency' ).find( '.pods-dependent-toggle' ).each( function () {
                        $( this ).trigger( 'change' );
                    } );
                }
            },
            dependency_tabs : function () {
                // Hide all dependents
                $( '.pods-dependency-tabs .pods-depends-on' ).hide();

                // Handle dependent toggle
                $( '.pods-admin' ).on( 'click', '.pods-dependency-tabs .pods-dependent-tab', function ( e ) {
                    var $el = $( this );
                    var $current = $el.closest( '.pods-dependency-tabs' );
                    var $field = $el;

                    var dependent_flag = '.pods-depends-on-' + $el.data( 'name-clean' );
                    var dependent_specific = dependent_flag + '-' + $el.val();

                    $current.find( dependent_flag ).each( function () {
                        var $dependent_el = $( this );

                        if ( $dependent_el.parent().is( ':visible' ) ) {
                            if ( $field.is( 'input[type=checkbox]' ) && $field.is( ':checked' ) ) {
                                if ( $dependent_el.is( 'tr' ) )
                                    $dependent_el.show().addClass( 'pods-dependent-visible' );
                                else
                                    $dependent_el.slideDown().addClass( 'pods-dependent-visible' );

                                $dependent_el.find( '.pods-dependency-tabs .pods-depends-on' ).hide();
                                $dependent_el.find( '.pods-dependency-tabs .pods-excludes-on' ).hide();

                                $dependent_el.find( '.pods-dependency-tabs .pods-dependent-tab.pods-dependent-tab-active' ).each( function () {
                                    $( this ).trigger( 'click' );
                                } );
                            }
                            else if ( $dependent_el.is( dependent_specific ) ) {
                                if ( $dependent_el.is( 'tr' ) )
                                    $dependent_el.show().addClass( 'pods-dependent-visible' );
                                else
                                    $dependent_el.slideDown().addClass( 'pods-dependent-visible' );

                                $dependent_el.find( '.pods-dependency-tabs .pods-depends-on' ).hide();
                                $dependent_el.find( '.pods-dependency-tabs .pods-excludes-on' ).hide();

                                $dependent_el.find( '.pods-dependency-tabs .pods-dependent-tab.pods-dependent-tab-active' ).each( function () {
                                    $( this ).trigger( 'click' );
                                } );
                            }
                            else {
                                if ( $dependent_el.is( 'tr' ) )
                                    $dependent_el.hide().removeClass( 'pods-dependent-visible' );
                                else
                                    $dependent_el.slideUp().removeClass( 'pods-dependent-visible' );
                            }
                        }
                        else {
                            if ( $field.is( 'input[type=checkbox]' ) && $field.is( ':checked' ) ) {
                                $dependent_el.show().addClass( 'pods-dependent-visible' );
                                $dependent_el.find( '.pods-dependency-tabs .pods-depends-on' ).hide();
                                $dependent_el.find( '.pods-dependency-tabs .pods-excludes-on' ).hide();

                                $dependent_el.find( '.pods-dependency-tabs .pods-dependent-tab.pods-dependent-tab-active' ).each( function () {
                                    $( this ).trigger( 'click' );
                                } );
                            }
                            else if ( $dependent_el.is( dependent_specific ) ) {
                                $dependent_el.show().addClass( 'pods-dependent-visible' );
                                $dependent_el.find( '.pods-dependency-tabs .pods-depends-on' ).hide();
                                $dependent_el.find( '.pods-dependency-tabs .pods-excludes-on' ).hide();

                                $dependent_el.find( '.pods-dependency-tabs .pods-dependent-tab.pods-dependent-tab-active' ).each( function () {
                                    $( this ).trigger( 'click' );
                                } );
                            }
                            else
                                $dependent_el.hide().removeClass( 'pods-dependent-visible' );
                        }
                    } );

                    var exclude_flag = '.pods-excludes-on-' + $el.data( 'name-clean' );
                    var exclude_specific = exclude_flag + '-' + $el.val();

                    $current.find( exclude_flag ).each( function () {
                        var $dependent_el = $( this );

                        if ( $dependent_el.parent().is( ':visible' ) ) {
                            if ( $field.is( 'input[type=checkbox]' ) && $field.is( ':checked' ) ) {
                                if ( $dependent_el.is( 'tr' ) )
                                    $dependent_el.hide().removeClass( 'pods-dependent-visible' );
                                else
                                    $dependent_el.slideUp().removeClass( 'pods-dependent-visible' );
                            }
                            else if ( $dependent_el.is( exclude_specific ) ) {
                                if ( $dependent_el.is( 'tr' ) )
                                    $dependent_el.hide().removeClass( 'pods-dependent-visible' );
                                else
                                    $dependent_el.slideUp().removeClass( 'pods-dependent-visible' );
                            }
                            else {
                                if ( $dependent_el.is( 'tr' ) )
                                    $dependent_el.show().addClass( 'pods-dependent-visible' );
                                else
                                    $dependent_el.slideDown().addClass( 'pods-dependent-visible' );

                                $dependent_el.find( '.pods-dependency-tabs .pods-depends-on' ).hide();
                                $dependent_el.find( '.pods-dependency-tabs .pods-excludes-on' ).hide();

                                $dependent_el.find( '.pods-dependency-tabs .pods-dependent-tab.pods-dependent-tab-active' ).each( function () {
                                    $( this ).trigger( 'click' );
                                } );
                            }
                        }
                        else {
                            if ( $field.is( 'input[type=checkbox]' ) && $field.is( ':checked' ) )
                                $dependent_el.hide().removeClass( 'pods-dependent-visible' );
                            else if ( $dependent_el.is( exclude_specific ) )
                                $dependent_el.hide().removeClass( 'pods-dependent-visible' );
                            else {
                                $dependent_el.show().addClass( 'pods-dependent-visible' );
                                $dependent_el.find( '.pods-dependency-tabs .pods-depends-on' ).hide();
                                $dependent_el.find( '.pods-dependency-tabs .pods-excludes-on' ).hide();

                                $dependent_el.find( '.pods-dependency-tabs .pods-dependent-tab.pods-dependent-tab-active' ).each( function () {
                                    $( this ).trigger( 'click' );
                                } );
                            }
                        }
                    } );
                } );

                $( '.pods-dependency-tabs .pods-dependent-tab.pods-dependent-tab-active' ).each( function () {
                    $( this ).click();
                } );
            },
            sortable : function () {
                $( 'tr.pods-manage-row:even' ).addClass( 'alternate' );
                $( 'tbody.pods-manage-list' ).addClass( 'pods-manage-sortable' ).sortable( {
                    items : 'tr.pods-manage-row',
                    axis : 'y',
                    handle : '.pods-manage-sort',
                    stop : function ( event, ui ) {
                        $( 'tr.pods-manage-row' ).removeClass( 'alternate' );
                        $( 'tr.pods-manage-row:even' ).addClass( 'alternate' );
                    }
                } );
            },
            advanced : function () {
                $( '.pods-advanced' ).hide();

                $( '.pods-admin' ).on( 'click', '.pods-advanced-toggle', function ( e ) {
                    $advanced = $( this ).closest( 'div' ).find( '.pods-advanced' );

                    if ( $advanced.is( ':visible' ) ) {
                        $( this ).text( $( this ).text().replace( '-', '+' ) );
                        $advanced.slideUp();
                    }
                    else {
                        $( this ).text( $( this ).text().replace( '+', '-' ) );
                        $advanced.slideDown();
                    }

                    e.preventDefault();
                } );
            },
            collapsible : function ( row ) {
                var new_row = row;

                if ( new_row[ 0 ] )
                    new_row = new_row.html();

                // Hide all rows
                $( 'div.pods-manage-row-wrapper' ).hide();

                var orig_fields = {};

                // Handle 'Edit' action
                $( 'tbody.pods-manage-list' ).on( 'click', 'a.pods-manage-row-edit', function ( e ) {
                    $( this ).css( 'cursor', 'default' );
                    $( this ).prop( 'disabled', true );

                    var $row = $( this ).closest( 'tr.pods-manage-row' );
                    var $row_label = $row.find( 'td.pods-manage-row-label' );
                    var $row_content = $row_label.find( 'div.pods-manage-row-wrapper' );

                    if ( 'undefined' == typeof orig_fields[ $row.data( 'id' ) ] )
                        orig_fields[ $row.data( 'id' ) ] = {};

                    // Row active, hide it
                    if ( $row_content.is( ':visible' ) ) {
                        if ( !$row.hasClass( 'pods-field-new' ) ) {
                            $row_content.slideUp( 'slow', function () {
                                $row.toggleClass( 'pods-manage-row-expanded' );
                                $row_label.prop( 'colspan', '1' );

                                $row_content.find( 'input, select, textarea' ).each( function () {
                                    if ( 'undefined' != typeof orig_fields[ $row.data( 'id' ) ][ $( this ).prop( 'name' ) ] ) {
                                        if ( $( this ).is( 'input[type=checkbox]' ) )
                                            $( this ).prop( 'checked', orig_fields[ $row.data( 'id' ) ][ $( this ).prop( 'name' ) ] );
                                        else
                                            $( this ).val( orig_fields[ $row.data( 'id' ) ][ $( this ).prop( 'name' ) ] );
                                    }
                                } );
                            } );
                        }
                        else {
                            var $tbody = $( this ).closest( 'tbody.pods-manage-list' );

                            $row.animate( {backgroundColor : '#B80000'} );

                            $row.fadeOut( 'slow', function () {
                                $( this ).remove();
                                if ( 0 == $( 'tbody.pods-manage-list tr.pods-manage-row' ).length )
                                    $tbody.find( 'tr.no-items' ).show();
                            } );

                            if ( $.fn.sortable && $tbody.hasClass( 'pods-manage-sortable' ) )
                                $( this ).closest( 'tbody.pods-manage-list' ).sortable( 'refresh' );
                        }
                    }
                    // Row inactive, show it
                    else {
                        if ( $row.hasClass( 'pods-field-init' ) && 'undefined' != typeof new_row && null !== new_row ) {
                            var row_counter = $row.data( 'row' );

                            var edit_row = new_row.replace( /\_\_1/gi, row_counter ).replace( /\-\-1/gi, row_counter );
                            var $field_wrapper = $row_content.find( 'div.pods-manage-field' );

                            $field_wrapper.append( edit_row );

                            $field_wrapper.find( '.pods-depends-on' ).hide();
                            $field_wrapper.find( '.pods-excludes-on' ).hide();

                            $field_wrapper.find( '.pods-dependent-toggle' ).each( function () {
                                $( this ).trigger( 'change' );
                            } );

                            var field_data = jQuery.parseJSON( $row_content.find( 'input.field_data' ).val() );

                            var field_array_counter = 0;

                            $field_wrapper.find( 'input, select, textarea' ).each( function () {
                                json_name = $( this ).prop( 'name' ).replace( 'field_data[' + row_counter + '][', '' ).replace( '[', '' ).replace( ']', '' );

                                if ( 0 < $( this ).prop( 'name' ).indexOf( '[]' ) ) {
                                    if ( 'undefined' != typeof field_data[ json_name ] && 'undefined' != typeof field_data[ json_name ][ field_array_counter ] ) {
                                        if ( $( this ).is( 'input[type=checkbox]' ) ) {
                                            $( this ).prop( 'checked', ( $( this ).val() == field_data[ json_name ][ field_array_counter ] ) );

                                            orig_fields[ $row.data( 'id' ) ][ $( this ).prop( 'name' ) ] = $( this ).prop( 'checked' );
                                        }
                                        else {
                                            $( this ).val( field_data[ json_name ][ field_array_counter ] );

                                            orig_fields[ $row.data( 'id' ) ][ $( this ).prop( 'name' ) ] = $( this ).val();
                                        }
                                    }

                                    field_array_counter++;
                                }
                                else {
                                    field_array_counter = 0;

                                    if ( 'undefined' != typeof field_data[ json_name ] ) {
                                        if ( $( this ).is( 'input[type=checkbox]' ) ) {
                                            $( this ).prop( 'checked', ( $( this ).val() == field_data[ json_name ] ) );

                                            orig_fields[ $row.data( 'id' ) ][ $( this ).prop( 'name' ) ] = $( this ).prop( 'checked' );
                                        }
                                        else {
                                            $( this ).val( field_data[ json_name ] );

                                            orig_fields[ $row.data( 'id' ) ][ $( this ).prop( 'name' ) ] = $( this ).val();
                                        }
                                    }
                                }
                            } );

                            $field_wrapper.find( '.pods-tabbed ul.pods-tabs .pods-tab:first a' ).addClass( 'selected' );
                            $field_wrapper.find( '.pods-tabbed .pods-tab-group .pods-tab:first' ).show();

                            $row.removeClass( 'pods-field-init' );

                            $( document ).Pods('qtip', $row);
                        }
                        else {
                           $row_content.find( 'input, select, textarea' ).each( function () {
                               if ( $( this ).is( 'input[type=checkbox]' ) )
                                   orig_fields[ $row.data( 'id' ) ][ $( this ).prop( 'name' ) ] = $( this ).prop( 'checked' );
                               else
                                   orig_fields[ $row.data( 'id' ) ][ $( this ).prop( 'name' ) ] = $( this ).val();
                            } );
                        }

                        $row.toggleClass( 'pods-manage-row-expanded' );
                        $row_label.prop( 'colspan', '3' );
                        $row_content.slideDown();
                        $row_content.find('.pods-dependency .pods-dependent-toggle' ).each( function () {
                           $( this ).trigger( 'change' );
                        } );
                    }

                    $( this ).css( 'cursor', 'pointer' );
                    $( this ).prop( 'disabled', false );

                    e.preventDefault();
                } )
                // Handle 'Save' action
                .on( 'click', '.pods-manage-row-save a.button-primary', function ( e ) {
                    $( this ).css( 'cursor', 'default' );
                    $( this ).prop( 'disabled', true );

                    var $row = $( this ).closest( 'tr.pods-manage-row' );
                    var $row_label = $row.find( 'td.pods-manage-row-label' );
                    var $row_content = $row_label.find( 'div.pods-manage-row-wrapper' );
                    var $field_wrapper = $row_content.find( 'div.pods-manage-field' );
                    var $row_value = $row_content.find( 'input.field_data' ).val();
                    var color = $.css( $row.get( 0 ), 'backgroundColor' );
                    var row_id = $row.data( 'row' );
                    var field_data = {};

                    if ( 'undefined' != typeof $row_value && null != $row_value && '' != $row_value ) {
                        field_data = jQuery.parseJSON( $row_value);
                    }

                    var valid_form = true;

                    $field_wrapper.find( 'input, select, textarea' ).each( function () {
                        var $el = $( this );

                        if ( '' != $el.prop( 'name' ) ) {
                            // TinyMCE support
                            if ( 'object' == typeof( tinyMCE ) && -1 < $el.prop( 'class' ).indexOf( 'pods-ui-field-tinymce' ) ) {
                                var ed = tinyMCE.get( $el.prop( 'id' ) );

                                $el.val( ed.getContent() );
                            }

                            var val = $el.val();

                            if ( $el.is( 'input[type=checkbox]' ) && !$el.is( ':checked' ) )
                                val = 0;
                            else if ( $el.is( 'input[type=radio]' ) && !$el.is( ':checked' ) )
                                val = '';

                            if ( $el.is( ':visible' ) && $el.hasClass( 'pods-validate pods-validate-required' ) && '' == $el.val() ) {
                                $el.trigger( 'change' );

                                if ( false !== valid_form )
                                    $el.focus();

                                valid_form = false;
                            }

                            field_name = $el.prop( 'name' );

                            var field_array = field_name.match( /\[(\w*|)\]/gi ),
                                field_name = '';

                            for ( var i in field_array ) {
                                the_field = field_array[ i ].replace( '[', '' ).replace( ']', '' );

                                if ( 1 == i ) {
                                    field_name = the_field;

                                    if ( 2 == field_array.length )
                                        field_data[ field_name ] = val;
                                }
                                else if ( 2 == i ) {
                                    the_field = parseInt( the_field );

                                    if ( 'NaN' == the_field )
                                        field_data[ field_name ] = val;
                                    else {
                                        if ( 'undefined' == typeof field_data[ field_name ] )
                                            field_data[ field_name ] = {};

                                        while ( 'undefined' != typeof( field_data[ field_name ][ the_field ] ) ) {
                                            the_field++;
                                        }

                                        field_data[ field_name ][ the_field ] = val;
                                    }
                                }
                            }
                         }
                     } );

                    if ( valid_form ) {
                        $row_content.find( 'input.field_data' ).val( $.toJSON( field_data ) );

                        $row.css( 'backgroundColor', '#FFFF33' ).animate(
                            { backgroundColor : color },
                            {
                                duration : 'slow',
                                complete : function () {
                                    $( this ).css( 'backgroundColor', '' );
                                }
                            }
                        );

                        if ( 'undefined' != typeof pods_field_types && null !== pods_field_types ) {
                            $row.find( 'td.pods-manage-row-label a.row-label' ).html( $row_content.find( 'input#pods-form-ui-field-data-' + row_id + '-label' ).val() );

                            if ( $row_content.find( 'input#pods-form-ui-field-data-' + row_id + '-required' ).is( ':checked' ) )
                               $row.find( 'td.pods-manage-row-label abbr.required' ).show();
                            else
                               $row.find( 'td.pods-manage-row-label abbr.required' ).hide();

                            $row.find( 'td.pods-manage-row-name a' ).html( $row_content.find( 'input#pods-form-ui-field-data-' + row_id + '-name' ).val() );

                            var field_type = $row_content.find( 'select#pods-form-ui-field-data-' + row_id + '-type' ).val();
                            var pick_object = $row_content.find( 'select#pods-form-ui-field-data-' + row_id + '-pick-object' ).val();
                            var field_type_desc = '';

                            if ( 'pick' == field_type && 0 != pick_object ) {
                               $.each( pods_pick_objects, function ( i, n ) {
                                    if ( pick_object == i ) {
                                        field_type_desc = '<br /><span class="pods-manage-field-type-desc">&rsaquo; ' + n + '</span>';
                                        return false;
                                    }
                                } );
                            }
                            $.each( pods_field_types, function ( i, n ) {
                                if ( field_type == i ) {
                                    field_type = n;
                                    return false;
                                }
                            } );

                            $row.find( 'td.pods-manage-row-type' ).html( field_type
                                + field_type_desc
                                + ' <span class="pods-manage-row-more">[type: ' + $row_content.find( 'select#pods-form-ui-field-data-' + row_id + '-type' ).val() + ']</span>' );
                        }

                        $row_content.slideUp( 'slow', function () {
                            $row.toggleClass( 'pods-manage-row-expanded' );
                            $row_label.prop( 'colspan', '1' );
                            $row.removeClass( 'pods-field-new' );
                        } );
                    }

                    $( this ).css( 'cursor', 'pointer' );
                    $( this ).prop( 'disabled', false );

                    e.preventDefault();
                } )
                // Handle 'Cancel' action
                .on( 'click', '.pods-manage-row-actions a.pods-manage-row-cancel', function ( e ) {
                    $( this ).closest( 'tr.pods-manage-row' ).find( 'a.pods-manage-row-edit' ).click();

                    e.preventDefault();
                } );
            },
            toggled : function () {
                $( '.pods-toggled .handlediv, .pods-toggled h3' ).live( 'click', function () {
                    $( this ).parent().find( '.inside' ).slideToggle();
                    return false;
                } );
            },
            flexible : function ( row ) {
                var new_row = row,
                    row_counter = 0;

                if ( new_row[ 0 ] ) {
                    new_row = new_row.html();

                    // Don't count flexible row
                    row_counter = -1;
                }

                row_counter += $( 'tr.pods-manage-row' ).length;

                // Handle 'Add' action
                if ( 'undefined' != typeof new_row && null !== new_row ) {
                    $( '.pods-manage-row-add' ).on( 'click', 'a', function ( e ) {
                        $( this ).css( 'cursor', 'default' );
                        $( this ).prop( 'disabled', true );

                        row_counter++;

                        var add_row = new_row.replace( /\_\_1/gi, row_counter ).replace( /\-\-1/gi, row_counter );
                        var $tbody = $( this ).parent().parent().find( 'tbody.pods-manage-list' );

                        $tbody.find( 'tr.no-items' ).hide();
                        $tbody.append( '<tr id="row-' + row_counter + '" class="pods-manage-row pods-field-new pods-field-' + row_counter + ' pods-submittable-fields" valign="top">' + add_row + '</tr>' );

                        $new_row = $tbody.find( 'tr#row-' + row_counter );

                        $new_row.data( 'row', row_counter );
                        $new_row.find( '.pods-dependency .pods-depends-on' ).hide();
                        $new_row.find( '.pods-dependency .pods-excludes-on' ).hide();

                        $new_row.find( '.pods-dependency .pods-dependent-toggle' ).each( function () {
                           $( this ).trigger( 'change' );
                        } );

                        $new_row.find( '.pods-manage-row-wrapper' ).hide( 0, function () {
                           $new_row.find( 'a.row-label.pods-manage-row-edit' ).click();
                        } );

                        $( '.pods-tabs .pods-tab:first a', $new_row ).addClass( 'selected' );
                        $( '.pods-tab-group', $new_row ).find( '.pods-tab:first' ).show();

                        if ( $.fn.sortable && $tbody.hasClass( 'pods-manage-sortable' ) )
                           $tbody.sortable( 'refresh' );

                        var sluggables = [];

                        $( 'tr#row-' + row_counter + ' .pods-slugged[data-sluggable], tr#row-' + row_counter + ' .pods-slugged-lower[data-sluggable]' ).each( function () {
                            if ( -1 == sluggables.indexOf( $( this ).data( 'sluggable' ) ) )
                                sluggables.push( $( this ).data( 'sluggable' ) );
                        } );

                        for ( var i in sluggables ) {
                            var sluggable = sluggables[ i ];

                            methods[ 'sluggable_single' ]( sluggable );
                        }

                        $( this ).css( 'cursor', 'pointer' );
                        $( this ).prop( 'disabled', false );

                        $(document ).Pods('qtip',$new_row);

                        e.preventDefault();
                    } );
                }

                // Handle 'Delete' action
                $( 'tbody.pods-manage-list' ).on( 'click', 'a.submitdelete', function ( e ) {
                    $( this ).css( 'cursor', 'default' );
                    $( this ).prop( 'disabled', true );

                    // @todo: Make this confirm pretty so that it's inline instead of JS confirm
                    if ( confirm( 'Are you sure you want to delete this field?' ) ) {
                        var $row = $( this ).closest( 'tr.pods-manage-row' );
                        var $tbody = $( this ).closest( 'tbody.pods-manage-list' );

                        $row.animate( {backgroundColor : '#B80000'} );

                        $row.fadeOut( 'slow', function () {
                           $( this ).remove();
                            if ( 0 == $( 'tbody.pods-manage-list tr.pods-manage-row' ).length )
                                $tbody.find( 'tr.no-items' ).show();
                        } );

                        if ( $.fn.sortable && $tbody.hasClass( 'pods-manage-sortable' ) )
                           $( this ).closest( 'tbody.pods-manage-list' ).sortable( 'refresh' );

                        pods_changed = true;

                        //row_counter--;
                    }

                    $( this ).css( 'cursor', 'pointer' );
                    $( this ).prop( 'disabled', false );

                    e.preventDefault();
                } );
            },
            confirm : function () {
                $( 'a.pods-confirm' ).on( 'click', function ( e ) {
                    var $el = $( this );

                    if ( 'undefined' != typeof $el.data( 'confirm' ) && !confirm( $el.data( 'confirm' ) ) )
                        return false;
                } );
            },
            exit_confirm : function () {
                $( 'form.pods-submittable' ).on( 'change', '.pods-submittable-fields input:not(:button,:submit), .pods-submittable-fields textarea, .pods-submittable-fields select', function () {
                    pods_changed = true;

                    window.onbeforeunload = function () {
                        if ( pods_changed )
                            return 'Navigating away from this page will discard any changes you have made.';
                    }
                } );
            },
            qtip: function(element) {
                $( element ).find('.pods-qtip').qtip( {
                    content : {
                        attr : 'alt'
                    },
                    style : {
                        classes : 'ui-tooltip-light ui-tooltip-shadow ui-tooltip-rounded'
                    },
                    show : {
                        effect : function ( offset ) {
                            $( this ).fadeIn( 'fast' );
                        }
                    },
                    hide : {
                        fixed : true,
                        delay : 300
                    },
                    position : {
                        my : 'bottom left',
                        adjust : {
                            y : -14
                        }
                    }
                } );
            }
        };

    $.fn.Pods = function ( method ) {
        if ( methods[method] ) {
            return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ) );
        }
        // Don't need this part (yet)
        /*
        else if (typeof method === 'object' || !method) {
        return methods.init.apply(this, arguments);
         }
         */
        else {
            $.error( 'Method ' + method + ' does not exist on jQuery.Pods' );
        }
    };
})( jQuery );

