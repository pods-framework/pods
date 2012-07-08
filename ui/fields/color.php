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

        jQuery( '#color_<?php echo $attributes[ 'id' ]; ?>' ).farbtastic( '#color' );

        jQuery( '#<?php echo $attributes[ 'id' ]; ?>' ).click( function () {
            jQuery( '#color_<?php echo $attributes[ 'id' ]; ?>' ).slideToggle();
        } );
    } );
</script>