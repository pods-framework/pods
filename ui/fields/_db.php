<?php
    $attributes = array();
    $attributes[ 'type' ] = 'text';
    $attributes[ 'value' ] = PodsForm::clean( $value, false, true );
    $attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$type, $options );
?>
<input<?php PodsForm::attributes( $attributes, $name, PodsForm::$type, $options ); ?> />
<script>
    jQuery( function ( $ ) {
        $( 'input#<?php echo $attributes[ 'id' ]; ?>' ).change( function () {
            var newval = $( this ).val().toLowerCase().replace( /([- ])/g, '_' ).replace( /([^0-9a-z_])/g, '' ).replace( /(_){2,}/g, '_' );
            $( this ).val( newval );
        } );
    } );
</script>