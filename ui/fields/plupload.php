<?php
    $file_limit = 1;

    if ( !wp_script_is( 'plupload-all', 'queue' ) && !wp_script_is( 'plupload-all', 'to_do' ) && !wp_script_is( 'plupload-all', 'done' ) )
        wp_print_scripts( 'plupload-all' );
?>
<script>
    jQuery( function () {
        plup_<?php echo esc_js( $name ); ?> = new plupload.Uploader( {
            runtimes: 'html5,flash,silverlight,html4',
            browse_button: '<?php echo esc_js( $css_id ); ?>-browse',
            container: 'plupload-container-<?php echo esc_js( $css_id ); ?>',
            file_data_name: 'Filedata',
            max_file_size: '<?php echo wp_max_upload_size(); ?>b',
            url: '<?php echo PODS_URL; ?>/ui/ajax/misc.php',
            flash_swf_url: '<?php echo includes_url( 'js/plupload/plupload.flash.swf' ); ?>',
            silverlight_xap_url: '<?php echo includes_url( 'js/plupload/plupload.silverlight.xap' ); ?>',
            multipart: true,
            urlstresm_upload: true,
            multipart_params: {
                "_wpnonce": "<?php echo wp_create_nonce( 'pods-wp_handle_upload_advanced' ); ?>",
                "action": "wp_handle_upload_advanced",
                "auth_cookie": "<?php echo ( is_ssl() ? esc_js( $_COOKIE[ SECURE_AUTH_COOKIE ] ) : esc_js( $_COOKIE[ AUTH_COOKIE ] ) ); ?>",
                "logged_in_cookie": "<?php echo esc_js( $_COOKIE[ LOGGED_IN_COOKIE ] ); ?>"
            }
        } );

        // Plupload Init Event Handler
        plup_<?php echo esc_js( $name ); ?>.bind( 'Init', function ( up, params ) {

        } );

        plup_<?php echo esc_js( $name ); ?>.init();

        // Plupload FilesAdded Event Handler
        plup_<?php echo esc_js( $name ); ?>.bind( 'FilesAdded', function ( up, files ) {
            // Hide any existing files (for use in single/limited field configuration)
            // jQuery('.pods_field_<?php echo $name; ?> .success').hide();

            jQuery.each( files, function ( index, file ) {
                jQuery( ".rightside.<?php echo esc_js( $name ); ?> .form" ).append( '<div id="' + file.id + '">' + file.name + '<div class="pods-progress"><div class="pods-bar"></div></div></div>' );
            } );

            up.refresh();
            up.start();
        } );

        // Plupload UploadProgress Event Handler
        plup_<?php echo esc_js( $name ); ?>.bind( 'UploadProgress', function ( up, file ) {
            jQuery( '#' + file.id + ' .pods-bar' ).css( 'width', file.percent + '%' );
        } );

        // Plupload FileUploaded Event Handler
        plup_<?php echo esc_js( $name ); ?>.bind( 'FileUploaded', function ( up, file, resp ) {
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
