<?php
    $attributes = array();
    $attributes[ 'type' ] = 'url';
    $attributes[ 'value' ] = $value;
    $attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );
?>
<input<?php PodsForm::attributes( $attributes, $name, PodsForm::$field_type, $options ); ?> />
<?php
    PodsForm::regex( PodsForm::$field_type, $options );