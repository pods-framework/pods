<?php
wp_enqueue_style( 'farbtastic' );

if ( !is_admin() )
    wp_register_script( 'farbtastic', admin_url( "js/farbtastic.js" ), array( 'jquery' ), '1.2', true );

wp_enqueue_script( 'farbtastic' );

$attributes = array();
$attributes[ 'type' ] = 'text';
$attributes[ 'value' ] = $value;
$attributes[ 'tabindex' ] = 2;
$attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );
?>
<input<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?> />

<div id="color_<?php echo esc_js( $attributes[ 'id' ] ); ?>"></div>

<script type="text/javascript">
    if ( 'undefined' == typeof pods_farbastic_changing ) {
        var pods_farbastic_changing = false;
    }

    jQuery( function () {
        jQuery( '#color_<?php echo esc_js( $attributes[ 'id' ] ); ?>' ).hide();

        var pods_farbtastic_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?> = jQuery.farbtastic(
                '#color_<?php echo esc_js( $attributes[ 'id' ] ); ?>',
                function ( color ) {
                    pods_pickColor( '#<?php echo esc_js( $attributes[ 'id' ] ); ?>', color );
                }
        );

        jQuery( '#<?php echo esc_js( $attributes[ 'id' ] ); ?>' ).on( 'focus blur', function () {
            jQuery( '#color_<?php echo esc_js( $attributes[ 'id' ] ); ?>' ).slideToggle();
        } );

        jQuery( '#<?php echo esc_js( $attributes[ 'id' ] ); ?>' ).on( 'keyup', function () {
            var color = jQuery( this ).val();

            pods_farbastic_changing = true;

            if ( '' != color.replace( '#', '' ) && color.match( '#' ) )
                pods_farbtastic_<?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>.setColor( color );

            pods_farbastic_changing = false;
        } );

        if ( 'undefined' == pods_pickColor ) {
            function pods_pickColor ( id, color ) {
                if ( !pods_farbastic_changing )
                    jQuery( id ).val( color.toUpperCase() );
            }
        }
    } );
</script>
