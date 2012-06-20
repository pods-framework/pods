<?php
    $field_file = PodsForm::field_loader( 'file' );

    $attributes = array();
    $attributes[ 'value' ] = $value;
    $attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );

    $css_id = $attributes[ 'id' ];

    $file_limit = ( isset( $options[ 'file_limit' ] ) ? (int) $options[ 'file_limit' ] : 1 );

    $plupload_init = array(
        'runtimes' => 'html5,silverlight,flash,html4',
        'browse_button' => $css_id . '-browse',
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

    if ( !wp_script_is( 'plupload-all', 'queue' ) && !wp_script_is( 'plupload-all', 'to_do' ) && !wp_script_is( 'plupload-all', 'done' ) )
        wp_print_scripts( 'plupload-all' );

    $js_row = str_replace(
        array(
            "'",
            '{{id}}',
            '{{icon}}',
            '{{name}}'
        ),
        array(
            "\\'",
            "' + json.ID + '",
            "' + json.thumbnail + '",
            "' + json.filename + '"
        ),
        $field_file->markup( $attributes )
    );
?>
    <table class="form-table pods-metabox">
        <tbody>
            <tr class="form-field">
                <th scope="row" valign="top">
                    <label class="pods-form-ui-label-pods-meta-files">Files</label>
                </th>
                <td>
                    <ul class="pods-files">
                        <?php
                            foreach ( $value as $val ) {
                                $field_file->markup( $attributes, $val[ 'ID' ], $val[ 'guid' ], $val[ 'post_title' ] );
                            }
                        ?>
                    </ul>

                    <a class="button pods-file-add plupload-add" id="<?php echo $css_id; ?>-browse" href="">Add File</a>

                    <p class="plupload-queue" id="<?php echo $css_id; ?>-queue"></p>
                </td>
            </tr>
        </tbody>
    </table>

    <script type="text/x-handlebars" id="<?php echo $css_id; ?>-js-row">
        <?php echo $field_file->markup( $attributes ); ?>
    </script>
    <script>
        jQuery( function ( $ ) {
            var pods_uploader = new plupload.Uploader(<?php echo json_encode( $plupload_init ); ?>),
                list = $( '#<?php echo esc_js( $css_id ); ?>-files' ),
                queue = $( '#<?php echo esc_js( $css_id ); ?>-queue' ),
                maxFiles = <?php echo esc_js( $file_limit ); ?>;

            pods_uploader.init();

            // Plupload FilesAdded Event Handler
            pods_uploader.bind( 'FilesAdded', function ( up, files ) {
                // Hide any existing files (for use in single/limited field configuration)
                if ( 1 == maxFiles )
                    jQuery( '.pods_field_<?php echo $name; ?> .success' ).hide();

                jQuery.each( files, function ( index, file ) {
                    var prog_container = $( '<div/>', {
                            'class' : 'plupload-progress',
                            'id' : file.id
                        } ),
                        prog_name = $( '<span/>', {
                            'class' : 'file-name',
                            text : file.name
                        } ),
                        prog_bar = $( '<span/>', {
                            'class' : 'progress-bar',
                            css : {
                                width : '0'
                            }
                        } );
                    prog_container.append( prog_name ).append( prog_bar ).appendTo( queue );
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
                    file_div.remove();

                    $.fn.reverse = [].reverse;

                    var json = eval( '(' + response.match( /\{(.*)\}/gi ) + ')' );
                    var binding = {
                        id: json.ID,
                        icon: json.thumbnail,
                        name: json.filename
                    };
                    var tmpl = Handlebars.compile($('<?php echo esc_js( $css_id ); ?>-js-row').html());
                    var html = tmpl(binding);
                    console.log(html);

                    list.append( html );

                    var items = list.find( 'li' ),
                        itemCount = items.size();

                    if ( 0 < maxFiles || itemCount > maxFiles ) {
                        var reversed = items.reverse();

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
