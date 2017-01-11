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
$attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );

$css_id = $attributes[ 'id' ];

$uri_hash = wp_create_nonce( 'pods_uri_' . $_SERVER[ 'REQUEST_URI' ] );

$uid = @session_id();

if ( is_user_logged_in() )
    $uid = 'user_' . get_current_user_id();

$field_nonce = wp_create_nonce( 'pods_upload_' . ( !is_object( $pod ) ? '0' : $pod->pod_id ) . '_' . $uid . '_' . $uri_hash . '_' . $options[ 'id' ] );

$file_limit = 1;

if ( 'multi' == pods_var( $form_field_type . '_format_type', $options, 'single' ) )
    $file_limit = (int) pods_var( $form_field_type . '_limit', $options, 0 );

$plupload_init = array(
    'runtimes' => 'html5,silverlight,flash,html4',
    'container' => $css_id,
    'browse_button' => $css_id . '-upload',
    'url' => admin_url( 'admin-ajax.php?pods_ajax=1', 'relative' ),
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
        'pod' => ( !is_object( $pod ) ? '0' : $pod->pod_id ),
        'field' => $options[ 'id' ],
        'uri' => $uri_hash
    ),
);

$limit_file_type = pods_var( $form_field_type . '_type', $options, 'images' );

$title_editable = pods_var( $form_field_type . '_edit_title', $options, 0 );
$linked = pods_var( $form_field_type . '_linked', $options, 0 );

if ( 'images' == $limit_file_type )
    $limit_types = 'jpg,jpeg,png,gif';
elseif ( 'video' == $limit_file_type )
    $limit_types = 'mpg,mov,flv,mp4';
elseif ( 'audio' == $limit_file_type )
    $limit_types = 'mp3,m4a,wav,wma';
elseif ( 'text' == $limit_file_type )
    $limit_types = 'txt,rtx,csv,tsv';
elseif ( 'any' == $limit_file_type )
    $limit_types = '';
else
    $limit_types = pods_var( $form_field_type . '_allowed_extensions', $options, '', null, true );

$limit_types = trim( str_replace( array( ' ', '.', "\n", "\t", ';' ), array( '', ',', ',', ',' ), $limit_types ), ',' );

if ( pods_version_check( 'wp', '3.5' ) ) {
    $mime_types = wp_get_mime_types();

    if ( in_array( $limit_file_type, array( 'images', 'audio', 'video' ) ) ) {
        $new_limit_types = array();

        foreach ( $mime_types as $type => $mime ) {
            if ( 0 === strpos( $mime, $limit_file_type ) ) {
                $type = explode( '|', $type );

                $new_limit_types = array_merge( $new_limit_types, $type );
            }
        }

        if ( !empty( $new_limit_types ) )
            $limit_types = implode( ',', $new_limit_types );
    }
    elseif ( 'any' != $limit_file_type ) {
        $new_limit_types = array();

        $limit_types = explode( ',', $limit_types );

        foreach ( $limit_types as $k => $limit_type ) {
            $found = false;

            foreach ( $mime_types as $type => $mime ) {
                if ( 0 === strpos( $mime, $limit_type ) ) {
                    $type = explode( '|', $type );

                    foreach ( $type as $t ) {
                        if ( !in_array( $t, $new_limit_types ) )
                            $new_limit_types[] = $t;
                    }

                    $found = true;
                }
            }

            if ( !$found )
                $new_limit_types[] = $limit_type;
        }

        if ( !empty( $new_limit_types ) )
            $limit_types = implode( ',', $new_limit_types );
    }
}

if ( !empty( $limit_types ) )
    $plupload_init[ 'filters' ][ 0 ][ 'extensions' ] = $limit_types;

if ( is_admin() && !empty( $post_ID ) )
    $plupload_init[ 'multipart_params' ][ 'post_id' ] = (int) $post_ID;
elseif ( is_object( $pod ) && in_array( $pod->pod_data[ 'type' ], array( 'post_type', 'media' ) ) && 0 < $id )
    $plupload_init[ 'multipart_params' ][ 'post_id' ] = (int) $id;

$plupload_init = apply_filters( 'plupload_init', $plupload_init );

if ( empty( $value ) )
    $value = array();
else
    $value = (array) $value;
