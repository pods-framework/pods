<?php
global $post_ID;

wp_enqueue_script( 'pods-handlebars' );
wp_enqueue_script( 'jquery-ui-core' );
wp_enqueue_script( 'jquery-ui-sortable' );
wp_enqueue_script( 'plupload-all' );
wp_enqueue_script( 'pods-attach' );

wp_enqueue_style( 'pods-attach' );

$field_file = PodsForm::field_loader( 'file' );

$attributes = array();
$attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );

$css_id = $attributes[ 'id' ];

$uri_hash = wp_create_nonce( 'pods_uri_' . $_SERVER[ 'REQUEST_URI' ] );

$uid = @session_id();

if ( is_user_logged_in() )
    $uid = 'user_' . get_current_user_id();

$field_nonce = wp_create_nonce( 'pods_upload_' . ( !is_object( $pod ) ? '0' : $pod->pod_id ) . '_' . $uid . '_' . $uri_hash . '_' . $options[ 'id' ] );

$file_limit = 1;

if ( 'multi' == pods_var( PodsForm::$field_type . '_format_type', $options, 'single' ) )
    $file_limit = (int) pods_var( PodsForm::$field_type . '_limit', $options, 0 );

$plupload_init = array(
    'runtimes' => 'html5,silverlight,flash,html4',
    'browse_button' => $css_id . '-upload',
    'url' => admin_url( 'admin-ajax.php', 'relative' ) . '?pods_ajax=1',
    'file_data_name' => 'Filedata',
    'multiple_queues' => false,
    'max_file_size' => wp_max_upload_size() . 'b',
    'flash_swf_url' => includes_url( 'js/plupload/plupload.flash.swf' ),
    'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
    'filters' => array( array( 'title' => __( 'Allowed Files', 'pods' ), 'extensions' => '*' ) ),
    'multipart' => true,
    'urlstream_upload' => true,
    'multipart_params' => array(
        '_wpnonce' => $field_nonce,
        'action' => 'pods_upload',
        'method' => 'upload',
        'pod' => $pod->pod_id,
        'field' => $options[ 'id' ],
        'uri' => $uri_hash
    ),
);

if ( is_admin() && false !== strpos( $_SERVER[ 'REQUEST_URI' ], '/post.php' ) && 0 < pods_var( 'post' ) && 'edit' == pods_var( 'action' ) )
    $plupload_init[ 'multipart_params' ][ 'post_id' ] = (int) pods_var( 'post' );
elseif ( is_admin() && false !== strpos( $_SERVER[ 'REQUEST_URI' ], '/post.php' ) && 0 < $post_ID )
    $plupload_init[ 'multipart_params' ][ 'post_id' ] = (int) $post_ID;

$plupload_init = apply_filters( 'plupload_init', $plupload_init );

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

                    <a class="button pods-file-add plupload-add" id="<?php echo $css_id; ?>-upload" href="" tabindex="2"><?php _e( 'Add File', 'pods' ); ?></a>

                    <ul class="pods-files pods-files-queue"></ul>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script type="text/x-handlebars" id="<?php echo $css_id; ?>-handlebars">
    <?php echo $field_file->markup( $attributes, $file_limit, pods_var( PodsForm::$field_type . '_edit_title', $options, 0 ) ); ?>
</script>

<script type="text/x-handlebars" id="<?php echo $css_id; ?>-progress-template">
    <li class="pods-file" id="{{id}}">
        <ul class="pods-file-meta media-item">
            <li class="pods-file-col pods-progress">
                <div class="progress-bar">&nbsp;</div>
            </li>
            <li class="pods-file-col pods-file-name">{{filename}}</li>
        </ul>
    </li>
</script>

<script>
    jQuery( function ( $ ) {

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

        var pods_uploader_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?> = new plupload.Uploader( <?php echo json_encode( $plupload_init ); ?> ),
            list_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?> = $( '#<?php echo esc_js( $css_id ); ?> ul.pods-files-list' ),
            queue_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?> = $( '#<?php echo esc_js( $css_id ); ?> ul.pods-files-queue' ),
            maxFiles_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?> = <?php echo esc_js( $file_limit ); ?>;

        pods_uploader_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?>.init();

        // Plupload FilesAdded Event Handler
        pods_uploader_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?>.bind( 'FilesAdded', function ( up, files ) {
            // Hide any existing files (for use in single/limited field configuration)
            if ( 1 == maxFiles_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?> ) {
                jQuery( '#<?php echo $css_id; ?> ul.pods-files-list li.pods-file' ).remove();
                jQuery( '#<?php echo $css_id; ?> ul.pods-files-list' ).hide();
            }

            jQuery.each( files, function ( index, file ) {
                var binding = { id : file.id, filename : file.name },
                    tmpl = Handlebars.compile( $( '#<?php echo esc_js( $css_id ); ?>-progress-template' ).html() ),
                    html = tmpl( binding );

                queue_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?>.append( html );
                //$('#' + file.id).show('slide', {direction: 'up'}, 1000);
                $( '#' + file.id ).fadeIn( 800 );

                jQuery( '#<?php echo $css_id; ?> ul.pods-files-queue' ).show();
            } );

            up.refresh();
            up.start();
        } );

        // Plupload UploadProgress Event Handler
        pods_uploader_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?>.bind( 'UploadProgress', function ( up, file ) {
            var prog_bar = $( '#' + file.id ).find( '.progress-bar' );

            prog_bar.css( 'width', file.percent + '%' );
        } );

        // Plupload FileUploaded Event Handler
        pods_uploader_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?>.bind( 'FileUploaded', function ( up, file, resp ) {
            var file_div = jQuery( '#' + file.id ),
                response = resp.response;

            if ( "Error: " == resp.response.substr( 0, 7 ) ) {
                response = response.substr( 7 );
                if ( window.console ) console.log( response );
                file_div.append( response );
            }
            else if ( "<e>" == resp.response.substr( 0, 3 ) ) {
                response = response.substr( 3 );
                if ( window.console ) console.log( response );
                file_div.append( response );
            }
            else {
                var json = response.match( /\{(.*)\}/gi );

                if ( json[ 0 ] ) {
                    json = jQuery.parseJSON( json[ 0 ] );
                }

                if ( 'object' != typeof json || jQuery.isEmptyObject( json ) ) {
                    if ( window.console ) console.log( response );
                    if ( window.console ) console.log( json );
                    file_div.append( '<?php echo esc_js( __( 'There was an issue with the file upload, please try again.', 'pods' ) ); ?>' );
                    return;
                }

                file_div.fadeOut( 800, function () {
                    list_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?>.show();

                    if ( $( this ).parent().children().length == 1 )
                        jQuery( '#<?php echo $css_id; ?> ul.pods-files-queue' ).hide();

                    $( this ).remove();
                } );

                var binding = {
                    id : json.ID,
                    icon : json.thumbnail,
                    name : json.post_title
                };

                var tmpl = Handlebars.compile( $( 'script#<?php echo esc_js( $css_id ); ?>-handlebars' ).html() );

                var html = tmpl( binding );

                list_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?>.prepend( html );
                list_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?>.find( 'li.pods-file:first' ).slideDown( 'fast' );

                var items = list_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?>.find( 'li.pods-file' ),
                    itemCount = items.size();

                if ( 0 < maxFiles_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?> && itemCount > maxFiles_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?> ) {
                    items.each( function ( idx, elem ) {
                        if ( idx + 1 > maxFiles_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?> ) {
                            jQuery( elem ).remove();
                        }
                    } );
                }
            }
        } );
    } );
</script>
