<?php
    $attributes = array();
    $attributes[ 'type' ] = 'email';
    $attributes[ 'value' ] = $value;
    $attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$type, $options );
?>
<input<?php PodsForm::attributes( $attributes, $name, PodsForm::$type, $options ); ?> />
<?php
    PodsForm::regex( PodsForm::$type, $options );