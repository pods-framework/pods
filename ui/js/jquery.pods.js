/*@global PodsI18n */
( function ( $ ) {
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

                    if ( $el.is( 'input[type=checkbox]' ) && !( $el.is( ':checked' ) ) ) {
                        valid_field = false;

                        // extra check for relationship checkboxes to see if siblings are checked
                        if ( $el.hasClass( 'pods-form-ui-field-type-pick' ) ) {
                            $el.closest( '.pods-pick-checkbox' ).find( 'input[type=checkbox]' ).each( function () {
                                if ( $( this ).is( ':checked' ) ) {
                                    valid_field = true;
                                }
                            } )

                        }

                    }
                    else if ( '' == $el.val() )
                        valid_field = false;

                    if ( !valid_field ) {
                        if ( -1 == jQuery.inArray( $el.prop( 'name' ), pods_form_field_names ) ) {
                            $el.closest( '.pods-field-input' ).find( '.pods-validate-error-message' ).remove();

                            if ( $el.closest( '.pods-field-input > td' ).length > 0 ) {
                                $el.closest( '.pods-field-input > td' ).last().prepend( '<div class="pods-validate-error-message">' + PodsI18n.__( '%s is required.' ).replace( '%s', label.replace( /( <([^>]+ )> )/ig, '' ) ) + '</div>' );
                            } else {
                                $el.closest( '.pods-field-input' ).append( '<div class="pods-validate-error-message">' + PodsI18n.__( '%s is required.' ).replace( '%s', label.replace( /( <([^>]+ )> )/ig, '' ) ) + '</div>' );
                            }
                            $el.addClass( 'pods-validate-error' );

                            pods_form_field_names.push( $el.prop( 'name' ) );
                        }
                    }
                    else {
                        $el.closest( '.pods-field-input' ).find( '.pods-validate-error-message' ).remove();
                        $el.removeClass( 'pods-validate-error' );

                        if ( -1 < jQuery.inArray( $el.prop( 'name' ), pods_form_field_names ) )
                            pods_form_field_names.splice( jQuery.inArray( $el.prop( 'name' ), pods_form_field_names ), 1 );
                    }
                } );
            },
            submit_meta : function () {
                var $submitbutton;

                // Verify required fields in WordPress posts with CPT
                $( 'form.pods-submittable' ).on( 'submit', function ( e ) {
                    var $submittable = $( this );

                    pods_changed = false;

                    /* e.preventDefault(); */

                    var postdata = {};
                    var field_data = {};

                    var valid_form = true;

                    var field_id = 0,
                        field_index = 0;

                    // See if we have any instances of tinyMCE and save them
                    if ( 'undefined' != typeof tinyMCE )
                        tinyMCE.triggerSave();

                    $submittable.find( '.pods-submittable-fields' ).find( 'input, select, textarea' ).each( function () {
                        var $el = $( this );
                        var field_name = $el.prop( 'name' );

                        if ( 'undefined' != typeof field_name && null !== field_name && '' != field_name && 0 != field_name.indexOf( 'field_data[' ) ) {
                            var val = $el.val();

                            if ( $el.is( 'input[type=checkbox]' ) && !$el.is( ':checked' ) ) {
                                if ( 1 == val )
                                    val = 0;
                                else
                                    return true; // This input isn't a boolean, continue the loop
                            }
                            else if ( $el.is( 'input[type=radio]' ) && !$el.is( ':checked' ) )
                                return true; // This input is not checked, continue the loop

                            if ( $el.is( ':visible' ) && $el.hasClass( 'pods-validate pods-validate-required' ) && ( '' == $el.val() ) ) {
                                $el.trigger( 'change' );

                                if ( false !== valid_form )
                                    $el.focus();

                                valid_form = false;
                            }
                            if ( null !== val ) {
                                postdata[field_name] = val;
                            }
                        }
                    } );

                    if ( 'undefined' != typeof pods_admin_submit_validation )
                        valid_form = pods_admin_submit_validation( valid_form, $submittable );

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

                    return true;
                } );
            },
            submit : function () {
                var $submitbutton, id, data;

                // Handle submit of form and translate to AJAX
                $( 'form.pods-submittable' ).on( 'submit', function ( e ) {
                    var $submittable = $( this );

                    pods_changed = false;

                    e.preventDefault();

                    pods_ajaxurl = $submittable.attr( 'action' );
                    pods_ajaxurl = pods_ajaxurl.replace( /\?nojs\=1/, '?pods_ajax=1' );

                    if ( 'undefined' != typeof ajaxurl && ( '' == pods_ajaxurl || '?pods_ajax=1' == pods_ajaxurl || document.location.href == pods_ajaxurl || document.location.href.replace( /\?nojs\=1/, '?pods_ajax=1' ) == pods_ajaxurl ) )
                        pods_ajaxurl = ajaxurl + '?pods_ajax=1';

                    if ( 'undefined' != typeof ajaxurl && $submittable.hasClass( 'pods-submittable-ajax' ) )
                        pods_ajaxurl = ajaxurl + '?pods_ajax=1';

                    var postdata = {};
                    var field_data = {};

                    var valid_form = true;

                    var field_id = 0,
                        field_index = 0;

                    // See if we have any instances of tinyMCE and save them
                    if ( 'undefined' != typeof tinyMCE )
                        tinyMCE.triggerSave();

                    $submittable.find( '.pods-submittable-fields' ).find( 'input, select, textarea' ).each( function () {
                        var $el = $( this );
                        var field_name = $el.prop( 'name' );

                        if ( 'undefined' != typeof field_name && null !== field_name && '' != field_name && 0 != field_name.indexOf( 'field_data[' ) ) {
                            var val = $el.val();

                            if ( $el.is( 'input[type=checkbox]' ) && !$el.is( ':checked' ) ) {
                                if ( $el.is( '.pods-boolean' ) || $el.is( '.pods-form-ui-field-type-boolean') )
                                    val = 0;
                                else
                                    return true; // This input isn't a boolean, continue the loop
                            }
                            else if ( $el.is( 'input[type=radio]' ) && !$el.is( ':checked' ) )
                                return true; // This input is not checked, continue the loop

                            if ( $el.is( ':visible' ) && $el.hasClass( 'pods-validate pods-validate-required' ) && ( '' == $el.val() ) ) {
                                $el.trigger( 'change' );

                                if ( false !== valid_form )
                                    $el.focus();

                                valid_form = false;
                            }

                            if ( null !== val ) {
                                postdata[field_name] = val;
                            }
                        }
                    } );

                    // Check for unsaved open fields. (separate if's to prevent possible unneeded jQuery selector)
                    if ( 'undefined' !== typeof postdata.method ) {
	                    if ( 'save_pod' === postdata.method ) {
	                        if ( $( 'tbody.pods-manage-list tr.pods-manage-row-expanded', $submittable ).length ) {
		                        alert( PodsI18n.__( 'Some fields have changes that were not saved yet, please save them or cancel the changes before saving the Pod.' ) );
		                        valid_form = false;
	                        }
	                    }
                    }

                    if ( 'undefined' != typeof pods_admin_submit_validation )
                        valid_form = pods_admin_submit_validation( valid_form, $submittable );

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

                            // Attempt to parse what was returned as data
                            try {
                                data = $.parseJSON( d );
                            }
                            catch ( e ) {
                                data = undefined;
                            }

                            if ( -1 === d.indexOf( '<e>' ) && -1 === d.indexOf( '</e>' ) && -1 !== d ) {

                                // Added for modal add/edit support.  If we get a valid JSON object, we assume we're modal
                                if ( 'object' === typeof data && null !== data ) {

                                    // Phone home with the data
                                    window.parent.jQuery( window.parent ).trigger('dfv:modal:update', data );
                                }
                                else {
                                    id = d.match( /\d*$/, '' );

                                    if ( 0 < id.length ) {
                                        id = parseInt( id[ 0 ] );

                                        if ( isNaN( id ) ) {
                                            id = 0;
                                        }
                                    }
                                    else {
                                        id = 0;
                                    }

                                    if ( 'undefined' != typeof pods_admin_submit_callback ) {
                                        pods_admin_submit_callback( d, $submittable, id );
                                    }
                                    else if ( 'undefined' != typeof $submittable.data( 'location' ) ) {
                                        document.location.href = $submittable.data( 'location' ).replace( 'X_ID_X', id );
                                    }
                                    else {
                                        document.location.reload( true );
                                    }
                                }
                            }
                            else if ( 'undefined' != typeof $submittable.data( 'error-location' ) ) {
                                document.location.href = $submittable.data( 'error-location' );
                            }
                            else {
                                var err_msg = d.replace( '<e>', '' ).replace( '</e>', '' );

                                if ( 'undefined' != typeof pods_admin_submit_error_callback ) {
                                    pods_admin_submit_error_callback( err_msg, $submittable );
                                }
                                else {
                                    alert( 'Error: ' + err_msg );
                                    if ( window.console ) console.log( err_msg );
                                }

                                $submitbutton.css( 'cursor', 'pointer' );
                                $submitbutton.prop( 'disabled', false );
                                $submitbutton.parent().find( '.waiting' ).fadeOut();

                                if ( $( '#pods-wizard-next' )[ 0 ] ) {
                                    $( '#pods-wizard-next' ).css( 'cursor', 'pointer' );
                                    $( '#pods-wizard-next' ).prop( 'disabled', false );
                                    $( '#pods-wizard-next' ).text( $( '#pods-wizard-next' ).data( 'next' ) );
                                }
                            }
                        },
                        error : function () {
                            var err_msg = 'Unable to process request, please try again.';

                            if ( 'undefined' != typeof pods_admin_submit_error_callback ) {
                                pods_admin_submit_error_callback( err_msg, $submittable );
                            }
                            else {
                                alert( 'Error: ' + err_msg );
                                if ( window.console ) console.log( err_msg );
                            }

                            $submitbutton.css( 'cursor', 'pointer' );
                            $submitbutton.prop( 'disabled', false );
                            $submitbutton.parent().find( '.waiting' ).fadeOut();

                            if ( $( '#pods-wizard-next' )[ 0 ] ) {
                                $( '#pods-wizard-next' ).css( 'cursor', 'pointer' );
                                $( '#pods-wizard-next' ).prop( 'disabled', false );
                                $( '#pods-wizard-next' ).text( $( '#pods-wizard-next' ).data( 'next' ) );
                            }
                        }
                    } );
                } )// Handle submit button and show waiting image
                    .on( 'click', 'input[type=submit], button[type=submit]', function ( e ) {
                    pods_changed = false;

                    e.preventDefault();

                    $( 'div#message' ).slideUp( 'fast', function () {
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
                    else if ( 'undefined' != typeof ajaxurl && ( 'undefined' == typeof pods_ajaxurl || '' == pods_ajaxurl || '?pods_ajax=1' == pods_ajaxurl || document.location.href == pods_ajaxurl || document.location.href.replace( /\?nojs\=1/, '?pods_ajax=1' ) == pods_ajaxurl ) )
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
                            if ( -1 == d.indexOf( '<e>' ) && -1 == d.indexOf( '</e>' ) && -1 != d ) {
                                var id = d.match( /\d*$/, '' );

                                if ( 0 < id.length ) {
                                    id = parseInt( id[ 0 ] );

                                    if ( isNaN( id ) )
                                        id = 0;
                                }
                                else
                                    id = 0;

                                if ( 'undefined' != typeof pods_admin_submit_callback )
                                    pods_admin_submit_callback( d, $submittable, id );
                                else if ( 'undefined' != typeof $submittable.data( 'location' ) )
                                    document.location.href = $submittable.data( 'location' ).replace( 'X_ID_X', id );
                                else
                                    document.location.reload( true );
                            }
                            else if ( 'undefined' != typeof $submitbutton.data( 'error-location' ) )
                                document.location.href = $submitbutton.data( 'error-location' );
                            else {
                                var err_msg = d.replace( '<e>', '' ).replace( '</e>', '' );

                                if ( 'undefined' != typeof pods_admin_submit_error_callback )
                                    pods_admin_submit_error_callback( err_msg, $submittable );
                                else {
                                    alert( 'Error: ' + err_msg );
                                    if ( window.console ) console.log( err_msg );
                                }

                                $submitbutton.css( 'cursor', 'pointer' );
                                $submitbutton.prop( 'disabled', false );
                                $submitbutton.parent().find( '.waiting' ).fadeOut();
                            }
                        },
                        error : function () {
                            var err_msg = PodsI18n.__( 'Unable to process request, please try again.' );

                            if ( 'undefined' != typeof pods_admin_submit_error_callback )
                                pods_admin_submit_error_callback( err_msg, $submittable );
                            else {
                                alert( 'Error: ' + err_msg );
                                if ( window.console ) console.log( err_msg );
                            }

                            $submitbutton.css( 'cursor', 'pointer' );
                            $submitbutton.prop( 'disabled', false );
                            $submitbutton.parent().find( '.waiting' ).fadeOut();
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

                        last_slug = last_slug.replace( /<(?:.)*?>/g, '' ).replace( /([^0-9a-zA-Z\_\- ])/g, '' );

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

                        last_slug = last_slug.replace( /<( ?:. )*?>/g, '' ).replace( /([^0-9a-zA-Z\_\- ])/g, '' );

                        $( this ).closest( '.pods-sluggable' ).find( '.pods-slug em' ).html( last_slug );
                        $( '.pods-slugged-lower:not(.pods-slugged[data-sluggable])' ).html( last_slug.toLowerCase() );
                        $( '.pods-slugged:not(.pods-slugged[data-sluggable])' ).html( last_slug.charAt( 0 ).toUpperCase() + last_slug.slice( 1 ) );
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

                methods[ 'sluggables' ]();
            },
            sluggable_single : function ( sluggable ) {
                var $slug = $( 'input[name="' + sluggable.replace( '[', '\\[' ).replace( ']', '\\]' ) + '"]' );

                if ( $slug[ 0 ] ) {
                    $( 'form' ).on( 'change', 'input[name="' + sluggable.replace( '[', '\\[' ).replace( ']', '\\]' ) + '"]', function () {
                        if ( 0 < $( this ).val().length ) {
                            var slug = $( this ).val();

                            slug = slug.replace( /<( ?:. )*?>/g, '' ).replace( /([^0-9a-zA-Z\_\- ])/g, '' );

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
                            $( '.pods-slugged-lower[data-sluggable="' + $( this ).prop( 'name' ).replace( '[', '\\[' ).replace( ']', '\\]' ) + '"]:not(input )' )
                                .html( slug.toLowerCase() )
                                .trigger( 'change' );

                            // trigger change
                            $( '.pods-slugged[data-sluggable="' + $( this ).prop( 'name' ).replace( '[', '\\[' ).replace( ']', '\\]' ) + '"]:not(input )' )
                                .html( slug.charAt( 0 ).toUpperCase() + slug.slice( 1 ) )
                                .trigger( 'change' );
                        }
                    } );

                    if ( 0 < $slug.val().length ) {
                        $slug.trigger( 'change' );
                    }
                }
            },
            sluggables : function ( parent ) {
                var sluggables = [];

                if ( 'undefined' == typeof parent )
                    parent = '.pods-admin';

                $( parent ).find( '.pods-slugged[data-sluggable], .pods-slugged-lower[data-sluggable]' ).each( function () {
                    if ( -1 == jQuery.inArray( $( this ).data( 'sluggable' ), sluggables ) )
                        sluggables.push( $( this ).data( 'sluggable' ) );
                } );

                for ( var i = 0; i < sluggables.length; i++ ) {
                    var sluggable = sluggables[ i ];

                    methods[ 'sluggable_single' ]( sluggable );
                }
            },
            tabbed : function () {
                $( '.pods-admin' ).on( 'click', '.pods-tabs .pods-tab a.pods-tab-link', function ( e ) {
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
                        $.when( $tabbed.find( '.pods-tab-group .pods-tab' ).not( tab_hash ).slideUp() ).done( function () {
                            var $current_tab = $tabbed.find( '.pods-tab-group .pods-tab' + tab_hash );

                            $( '.pods-dependent-toggle', $current_tab ).each( function () {
                                var elementId = $( this ).attr( 'id' );
                                var runDependencies = true;
                                var selectionTypes = [
                                    {
                                        name           : 'single',
                                        pickFormatRegex: /pick-format-single$/g
                                    },
                                    {
                                        name           : 'multi',
                                        pickFormatRegex: /pick-format-multi$/g
                                    }
                                ];

                                // Pick multi/single select: Bypass dependency checks on the format of selection types
                                // that aren't currently chosen. We shouldn't check dependencies against format_single
                                // if multi is selected and vice versa.
                                selectionTypes.forEach( function( thisSelectionType ) {
                                    var pickSelectionTypeId = null;

                                    // Is this the format list for one of the selection types?
                                    if ( thisSelectionType.pickFormatRegex.test( elementId ) ) {

                                        // Get the HTML ID of the "selection type" select box so we can check its value
                                        pickSelectionTypeId = elementId.replace( thisSelectionType.pickFormatRegex, 'pick-format-type' );

                                        // Bypass dependency checks if this format value is for a selection type
                                        // that isn't currently selected
                                        if ( $( '#' + pickSelectionTypeId ).val() !== thisSelectionType.name ) {
                                            runDependencies = false;
                                        }
                                    }
                                } );

                                if ( runDependencies ) {
                                    methods[ 'setup_dependencies' ]( $( this ) );
                                }
                            } );

                            $current_tab.slideDown();
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
            nav_tabbed : function () {
                $( '.pods-admin' ).on( 'click', '.pods-nav-tabs a.pods-nav-tab-link', function ( e ) {
                    $( this ).css( 'cursor', 'default' );
                    $( this ).prop( 'disabled', true );

                    var tab_class = '.pods-nav-tabbed';

                    if ( 'undefined' != typeof $( this ).closest( '.pods-nav-tabs' ).data( 'tabbed' ) )
                        tab_class = $( this ).closest( '.pods-nav-tabs' ).data( 'tabbed' );

                    var $tabbed = $( this ).closest( tab_class );
                    var tab_hash = this.hash;

                    if ( $tabbed.find( '.pods-nav-tabs a.pods-nav-tab-link[data-tabs]' )[ 0 ] ) {
                        $tabbed.find( '.pods-nav-tabs a.pods-nav-tab-link[data-tabs]' ).each( function () {
                            var tabs = $( this ).data( 'tabs' ),
                                this_tab_hash = this.hash;

                            if ( tab_hash != this_tab_hash )
                                $tabbed.find( tabs ).hide();
                            else
                                $tabbed.find( tabs ).show();
                        } );
                    }
                    else {
                        $tabbed.find( '.pods-nav-tab-group .pods-nav-tab' ).not( tab_hash ).each( function () {
                            $( this ).hide();
                        } );

                        $tabbed.find( '.pods-nav-tab-group .pods-nav-tab' ).filter( tab_hash ).each( function () {
                            $( '.pods-dependent-toggle', this ).trigger( 'change' );

                            $( this ).show();
                        } );
                    }

                    $tabbed.find( '.pods-nav-tabs a.pods-nav-tab-link' ).removeClass( 'nav-tab-active' );

                    $( this ).addClass( 'nav-tab-active' );

                    $( this ).css( 'cursor', 'pointer' );
                    $( this ).prop( 'disabled', false );

                    e.preventDefault();
                } );

                $( '.pods-nav-tabbed' ).each( function () {
                    $nav_tabbed = $( this );
                    $nav_tabbed.find( '.pods-nav-tabs a.pods-nav-tab-link:first' ).addClass( 'nav-tab-active' );
                    $nav_tabbed.find( '.pods-nav-tab-group .pods-nav-tab:first' ).each( function () {
                        $( '.pods-dependent-toggle', this ).trigger( 'change' );
                        $( this ).show();
                    } )
                } );
            },
            wizard : function () {
                var methods = {
                    setFinished : function () {
                        $( '#pods-wizard-next' ).text( $( '#pods-wizard-next' ).data( 'finished' ) );
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
                                check = pods_admin_wizard_callback( step, false );

                            if ( false === check )
                                return check;

                            window.location.hash = '';
                        }
                        else if ( $( '#pods-wizard-box' ).closest( 'form' )[ 0 ] ) {
                            $( '#pods-wizard-next' ).css( 'cursor', 'default' );
                            $( '#pods-wizard-next' ).prop( 'disabled', true );
                            $( '#pods-wizard-next' ).text( $( '#pods-wizard-next' ).data( 'processing' ) );

                            // Allow for override
                            if ( 'undefined' != typeof pods_admin_wizard_callback )
                                check = pods_admin_wizard_callback( step, true );

                            if ( false === check )
                                return check;

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
                                check = pods_admin_wizard_callback( step, true );

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
                    if ( $( this ).is( ':disabled' ) )
                        return;

                    e.preventDefault();

                    methods.stepForward();
                } );

                // Start over button event binding
                $( '#pods-wizard-start' ).hide().on( 'click', function ( e ) {
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
            setup_dependencies : function( $el ) {
                var $current = $el.closest( '.pods-dependency' ),
                    $field = $el,
                    val = $el.val(),
                    $field_type,
                    dependent_flag,
                    dependent_specific,
                    exclude_flag,
                    exclude_specific,
                    wildcard_target,
                    wildcard_target_value;

                /**
                 * Check if this element is a child from an 'advanced field options' group.
                 * If so, set the value to empty if this is not the current field type group
                 * Fixes dependency compatibility
                 *
                 * @todo Validate & improve this
                 */
                // Are we in the "Fields" tab?
                if ( $current.parents('#pods-manage-fields').length ) {
                    // And are we also in the "Additional Field Options" tab?
                    if ( $el.parents('.pods-additional-field-options').length ) {
                        // Get this field's type
                        $field_type = $current.find( '.pods-form-ui-field-name-field-data-type' ).val();
                        // Check if this element resides within the correct "Additional Field Options" tab
                        if ( ! $el.parents( '.pods-additional-field-options > .pods-depends-on-field-data-type-' + $field_type ).length ) {
                            // This is not an option for this field. Empty the value
                            val = '';
                        }
                    }
                }

                if ( null === val ) {
                    val = '';
                }

                dependent_flag = '.pods-depends-on-' + $el.data( 'name-clean' ).replace( /\_/gi, '-' );
                dependent_specific = dependent_flag + '-' + val.replace( /\_/gi, '-' );

                $current.find( dependent_flag ).each( function () {
                    var $dependent_el = $( this ),
                        dependency_trigger;

                    if ( $dependent_el.parent().is( ':visible' ) ) {
                        if ( $field.is( 'input[type=checkbox]' ) ) {
                            if ( $field.is( ':checked' ) && ( 1 == $field.val() || $dependent_el.is( dependent_specific ) ) ) {
                                if ( $dependent_el.is( 'tr' ) )
                                    $dependent_el.show().addClass( 'pods-dependent-visible' );
                                else
                                    $dependent_el.slideDown().addClass( 'pods-dependent-visible' );

                                $dependent_el.find( '.pods-dependency .pods-depends-on' ).hide();
                                $dependent_el.find( '.pods-dependency .pods-excludes-on' ).hide();

                                $dependent_el.find( '.pods-dependency .pods-dependent-toggle' ).each( function () {
                                    methods[ 'setup_dependencies' ]( $( this ) );
                                } );

                                if ( $dependent_el.is( '[data-dependency-trigger]' ) ) {
                                    dependency_trigger = $dependent_el.data( 'dependency-trigger' );

                                    dependency_trigger = window[ dependency_trigger ];

                                    dependency_trigger( $dependent_el );
                                }
                            }
                            else if ( !$field.is( ':checked' ) && ( !$field.is( '.pods-dependent-multi' ) || $dependent_el.is( dependent_specific ) ) ) {
                                if ( $dependent_el.is( 'tr' ) )
                                    $dependent_el.hide().removeClass( 'pods-dependent-visible' );
                                else
                                    $dependent_el.slideUp().removeClass( 'pods-dependent-visible' );
                            }
                        }
                        else if ( $dependent_el.is( dependent_specific ) ) {
                            if ( $dependent_el.is( 'tr' ) )
                                $dependent_el.show().addClass( 'pods-dependent-visible' );
                            else
                                $dependent_el.slideDown().addClass( 'pods-dependent-visible' );

                            $dependent_el.find( '.pods-dependency .pods-depends-on' ).hide();
                            $dependent_el.find( '.pods-dependency .pods-excludes-on' ).hide();

                            $dependent_el.find( '.pods-dependency .pods-dependent-toggle' ).each( function () {
                                methods[ 'setup_dependencies' ]( $( this ) );
                            } );

                            if ( $dependent_el.is( '[data-dependency-trigger]' ) ) {
                                dependency_trigger = $dependent_el.data( 'dependency-trigger' );

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
                        if ( $field.is( 'input[type=checkbox]' ) ) {
                            if ( $field.is( ':checked' ) && ( 1 == $field.val() || $dependent_el.is( dependent_specific ) ) ) {
                                $dependent_el.show().addClass( 'pods-dependent-visible' );
                                $dependent_el.find( '.pods-dependency .pods-depends-on' ).hide();
                                $dependent_el.find( '.pods-dependency .pods-excludes-on' ).hide();

                                $dependent_el.find( '.pods-dependency .pods-dependent-toggle' ).each( function () {
                                    methods[ 'setup_dependencies' ]( $( this ) );
                                } );

                                if ( $dependent_el.is( '[data-dependency-trigger]' ) ) {
                                    dependency_trigger = $dependent_el.data( 'dependency-trigger' );

                                    dependency_trigger = window[ dependency_trigger ];

                                    dependency_trigger( $dependent_el );
                                }
                            }
                            else if ( !$field.is( ':checked' ) && ( !$field.is( '.pods-dependent-multi' ) || $dependent_el.is( dependent_specific ) ) )
                                $dependent_el.hide().removeClass( 'pods-dependent-visible' );
                        }
                        else if ( $dependent_el.is( dependent_specific ) ) {
                            $dependent_el.show().addClass( 'pods-dependent-visible' );
                            $dependent_el.find( '.pods-dependency .pods-depends-on' ).hide();
                            $dependent_el.find( '.pods-dependency .pods-excludes-on' ).hide();

                            $dependent_el.find( '.pods-dependency .pods-dependent-toggle' ).each( function () {
                                methods[ 'setup_dependencies' ]( $( this ) );
                            } );

                            if ( $dependent_el.is( '[data-dependency-trigger]' ) ) {
                                dependency_trigger = $dependent_el.data( 'dependency-trigger' );

                                dependency_trigger = window[ dependency_trigger ];

                                dependency_trigger( $dependent_el );
                            }
                        }
                        else
                            $dependent_el.hide().removeClass( 'pods-dependent-visible' );
                    }
                } );

                exclude_flag = '.pods-excludes-on-' + $el.data( 'name-clean' ).replace( /\_/gi, '-' );
                exclude_specific = exclude_flag + '-' + val.replace( /\_/gi, '-' );

                $current.find( exclude_flag ).each( function () {
                    var $dependent_el = $( this ),
                        dependency_trigger;

                    if ( $dependent_el.parent().is( ':visible' ) ) {
                        if ( $field.is( 'input[type=checkbox]' ) ) {
                            if ( $field.is( ':checked' ) && ( 1 == $field.val() || $dependent_el.is( exclude_specific ) ) ) {
                                if ( $dependent_el.is( 'tr' ) )
                                    $dependent_el.hide().removeClass( 'pods-dependent-visible' );
                                else
                                    $dependent_el.slideUp().removeClass( 'pods-dependent-visible' );
                            }
                            else if ( !$field.is( ':checked' ) && ( !$field.is( '.pods-dependent-multi' ) || $dependent_el.is( exclude_specific ) ) ) {
                                if ( $dependent_el.is( 'tr' ) )
                                    $dependent_el.show().addClass( 'pods-dependent-visible' );
                                else
                                    $dependent_el.slideDown().addClass( 'pods-dependent-visible' );

                                $dependent_el.find( '.pods-dependency .pods-depends-on' ).hide();
                                $dependent_el.find( '.pods-dependency .pods-excludes-on' ).hide();

                                $dependent_el.find( '.pods-dependency .pods-dependent-toggle' ).each( function () {
                                    methods[ 'setup_dependencies' ]( $( this ) );
                                } );

                                if ( $dependent_el.is( '[data-dependency-trigger]' ) ) {
                                    dependency_trigger = $dependent_el.data( 'dependency-trigger' );

                                    dependency_trigger = window[ dependency_trigger ];

                                    dependency_trigger( $dependent_el );
                                }
                            }
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
                                methods[ 'setup_dependencies' ]( $( this ) );
                            } );

                            if ( $dependent_el.is( '[data-dependency-trigger]' ) ) {
                                dependency_trigger = $dependent_el.data( 'dependency-trigger' );

                                dependency_trigger = window[ dependency_trigger ];

                                dependency_trigger( $dependent_el );
                            }
                        }
                    }
                    else {
                        if ( $field.is( 'input[type=checkbox]' ) ) {
                            if ( $field.is( ':checked' ) && ( 1 == $field.val() || $dependent_el.is( exclude_specific ) ) )
                                $dependent_el.hide().removeClass( 'pods-dependent-visible' );
                            else if ( !$field.is( ':checked' ) && ( !$field.is( '.pods-dependent-multi' ) || $dependent_el.is( exclude_specific ) ) ) {
                                $dependent_el.show().addClass( 'pods-dependent-visible' );
                                $dependent_el.find( '.pods-dependency .pods-depends-on' ).hide();
                                $dependent_el.find( '.pods-dependency .pods-excludes-on' ).hide();

                                $dependent_el.find( '.pods-dependency .pods-dependent-toggle' ).each( function () {
                                    methods[ 'setup_dependencies' ]( $( this ) );
                                } );

                                if ( $dependent_el.is( '[data-dependency-trigger]' ) ) {
                                    dependency_trigger = $dependent_el.data( 'dependency-trigger' );

                                    dependency_trigger = window[ dependency_trigger ];

                                    dependency_trigger( $dependent_el );
                                }
                            }
                        }
                        else if ( $dependent_el.is( exclude_specific ) )
                            $dependent_el.hide().removeClass( 'pods-dependent-visible' );
                        else {
                            $dependent_el.show().addClass( 'pods-dependent-visible' );
                            $dependent_el.find( '.pods-dependency .pods-depends-on' ).hide();
                            $dependent_el.find( '.pods-dependency .pods-excludes-on' ).hide();

                            $dependent_el.find( '.pods-dependency .pods-dependent-toggle' ).each( function () {
                                methods[ 'setup_dependencies' ]( $( this ) );
                            } );

                            if ( $dependent_el.is( '[data-dependency-trigger]' ) ) {
                                dependency_trigger = $dependent_el.data( 'dependency-trigger' );

                                dependency_trigger = window[ dependency_trigger ];

                                dependency_trigger( $dependent_el );
                            }
                        }
                    }
                } );

                // Search for wildcard dependencies on this element's value
                wildcard_target = '.pods-wildcard-on-' + $el.data( 'name-clean' ).replace( /\_/gi, '-' );
                wildcard_target_value = val.replace( /\_/gi, '-' );

                $current.find( wildcard_target ).each( function () {
                    var $dependent_el = $( this ),
                        data_attribute = 'pods-wildcard-' + $field.data( 'name-clean' ),
                        wildcard_data = $dependent_el.data( data_attribute ),
                        match_found,
                        dependency_trigger;

                    // Could support objects but limiting to a single string for now
                    if ( 'string' !== typeof wildcard_data ) {
                        return true; // Continues the outer each() loop
                    }

                    // Check for a wildcard match.  Can be multiple wildcards in a comma separated list
                    match_found = false;
                    $.each( wildcard_data.split( ',' ), function( index, this_wildcard ) {
                        if ( null !== wildcard_target_value.match( this_wildcard ) ) {
                            match_found = true;
                            return false; // Stop iterating through further each() elements
                        }
                    } );

                    // Set the state of the dependent element
                    if ( $dependent_el.parent().is( ':visible' ) ) {
                        if ( match_found ) {
                            if ( $dependent_el.is( 'tr' ) ) {
                                $dependent_el.show().addClass( 'pods-dependent-visible' );
                            }
                            else {
                                $dependent_el.slideDown().addClass( 'pods-dependent-visible' );
                            }

                            $dependent_el.find( '.pods-dependency .pods-depends-on' ).hide();
                            $dependent_el.find( '.pods-dependency .pods-excludes-on' ).hide();
                            $dependent_el.find( '.pods-dependency .pods-wildcard-on' ).hide();

                            $dependent_el.find( '.pods-dependency .pods-dependent-toggle' ).each( function () {
                                methods[ 'setup_dependencies' ]( $( this ) );
                            } );

                            if ( $dependent_el.is( '[data-dependency-trigger]' ) ) {
                                dependency_trigger = $dependent_el.data( 'dependency-trigger' );

                                dependency_trigger = window[ dependency_trigger ];

                                dependency_trigger( $dependent_el );
                            }
                        }
                        else { // No wildcard matches
                            if ( $dependent_el.is( 'tr' ) ) {
                                $dependent_el.hide().removeClass( 'pods-dependent-visible' );
                            }
                            else {
                                $dependent_el.slideUp().removeClass( 'pods-dependent-visible' );
                            }
                        }
                    }
                    else { // Parent element wasn't visible
                        if ( match_found ) {
                            $dependent_el.show().addClass( 'pods-dependent-visible' );
                            $dependent_el.find( '.pods-dependency .pods-depends-on' ).hide();
                            $dependent_el.find( '.pods-dependency .pods-excludes-on' ).hide();
                            $dependent_el.find( '.pods-dependency .pods-wildcard-on' ).hide();

                            $dependent_el.find( '.pods-dependency .pods-dependent-toggle' ).each( function () {
                                methods[ 'setup_dependencies' ]( $( this ) );
                            } );

                            if ( $dependent_el.is( '[data-dependency-trigger]' ) ) {
                                dependency_trigger = $dependent_el.data( 'dependency-trigger' );

                                dependency_trigger = window[ dependency_trigger ];

                                dependency_trigger( $dependent_el );
                            }
                        }
                        else { // No wildcard matches
                            $dependent_el.hide().removeClass( 'pods-dependent-visible' );
                        }
                    }
                } );
            },
            dependency : function ( init ) {
                // Hide all dependents
                $( '.pods-dependency .pods-depends-on, .pods-dependency .pods-excludes-on, .pods-dependency .pods-wildcard-on' ).hide();

                // Handle dependent toggle
                $( '.pods-admin' ).on( 'change', '.pods-dependent-toggle[data-name-clean]', function ( e ) {
                    var selectionTypeRegex = /pick-format-type$/g;
                    var elementId = $( this ).attr( 'id' );
                    var selectionType, selectionFormatId;

                    // Setup dependencies for the field that changed
                    methods[ 'setup_dependencies' ]( $( this ) );

                    // Also force a dependency update for the appropriate format when "selection type" changes
                    if ( selectionTypeRegex.test( elementId ) ) {
                        selectionType = $( this ).val();
                        selectionFormatId = elementId.replace( selectionTypeRegex, 'pick-format-' + selectionType );
                        methods[ 'setup_dependencies' ]( $( '#' + selectionFormatId ) );
                    }

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

                    var dependent_flag = '.pods-depends-on-' + $el.data( 'name-clean' ).replace( /\_/gi, '-' );
                    var dependent_specific = dependent_flag + '-' + $el.val().replace( /\_/gi, '-' );

                    $current.find( dependent_flag ).each( function () {
                        var $dependent_el = $( this );

                        if ( $dependent_el.parent().is( ':visible' ) ) {
                            if ( $field.is( 'input[type=checkbox]' ) && $field.is( ':checked' ) && 1 == $field.val() ) {
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
                            if ( $field.is( 'input[type=checkbox]' ) && $field.is( ':checked' ) && 1 == $field.val() ) {
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

                    var exclude_flag = '.pods-excludes-on-' + $el.data( 'name-clean' ).replace( /\_/gi, '-' );
                    var exclude_specific = exclude_flag + '-' + $el.val().replace( /\_/gi, '-' );

                    $current.find( exclude_flag ).each( function () {
                        var $dependent_el = $( this );

                        if ( $dependent_el.parent().is( ':visible' ) ) {
                            if ( $field.is( 'input[type=checkbox]' ) && $field.is( ':checked' ) && 1 == $field.val() ) {
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
                            if ( $field.is( 'input[type=checkbox]' ) && $field.is( ':checked' ) && 1 == $field.val() )
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
            	var new_row, orig_fields;

                new_row = row;

                if ( new_row[ 0 ] )
                    new_row = new_row.html();

                // Hide all rows
                $( 'div.pods-manage-row-wrapper' ).hide();

                orig_fields = {};

                // Handle 'Edit' action
                $( 'tbody.pods-manage-list' ).on( 'click', 'a.pods-manage-row-edit', function ( e ) {
                    var $row, $row_label, $row_content, $tbody;
                    var row_counter, edit_row, $field_wrapper, field_data, field_array_counter, json_name;

                    $( this ).css( 'cursor', 'default' );
                    $( this ).prop( 'disabled', true );

                    $row = $( this ).closest( 'tr.pods-manage-row' );
                    $row_label = $row.find( 'td.pods-manage-row-label' );
                    $row_content = $row_label.find( 'div.pods-manage-row-wrapper' );

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
                            $tbody = $( this ).closest( 'tbody.pods-manage-list' );

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
                            row_counter = $row.data( 'row' );

                            edit_row = new_row.replace( /\_\_1/gi, row_counter ).replace( /\-\-1/gi, row_counter );
                            $field_wrapper = $row_content.find( 'div.pods-manage-field' );

                            if ( $row.hasClass( 'pods-field-duplicated' ) ) {
                                $row.removeClass( 'pods-field-duplicated' );
                            } else {
                                $field_wrapper.append( edit_row );

                                // ToDo: Duct tape to handle fields added dynamically.  Find out if we can avoid this
                                $row_content.find( '.pods-form-ui-field' ).PodsDFVInit( PodsDFV.fieldInstances );
                            }

                            $field_wrapper.find( '.pods-depends-on' ).hide();
                            $field_wrapper.find( '.pods-excludes-on' ).hide();

                            $field_wrapper.find( '.pods-dependent-toggle' ).each( function () {
                                $( this ).trigger( 'change' );
                            } );

                            field_data = jQuery.parseJSON( $row_content.find( 'input.field_data' ).val() );

                            field_array_counter = 0;

                            $field_wrapper.find( 'input, select, textarea' ).each( function () {
                                json_name = $( this ).prop( 'name' ).replace( 'field_data[' + row_counter + '][', '' ).replace( /\[\d*\]/gi, '' ).replace( '[', '' ).replace( ']', '' );

                                if ( 'undefined' == typeof field_data[ json_name ] )
                                    return;

                                if ( 0 < $( this ).prop( 'name' ).indexOf( '[]' ) || $( this ).prop( 'name' ).replace( 'field_data[' + row_counter + ']', '' ).match( /\[\d*\]/ ) ) {
                                    if ( $( this ).is( 'input[type=checkbox]' ) ) {
                                        $( this ).prop( 'checked', ( -1 < jQuery.inArray( $( this ).val(), field_data[ json_name ] ) ) );

                                        orig_fields[ $row.data( 'id' ) ][ $( this ).prop( 'name' ) ] = $( this ).prop( 'checked' );
                                    }
                                    else if ( 'undefined' != typeof field_data[ json_name ][ field_array_counter ] ) {
                                        $( this ).val( field_data[ json_name ][ field_array_counter ] );

                                        orig_fields[ $row.data( 'id' ) ][ $( this ).prop( 'name' ) ] = $( this ).val();
                                    }

                                    field_array_counter++;
                                }
                                else {
                                    field_array_counter = 0;

                                    if ( $( this ).is( 'input[type=checkbox]' ) ) {
                                        $( this ).prop( 'checked', ( $( this ).val() == field_data[ json_name ] ) );

                                        orig_fields[ $row.data( 'id' ) ][ $( this ).prop( 'name' ) ] = $( this ).prop( 'checked' );
                                    }
                                    else {
                                        $( this ).val( field_data[ json_name ] );

                                        orig_fields[ $row.data( 'id' ) ][ $( this ).prop( 'name' ) ] = $( this ).val();
                                    }
                                }
                            } );

                            $field_wrapper.find( '.pods-tabbed ul.pods-tabs .pods-tab:first a' ).addClass( 'selected' );
                            $field_wrapper.find( '.pods-tabbed .pods-tab-group .pods-tab:first' ).show();

                            $row.removeClass( 'pods-field-init' );

                            $( document ).Pods( 'qtip', $row );
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

                        methods[ 'scroll' ]( $row );

                        $row_content.slideDown();


                        $row_content.find( '.pods-dependency .pods-dependent-toggle' ).each( function () {
                            methods[ 'setup_dependencies' ]( $( this ) );
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
                    var color = ( $row.hasClass( 'alternate' ) ? '#F1F1F1' : '#FFFFFF' );
                    var row_id = $row.data( 'row' );
                    var field_data = {};

                    if ( 'undefined' != typeof $row_value && null != $row_value && '' != $row_value ) {
                        field_data = jQuery.parseJSON( $row_value );
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

                            var val = $el.val(),
                                field_array = $el.prop( 'name' ).match( /\[(\w*|)\]/gi ),
                                field_name = ( ( null != field_array && 1 < field_array.length ) ? field_array[ 1 ].replace( '[', '' ).replace( ']', '' ) : '' ),
                                field_found = -1;

                            if ( '' == field_name )
                                return;

                            if ( $el.is( 'input[type=checkbox]' ) && $el.is( '.pods-form-ui-field-type-pick' ) ) {
                                if ( 'object' == typeof field_data[ field_name ] || 'array' == typeof field_data[ field_name ] ) {
                                    field_found = jQuery.inArray( val, field_data[ field_name ] );

                                    if ( -1 < field_found ) {
                                        if ( !$el.is( ':checked' ) )
                                            field_data[ field_name ].splice( field_found, 1 );
                                    }
                                    else if ( $el.is( ':checked' ) )
                                        field_data[ field_name ].push( val );
                                }
                                else {
                                    field_data[ field_name ] = [];

                                    if ( $el.is( ':checked' ) )
                                        field_data[ field_name ].push( val );
                                }

                                return;
                            }
                            else if ( $el.is( 'input[type=checkbox]' ) && !$el.is( ':checked' ) ) {
                                if ( 1 == val )
                                    val = 0;
                                else
                                    val = '';
                            }
                            else if ( $el.is( 'input[type=radio]' ) && !$el.is( ':checked' ) )
                                val = '';

                            if ( $el.is( ':visible' ) && $el.hasClass( 'pods-validate pods-validate-required' ) && '' == $el.val() ) {
                                $el.trigger( 'change' );

                                if ( false !== valid_form )
                                    $el.focus();

                                valid_form = false;
                            }

                            if ( $el.is( 'input[type=checkbox]' ) && $el.is( '.pods-form-ui-field-type-pick' ) ) {
                                if ( -1 == field_found ) {
                                    if ( 'object' != typeof field_data[ field_name ] && 'array' != typeof field_data[ field_name ] )
                                        field_data[ field_name ] = [];

                                    if ( '' != val )
                                        field_data[ field_name ].push( val );
                                }
                            }
                            else if ( 2 == field_array.length )
                                field_data[ field_name ] = val;
                            else if ( 3 == field_array.length ) {
                                the_field = parseInt( field_array[ 2 ].replace( '[', '' ).replace( ']', '' ) );

                                if ( isNaN( the_field ) )
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
                    } );

                    if ( valid_form ) {
                        $row_content.find( 'input.field_data' ).val( $.toJSON( field_data ) );

                        $row.css( 'backgroundColor', '#FFFF33' ).animate(
                            { backgroundColor : color },
                            {
                                duration : 600,
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
	                        $row_label.prop( 'colspan', '1' );
	                        $row.removeClass( 'pods-manage-row-expanded' );
	                        $row.removeClass( 'pods-field-new' );
	                        $row.addClass( 'pods-field-updated' );
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
                $( 'body' ).on( 'click', '.pods-toggled .handlediv, .pods-toggled h3', function () {
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

                if ( 'undefined' != typeof new_row && null !== new_row ) {
                    // Handle 'Add' action
                    $( '.pods-manage-row-add' ).on( 'click', 'a', function ( e ) {
                        var add_row, $new_row, $tbody;

                        e.preventDefault();

                        $( this ).css( 'cursor', 'default' );
                        $( this ).prop( 'disabled', true );

                        row_counter++;

                        add_row = new_row.replace( /__1/gi, row_counter ).replace( /--1/gi, row_counter );
                        $tbody = $( this ).parent().parent().find( 'tbody.pods-manage-list' );

                        $tbody.find( 'tr.no-items' ).hide();
                        $tbody.append( '<tr id="row-' + row_counter + '" class="pods-manage-row pods-field-new pods-field-' + row_counter + ' pods-submittable-fields" valign="top">' + add_row + '</tr>' );

                        $new_row = $tbody.find( 'tr#row-' + row_counter );

                        // ToDo: Duct tape to handle fields added dynamically.  Find out if we can avoid this
                        $new_row.find( '.pods-form-ui-field' ).PodsDFVInit( PodsDFV.fieldInstances );

                        $new_row.data( 'row', row_counter );
                        $new_row.find( '.pods-dependency .pods-depends-on' ).hide();
                        $new_row.find( '.pods-dependency .pods-excludes-on' ).hide();

                        $new_row.find( '.pods-manage-row-wrapper' ).hide( 0, function () {
                            $new_row.find( 'a.row-label.pods-manage-row-edit' ).click();
                        } );

                        $( '.pods-tabs .pods-tab:first a', $new_row ).addClass( 'selected' );
                        $( '.pods-tab-group', $new_row ).find( '.pods-tab:first' ).show();

                        if ( $.fn.sortable && $tbody.hasClass( 'pods-manage-sortable' ) )
                            $tbody.sortable( 'refresh' );

                        $( 'tr.pods-manage-row' ).removeClass( 'alternate' );
                        $( 'tr.pods-manage-row:even' ).addClass( 'alternate' );

                        methods[ 'sluggables' ]( $new_row );

                        $( this ).css( 'cursor', 'pointer' );
                        $( this ).prop( 'disabled', false );

                        $( document ).Pods( 'qtip', $new_row );

                        methods[ 'scroll' ]( $new_row );
                    } );

                    // Handle 'Duplicate' action
                    $( 'tbody.pods-manage-list' ).on( 'click', 'a.pods-manage-row-duplicate', function ( e ) {
                        var add_row, field_data;
                        var $tbody, $row, $row_label, $row_content, $new_row, $new_row_label, $new_row_content;

                        e.preventDefault();

                        $( this ).css( 'cursor', 'default' );
                        $( this ).prop( 'disabled', true );

                        $row = $( this ).closest( 'tr.pods-manage-row' );
                        $row_label = $row.find( 'td.pods-manage-row-label' );
                        $row_content = $row_label.find( 'div.pods-manage-row-wrapper' );

                        field_data = jQuery.parseJSON( $row_content.find( 'input.field_data' ).val() );

                        row_counter++;

                        add_row = new_row.replace( /__1/gi, row_counter ).replace( /--1/gi, row_counter );
                        $tbody = $( this ).closest( 'tbody.pods-manage-list' );

                        $tbody.find( 'tr.no-items' ).hide();
                        $tbody.append( '<tr id="row-' + row_counter + '" class="pods-manage-row pods-field-init pods-field-new pods-field-duplicated pods-field-' + row_counter + ' pods-submittable-fields" valign="top">' + add_row + '</tr>' );

                        $new_row = $tbody.find( 'tr#row-' + row_counter );
                        $new_row_label = $new_row.find( 'td.pods-manage-row-label' );
                        $new_row_content = $new_row_label.find( 'div.pods-manage-row-wrapper' );

                        // ToDo: Duct tape to handle fields added dynamically.  Find out if we can avoid this
                        $new_row.find( '.pods-form-ui-field' ).PodsDFVInit( PodsDFV.fieldInstances );

                        field_data[ 'name' ] += '_copy';
                        field_data[ 'label' ] += ' (' + PodsI18n.__( 'Copy' ) + ')';
                        field_data[ 'id' ] = 0;

                        $new_row_label.find( 'a.pods-manage-row-edit.row-label' ).html( field_data[ 'label' ] );

                        $new_row_content.find( 'input.field_data' ).val( $.toJSON( field_data ) );

                        $new_row.data( 'row', row_counter );
                        $new_row.find( '.pods-dependency .pods-depends-on' ).hide();
                        $new_row.find( '.pods-dependency .pods-excludes-on' ).hide();

                        $new_row.find( '.pods-dependency .pods-dependent-toggle' ).each( function () {
                            methods[ 'setup_dependencies' ]( $( this ) );
                        } );

                        $new_row.find( '.pods-manage-row-wrapper' ).hide( 0, function () {
                            $new_row.find( 'a.pods-manage-row-edit' ).click();
                        } );

                        $( '.pods-tabs .pods-tab:first a', $new_row ).addClass( 'selected' );
                        $( '.pods-tab-group', $new_row ).find( '.pods-tab:first' ).show();

                        if ( $.fn.sortable && $tbody.hasClass( 'pods-manage-sortable' ) )
                            $tbody.sortable( 'refresh' );

                        $( 'tr.pods-manage-row' ).removeClass( 'alternate' );
                        $( 'tr.pods-manage-row:even' ).addClass( 'alternate' );

                        methods[ 'sluggables' ]( $new_row );

                        $( this ).css( 'cursor', 'pointer' );
                        $( this ).prop( 'disabled', false );

                        $( document ).Pods( 'qtip', $new_row );

                        methods[ 'scroll' ]( $new_row );
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
                            return PodsI18n.__( 'Navigating away from this page will discard any changes you have made.' );
                    }
                } );

                $( 'form.pods-submittable' ).on( 'click', '.submitdelete', function () {
                    pods_changed = false;
                } );
            },
            qtip: function( element ) {
                $( element ).find( '.pods-qtip' ).qtip( {
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
            },
            scroll : function ( selector, callback ) {
                var offset = 10;

                if ( $( '#wpadminbar' )[ 0 ] )
                    offset += $( '#wpadminbar' ).height();

                $( 'html, body' ).animate( { scrollTop : $( selector ).offset().top - offset }, 'slow', callback );
            },
            scroll_to : function () {
                $( '.pods-admin' ).on( 'click', 'a.pods-scroll-to', function ( e ) {
                    e.preventDefault();

                    methods[ 'scroll' ]( '#' + this.hash );
                } );
            }
        };

    $.fn.Pods = function ( method ) {
        if ( methods[ method ] ) {
            return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ) );
        }
        // Don't need this part (yet)
        /*
        else if ( typeof method === 'object' || !method ) {
        return methods.init.apply( this, arguments );
        }
        */
        else {
            $.error( 'Method ' + method + ' does not exist on jQuery.Pods' );
        }
    };
} )( jQuery );
