<?php
$attributes = array();

$type = 'text';

if ( 1 == pods_var( 'email_html5', $options ) )
    $type = 'email';

$attributes[ 'type' ] = $type;
$attributes[ 'value' ] = $value;
$attributes[ 'tabindex' ] = 2;
$attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );
?>
<input<?php PodsForm::attributes( $attributes, $name, PodsForm::$field_type, $options ); ?> />
<?php
PodsForm::regex( PodsForm::$field_type, $options );
