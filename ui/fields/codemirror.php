<?php
wp_enqueue_script( 'pods-codemirror' );
wp_enqueue_style( 'pods-styles' );
wp_enqueue_script( 'pods-codemirror-loadmode' );

$type                   = 'textarea';
$attributes             = array();
$attributes['tabindex'] = 2;
$attributes             = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options, 'pods-ui-field-codemirror' );
?>
<div class="pods-codemirror pods-compat-container pods-form-ui-field">
	<div class="code-toolbar"><!-- Placeholder --></div>
	<textarea<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?>><?php echo esc_textarea( $value ); ?></textarea>
	<div class="code-footer"><!-- Placeholder --></div>

	<script>
		var $textarea_<?php echo esc_js( pods_js_name( $attributes['id'] ) ); ?>,
			codemirror_<?php echo esc_js( pods_js_name( $attributes['id'] ) ); ?>;

		jQuery( function ( $ ) {
			$textarea_<?php echo esc_js( pods_js_name( $attributes['id'] ) ); ?> = jQuery( 'textarea#<?php echo esc_js( $attributes['id'] ); ?>' );

			CodeMirror.modeURL = "<?php echo esc_js( PODS_URL ); ?>ui/js/codemirror/mode/%N/%N.js";
			if ( 'undefined' == typeof codemirror_<?php echo esc_js( pods_js_name( $attributes['id'] ) ); ?> ) {

				codemirror_<?php echo esc_js( pods_js_name( $attributes['id'] ) ); ?> = CodeMirror.fromTextArea( document.getElementById( "<?php echo esc_js( $attributes['id'] ); ?>" ), {
					lineNumbers    : true,
					matchBrackets  : true,
					mode           : "application/x-httpd-php",
					indentUnit     : 4,
					indentWithTabs : false,
					lineWrapping   : true,
					enterMode      : "keep",
					tabMode        : "shift"
				} );
				codemirror_<?php echo esc_js( pods_js_name( $attributes['id'] ) ); ?>.on( 'blur', function () {
					var value = codemirror_<?php echo esc_js( pods_js_name( $attributes['id'] ) ); ?>.getValue();
					$textarea_<?php echo esc_js( pods_js_name( $attributes['id'] ) ); ?>.val( value );
				} );

				CodeMirror.autoLoadMode( codemirror_<?php echo esc_js( pods_js_name( $attributes['id'] ) ); ?>, 'php' );
			}
		} );
	</script>
</div>
