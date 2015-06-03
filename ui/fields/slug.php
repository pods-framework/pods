<?php
/**
 * @package  Pods
 * @category Field Types
 */

$attributes               = array();
$attributes[ 'type' ]     = 'text';
$attributes[ 'value' ]    = $value;
$attributes[ 'tabindex' ] = 2;
$attributes               = Pods_Form::merge_attributes( $attributes, $name, $form_field_type, $options );
?>
<input<?php Pods_Form::attributes( $attributes, $name, $form_field_type, $options ); ?> />
<script>
	jQuery( function ( $ ) {
        $( 'input#<?php echo esc_js( $attributes[ 'id' ] ); ?>' ).change( function () {
			var newval = $( this )
				.val()
				.toLowerCase()
				.replace( /([_ ])/g, '-' )
				.replace( /([`~!@#$%^&*()_|+=?;:'",.<>\{\}\[\]\\\/])/g, '' )
				.replace( /(-){2,}/g, '-' );
			$( this ).val( newval );
		} );
	} );
</script>
