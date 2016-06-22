<?php
global $post_ID;

wp_enqueue_script( 'pods-handlebars' );
wp_enqueue_script( 'jquery-ui-core' );
wp_enqueue_script( 'jquery-ui-sortable' );
wp_enqueue_style( 'pods-attach' );

$args = array();

if ( is_admin() && !empty( $post_ID ) )
    $args = array( 'post' => (int) $post_ID );

wp_enqueue_media( $args );

$field_file = PodsForm::field_loader( 'file' );

$attributes = array();
$attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );

$css_id = $attributes[ 'id' ];

$router = pods_var( $form_field_type . '_attachment_tab', $options, 'browse' );

$file_limit = 1;

// @todo: File limit not yet supported in the UI, it's either single or multiple
if ( 'multi' == pods_var( $form_field_type . '_format_type', $options, 'single' ) )
    $file_limit = (int) pods_var( $form_field_type . '_limit', $options, 0 );

$limit_file_type = pods_var( $form_field_type . '_type', $options, 'images' );

$title_editable = pods_var( $form_field_type . '_edit_title', $options, 0 );
$linked = pods_var( $form_field_type . '_linked', $options, 0 );

if ( 'images' == $limit_file_type ) {
    $limit_types = 'image';
    $limit_extensions = 'jpg,jpeg,png,gif';
}
elseif ( 'video' == $limit_file_type ) {
    $limit_types = 'video';
    $limit_extensions = 'mpg,mov,flv,mp4';
}
elseif ( 'audio' == $limit_file_type ) {
    $limit_types = 'audio';
    $limit_extensions = 'mp3,m4a,wav,wma';
}
elseif ( 'text' == $limit_file_type ) {
    $limit_types = 'text';
    $limit_extensions = 'txt,rtx,csv,tsv';
}
elseif ( 'any' == $limit_file_type ) {
    $limit_types = '';
    $limit_extensions = '*';
}
else {
    $limit_types = $limit_extensions = pods_var( $form_field_type . '_allowed_extensions', $options, '', null, true );
}
$limit_types = trim( str_replace( array( ' ', '.', "\n", "\t", ';' ), array( '', ',', ',', ',' ), $limit_types ), ',' );
$limit_extensions = trim( str_replace( array( ' ', '.', "\n", "\t", ';' ), array( '', ',', ',', ',' ), $limit_extensions ), ',' );

$mime_types = wp_get_mime_types();

if ( !in_array( $limit_file_type, array( 'images', 'video', 'audio', 'text', 'any' ) ) ) {
    $new_limit_types = array();

    $limit_types = explode( ',', $limit_types );

    foreach ( $limit_types as $k => $limit_type ) {
        if ( isset( $mime_types[ $limit_type ] ) ) {
            $mime = explode( '/', $mime_types[ $limit_type ] );
            $mime = $mime[ 0 ];

            if ( !in_array( $mime, $new_limit_types ) )
                $new_limit_types[] = $mime;
        }
        else {
            $found = false;

            foreach ( $mime_types as $type => $mime ) {
                if ( false !== strpos( $type, $limit_type ) ) {
                    $mime = explode( '/', $mime );
                    $mime = $mime[ 0 ];

                    if ( !in_array( $mime, $new_limit_types ) ) {
                        $new_limit_types[] = $mime;
                    }
                    $found = true;
                }
            }

            if ( !$found )
                $new_limit_types[] = $limit_type;
        }
    }

    if ( !empty( $new_limit_types ) )
        $limit_types = implode( ',', $new_limit_types );
}

if ( empty( $value ) )
    $value = array();
else
    $value = (array) $value;
?>
<div<?php PodsForm::attributes( array( 'class' => $attributes[ 'class' ], 'id' => $attributes[ 'id' ] ), $name, $form_field_type, $options ); ?>>
    <ul class="pods-files pods-files-list"><?php // no extra space in ul or CSS:empty won't work
        foreach ( $value as $val ) {
            $attachment = get_post( $val, ARRAY_A );

            if ( empty( $attachment ) ) {
                continue;
            }

            $attachment[ 'filename' ] = basename( $attachment[ 'guid' ] );

            $thumb = wp_get_attachment_image_src( $attachment[ 'ID' ], 'thumbnail', true );
            $attachment[ 'thumbnail' ] = $thumb[ 0 ];

            $attachment[ 'link' ] = '';

            if ( $linked ) {
                $attachment[ 'link' ] = wp_get_attachment_url( $attachment[ 'ID' ] );
            }

            $attachment = apply_filters( 'pods_media_attachment', $attachment );

            echo $field_file->markup( $attributes, $file_limit, $title_editable, $attachment[ 'ID' ], $attachment[ 'thumbnail' ], $attachment[ 'post_title' ], $linked, $attachment[ 'link' ] );
        }
        ?></ul>

    <a class="button pods-file-add pods-media-add" id="<?php echo esc_attr( $css_id ); ?>-upload" href="#" tabindex="2"><?php echo pods_v( $form_field_type . '_add_button', $options, __( 'Add File', 'pods' ) ); ?></a>
</div>

<script type="text/x-handlebars" id="<?php echo esc_attr( $css_id ); ?>-handlebars">
    <?php echo $field_file->markup( $attributes, $file_limit, $title_editable, null, null, null, $linked ); ?>
</script>

