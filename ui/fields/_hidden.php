<?php

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// phpcs:ignoreFile WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

if ( is_array( $value ) ) {
	$value = implode( ',', $value );
}
$attributes          = array();
$attributes['type']  = 'hidden';
$attributes['value'] = $value;
$attributes          = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );
?>
<input<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?> />
