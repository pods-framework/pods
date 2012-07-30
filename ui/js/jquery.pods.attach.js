var pods_file_context = false; // tracks whether or not we've got a thickbox displayed in our context
var pods_file_thickbox_modder; // stores our interval for making necessary changes to thickbox content

// handle our thickbox mods
function pods_attachments ( src, limit ) {
    var pods_thickbox = jQuery( '#TB_iframeContent' ).contents();

    pods_thickbox.find( 'td.savesend input' ).unbind( 'click' ).click( function ( e ) {
        // grab our meta as per the Media library
        var wp_media_meta = jQuery( this ).parent().parent().parent();
        var wp_media_title = wp_media_meta.find( 'tr.post_title td.field input' ).val();
        var wp_media_caption = wp_media_meta.find( 'tr.post_excerpt td.field input' ).val();
        var wp_media_id = wp_media_meta.find( 'td.imgedit-response' ).attr( 'id' ).replace( 'imgedit-response-', '' );
        var wp_media_thumb = wp_media_meta.parent().find( 'img.thumbnail' ).attr( 'src' );

        // use the data we found to form a new Pods file entry and append it to the DOM
        var source = jQuery( '#' + src + '-handlebars' ).html();

        var binding = {
            id : wp_media_id,
            name : wp_media_title,
            icon : wp_media_thumb
        };

        var tmpl = Handlebars.compile( source );

        pods_file_context.append( tmpl( binding ) );
        pods_file_context.find( 'li#pods-file-' + wp_media_id ).slideDown( 'fast' );

        if ( 1 < limit ) {
            jQuery( this ).after( ' <span class="pods-attached">Added! Choose another or <a href="#">close this box</a>.</span>' );
            jQuery( this ).parent().find( 'span.pods-attached a' ).on( 'click', function ( e ) {
                parent.eval( 'tb_remove()' );

                e.preventDefault();
            } );
            jQuery( this ).parent().find( 'span.pods-attached' ).delay( 6000 ).fadeOut( 'fast' );
        }
        else {
            parent.eval( 'tb_remove()' );
        }

        e.preventDefault();
    } );

    // update button
    if ( pods_thickbox.find( '.media-item .savesend input[type=submit], #insertonlybutton' ).length ) {
        pods_thickbox.find( '.media-item .savesend input[type=submit], #insertonlybutton' ).val( 'Select' );
    }

    // hide the URL tab
    if ( pods_thickbox.find( '#tab-type_url' ).length ) {
        pods_thickbox.find( '#tab-type_url' ).hide();
    }

    // we need to ALWAYS get the fullsize since we're retrieving the guid
    // if the user inserts an image somewhere else and chooses another size, everything breaks, so we'll force it
    if ( pods_thickbox.find( 'tr.post_title' ).length ) {
        pods_thickbox.find( 'tr.image-size input[value="full"]' ).prop( 'checked', true );
        pods_thickbox.find( 'tr.image-size,tr.post_content,tr.url,tr.align,tr.submit>td>a.del-link' ).hide();
    }

    // was the thickbox closed?
    if ( pods_thickbox.length == 0 && pods_file_context ) {
        clearInterval( pods_file_thickbox_modder );
        pods_file_context = false;
    }
}