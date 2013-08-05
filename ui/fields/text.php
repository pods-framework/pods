<?php
    $attributes = array();
    $attributes[ 'type' ] = 'text';
    $attributes[ 'value' ] = $value;
    $attributes[ 'tabindex' ] = 2;
    $attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );

    if ( pods_var( 'readonly', $options, false ) ) {
        $attributes[ 'readonly' ] = 'READONLY';

        $attributes[ 'class' ] .= ' pods-form-ui-read-only';
    }
?>
    <input<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?> />
<?php
    PodsForm::regex( $form_field_type, $options );