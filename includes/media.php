<?php
/**
 * @package Pods\Global\Functions\Media
 */
/**
 * Get the Attachment ID for a specific image field
 *
 * @param array|int|string $image The image field array, ID, or guid
 *
 * @return int Attachment ID
 *
 * @since 2.0.5
 */
function pods_image_id_from_field ( $image ) {
    $id = 0;

    if ( !empty( $image ) ) {
        if ( is_array( $image ) ) {
            if ( isset( $image[ 0 ] ) )
                $id = pods_image_id_from_field( $image[ 0 ] );
            elseif ( isset( $image[ 'ID' ] ) )
                $id = $image[ 'ID' ];
            elseif ( isset( $image[ 'guid' ] ) )
                $id = pods_image_id_from_field( $image[ 'guid' ] );
            elseif ( isset( $image[ 'id' ] ) )
                $id = $image[ 'id' ];
            else
                $id = pods_image_id_from_field( current( $image ) );
        }
        else {
            if ( false === strpos( $image, '.' ) && is_numeric( $image ) ) {
                $id = $image;

                $the_post_type = get_post_type( $id );

                if ( false === $the_post_type )
                    $id = 0;
                elseif ( 'attachment' != $the_post_type )
                    $id = get_post_thumbnail_id( $id );
            }
            else {
                $guid = pods_query( "SELECT `ID` FROM @wp_posts WHERE `post_type` = 'attachment' AND `guid` = %s", array( $image ) );

                if ( !empty( $guid ) )
                    $id = $guid[ 0 ]->ID;
            }
        }
    }

    $id = (int) $id;

    return $id;
}

/**
 * Get the <img> HTML for a specific image field
 *
 * @param array|int|string $image The image field array, ID, or guid
 * @param string|array $size Image size to use
 * @param int $default Default image to show if image not found, can be field array, ID, or guid
 * @param string|array $attributes <img> Attributes array or string (passed to wp_get_attachment_image
 * @param boolean $force Force generation of image (if custom size array provided)
 *
 * @return string <img> HTML or empty if image not found
 *
 * @since 2.0.5
 */
function pods_image ( $image, $size = 'thumbnail', $default = 0, $attributes = '', $force = false ) {
    $html = '';

    $id = pods_image_id_from_field( $image );
    $default = pods_image_id_from_field( $default );

    if ( 0 < $id ) {
        if ( $force ) {
            $full = wp_get_attachment_image_src( $id, 'full' );
            $src = wp_get_attachment_image_src( $id, $size );

            if ( !empty( $full ) && ( empty( $src ) || $full[ 0 ] != $src[ 0 ] ) )
                pods_image_resize( $id, $size );
        }

        $html = wp_get_attachment_image( $id, $size, true, $attributes );
    }

    if ( empty( $html ) && 0 < $default ) {
        if ( $force ) {
            $full = wp_get_attachment_image_src( $default, 'full' );
            $src = wp_get_attachment_image_src( $default, $size );

            if ( !empty( $full ) && ( empty( $src ) || $full[ 0 ] != $src[ 0 ] ) )
                pods_image_resize( $default, $size );
        }

        $html = wp_get_attachment_image( $default, $size, true, $attributes );
    }

    return $html;
}

/**
 * Get the Image URL for a specific image field
 *
 * @param array|int|string $image The image field array, ID, or guid
 * @param string|array $size Image size to use
 * @param int $default Default image to show if image not found, can be field array, ID, or guid
 * @param boolean $force Force generation of image (if custom size array provided)
 *
 * @return string Image URL or empty if image not found
 *
 * @since 2.0.5
 */
function pods_image_url ( $image, $size = 'thumbnail', $default = 0, $force = false ) {
    $url = '';

    $id = pods_image_id_from_field( $image );
    $default = pods_image_id_from_field( $default );

    if ( 0 < $id ) {
        if ( $force ) {
            $full = wp_get_attachment_image_src( $id, 'full' );
            $src = wp_get_attachment_image_src( $id, $size );

            if ( !empty( $full ) && ( empty( $src ) || $full[ 0 ] != $src[ 0 ] ) )
                pods_image_resize( $id, $size );
        }

        $src = wp_get_attachment_image_src( $id, $size );

        if ( !empty( $src ) )
            $url = $src[ 0 ];
        // Handle non-images
        else {
            $attachment = get_post( $id );

            if ( !preg_match( '!^image/!', get_post_mime_type( $attachment ) ) )
                $url = wp_get_attachment_url( $id );
        }
    }

    if ( empty( $url ) && 0 < $default ) {
        if ( $force ) {
            $full = wp_get_attachment_image_src( $default, 'full' );
            $src = wp_get_attachment_image_src( $default, $size );

            if ( !empty( $full ) && ( empty( $src ) || $full[ 0 ] != $src[ 0 ] ) )
                pods_image_resize( $default, $size );
        }

        $src = wp_get_attachment_image_src( $default, $size );

        if ( !empty( $src ) )
            $url = $src[ 0 ];
        // Handle non-images
        else {
            $attachment = get_post( $default );

            if ( !preg_match( '!^image/!', get_post_mime_type( $attachment ) ) )
                $url = wp_get_attachment_url( $default );
        }
    }

    return $url;
}

