<?php

if ( ! function_exists( 'wp_get_attachment_image_url' ) ) {
	/**
	 * Get the URL of an image attachment.
	 *
	 * @since WordPress 4.4.0
	 *
	 * @param int          $attachment_id Image attachment ID.
	 * @param string|array $size          Optional. Image size to retrieve. Accepts any valid image size, or an array
	 *                                    of width and height values in pixels (in that order). Default 'thumbnail'.
	 * @param bool         $icon          Optional. Whether the image should be treated as an icon. Default false.
	 * @return string|false Attachment URL or false if no image is available.
	 */
	function wp_get_attachment_image_url( $attachment_id, $size = 'thumbnail', $icon = false ) {
		$image = wp_get_attachment_image_src( $attachment_id, $size, $icon );
		return isset( $image['0'] ) ? $image['0'] : false;
	}
}


/**
 * Get Icon URL
 *
 * @since  0.2.0
 *
 * @param  string  $type  Icon type.
 * @param  mixed   $id    Icon ID.
 * @param  string  $size  Optional. Icon size, defaults to 'thumbnail'.
 *
 * @return string
 */
function icon_picker_get_icon_url( $type, $id, $size = 'thumbnail' ) {
	$url = '';

	if ( ! in_array( $type, array( 'image', 'svg' ), true ) ) {
		return $url;
	}

	if ( empty( $id ) ) {
		return $url;
	}

	return wp_get_attachment_image_url( $id, $size, false );
}


/**
 * The Icon Picker Field
 *
 * @since 0.2.0
 *
 * @param mixed $args {
 * }
 * @param bool  $echo Whether to return the field or print it. Defaults to TRUE.
 *
 * @return mixed
 */
function icon_picker_field( $args, $echo = true ) {
	$defaults = array(
		'id'    => '',
		'name'  => '',
		'value' => array(
			'type' => '',
			'icon' => '',
		),
		'select' => sprintf( '<a class="ipf-select">%s</a>', esc_html__( 'Select Icon', 'icon-picker-field' ) ),
		'remove' => sprintf( '<a class="ipf-remove button hidden">%s</a>', esc_html__( 'Remove', 'icon-picker-field' ) ),
	);

	$args          = wp_parse_args( $args, $defaults );
	$args['value'] = wp_parse_args( $args['value'], $defaults['value'] );

	$field  = sprintf( '<div id="%s" class="ipf">', $args['id'] );
	$field .= $args['select'];
	$field .= $args['remove'];

	foreach ( $args['value'] as $key => $value ) {
		$field .= sprintf(
			'<input type="hidden" id="%s" name="%s" class="%s" value="%s" />',
			esc_attr( "{$args['id']}-{$key}" ),
			esc_attr( "{$args['name']}[{$key}]" ),
			esc_attr( "ipf-{$key}" ),
			esc_attr( $value )
		);
	}

	// This won't be saved. It's here for the preview.
	$field .= sprintf(
		'<input type="hidden" class="url" value="%s" />',
		esc_attr( icon_picker_get_icon_url( $args['value']['type'], $args['value']['icon'] ) )
	);
	$field .= '</div>';

	if ( $echo ) {
		echo $field; // xss ok
	} else {
		return $field;
	}
}


// Add-ons for other plugins.

