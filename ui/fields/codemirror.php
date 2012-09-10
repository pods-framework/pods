<?php
wp_enqueue_script( 'pods-codemirror' );
wp_enqueue_style( 'pods-codemirror' );
wp_enqueue_script( 'pods-codemirror-loadmode' );

$type = 'textarea';
$attributes = array();
$attributes[ 'tabindex' ] = 2;
$attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );
?>
<div class="code-toolbar"><!-- Placeholder --></div>
<textarea<?php PodsForm::attributes( $attributes, $name, PodsForm::$field_type, $options ); ?>><?php echo esc_html( $value ); ?></textarea>
<div class="code-footer"><!-- Placeholder --></div>
<script>
    jQuery( function ( $ ) {
        var $textarea = jQuery( 'textarea#<?php echo $attributes[ 'id' ]; ?>' );
        CodeMirror.modeURL = "<?php echo PODS_URL ?>ui/js/codemirror/mode/%N/%N.js";
        var codemirror = CodeMirror.fromTextArea( document.getElementById( "<?php echo $attributes[ 'id' ] ?>" ), {
            lineNumbers : true,
            matchBrackets : true,
            mode : "application/x-httpd-php",
            indentUnit : 4,
            indentWithTabs : false,
            enterMode : "keep",
            tabMode : "shift",
            onBlur : function () {
                var value = codemirror.getValue();
                $textarea.val( value );
            }
        } );
        CodeMirror.autoLoadMode( codemirror, 'php' );
    } );

</script>
