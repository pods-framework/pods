<?php

wp_enqueue_script( 'wplink' );
wp_enqueue_style( 'editor-buttons' );

wp_enqueue_script( 'pods-link-picker', PODS_URL . 'ui/js/pods-link-picker.js', array( 'jquery' ), '1.0.0' );

PodsForm::field_method( 'link', 'validate_link_modal' );

$url_attributes = array();
$url_type = 'text';
if ( 1 == pods_var( 'link_html5', $options ) ) {
	$url_type = 'url';
}
$url_attributes[ 'type' ] = $url_type;
$url_attributes[ 'class' ] = 'linkPickerUrl';
$url_attributes[ 'value' ] = (isset($value['url'])?$value['url']:'');
$url_attributes[ 'tabindex' ] = 2;
$url_name = $name.'[url]';
$url_attributes = PodsForm::merge_attributes( $url_attributes, $url_name, $form_field_type, $options );

$text_attributes = array();
$text_type = 'text';
$text_attributes[ 'type' ] = $text_type;
$text_attributes[ 'class' ] = 'linkPickerText';
$text_attributes[ 'value' ] = (isset($value['text'])?$value['text']:'');
$text_attributes[ 'tabindex' ] = 2;
$text_name = $name.'[text]';
$text_attributes = PodsForm::merge_attributes( $text_attributes, $text_name, $form_field_type, $options );

$target_attributes = array();
$target_type = 'checkbox';
$target_attributes[ 'type' ] = $target_type;
$target_attributes[ 'class' ] = 'linkPickerTarget';
$target_attributes[ 'value' ] = '_blank';
$target_attributes[ 'tabindex' ] = 2;
$target_attributes[ 'style' ] = 'display: inline-block;';
if ( isset( $value['target'] ) && $value['target'] == '_blank' || ( ! isset( $value['target'] ) && ! empty( $options['link_new_window'] ) ) ) {
	$target_attributes[ 'checked' ] = 'checked';
}
$target_name = $name.'[target]';
$target_attributes = PodsForm::merge_attributes( $target_attributes, $target_name, $form_field_type, $options );

$attributes = array();
$attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options, 'pods-ui-field-link' );
$class_attributes = array( 'class' => $attributes[ 'class' ] );
?>

<div<?php PodsForm::attributes( $class_attributes, $name, $form_field_type, $options ); ?>>
	<div class="pods-link-options">
		<p class="howto"><?php _e('Enter the destination URL') ?></p>
		<p>
			<div class="alignleft">
				<label><span><?php _e('URL') ?></span><input<?php PodsForm::attributes( $url_attributes, $url_name, $form_field_type, $options ); ?> /></label>
			</div>
			<div class="alignleft">
				<label><span><?php _e('Link Text') ?></span><input<?php PodsForm::attributes( $text_attributes, $text_name, $form_field_type, $options ); ?> /></label>
			</div>
			<div class="link-target">
				<label><div>&nbsp;</div><input<?php PodsForm::attributes( $target_attributes, $target_name, $form_field_type, $options ); ?> /> <?php _e('Open link in a new tab') ?></label>
			</div>
		</p>
		<br clear="both">

		<?php if ( 1 == pods_v( 'link_select_existing', $options, 1 ) ) { ?>
			<div class="howto link-existing-content" style="display: none;">
				<a href="#" class="podsLinkPopup"><?php _e('Or link to existing content') ?></a>
				<textarea id="pods-link-editor-hidden" disabled="disabled" style="display: none;"></textarea>
			</div>
		<?php } ?>
	</div>
</div>

<?php
PodsForm::regex( $form_field_type, $options );