<?php
/**
 * @package  Pods
 * @category Field Types
 */
?>
<p<?php Pods_Form::attributes( $attributes, $name, $type, $options ); ?>>
	<?php
	if ( apply_filters( 'pods_form_ui_comment_allow_html', true, $options ) ) {
		echo $message;
	} else {
		echo esc_html( $message );
	}
	?>
</p>