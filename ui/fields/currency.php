<?php
$field_number = PodsForm::field_loader( 'currency' );

$value = $field_number->format( $value, $name, $options, $pod, $id );

$attributes = array();
$attributes[ 'type' ] = 'text';
$attributes[ 'value' ] = $value;
$attributes[ 'tabindex' ] = 2;
$attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );

global $wp_locale;

if ( '9999.99' == pods_var( 'currency_format', $options ) ) {
    $thousands = '';
    $dot = '.';
}
elseif ( '9999,99' == pods_var( 'currency_format', $options ) ) {
    $thousands = '';
    $dot = ',';
}
elseif ( '9.999,99' == pods_var( 'currency_format', $options ) ) {
    $thousands = '.';
    $dot = ',';
}
else {
    $thousands = $wp_locale->number_format[ 'thousands_sep' ];
    $dot = $wp_locale->number_format[ 'decimal_point' ];
}

$currency = 'usd';

if ( isset( PodsField_Currency::$currencies[ pods_var( 'currency_format_sign', $options, -1 ) ] ) )
    $currency = pods_var( 'currency_format_sign', $options );

$currency_sign = PodsField_Currency::$currencies[ $currency ]['sign'];
?>
<input<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?>/>
<script>
    jQuery( function ( $ ) {
        $( 'input#<?php echo esc_js( $attributes[ 'id' ] ); ?>' ).on( 'blur', function () {
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
    } );
</script>
