<?php
    $type = 'textarea';
    $attributes = array();
    $attributes = PodsForm::merge_attributes($attributes, $name, PodsForm::$field_type, $options);
?>
<textarea<?php PodsForm::attributes( $attributes, $name, PodsForm::$field_type, $options ); ?>><?php echo esc_html( $value ); ?></textarea>
<?php
    PodsForm::regex( PodsForm::$field_type, $options );