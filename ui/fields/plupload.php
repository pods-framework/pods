<?php
    $file_limit = 1;

    if ( !wp_script_is( 'plupload-all', 'queue' ) && !wp_script_is( 'plupload-all', 'to_do' ) && !wp_script_is( 'plupload-all', 'done' ) )
        wp_print_scripts( 'plupload-all' );
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
        '_wpnonce' => wp_create_nonce('upload_file'),
        'action' => 'wp_handle_upload_advanced',
        'method' => 'upload_file',
        'pods_ajax' => 1,
    ),
);
$plupload_init = apply_filters('plupload_init', $plupload_init);
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
            jQuery( '#' + file.id + ' .pods-bar' ).css( 'width', file.percent + '%' );
        } );

        // Plupload FileUploaded Event Handler
        pods_uploader.bind( 'FileUploaded', function ( up, file, resp ) {
            var file_div = jQuery( '#' + file.id );
            var file_limit = <?php echo $file_limit; ?>;
            file_div.find( '.pods-progress' ).remove();

            if ( "Error" == resp.response.substr( 0, 5 ) ) {
                var response = resp.response.substr( 7 );
                file_div.append( response );
            }
            else if ( "<e>" == resp.response.substr( 0, 3 ) ) {
                var response = resp.response;
                file_div.append( resp.response );
            }
            else {
                var response = eval( '(' + resp.response.match( /\{(.*)\}/gi ) + ')' );
                file_div.html( '<div class="btn dropme"></div><a href="' + response.guid + '" target="_blank">' + response.post_title + '</a>' );
                file_div.attr( 'class', 'success' );
                file_div.data( 'post-id', response.ID );
            }

            jQuery.fn.reverse = [].reverse;
            var files = jQuery( '.pods_field_<?php echo $name; ?> .success' ), file_count = files.size();
            files.reverse().each( function ( idx, elem ) {
                if ( idx + 1 > file_limit )
                    jQuery( elem ).remove();
            } );
        } );
    } );
</script>
