<?php
pods_form_enqueue_script( 'pods-cleditor' );
pods_form_enqueue_style( 'pods-styles' );

$type                   = 'textarea';
$attributes             = array();
$attributes['tabindex'] = 2;
$attributes             = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options, 'pods-ui-field-cleditor' );
?>
<textarea<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?>><?php echo esc_textarea( $value ); ?></textarea>
<script>
	jQuery( function ( $ ) {
		var $textarea = $( 'textarea#<?php echo esc_js( $attributes['id'] ); ?>' );
		var editorWidth = $textarea.outerWidth();
		$textarea.cleditor( {
								width : editorWidth
							} );
	} );
</script>
