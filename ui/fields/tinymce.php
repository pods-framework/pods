<?php
$settings = array();
$settings[ 'textarea_name' ] = $name;
$settings[ 'media_buttons' ] = false;
if (
    !( defined( 'PODS_DISABLE_FILE_UPLOAD' ) && true === PODS_DISABLE_FILE_UPLOAD )
    && !( defined( 'PODS_UPLOAD_REQUIRE_LOGIN' ) && is_bool( PODS_UPLOAD_REQUIRE_LOGIN ) && true === PODS_UPLOAD_REQUIRE_LOGIN && !is_user_logged_in() )
    && !( defined( 'PODS_UPLOAD_REQUIRE_LOGIN' ) && !is_bool( PODS_UPLOAD_REQUIRE_LOGIN )
          && ( !is_user_logged_in() || !current_user_can( PODS_UPLOAD_REQUIRE_LOGIN ) ) )
) {
    $settings[ 'media_buttons' ] = (boolean) pods_var( 'wysiwyg_media_buttons', $options, true );
}

if ( isset( $options[ 'settings' ] ) )
    $settings = array_merge( $settings, $options[ 'settings' ] );

$attributes = array();
$attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options, 'pods-ui-field-tinymce' );
$class_attributes = array( 'class' => $attributes[ 'class' ] );
?>
<div<?php PodsForm::attributes( $class_attributes, $name, $form_field_type, $options ); ?>>
    <?php wp_editor( $value, $attributes[ 'id' ], $settings ); ?>
</div>
