<?php
$field_number = PodsForm::field_loader( 'currency' );

$value = $field_number->format( $value, $name, $options, $pod, $id );

$attributes = array();
$attributes[ 'type' ] = 'text';
$attributes[ 'value' ] = $value;
$attributes[ 'tabindex' ] = 2;
$attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );

$thousands = ',';
$dot = '.';

if ( '9999.99' == pods_var_raw( 'number_format', $options ) )
    $thousands = '';
elseif ( '9999,99' == pods_var_raw( 'number_format', $options ) ) {
    $thousands = '';
    $dot = ',';
}
elseif ( '9.999,99' == pods_var_raw( 'number_format', $options ) ) {
    $thousands = '.';
    $dot = ',';
}
?>
<input<?php PodsForm::attributes( $attributes, $name, PodsForm::$field_type, $options ); ?>/>
<script>
    jQuery( function ( $ ) {
        $( 'input#<?php echo $attributes[ 'id' ]; ?>' ).on( 'blur', function () {
            if ( !/^[0-9\<?php
            echo implode( '\\', array_filter( array( $dot, $thousands ) ) );
            ?>]$/.test( $( this ).val() ) ) {
                var newval = $( this )
                    .val()
                    .replace( /[^0-9\<?php
                              echo implode( '\\', array_filter( array( $dot, $thousands ) ) );
                              ?>]/g, '' );
                $( this ).val( newval );
            }
        } );
    } );
</script>
