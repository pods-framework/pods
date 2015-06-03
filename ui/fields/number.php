<?php
/**
 * @package  Pods
 * @category Field Types
 */

$field_number = Pods_Form::field_loader( 'number' );

$value = $field_number->format( $value, $name, $options, $pod, $id );

$attributes               = array();
$attributes[ 'type' ]     = 'text';
$attributes[ 'value' ]    = $value;
$attributes[ 'tabindex' ] = 2;
$attributes               = Pods_Form::merge_attributes( $attributes, $name, $form_field_type, $options );

global $wp_locale;

if ( '9999.99' == pods_v( 'number_format', $options ) ) {
	$thousands = ',';
	$dot = '.';
} elseif ( '9999,99' == pods_v( 'number_format', $options ) ) {
	$thousands = '.';
	$dot = ',';
} elseif ( '9.999,99' == pods_v( 'number_format', $options ) ) {
	$thousands = '.';
	$dot = ',';
} else {
	$thousands = $wp_locale->number_format[ 'thousands_sep' ];
	$dot = $wp_locale->number_format[ 'decimal_point' ];
}
?>
<input<?php Pods_Form::attributes( $attributes, $name, $form_field_type, $options ); ?>/>
<script>
	jQuery( function ( $ ) {
		$( 'input#<?php echo $attributes[ 'id' ]; ?>' ).on( 'blur', function () {
			if ( !/^[0-9\<?php
            echo esc_js( implode( '\\', array_filter( array( $dot, $thousands ) ) ) );
            ?>\-]$/.test( $( this ).val() ) ) {
				var newval = $( this )
					.val()
					.replace( /[^0-9\<?php
                              echo esc_js( implode( '\\', array_filter( array( $dot, $thousands ) ) ) );
                              ?>\-]/g, '' );
				$( this ).val( newval );
			}
		} );
	} );
</script>
