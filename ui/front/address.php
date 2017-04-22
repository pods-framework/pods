<?php
if ( ! empty( $value['address'] ) ) {
	$format = PodsForm::field_method( 'address', 'default_display_format' );

	if ( 'custom' === pods_v( 'address_display_type', $options ) ) {
		$custom_format = trim( pods_v( 'address_display_type_custom', $options ) );

		if ( ! empty( $custom_format ) ) {
			$format = $custom_format;
		}
	}

	echo PodsForm::field_method( 'address', 'format_to_html', $format, $value, $options );
}