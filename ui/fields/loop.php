<?php
/**
 * @package  Pods
 * @category Field Types
 */

$attributes               = array();
$attributes[ 'type' ]     = 'text';
$attributes[ 'value' ]    = $value;
$attributes[ 'tabindex' ] = 2;
$attributes               = Pods_Form::merge_attributes( $attributes, $name, $form_field_type, $options );
?>
	<input<?php Pods_Form::attributes( $attributes, $name, $form_field_type, $options ); ?> />
<?php
Pods_Form::regex( $form_field_type, $options );