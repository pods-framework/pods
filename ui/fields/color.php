<?php
wp_enqueue_style( 'wp-color-picker' );

if ( !is_admin() )
    wp_register_script( 'wp-color-picker', admin_url( "js/color-picker.js" ), array( 'jquery' ), '3.5', true );

wp_enqueue_script( 'wp-color-picker' );

$attributes = array();
$attributes[ 'type' ] = 'text';
$attributes[ 'value' ] = $value;
$attributes[ 'tabindex' ] = 2;
$attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );
?>
<input<?php PodsForm::attributes( $attributes, $name, PodsForm::$field_type, $options ); ?> />

<script type="text/javascript">
    jQuery( function () {
        jQuery( '#color_<?php echo $attributes[ 'id' ]; ?>' ).hide();

        var pods_wp_color_obj_<?php echo pods_clean_name( $attributes[ 'id' ] ); ?> = jQuery( '#<?php echo $attributes[ 'id' ]; ?>' ).wpColorPicker();

        jQuery( '#<?php echo $attributes[ 'id' ]; ?>' ).on( 'focus blur', function () {
            jQuery( '#color_<?php echo $attributes[ 'id' ]; ?>' ).slideToggle();
        } );

        jQuery( '#<?php echo $attributes[ 'id' ]; ?>' ).on( 'keyup', function () {
            var color = jQuery( this ).val();

            if ( '' != color.replace( '#', '' ) && color.match( '#' ) )
                pods_wp_color_obj_<?php echo pods_clean_name( $attributes[ 'id' ] ); ?>.wpColorPicker( 'color', color );
        } );
    } );
</script>
