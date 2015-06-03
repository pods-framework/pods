<?php
/**
 * @package  Pods
 * @category Field Types
 */

$field_number = Pods_Form::field_loader( 'currency' );

$value = $field_number->format( $value, $name, $options, $pod, $id );

$attributes               = array();
$attributes[ 'type' ]     = 'text';
$attributes[ 'value' ]    = $value;
$attributes[ 'tabindex' ] = 2;
$attributes               = Pods_Form::merge_attributes( $attributes, $name, $form_field_type, $options );

global $wp_locale;

if ( '9999.99' == pods_v( 'currency_format', $options ) ) {
	$thousands = '';
	$dot = '.';
} elseif ( '9999,99' == pods_v( 'currency_format', $options ) ) {
	$thousands = '';
	$dot = ',';
} elseif ( '9.999,99' == pods_v( 'currency_format', $options ) ) {
	$thousands = '.';
	$dot = ',';
} else {
	$thousands = $wp_locale->number_format[ 'thousands_sep' ];
	$dot = $wp_locale->number_format[ 'decimal_point' ];
}

$currency = 'usd';

if ( isset( Pods_Field_Currency::$currencies[ pods_v( 'currency_format_sign', $options, - 1 ) ] ) ) {
	$currency = pods_v( 'currency_format_sign', $options );
}

$currency_sign = Pods_Field_Currency::$currencies[ $currency ];
?>
<input<?php Pods_Form::attributes( $attributes, $name, $form_field_type, $options ); ?>/>
<script>
	jQuery( function ( $ ) {
		$( 'input#<?php echo esc_js( $attributes[ 'id' ] ); ?>' ).on( 'blur', function () {
			if ( !/^[0-9\<?php
            echo esc_js( implode( '\\', array_filter( array( $dot, $thousands ) ) ) );
            ?>]$/.test( $( this ).val() ) ) {
				var newval = $( this )
					.val()
					.replace( /[^0-9\<?php echo esc_js( $currency_sign ); ?>\<?php
                              echo esc_js( implode( '\\', array_filter( array( $dot, $thousands ) ) ) );
                              ?>]/g, '' );
				$( this ).val( newval );
			}
		} );
	} );
</script>
