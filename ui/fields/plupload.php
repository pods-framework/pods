<?php
    $attributes = array();
    $attributes['value'] = $value;
    $attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );
    $css_id = $attributes['id'];
    $field_name = $attributes['name'];
    $file_limit = isset($options['file_limit']) ? (int) $options['file_limit'] : 1;
    $plupload_init = array(
        'runtimes' => 'html5,silverlight,flash,html4',
        'browse_button' => $css_id . '-browse',
        'url' => PODS_URL . 'ui/admin/misc.php',
        'file_data_name' => 'Filedata',
        'multiple_queues' => false,
        'max_file_size' => wp_max_upload_size().'b',
        'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
        'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
        'filters' => array(array('title' => __('Allowed Files', 'pods'), 'extensions' => '*')),
        'multipart' => true,
        'urlstream_upload' => true,
        'multipart_params' => array(
            '_wpnonce' => wp_create_nonce('pods-wp_handle_upload_advanced'),
            'action' => 'wp_handle_upload_advanced',
            'method' => 'upload_file',
            'pods_ajax' => 1,
        ),
    );
    $plupload_init = apply_filters('plupload_init', $plupload_init);

    if ( !wp_script_is( 'plupload-all', 'queue' ) && !wp_script_is( 'plupload-all', 'to_do' ) && !wp_script_is( 'plupload-all', 'done' ) )
        wp_print_scripts( 'plupload-all' );
?>
    <ul class="pods-files" id="<?php echo $css_id; ?>-files">
        <?php foreach($value as $val): ?>
            <?php
            $filepath = $file[ 'guid' ];
            $filename = substr($filepath, strrpos($filepath, '/') + 1);
            ?>
            <li>
                <span class="pods-file-reorder">
                    <img src="<?php echo PODS_URL . 'ui/images/handle.gif'; ?>" alt="drag to reorder" />
                </span>

                <span class="pods-file-thumb">
                    <span>
                        <img class="pinkynail" src="<?php echo $val['guid']; ?>" alt="thumbnail" />
                    </span>
                </span>

                <span class="pods-file-name"><?php echo $filename; ?></span>

                <span class="pods-file-remove">
                    <img src="<?php echo PODS_URL . 'ui/images/del.png'; ?>" alt="remove" class="pods-icon-minus" />
                </span>

                <input type="hidden" name="<?php echo $field_name; ?>[]" value="<?php echo $val['ID']; ?>" />
            </li>
        <?php endforeach; ?>
    </ul>

    <p><a href="" class="plupload-add button" id="<?php echo $css_id; ?>-browse">Add New</a></p>
    <p class="plupload-queue" id="<?php echo $css_id; ?>-queue"></p>

    <script>
        jQuery( function ($) {
            var pods_uploader = new plupload.Uploader(<?php echo json_encode($plupload_init); ?>);

            pods_uploader.init();

            // Plupload FilesAdded Event Handler
            pods_uploader.bind( 'FilesAdded', function ( up, files ) {
                // Hide any existing files (for use in single/limited field configuration)
                // jQuery('.pods_field_<?php echo $name; ?> .success').hide();
                var list = $('#<?php echo esc_js( $css_id ); ?>-files'),
                    queue = $('#<?php echo esc_js( $css_id ); ?>-queue'),
                    maxFiles = <?php echo esc_js( $file_limit ); ?>;

                jQuery.each( files, function ( index, file ) {
                    var prog_container = $('<div/>', {
                            'class': 'plupload-progress',
                            'id': file.id
                        }),
                        prog_name = $('<span/>', {
                            'class': 'file-name',
                            text: file.name
                        }),
                        prog_bar = $('<span/>', {
                            'class': 'progress-bar',
                            css: {
                                width: '0'
                            }
                        });
                    prog_container.append(prog_name).append(prog_bar).appendTo(queue);
                } );

                up.refresh();
                up.start();
            } );

            // Plupload UploadProgress Event Handler
            pods_uploader.bind( 'UploadProgress', function ( up, file ) {
                var prog_bar = $('#' + file.id).find('.progress-bar');
                prog_bar.css('width', file.percent + '%');
            } );

            // Plupload FileUploaded Event Handler
            pods_uploader.bind( 'FileUploaded', function ( up, file, resp ) {
                var file_div = jQuery( '#' + file.id );
                var file_limit = <?php echo $file_limit; ?>;
                file_div.remove();

                if ( "Error" == resp.response.substr( 0, 5 ) ) {
                    var response = resp.response.substr( 7 );
                    file_div.append( response );
                }
                else if ( "<e>" == resp.response.substr( 0, 3 ) ) {
                    var response = resp.response;
                    file_div.append( resp.response );
                }
                else {
                    $.fn.reverse = [].reverse;
                    var json = eval( '(' + resp.response.match( /\{(.*)\}/gi ) + ')' ),
                        response = json,
                        sort_array = $('#<?php echo esc_js( $css_id ); ?>-files'),
                        maxFiles = <?php echo esc_js( $file_limit ); ?>;

                    sort_array.append('<li><span class="pods-file-reorder"><img src="' + PODS_URL + 'ui/images/handle.gif" alt="reorder"/></span><span class="pods-file-thumb"><span><img class="pinkynail" src="' + json.guid + '" /></span><input type="hidden" name="file3[]" value="' + json.ID + '" /></span><span class="pods-file-name">' + file.name + '</span><span class="pods-file-remove"><img src="' + PODS_URL + 'ui/images/del.png"/></span><input type="hidden" name="<?php echo $field_name; ?>[]" value="' + json.ID + '" />');

                    var items = sort_array.find('li'), itemCount = items.size();

                    if (maxFiles < 1 || itemCount > maxFiles) {
                        var reversed = items.reverse();

                        reversed.each(function(idx, elem) {
                            if (0 < maxFiles && idx + 1 > maxFiles) {
                                jQuery(elem).remove();
                            }
                        });
                    }
                }
            } );
        } );
    </script>