<script type="text/javascript">
    jQuery( function( $ ){

        var $element_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?> = $( '#<?php echo esc_js( $css_id ); ?>' ),
            $list_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?> = $( '#<?php echo esc_js( $css_id ); ?> ul.pods-files-list' ),
            title_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?> = "<?php echo esc_js( pods_var_raw( $form_field_type . '_modal_title', $options, __( 'Attach a file', 'pods' ) ) ); ?>",
            button_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?> = "<?php echo esc_js( pods_var_raw( $form_field_type . '_modal_add_button', $options, __( 'Add File', 'pods' ) ) ); ?>",
            pods_media_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>,
            maxFiles_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?> = <?php echo esc_js( $file_limit ); ?>;

        <?php if ( 1 != $file_limit ) { ?>
            // init sortable
            $list_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>.sortable( {
                containment : 'parent',
                axis: 'y',
                scrollSensitivity : 40,
                tolerance : 'pointer',
                opacity : 0.6
            } );
        <?php } ?>

        // hook delete links
        $element_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>.on( 'click', 'li.pods-file-delete a', function ( e ) {
			e.preventDefault();

            var podsfile = $( this ).parent().parent().parent();
            podsfile.slideUp( function () {
                // check to see if this was the only entry
                if ( podsfile.parent().children().length == 1 ) { // 1 because we haven't removed our target yet
                    podsfile.parent().hide();
                }
                // remove the entry
                $(this).remove();
            } );
        } );

        $element_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>.on( 'click', '.pods-file-add', function( event ) {
            var options, attachment;

            event.preventDefault();

	        if(typeof wp.Uploader.defaults.filters.mime_types == 'undefined') {
		        wp.Uploader.defaults.filters.mime_types = [{title:'Allowed Files', extensions: '*'}];
	        }

	        var default_ext = wp.Uploader.defaults.filters.mime_types[0].extensions;

	        wp.Uploader.defaults.filters.mime_types[0].extensions = "<?php echo esc_js( $limit_extensions ); ?>";

            // if the frame already exists, open it
            if ( pods_media_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?> ) {
                pods_media_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>.open();
                pods_media_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>.content.mode('<?php echo esc_js( $router ); ?>');
            }
            else {
                // set our settings
                pods_media_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?> = wp.media({
                    title: title_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>,

                    <?php if( $file_limit !== 1 ) : ?>
                        multiple: true,
                    <?php endif; ?>

                    <?php if ( !empty( $limit_types ) ) : ?>
                        library: {
                            type: '<?php echo esc_js( $limit_types ); ?>'
                        },
                    <?php endif; ?>

                    // Customize the submit button.
                    button: {
                        // Set the text of the button.
                        text: button_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>
                    }
                });

                // set up our select handler
                pods_media_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>.on( 'select', function() {

                    selection = pods_media_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>.state().get( 'selection' );

                    if ( ! selection )
                        return;

                    // compile our Underscore template using Mustache syntax
                    _.templateSettings = {
                        interpolate : /\{\{(.+?)\}\}/g
                    }

                    var template = _.template($('script#<?php echo esc_js( $css_id ); ?>-handlebars').html());

                    // loop through the selected files
                    selection.each( function( attachment ) {
                        // by default use the generic icon
                        attachment_thumbnail = attachment.attributes.icon;

                        // only thumbnails have sizes which is what we're on the hunt for
                        if ( 'undefined' != typeof attachment.attributes.sizes ) {
                            // Get thumbnail if it exists
                            if ( 'undefined' != typeof attachment.attributes.sizes.thumbnail && 'undefined' != typeof attachment.attributes.sizes.thumbnail.url )
                                attachment_thumbnail = attachment.attributes.sizes.thumbnail.url;
                            // If thumbnail doesn't exist, get full because this is a small image
                            else if ( 'undefined' != typeof attachment.attributes.sizes.full && 'undefined' != typeof attachment.attributes.sizes.full.url )
                                attachment_thumbnail = attachment.attributes.sizes.full.url;
                        }

                        <?php if ( !empty( $limit_types ) ) : ?>
                            if ( '<?php echo implode( '\' != attachment.attributes.type && \'', explode( ',', pods_slash( $limit_types ) ) ); ?>' != attachment.attributes.type )
                                return;
                        <?php endif; ?>

                        // set our object properties
                        var binding = {
                            id: attachment.id,
                            icon: attachment_thumbnail,
                            name: attachment.attributes.title,
                            filename: attachment.filename,
                            link: attachment.attributes.url
                        };

                        var tmpl = Handlebars.compile( $( 'script#<?php echo esc_js( $css_id ); ?>-handlebars' ).html() );

                        var html = tmpl( binding );

                        $list_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>.prepend( html );

                        if ( !$list_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>.is( ':visible' ) )
                            $list_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>.show().removeClass( 'hidden' );

                        $list_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>.find( 'li.pods-file:first' ).slideDown( 'fast' );

                        var items = $list_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>.find( 'li.pods-file' ),
                            itemCount = items.size();

                        if ( 0 < maxFiles_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?> && itemCount > maxFiles_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?> ) {
                            items.each( function ( idx, elem ) {
                                if ( idx + 1 > maxFiles_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?> ) {
                                    jQuery( elem ).remove();
                                }
                            } );
                        }
                    });
                });

                // open the frame
                pods_media_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>.open();
                pods_media_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>.content.mode('<?php echo esc_js( $router ); ?>');
            }

            // Reset the allowed file extensions
            wp.Uploader.defaults.filters.mime_types[0].extensions = default_ext;
        });

    });
</script>