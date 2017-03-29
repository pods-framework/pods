/*@global PodsI18n */
var pods_file_context = false; // tracks whether or not we've got a thickbox displayed in our context
var pods_file_thickbox_modder; // stores our interval for making necessary changes to thickbox content

// handle our thickbox mods
function pods_attachments ( src, file_limit ) {
    var pods_thickbox = jQuery( '#TB_iframeContent' ).contents();

    // add quick add text so we dont have to expand each line item
    var wp_media_show_links = pods_thickbox.find( 'div.media-item a.describe-toggle-on' );

    // loop through each 'Show' link and check if we added an 'Add' action next to it
    for ( var x = 0, len = wp_media_show_links.length; x < len; x++ ) {
        var wp_media_show = jQuery( wp_media_show_links[x] );

        if ( wp_media_show.data( 'pods-injected-quick-add') !== true ) {
            // Create 'Add' link
            var pods_file_quick_add = jQuery( '<a href="#">' + PodsI18n.__( 'Add' ) + '</a>' ).addClass( 'pods-quick-add' );

            pods_file_quick_add.bind( 'click', function( e ) {
                var item = jQuery( this );
                var item_parent = item.parent();

                item.fadeOut( 'fast', function() {

                    // Not sure if the close link should be there for each link?
                    item.before( '<span class="pods-attached pods-quick-add">' + PodsI18n.__( 'Added!' ) + '</span>' );
                    //item.before( '<span class="pods-attached pods-quick-add">Added! <a href="#">close this box</a>.</span>' );

                    item.remove(); }
                );

                var wp_media_meta = item_parent;

                pods_thickbox_send( wp_media_meta, e );

                item_parent.find( 'span.pods-attached a' ).on( 'click', function ( e ) {
                    parent.eval( 'tb_remove()' );
                } );

                item_parent.find( 'span.pods-attached' ).delay( 6000 ).fadeOut( 'fast' );

                e.preventDefault();
            } );

            wp_media_show.after( pods_file_quick_add );

            wp_media_show.data( 'pods-injected-quick-add', true );
        }
    }

    pods_thickbox.find( 'td.savesend input' ).unbind( 'click' ).click( function ( e ) {
        var wp_media_meta = jQuery( this ).parent().parent().parent();

        pods_thickbox_send( wp_media_meta, e );
    } );

    function pods_thickbox_send ( wp_media_meta, e ) {
        // grab our meta as per the Media library
        var wp_media_title = wp_media_meta.find( 'tr.post_title td.field input' ).val();
        //var wp_media_caption = wp_media_meta.find( 'tr.post_excerpt td.field input' ).val();
        var wp_media_id = wp_media_meta.find( 'td.imgedit-response' ).attr( 'id' ).replace( 'imgedit-response-', '' );
        var wp_media_thumb = wp_media_meta.parent().find( 'img.thumbnail' ).attr( 'src' );
        var wp_media_link = wp_media_meta.find( 'tr.url td.field input.urlfield' ).val();

        // use the data we found to form a new Pods file entry and append it to the DOM
        var source = jQuery( '#' + src + '-handlebars' ).html();

        var binding = {
            id : wp_media_id,
            name : wp_media_title,
            icon : wp_media_thumb
        };

        var tmpl = Handlebars.compile( source );

        pods_file_context.prepend( tmpl( binding ) );

        if ( !pods_file_context.is( ':visible' ) )
            pods_file_context.show().removeClass( 'hidden' );

        pods_file_context.find( 'li#pods-file-' + wp_media_id ).slideDown( 'fast' );

        var items = pods_file_context.find( 'li.pods-file' ),
            itemCount = items.size();

        if ( 0 < file_limit && itemCount > file_limit ) {
            items.each( function ( idx, elem ) {
                if ( idx + 1 > file_limit ) {
                    jQuery( elem ).remove();
                }
            } );
        }

        if ( 1 < file_limit || file_limit == 0 ) {
            jQuery( this ).after( ' <span class="pods-attached">' + PodsI18n.__( 'Added! Choose another or <a href="#">close this box</a>' ) + '</span>' );
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
    }

    // update button
    if ( pods_thickbox.find( '.media-item .savesend input[type=submit], #insertonlybutton' ).length ) {
        pods_thickbox.find( '.media-item .savesend input[type=submit], #insertonlybutton' ).val( 'Select' );
    }

    // hide the URL tab
    if ( pods_thickbox.find( '#tab-type_url' ).length )
        pods_thickbox.find( '#tab-type_url' ).hide();

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
