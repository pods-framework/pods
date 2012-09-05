<?php
    $attributes = array();

    $type = 'text';

    if ( 1 == $options[ 'text_html5' ] )
        $type = 'url';

    $attributes[ 'type' ] = $type;
    $attributes[ 'value' ] = $value;
    $attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );
?>
<input<?php PodsForm::attributes( $attributes, $name, PodsForm::$field_type, $options ); ?> />
<?php
    PodsForm::regex( PodsForm::$field_type, $options );