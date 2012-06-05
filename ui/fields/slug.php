<?php
    $attributes = array();
    $attributes[ 'type' ] = 'text';
    $attributes[ 'value' ] = $value;
    $attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$type, $options );
?>
<input<?php PodsForm::attributes( $attributes, $name, PodsForm::$type, $options ); ?> />
<?php
    if ( !wp_script_is( 'jquery', 'queue' ) && !wp_script_is( 'jquery', 'to_do' ) && !wp_script_is( 'jquery', 'done' ) )
        wp_print_scripts( 'jquery' );
?>
<script>
    jQuery( function ( $ ) {
        $( 'input#<?php echo $attributes[ 'id' ]; ?>' ).change( function () {
            var newval = $( this )
                            .val()
                            .toLowerCase()
                            .replace( /([_ ])/g, '-' )
                            .replace( /([^0-9a-z-])/g, '' )
                            .replace( /(-){2,}/g, '-' );
            $( this ).val( newval );
        } );
    } );
</script>