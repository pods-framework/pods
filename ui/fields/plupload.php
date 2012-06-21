<?php
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'plupload-all' );
    wp_enqueue_script( 'handlebars', PODS_URL . 'ui/js/handlebars-1.0.0.beta.6.js' );

    wp_enqueue_style( 'pods-file-browser', PODS_URL . 'ui/css/pods-file-browser.css' );

    $field_file = PodsForm::field_loader( 'file' );

    $attributes = array();
    $attributes[ 'value' ] = $value;
    $attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );

    $css_id = $attributes[ 'id' ];

    $file_limit = 1;
    if ( isset( $options[ 'file_limit' ] ) && 'multiple' == $options[ 'file_format_type' ] )
        $file_limit = (int) $options[ 'file_limit' ];

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
            '_wpnonce' => wp_create_nonce( 'pods-upload-' . $attributes[ 'id' ] ),
            'action' => 'pods_upload',
            'method' => 'upload',
            'id' => $attributes[ 'id' ]
        ),
    );
    $plupload_init = apply_filters( 'plupload_init', $plupload_init );

    $value = (array) $value;
?>
    <table class="form-table pods-metabox" id="<?php echo $css_id; ?>">
        <tbody>
            <tr class="form-field">
                <td>
                    <ul class="pods-files">
                        <?php
                            foreach ( $value as $val ) {
                                $thumb = wp_get_attachment_image_src( $val[ 'id' ], 'thumbnail', true );
                                echo $field_file->markup( $attributes, $file_limit, $val[ 'ID' ], $thumb[ 0 ], basename( $val[ 'guid' ] ) );
                            }
                        ?>
                    </ul>

                    <a class="button pods-file-add plupload-add" id="<?php echo $css_id; ?>-upload" href="">Add File</a>

                    <table class="plupload-queue">
                        <tbody>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    <script type="text/x-handlebars" id="<?php echo $css_id; ?>-handlebars">
        <?php echo $field_file->markup( $attributes, $file_limit ); ?>
    </script>

    <script type="text/x-handlebars" id="<?php echo $css_id; ?>-progress-template">
        <tr class="hide" id="{{id}}">
            <td>{{filename}}</td>
            <td class="progress" width="175">
                <div class="progress-bar">&nbsp;</div>
            </td>
        </tr>
    </script>

    <script>
        jQuery( function ( $ ) {
            // init sortable
            $( '#<?php echo esc_js( $css_id ); ?> ul.pods-files' ).sortable( {
                containment : 'parent',
                axis: 'y',
                scrollSensitivity : 40,
                tolerance : 'pointer',
                opacity : 0.6
            } )

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

            var pods_uploader = new plupload.Uploader( <?php echo json_encode( $plupload_init ); ?> ),
                list = $( '#<?php echo esc_js( $css_id ); ?> ul.pods-files' ),
                queue = $( '#<?php echo esc_js( $css_id ); ?> table.plupload-queue' ),
                maxFiles = <?php echo esc_js( $file_limit ); ?>;

            pods_uploader.init();

            // Plupload FilesAdded Event Handler
            pods_uploader.bind( 'FilesAdded', function ( up, files ) {
                // Hide any existing files (for use in single/limited field configuration)
                if ( 1 == maxFiles )
                    jQuery( '#<?php echo $css_id; ?> ul.pods-files li.pods-file' ).hide();

                jQuery.each( files, function ( index, file ) {
                    var binding = { id: file.id, filename: file.name },
                        tmpl = Handlebars.compile( $('#<?php echo esc_js( $css_id ); ?>-progress-template').html() ),
                        html = tmpl( binding );
                    queue.append( html );
                    //$('#' + file.id).show('slide', {direction: 'up'}, 1000);
                    $('#' + file.id).fadeIn(800);
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
                    file_div.append( response );
                }
                else if ( "<e>" == resp.response.substr( 0, 3 ) ) {
                    response = response.substr( 3 );
                    file_div.append( response );
                }
                else {

                    file_div.fadeOut(800, function() {
                        $(this).remove();
                    });
                    var json = eval( '(' + response.match( /\{(.*)\}/gi ) + ')' );

                    var binding = {
                        id : json.ID,
                        icon : json.thumbnail,
                        name : json.filename
                    };

                    var tmpl = Handlebars.compile( $( 'script#<?php echo esc_js( $css_id ); ?>-handlebars' ).html() );

                    var html = tmpl( binding );

                    list.prepend( html );

                    var items = list.find( 'li.pods-file' ),
                        itemCount = items.size();

                    $.fn.reverse = [].reverse;
                    if ( 0 < maxFiles || itemCount > maxFiles ) {
                        var reversed = items;

                        reversed.each( function ( idx, elem ) {
                            if ( idx + 1 > maxFiles ) {
                                jQuery( elem ).remove();
                            }
                        } );
                    }
                }
            } );
        } );
    </script>
