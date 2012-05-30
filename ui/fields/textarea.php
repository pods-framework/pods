<?php
    $type = 'textarea';
    $attributes = array();
    $attributes = PodsForm::merge_attributes($attributes, $name, $type, $options);
?>
<textarea<?php PodsForm::attributes( $attributes, $name, $type, $options ); ?>><?php echo esc_html( $value ); ?></textarea>