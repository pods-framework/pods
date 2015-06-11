<?php
/**
 * @package  Pods
 * @category Field Types
 */

$attributes = array();
$attributes[ 'type' ] = 'text';
$attributes[ 'value' ] = Pods_Form::clean( $value, false, true );
$attributes[ 'tabindex' ] = 2;
$attributes = Pods_Form::merge_attributes( $attributes, $name, $form_field_type, $options );
?>
<input<?php Pods_Form::attributes( $attributes, $name, $form_field_type, $options ); ?> />
<script>
	jQuery( function ( $ ) {
		$( 'input#<?php echo esc_js( $attributes[ 'id' ] ); ?>' ).change( function () {
			var newval = $( this ).val().toLowerCase().replace( /(\s)/g, '_' ).replace( /([^0-9a-z_\-])/g, '' ).replace( /(_){2,}/g, '_' ).replace( /_$/, '' );
			$( this ).val( newval );
		} );
	} );
</script>
