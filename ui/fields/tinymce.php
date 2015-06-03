<?php
/**
 * @package  Pods
 * @category Field Types
 */

$settings                    = array();
$settings[ 'textarea_name' ] = $name;
$settings[ 'media_buttons' ] = false;

if (
	! ( defined( 'PODS_DISABLE_FILE_UPLOAD' ) && true === PODS_DISABLE_FILE_UPLOAD )
	&& ! ( defined( 'PODS_UPLOAD_REQUIRE_LOGIN' ) && is_bool( PODS_UPLOAD_REQUIRE_LOGIN ) && true === PODS_UPLOAD_REQUIRE_LOGIN && ! is_user_logged_in() )
	&& ! ( defined( 'PODS_UPLOAD_REQUIRE_LOGIN' ) && ! is_bool( PODS_UPLOAD_REQUIRE_LOGIN )
	       && ( ! is_user_logged_in() || ! current_user_can( PODS_UPLOAD_REQUIRE_LOGIN ) ) )
) {
	$settings[ 'media_buttons' ] = (boolean) pods_v( 'wysiwyg_media_buttons', $options, true );
}

$rows = (int) pods_v( Pods_Form::$field_type . '_rows', $options, 0 );

if ( 0 < $rows ) {
	$settings[ 'textarea_rows' ] = $rows;
}

if ( isset( $options[ 'settings' ] ) ) {
	$settings = array_merge( $settings, $options[ 'settings' ] );
}

$attributes = array();
$attributes = Pods_Form::merge_attributes( $attributes, $name, $form_field_type, $options, 'pods-ui-field-tinymce' );
$class_attributes = array( 'class' => $attributes[ 'class' ] );
?>
<div<?php Pods_Form::attributes( $class_attributes, $name, $form_field_type, $options ); ?>>
	<?php wp_editor( $value, $attributes[ 'id' ], $settings ); ?>
</div>