/**
 * Import media from a specific URL, saving as an attachment
 *
 * @param string $url URL to media for import
 * @param int $post_parent ID of post parent, default none
 * @param boolean $featured Whether to set it as the featured (post thumbnail) of the post parent
 *
 * @return int Attachment ID
 *
 * @since 2.3
 */
function pods_attachment_import ( $url, $post_parent = null, $featured = false ) {
    $filename = substr( $url, ( strrpos( $url, '/' ) ) + 1 );

    $title = substr( $filename, 0, ( strrpos( $filename, '.' ) ) );

    if ( !( ( $uploads = wp_upload_dir( current_time( 'mysql' ) ) ) && false === $uploads[ 'error' ] ) )
        return 0;

    $filename = wp_unique_filename( $uploads[ 'path' ], $filename );
    $new_file = $uploads[ 'path' ] . '/' . $filename;

    $file_data = @file_get_contents( $url );

    if ( !$file_data )
        return 0;

    file_put_contents( $new_file, $file_data );

    $stat = stat( dirname( $new_file ) );
    $perms = $stat[ 'mode' ] & 0000666;
    @chmod( $new_file, $perms );

    $wp_filetype = wp_check_filetype( $filename );

    if ( !$wp_filetype[ 'type' ] || !$wp_filetype[ 'ext' ] )
        return 0;

    $attachment = array(
        'post_mime_type' => $wp_filetype[ 'type' ],
        'guid' => $uploads[ 'url' ] . '/' . $filename,
        'post_parent' => null,
        'post_title' => $title,
        'post_content' => '',
    );

    $attachment_id = wp_insert_attachment( $attachment, $new_file, $post_parent );

    if ( is_wp_error( $attachment_id ) )
        return 0;

    require_once( ABSPATH . 'wp-admin/includes/image.php' );

    wp_update_attachment_metadata( $attachment_id, $meta_data = wp_generate_attachment_metadata( $attachment_id, $new_file ) );

    if ( 0 < $post_parent && $featured )
        update_post_meta( $post_parent, '_thumbnail_id', $attachment_id );

    return $attachment_id;
}

/**
 * Resize an image on demand
 *
 * @param int $attachment_id Attachment ID
 * @param string|array $size Size to be generated
 *
 * @return boolean Image generation result
 *
 * @since 2.3
 */
function pods_image_resize ( $attachment_id, $size ) {
    $size_data = array();

	// Basic image size string
    if ( !is_array( $size ) ) {
        global $wp_image_sizes;

		// Registered image size
        if ( isset( $wp_image_sizes[ $size ] ) && !empty( $wp_image_sizes[ $size ] ) )
            $size_data = $wp_image_sizes[ $size ];
		// Custom on-the-fly image size
		elseif ( preg_match( '/[0-9]+x[0-9]+/', $size ) || preg_match( '/[0-9]+x[0-9]+x[0-1]/', $size ) ) {
			$size = explode( 'x', $size );

            $size_data = array(
                'width' => (int) $size[ 0 ],
                'height' => (int) $size[ 1 ],
                'crop' => (int) ( isset( $size[ 2 ] ) ? $size[ 2 ] : 1 ),
            );

        	$size = $size_data[ 'width' ] . 'x' . $size_data[ 'height' ];
		}
    }
	// Image size array
    elseif ( 2 <= count( $size ) ) {
        if ( isset( $size[ 'width' ] ) )
            $size_data = $size;
        else {
            $size_data = array(
                'width' => (int) $size[ 0 ],
                'height' => (int) $size[ 1 ],
                'crop' => (int) ( isset( $size[ 2 ] ) ? $size[ 2 ] : 1 ),
            );
        }

        $size = $size_data[ 'width' ] . 'x' . $size_data[ 'height' ];
    }

    if ( empty( $size_data ) )
        return false;

    require_once( ABSPATH . 'wp-admin/includes/image.php' );

    $attachment = get_post( $attachment_id );
    $file = get_attached_file( $attachment_id );

    if ( $file && file_exists( $file ) ) {
 	    $metadata = wp_get_attachment_metadata( $attachment_id );

        if ( !empty( $metadata ) && preg_match( '!^image/!', get_post_mime_type( $attachment ) ) && file_is_displayable_image( $file ) ) {
            $editor = wp_get_image_editor( $file );

            if ( !is_wp_error( $editor ) ) {
                $metadata[ 'sizes' ] = array_merge( $metadata[ 'sizes' ], $editor->multi_resize( array( $size => $size_data ) ) );

                wp_update_attachment_metadata( $attachment_id, $metadata );

                return true;
            }
        }
    }

    return false;
}