<?php
    $attributes = array();
    $attributes['value'] = $value;
    $attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );
    $css_id = $attributes['id'];
    $file_limit = isset($options['file_limit']) ? $options['file_limit'] : 1000;
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

// If this is a multi-file sortable queue...
if ('multiple' === $options['file_format_type']):
?>
<ul class="pods-files" id="<?php echo $css_id; ?>-files">
    <?php foreach($value as $val): ?>
        <li>
            <?php print_r($val); ?>
        </li>
    <?php endforeach; ?>
</ul>
<p>
    <a href="" class="plupload-add button" id="<?php echo $css_id; ?>-browse">Add New</a>
</p>
<p class="plupload-queue" id="<?php echo $css_id; ?>-queue">
</p>
<?php
?>
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

                sort_array.append('<li><span class="pods-file-reorder"><img src="' + PODS_URL + 'ui/images/handle.gif" alt="reorder"/></span><span class="pods-file-thumb"><span><img class="pinkynail" src="' + json.guid + '" /></span><input type="hidden" name="file3[]" value="' + json.ID + '" /></span><span class="pods-file-name">' + file.name + '</span><span class="pods-file-remove"><img src="' + PODS_URL + 'ui/images/del.png"/></span>');

                var items = sort_array.find('li'), itemCount = items.size();
                
                if (itemCount > maxFiles) {
                    var reversed = items.reverse();

                    reversed.each(function(idx, elem) {
                        if (idx + 1 > maxFiles) {
                            jQuery(elem).remove();
                        }
                    });
                }
            }
        } );
    } );
</script>
<?php else: ?>
    <?php $file_limit = 1; ?>
    <a class="button plupload-add" id="<?php echo $css_id; ?>-browse" href="">Select + Upload</a>
    <ul class="pods-inline-files" id="<?php echo $css_id; ?>-inline-files">
    </ul>

    <script type="text/javascript">
    jQuery(function($) {
        var pods_uploader = new plupload.Uploader(<?php echo json_encode( $plupload_init ); ?>);
        pods_uploader.init();

        pods_uploader.bind('FilesAdded', function(up, files) {
            var queue = $('#<?php echo esc_js( $css_id ); ?>-inline-files'),
                items = queue.find('li'),
                itemCount = items.size(),
                fileCount = files.length,
                maxFiles = <?php echo esc_js( $file_limit ); ?>;

            $.each(files, function(idx, file) {
                var list_item = $('<li/>'),
                    prog_wrap = $('<div/>', {
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
                            width: '0%'
                        }
                    });
                prog_wrap.append(prog_name).append(prog_bar).appendTo(list_item);
                list_item.appendTo(queue);
            });

            if (itemCount + fileCount > maxFiles) {
                $.fn.reverse = [].reverse;
                var reversed = queue.find('li').reverse();

                reversed.each(function(idx, elem) {
                    if (idx + 1 > maxFiles) {
                        $(elem).remove();
                    }
                });
            }

            up.refresh();
            up.start();
        });

        pods_uploader.bind('UploadProgress', function(up, file) {
            var upbar = $('#' + file.id),
                prog  = upbar.find('.progress-bar');
            prog.css('width', file.percent + '%');
        });

        pods_uploader.bind('FileUploaded', function(up, file, resp) {
            var upbar = $('#' + file.id),
                prog = upbar.find('.progress-bar'),
                response = resp.response;

            if (response.substr(0, 5) == "Error") {
                var div = response.substr(7);
                alert($(div).text());
                upbar.remove();
            } else if (response.substr(0, 3) == "<e>") {
                var r = response.substr(4);
                alert(r);
                upbar.remove();
            } else {
                var json = $.parseJSON(response);
                var input = $('<input/>', {
                    'type': 'hidden',
                    name: "<?php echo esc_js( $field_name ); ?>[]",
                    value: json.ID
                });
                prog.remove();
                upbar.prepend('<span class="remove"><img src="<?php echo PODS_URL; ?>ui/images/del.png" alt="remove" /></span>');
                upbar.append(input);
            }
        });

        $('span.remove').live('click', function(evt) {
            $(this).closest('li').remove();
        });
    });
    </script>
<?php endif; ?>
