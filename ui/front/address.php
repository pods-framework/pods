<?php

if ( ! empty( $value['address'] ) ) {

	$format = PodsForm::field_method( 'address', 'default_display_format' );
	if ( $options['address_display_type'] == 'custom' ) {
		$format = $options['address_display_type_custom'];
	}

	$html = PodsForm::field_method( 'address', 'format_to_html', $format, $value, $options );

	echo $html;

}

?>