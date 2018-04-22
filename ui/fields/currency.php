<?php
$field_number = PodsForm::field_loader( 'currency' );

$value = $field_number->format( $value, $name, $options, $pod, $id );

$attributes             = array();
$attributes['type']     = 'text';
$attributes['value']    = $value;
$attributes['tabindex'] = 2;
$attributes             = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );

global $wp_locale;

if ( '9999.99' == pods_var( 'currency_format', $options ) ) {
	$thousands = '';
	$dot       = '.';
} elseif ( '9999,99' == pods_var( 'currency_format', $options ) ) {
	$thousands = '';
	$dot       = ',';
} elseif ( '9.999,99' == pods_var( 'currency_format', $options ) ) {
	$thousands = '.';
	$dot       = ',';
} else {
	$thousands = $wp_locale->number_format['thousands_sep'];
	$dot       = $wp_locale->number_format['decimal_point'];
}

$currency = 'usd';

if ( isset( PodsField_Currency::$currencies[ pods_var( 'currency_format_sign', $options, - 1 ) ] ) ) {
	$currency = pods_var( 'currency_format_sign', $options );
}

$currency_sign = PodsField_Currency::$currencies[ $currency ]['sign'];

echo '<code class="currency-sign">' . $currency_sign . '</code>';
?>
<input<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?>/>
<script>
	jQuery( function ( $ ) {
		var input = $( 'input#<?php echo esc_js( $attributes[ 'id' ] ); ?>' ),
			currency_sign = input.siblings( 'code.currency-sign' );

		input.on( 'blur', function () {
			if ( !/^[0-9\<?php
			echo esc_js( implode( '\\', array_filter( array( $dot, $thousands ) ) ) );
			?>]$/.test( $( this ).val() ) ) {
				var newval = $( this )
					.val()
					.replace( /[^0-9-\\<?php echo esc_js( $currency_sign ); ?>\<?php
						echo esc_js( implode( '\\', array_filter( array( $dot, $thousands ) ) ) );
						?>]/g, '' );
				$( this ).val( newval );
			}
		} );

		if ( currency_sign.length ) {
			function resize_currency_sign() {
				input.css( 'padding-left', parseInt( input.css( 'padding-left' ), 10 ) + currency_sign.width() + 12 );
				currency_sign.css( 'line-height', parseInt( input.innerHeight() ) + 'px' );
			}
			$(window).resize( resize_currency_sign );
			resize_currency_sign();
			currency_sign.show();
		}
	} );
</script>
