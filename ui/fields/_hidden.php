<?php
$attributes = array();
$attributes[ 'type' ] = 'hidden';
$attributes[ 'value' ] = $value;
$attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );
?>
<input<?php PodsForm::attributes( $attributes, $name, PodsForm::$field_type, $options ); ?> />
