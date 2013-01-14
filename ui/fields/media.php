<?php
global $post_ID;

wp_enqueue_script( 'pods-handlebars' );
wp_enqueue_script( 'jquery-ui-core' );
wp_enqueue_script( 'jquery-ui-sortable' );
wp_enqueue_style( 'pods-attach' );

wp_enqueue_media( array( 'post' => $post_ID ) );

$field_file = PodsForm::field_loader( 'file' );

$attributes = array();
$attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );

$css_id = $attributes[ 'id' ];

$file_limit = 1;

// @todo: File limit not yet supported in the UI, it's either single or multiple
if ( 'multi' == pods_var( PodsForm::$field_type . '_format_type', $options, 'single' ) )
    $file_limit = (int) pods_var( PodsForm::$field_type . '_limit', $options, 0 );

$limit_file_type = pods_var( PodsForm::$field_type . '_type', $options, 'images' );

if ( 'images' == $limit_file_type )
    $limit_types = 'image';
elseif ( 'video' == $limit_file_type )
    $limit_types = 'video';
elseif ( 'audio' == $limit_file_type )
    $limit_types = 'audio';
elseif ( 'text' == $limit_file_type )
    $limit_types = 'text';
elseif ( 'any' == $limit_file_type )
    $limit_types = '';
else
    $limit_types = str_replace( ' ', '', pods_var( PodsForm::$field_type . '_allowed_extensions', $options, '', null, true ) );

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

                    if ( !in_array( $mime, $new_limit_types ) )
                        $new_limit_types[] = $mime;

                    $found = true;
                }
            }

            if ( !$found )
                $new_limit_types[] = $limit_type;
        }
    }

    if ( !empty( $new_limit_types ) )
        $limit_types = implode( ', ', $new_limit_types );
}


if ( empty( $value ) )
    $value = array();
else
    $value = (array) $value;
?>
<div<?php PodsForm::attributes( array( 'class' => $attributes[ 'class' ] ), $name, PodsForm::$field_type, $options ); ?>>
    <table class="form-table pods-metabox" id="<?php echo $css_id; ?>">
        <tbody>
            <tr class="form-field">
                <td>
                    <ul class="pods-files pods-files-list"><?php // no extra space in ul or CSS:empty won't work
                        foreach ( $value as $val ) {
                            $attachment = get_post( $val );

                            if ( empty( $attachment ) )
                                continue;

                            $thumb = wp_get_attachment_image_src( $val, 'thumbnail', true );

                            $title = $attachment->post_title;

                            if ( 0 == pods_var( PodsForm::$field_type . '_edit_title', $options, 0 ) )
                                $title = basename( $attachment->guid );

                            echo $field_file->markup( $attributes, $file_limit, pods_var( PodsForm::$field_type . '_edit_title', $options, 0 ), $val, $thumb[ 0 ], $title );
                        }
                        ?></ul>

                    <a class="button pods-file-add pods-media-add" id="<?php echo $css_id; ?>-upload" href="#" tabindex="2"><?php echo pods_var_raw( PodsForm::$field_type . '_add_button', $options, __( 'Add File', 'pods' ) ); ?></a>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script type="text/x-handlebars" id="<?php echo $css_id; ?>-handlebars">
    <?php echo $field_file->markup( $attributes, $file_limit, pods_var( PodsForm::$field_type . '_edit_title', $options, 0 ) ); ?>
</script>

<script type="text/javascript">
    jQuery(document).ready(function($){

        <?php if ( 1 != $file_limit ) { ?>
            // init sortable
            $( '#<?php echo esc_js( $css_id ); ?> ul.pods-files-list' ).sortable( {
                containment : 'parent',
                axis: 'y',
                scrollSensitivity : 40,
                tolerance : 'pointer',
                opacity : 0.6
            } );
        <?php } ?>

        // hook delete links
        $( '#<?php echo esc_js( $css_id ); ?>' ).on( 'click', 'li.pods-file-delete', function () {
            var podsfile = $( this ).parent().parent();
            podsfile.slideUp( function () {
                // check to see if this was the only entry
                if ( podsfile.parent().children().length == 1 ) { // 1 because we haven't removed our target yet
                    podsfile.parent().hide();
                }
                // remove the entry
                $(this).remove();
            } );
        } );


        // set up our modal
        var $element_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?> = $('#<?php echo $css_id; ?>'),
            title_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?> = "<?php echo esc_js( pods_var_raw( PodsForm::$field_type . '_modal_title', $options, __( 'Attach a file', 'pods' ) ) ); ?>",
            button_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?> = "<?php echo esc_js( pods_var_raw( PodsForm::$field_type . '_modal_add_button', $options, __( 'Add File', 'pods' ) ) ); ?>",
            list_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?> = $( '#<?php echo esc_js( $css_id ); ?> ul.pods-files-list' ),
            frame_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?>;

        $element_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?>.on( 'click', '.pods-file-add', function( event ) {
            var options, attachment;

            event.preventDefault();

            // if the frame already exists, open it
            if ( frame_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?> ) {
                frame_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?>.open();
                return;
            }

            // set our seetings
            frame_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?> = wp.media({
                title: title_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?>,

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
                    text: button_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?>
                }
            });

            // set up our select handler
            frame_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?>.on( 'select', function() {

                selection = frame_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?>.state().get('selection');

                if ( ! selection )
                    return;

                // compile our Underscore template using Mustache syntax
                _.templateSettings = {
                    interpolate : /\{\{(.+?)\}\}/g
                }

                var template = _.template($('script#<?php echo $css_id; ?>-handlebars').html());

                // loop through the selected files
                selection.each( function( attachment ) {

                    <?php if( $file_limit === 1 ) : ?>
                        jQuery( '#<?php echo $css_id; ?> ul.pods-files-list li.pods-file' ).remove();
                    <?php endif; ?>

                    // by default use the generic icon
                    attachment_thumbnail = attachment.attributes.icon;

                    // only thumbnails have sizes which is what we're on the hunt for
                    if(typeof attachment.attributes.sizes !== 'undefined'){
                        if(typeof attachment.attributes.sizes.thumbnail !== 'undefined'){
                            if(typeof attachment.attributes.sizes.thumbnail.url !== 'undefined'){
                                // use the thumbnail
                                attachment_thumbnail = attachment.attributes.sizes.thumbnail.url;
                            }
                        }
                    }

                    // set our object properties
                    var binding = {
                        id: attachment.id,
                        icon: attachment_thumbnail,
                        name: attachment.attributes.title,
                        filename: attachment.filename
                    };

                    var tmpl = Handlebars.compile( $( 'script#<?php echo esc_js( $css_id ); ?>-handlebars' ).html() );

                    var html = tmpl( binding );

                    list_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?>.prepend( html );
                    list_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?>.find( 'li.pods-file:first' ).slideDown( 'fast' );

                    var items = list_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?>.find( 'li.pods-file' ),
                        itemCount = items.size();

                });
            });

            // open the frame
            frame_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?>.open();

        });

    });
</script>