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

$currency_format_sign = pods_v( 'currency_format_sign', $options, $currency );

if ( isset( PodsField_Currency::$currencies[ $currency_format_sign ] ) ) {
	$currency = $currency_format_sign;
}

$currency_sign = PodsField_Currency::$currencies[ $currency ]['sign'];
?>
<div class="pods-currency-container">
	<code class="pods-currency-sign pods-hidden"><?php echo $currency_sign; ?></code>
	<input<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?>/>
</div>
<script>
	jQuery( function ( $ ) {
		var input = $( 'input#<?php echo esc_js( $attributes[ 'id' ] ); ?>' ),
			currency_sign = input.siblings( 'code.pods-currency-sign' );

		input.on( 'blur', function () {
			if ( !/^[0-9\<?php
			echo esc_js( implode( '\\', array_filter( array( $dot, $thousands ) ) ) );
			?>]$/.test( $( this ).val() ) ) {
				var newval = $( this )
					.val()
					.replace( /[^0-9-\<?php
						echo esc_js( implode( '\\', array_filter( array( $dot, $thousands ) ) ) );
						?>]/g, '' );
				$( this ).val( newval );
			}
		} );

		if ( currency_sign.length ) {
			currency_sign.removeClass( 'pods-hidden' );

			function resize_currency_sign() {
				if ( currency_sign.width() < 1 ) {
					return;
				}

				input.css( 'padding-left', currency_sign.width() + 12 );
				currency_sign.css( 'line-height', parseInt( input.innerHeight(), 10 ) + 'px' );
			}

			// Cover most events we need.
			$(window).on( 'resize load visibilitychange postbox-toggled postbox-columnchange postboxes-columnchange', resize_currency_sign );

			// Gutenberg show/hide panels do not trigger any known events, this is one final hackaround for that.
			input.on( 'hover focus', resize_currency_sign );
		}
	} );
</script>
