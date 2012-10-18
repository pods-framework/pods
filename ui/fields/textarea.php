<?php
$type = 'textarea';
$attributes = array();
$attributes[ 'tabindex' ] = 2;
$attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );
?>
<textarea<?php PodsForm::attributes( $attributes, $name, PodsForm::$field_type, $options ); ?>><?php echo esc_textarea( $value ); ?></textarea>
<?php
PodsForm::regex( PodsForm::$field_type, $options );
