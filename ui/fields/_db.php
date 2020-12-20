<?php
$attributes             = array();
$attributes['type']     = 'text';
$attributes['value']    = PodsForm::clean( $value, false, true );
$attributes['tabindex'] = 2;
$attributes             = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );
?>
<input<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?> />
<script>
	jQuery( function ( $ ) {
		$( 'input#<?php echo esc_js( $attributes['id'] ); ?>' ).on( 'change', function () {
			var newval = $( this ).val().toLowerCase().replace( /(\s)/g, '_' ).replace( /([^0-9a-z_\-])/g, '' ).replace( /(_){2,}/g, '_' ).replace( /_$/, '' );
			$( this ).val( newval );
		} );
	} );
</script>
