<?php
    $attributes = array();
    $attributes[ 'type' ] = 'text';
    $attributes[ 'value' ] = $value;
    $attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$type, $options );

    $thousands = ',';
    $dot = '.';
    if ( '9999.99' == $options[ 'number_format' ] )
        $thousands = '';
    elseif ( '9999,99' == $options[ 'number_format' ] ) {
        $thousands = '';
        $dot = ',';
    }
    elseif ( '9.999,99' == $options[ 'number_format' ] ) {
        $thousands = '.';
        $dot = ',';
    }

    if ( 'i18n' == $options[ 'number_format' ] )
        $attributes[ 'value' ] = number_format_i18n( (float) $attributes[ 'value' ], $options[ 'number_decimals' ] );
    else
        $attributes[ 'value' ] = number_format( (float) $attributes[ 'value' ], $options[ 'number_decimals' ], $dot, $thousands );
?>
<input<?php PodsForm::attributes( $attributes, $name, PodsForm::$type, $options ); ?> />
<script>
    jQuery( function ( $ ) {
        $( 'input#<?php echo $attributes[ 'id' ]; ?>' ).keyup( function () {
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
        $( 'input#<?php echo $attributes[ 'id' ]; ?>' ).blur( function () {
            $( this ).keyup();
        } );
    } );
</script>