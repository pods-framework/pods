<?php
wp_enqueue_style( 'wp-color-picker' );

if ( ! is_admin() ) {
	wp_enqueue_script(
		'iris', admin_url( 'js/iris.min.js' ), array(
			'jquery-ui-draggable',
			'jquery-ui-slider',
			'jquery-touch-punch',
		)
	);
	wp_enqueue_script( 'wp-color-picker', admin_url( 'js/color-picker.min.js' ), array( 'iris' ), '3.5', true );
	$colorpicker_l10n = array(
		'clear'         => __( 'Clear' ),
		'defaultString' => __( 'Default' ),
		'pick'          => __( 'Select Color' ),
		'current'       => __( 'Current Color' ),
	);
	wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', $colorpicker_l10n );
} else {
	wp_enqueue_script( 'wp-color-picker' );
}

$attributes             = array();
$attributes['type']     = 'text';
$attributes['value']    = $value;
$attributes['tabindex'] = 2;
$attributes             = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );
?>
<input<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?> />

<script type="text/javascript">
	jQuery( function () {
		jQuery( '#color_<?php echo esc_js( $attributes['id'] ); ?>' ).hide();

		var pods_wp_color_obj_<?php echo esc_js( pods_js_name( $attributes['id'] ) ); ?> = jQuery( '#<?php echo esc_js( $attributes['id'] ); ?>' ).wpColorPicker();

		pods_wp_color_obj_<?php echo esc_js( pods_js_name( $attributes['id'] ) ); ?>.parents( '.wp-picker-container' ).find( '.iris-slider-offset' ).removeClass( 'ui-widget-content ui-slider-vertical' );

		jQuery( '#<?php echo esc_js( $attributes['id'] ); ?>' ).on( 'focus blur', function () {
			jQuery( '#color_<?php echo esc_js( $attributes['id'] ); ?>' ).slideToggle();
		} );

		jQuery( '#<?php echo esc_js( $attributes['id'] ); ?>' ).on( 'keyup', function () {
			var color = jQuery( this ).val();

			if ( '' != color.replace( '#', '' ) && color.match( '#' ) )pods_wp_color_obj_<?php echo esc_js( pods_js_name( $attributes['id'] ) ); ?>.wpColorPicker( 'color', color );
		} );
	} );
</script>
