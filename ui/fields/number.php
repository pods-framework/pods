<?php
$field_number = PodsForm::field_loader( 'number' );

$attributes             = array();
$attributes['type']     = 'text';
$attributes['value']    = $value;
$attributes['tabindex'] = 2;
$attributes             = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );

global $wp_locale;

if ( '9999.99' == pods_var( 'number_format', $options ) ) {
	$thousands = ',';
	$dot       = '.';
} elseif ( '9999,99' == pods_var( 'number_format', $options ) ) {
	$thousands = '.';
	$dot       = ',';
} elseif ( '9.999,99' == pods_var( 'number_format', $options ) ) {
	$thousands = '.';
	$dot       = ',';
} else {
	$thousands = $wp_locale->number_format['thousands_sep'];
	$dot       = $wp_locale->number_format['decimal_point'];
}
$regex_test = '^[0-9\\' . implode( '\\', array_filter( array( $dot, $thousands ) ) ) . '\\-]$';
$regex_replace = '[^0-9\\' . implode( '\\', array_filter( array( $dot, $thousands ) ) ) . '\\-]';
?>
<input<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?>/>
<script>
	jQuery( function ( $ ) {
		$( 'input#<?php echo esc_js( $attributes['id'] ); ?>' ).on( 'blur', function () {
            if ( !/<?php echo $regex_test; ?>/.test( $( this ).val() ) ) {
				var newval = $( this )
					.val()
                    .replace( /<?php echo $regex_replace; ?>/g, '' );
				$( this ).val( newval );
			}
		} );
	} );
</script>
