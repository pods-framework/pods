<?php
$attributes = array();

$type = 'text';

if ( 1 == pods_var( 'website_html5', $options ) )
    $type = 'url';

$attributes[ 'type' ] = $type;
$attributes[ 'value' ] = $value;
$attributes[ 'tabindex' ] = 2;
$attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );
?>
<input<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?> />
<?php
PodsForm::regex( $form_field_type, $options );