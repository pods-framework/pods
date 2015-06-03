<?php
/**
 * @package  Pods
 * @category Field Types
 */

if ( is_array( $value ) ) {
	$value = implode( ',', $value );
}
$attributes = array();
$attributes[ 'type' ] = 'hidden';
$attributes[ 'value' ] = $value;
$attributes = Pods_Form::merge_attributes( $attributes, $name, $form_field_type, $options );
?>
<input<?php Pods_Form::attributes( $attributes, $name, $form_field_type, $options ); ?> />
