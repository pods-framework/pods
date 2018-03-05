<?php

wp_enqueue_style( 'icon-picker' );
wp_enqueue_script( 'icon-picker' );

$attributes = array();
$attributes[ 'type' ] = 'text';
$attributes[ 'value' ] = $value;
$attributes[ 'tabindex' ] = 2;
$attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );

icon_picker_field( array(
	'name'  => $attributes['name'],
	'id'    => $attributes['id'],
	'class' => $attributes['class'],
	'value' => $attributes['value'],
) );
