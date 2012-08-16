<?php
    $field_number = PodsForm::field_loader( 'number' );

    $value = $field_number->format( $value, $name, $options, $pod, $id );

    $attributes = array();
    $attributes[ 'type' ] = 'text';
    $attributes[ 'value' ] = $value;
    $attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );
?>
<input<?php PodsForm::attributes( $attributes, $name, PodsForm::$field_type, $options ); ?> />
<script>
    jQuery( function ( $ ) {
        $( 'input#<?php echo $attributes[ 'id' ]; ?>' ).on( 'blur', function () {
            if ( !/^[0-9<?php
            echo implode( '\\', array_filter( array( $dot, $thousands ) ) );
            ?>]$/.test( $( this ).val() ) ) {
                var newval = $( this )
                    .val()
                    .replace( /[^0-9<?php
                              echo implode( '\\', array_filter( array( $dot, $thousands ) ) );
                              ?>]/g, '' );
                $( this ).val( newval );
            }
        } );
    } );
</script>