<?php
    $type = 'textarea';
    $attributes = array();
    $attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );
?>
<textarea<?php PodsForm::attributes( $attributes, $name, PodsForm::$field_type, $options ); ?>><?php echo esc_html( $value ); ?></textarea>
<script>
    jQuery( function( $ ) {
        var $textarea = $( 'textarea#<?php echo $attributes[ 'id' ]; ?>' );
        if ( $textarea.data( 'width' ) ) {
            $textarea.cleditor( {
                width : $textarea.data( 'width' )
            } );
        }
        else
            $textarea.cleditor();
    } );
</script>