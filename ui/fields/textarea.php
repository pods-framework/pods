<?php
    $type = 'textarea';
    $attributes = array();
    $attributes[ 'tabindex' ] = 2;
    $attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );

    if ( pods_var( 'readonly', $options, false ) ) {
        $attributes[ 'readonly' ] = 'READONLY';

        $attributes[ 'class' ] .= ' pods-form-ui-read-only';
    }
?>
    <textarea<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?>><?php echo esc_textarea( $value ); ?></textarea>
<?php
    PodsForm::regex( $form_field_type, $options );