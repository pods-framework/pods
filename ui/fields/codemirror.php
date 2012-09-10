<?php
    wp_enqueue_script( 'pods-codemirror' );
    wp_enqueue_style( 'pods-codemirror' );

    $type = 'textarea';
    $attributes = array();
    $attributes[ 'tabindex' ] = 2;
    $attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );
?>
<textarea<?php PodsForm::attributes( $attributes, $name, PodsForm::$field_type, $options ); ?>><?php echo esc_html( $value ); ?></textarea>
<script>
    jQuery( function( $ ) {
        var $textarea = $( 'textarea#<?php echo $attributes[ 'id' ]; ?>' );
    } );
</script>