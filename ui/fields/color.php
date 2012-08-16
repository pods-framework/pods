<?php
    wp_enqueue_style( 'farbtastic' );
    wp_enqueue_script( 'farbtastic' );

    $attributes = array();
    $attributes[ 'type' ] = 'text';
    $attributes[ 'value' ] = $value;
    $attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );
?>
<input<?php PodsForm::attributes( $attributes, $name, PodsForm::$field_type, $options ); ?> />

<div id="color_<?php echo $attributes[ 'id' ]; ?>"></div>

<script type="text/javascript">
    jQuery( function () {
        jQuery( '#color_<?php echo $attributes[ 'id' ]; ?>' ).hide();

        jQuery.farbtastic( '#color_<?php echo $attributes[ 'id' ]; ?>', function ( color ) {
            pods_pickColor( '#<?php echo $attributes[ 'id' ]; ?>', color );
        } );

        jQuery( '#<?php echo $attributes[ 'id' ]; ?>' ).on( 'click', function () {
            jQuery( '#color_<?php echo $attributes[ 'id' ]; ?>' ).slideToggle();
        } );

        if ( 'undefined' == pods_pickColor ) {
            function pods_pickColor ( id, color ) {
                jQuery( id ).val( color.toUpperCase() );
            }
        }
    } );
</script>