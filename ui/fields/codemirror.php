<?php
wp_enqueue_script( 'pods-codemirror' );
wp_enqueue_style( 'pods-codemirror' );
wp_enqueue_script( 'pods-codemirror-loadmode' );

$type = 'textarea';
$attributes = array();
$attributes[ 'tabindex' ] = 2;
$attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options, 'pods-ui-field-codemirror' );
?>
<div class="code-toolbar"><!-- Placeholder --></div>
<textarea<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?>><?php echo esc_textarea( $value ); ?></textarea>
<div class="code-footer"><!-- Placeholder --></div>

<script>
    var $textarea_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?>, codemirror_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?>;

    jQuery( function ( $ ) {
        $textarea_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?> = jQuery( 'textarea#<?php echo $attributes[ 'id' ]; ?>' );

        CodeMirror.modeURL = "<?php echo PODS_URL ?>ui/js/codemirror/mode/%N/%N.js";
        if ( 'undefined' == typeof codemirror_<?php echo pods_clean_name( $attributes[ 'name' ] ) ?> ) {

            codemirror_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?> = CodeMirror.fromTextArea( document.getElementById( "<?php echo $attributes[ 'id' ] ?>" ), {
                lineNumbers : true,
                matchBrackets : true,
                mode : "application/x-httpd-php",
                indentUnit : 4,
                indentWithTabs : false,
                lineWrapping : true,
                enterMode : "keep",
                tabMode : "shift",
                onBlur : function () {
                    var value = codemirror_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?>.getValue();
                    $textarea_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?>.val( value );
                }
            } );

            CodeMirror.autoLoadMode( codemirror_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?>, 'php' );
        }
    } );
</script>
