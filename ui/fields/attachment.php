<?php
    wp_enqueue_script( 'pods-handlebars' );
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-sortable' );
    wp_enqueue_script( 'thickbox' );
    wp_enqueue_script( 'pods-attach' );

    wp_enqueue_style( 'thickbox' );
    wp_enqueue_style( 'pods-attach' );

    $field_file = PodsForm::field_loader( 'file' );

    $attributes = array();
    $attributes[ 'value' ] = $value;
    $attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );

    $css_id = $attributes[ 'id' ];

    $file_limit = 1;

    if ( 'multi' == pods_var( 'file_format_type', $options, 'single' ) )
        $file_limit = (int) pods_var( 'file_limit', $options, 0 );

    if ( empty( $value ) )
        $value = array();
    else
        $value = (array) $value;
?>
    <table class="form-table pods-metabox" id="<?php echo $css_id; ?>">
        <tbody>
            <tr class="form-field">
                <td>
                    <ul class="pods-files pods-files-list"><?php // no extra space in ul or CSS:empty won't work
                            foreach ( $value as $val ) {
                                $thumb = wp_get_attachment_image_src( $val[ 'id' ], 'thumbnail', true );
                                echo $field_file->markup( $attributes, $file_limit, pods_var( 'file_edit_title', $options, 0 ), $val[ 'ID' ], $thumb[ 0 ], basename( $val[ 'guid' ] ) );
                            }
                        ?></ul>

                    <a class="button pods-file-add" href="media-upload.php?TB_iframe=1&amp;width=640&amp;height=1500"><?php _e('Add File', 'pods'); ?></a>
                </td>
            </tr>
        </tbody>
    </table>

    <script type="text/x-handlebars" id="<?php echo $css_id; ?>-handlebars">
        <?php echo $field_file->markup( $attributes, $file_limit, pods_var( 'file_edit_title', $options, 0 ) ); ?>
    </script>

    <script type="text/javascript">
        jQuery( function ( $ ) {
            // init sortable
            $( '#<?php echo esc_js( $css_id ); ?> ul.pods-files' ).sortable( {
                containment : 'parent',
                axis : 'y',
                scrollSensitivity : 40,
                tolerance : 'pointer',
                opacity : 0.6
            } );

            // hook delete links
            $( '#<?php echo esc_js( $css_id ); ?>' ).on( 'click', 'li.pods-file-delete', function () {
                var podsfile = $( this ).parent().parent();
                podsfile.slideUp( function () {

                    // check to see if this was the only entry
                    if ( podsfile.parent().children().length == 1 ) { // 1 because we haven't removed our target yet
                        podsfile.parent().hide();
                    }

                    // remove the entry
                    podsfile.remove();
                } );
            } );

            var maxFiles_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?> = <?php echo esc_js( $file_limit ); ?>;

            // hook the add link
            $( '#<?php echo esc_js( $css_id ); ?>' ).on( 'click', 'a.pods-file-add', function ( e ) {
                e.preventDefault();
                var trigger = $( this );
                var href = trigger.attr( 'href' ), width = $( window ).width(), H = $( window ).height(), W = ( 720 < width ) ? 720 : width;
                if ( !href ) return;
                href = href.replace( /&width=[0-9]+/g, '' );
                href = href.replace( /&height=[0-9]+/g, '' );
                trigger.attr( 'href', href + '&width=' + ( W - 80 ) + '&height=' + ( H - 85 ) );

                pods_file_context = trigger.parent().find( 'ul.pods-files' );
                pods_file_thickbox_modder = setInterval( function () {
                    if ( pods_file_context )
                        pods_attachments( '<?php echo esc_js( $css_id ); ?>', maxFiles_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?> );
                }, 500 );

                tb_show( 'Attach a file', e.target.href, false );
                return false;
            } );
        } );
    </script>
