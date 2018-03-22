<?php

$wp_icon_picker = Icon_Picker::instance();
$wp_icon_picker->load();

if ( ! did_action( 'icon_picker_admin_loaded' ) ) {
	$wp_icon_picker_loader = Icon_Picker_Loader::instance();
	$wp_icon_picker_loader->_enqueue_assets();
	//add_action( 'print_media_templates', array( $wp_icon_picker_loader, '_media_templates' ) );
	//add_filter( 'media_view_strings', array( $wp_icon_picker_loader, '_media_view_strings' ) );
}

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
