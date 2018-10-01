<?php
$attributes = array();

$type = 'text';

if ( 1 == pods_var( 'phone_html5', $options ) ) {
	$type = 'tel';
}

$attributes['type']     = $type;
$attributes['value']    = $value;
$attributes['tabindex'] = 2;
$attributes             = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );
?>
	<input<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?> />
<?php
PodsForm::regex( $form_field_type, $options );