?>
<div<?php PodsForm::attributes( array( 'class' => $attributes[ 'class' ], 'id' => $attributes[ 'id' ] ), $name, $form_field_type, $options ); ?>>
    <ul class="pods-files pods-files-list"><?php // no extra space in ul or CSS:empty won't work
        foreach ( $value as $val ) {
            $attachment = get_post( $val );

            if ( empty( $attachment ) )
                continue;

            $thumb = wp_get_attachment_image_src( $val, 'thumbnail', true );

            $title = $attachment->post_title;

            if ( 0 == $title_editable )
                $title = basename( $attachment->guid );

			$link = wp_get_attachment_url( $attachment->ID );

            echo $field_file->markup( $attributes, $file_limit, $title_editable, $val, $thumb[ 0 ], $title, $linked, $link );
        }
        ?></ul>

    <a class="button pods-file-add pods-media-add" id="<?php echo esc_attr( $css_id ); ?>-upload" href="#" tabindex="2"><?php echo pods_v( $form_field_type . '_add_button', $options, __( 'Add File', 'pods' ) ); ?></a>

    <ul class="pods-files pods-files-queue"></ul>
</div>

<script type="text/x-handlebars" id="<?php echo esc_attr( $css_id ); ?>-handlebars">
    <?php echo $field_file->markup( $attributes, $file_limit, $title_editable, null, null, null, $linked ); ?>
</script>

<script type="text/x-handlebars" id="<?php echo esc_attr( $css_id ); ?>-progress-template">
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
        $( '#<?php echo esc_js( $css_id ); ?>' ).on( 'click', 'li.pods-file-delete a', function ( e ) {
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

        var pods_uploader = new plupload.Uploader( <?php echo json_encode( $plupload_init ); ?> ),
            list = $( '#<?php echo esc_js( $css_id ); ?> ul.pods-files-list' ),
            queue = $( '#<?php echo esc_js( $css_id ); ?> ul.pods-files-queue' ),
            maxFiles = <?php echo esc_js( $file_limit ); ?>;

        list.find( 'li.pods-file:first' ).removeClass('hidden');

        pods_uploader.init();

        // Store a reference to this Plupload instance in window.pods_uploaders
        if (!('pods_uploaders' in window)) {
            pods_uploaders = {};
        }
        pods_uploaders['<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>'] = pods_uploader;

        // Plupload FilesAdded Event Handler
        pods_uploader.bind( 'FilesAdded', function ( up, files ) {
            // Hide any existing files (for use in single/limited field configuration)
            if ( 1 == maxFiles ) {
                jQuery( '#<?php echo esc_js( $css_id ); ?> ul.pods-files-list li.pods-file' ).remove();
                jQuery( '#<?php echo esc_js( $css_id ); ?> ul.pods-files-list' ).hide();
            }

            jQuery.each( files, function ( index, file ) {
                var binding = { id : file.id, filename : file.name },
                    tmpl = Handlebars.compile( $( '#<?php echo esc_js( $css_id ); ?>-progress-template' ).html() ),
                    html = tmpl( binding );

                queue.append( html );
                //$('#' + file.id).show('slide', {direction: 'up'}, 1000);
                $( '#' + file.id ).fadeIn( 800 );

                jQuery( '#<?php echo esc_js( $css_id ); ?> ul.pods-files-queue' ).show();
            } );

            up.refresh();
            up.start();
        } );

        // Plupload UploadProgress Event Handler
        pods_uploader.bind( 'UploadProgress', function ( up, file ) {
            var prog_bar = $( '#' + file.id ).find( '.progress-bar' );

            prog_bar.css( 'width', file.percent + '%' );
        } );

        // Plupload FileUploaded Event Handler
        pods_uploader.bind( 'FileUploaded', function ( up, file, resp ) {
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
                var json = response.match( /{.*}$/ );

                if ( null !== json && 0 < json.length )
                    json = jQuery.parseJSON( json[ 0 ] );
                else
                    json = {};

                if ( 'object' != typeof json || jQuery.isEmptyObject( json ) ) {
                    if ( window.console ) console.log( response );
                    if ( window.console ) console.log( json );
                    file_div.append( '<?php echo esc_js( __( 'There was an issue with the file upload, please try again.', 'pods' ) ); ?>' );
                    return;
                }

                file_div.fadeOut( 800, function () {
                    list.show();

                    if ( $( this ).parent().children().length == 1 )
                        jQuery( '#<?php echo esc_js( $css_id ); ?> ul.pods-files-queue' ).hide();

                    $( this ).remove();
                } );

                var binding = {
                    id : json.ID,
                    icon : json.thumbnail,
                    name : json.post_title,
                    link : json.link
                };

                var tmpl = Handlebars.compile( $( 'script#<?php echo esc_js( $css_id ); ?>-handlebars' ).html() );

                var html = tmpl( binding );

                list.prepend( html );
                list.find( 'li.pods-file:first' ).hide().removeClass('hidden').slideDown( 'fast' );

                var items = list.find( 'li.pods-file' ),
                    itemCount = items.length;

                if ( 0 < maxFiles && itemCount > maxFiles ) {
                    items.each( function ( idx, elem ) {
                        if ( idx + 1 > maxFiles ) {
                            jQuery( elem ).remove();
                        }
                    } );
                }
            }
        } );
    } );
</script>
