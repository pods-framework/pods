<?php
$attributes = array();

$type = 'text';

if ( pods_v( 'website_html5', $options, false ) && ! in_array( pods_v( 'website_format', $options ), array( 'no-http', 'no-http-no-www', 'no-http-force-www' ) ) ) {
	$type = 'url';
}

$attributes[ 'type' ] = $type;
$attributes[ 'value' ] = $value;
$attributes[ 'tabindex' ] = 2;
$attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );
?>
<input<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?> />
<?php
PodsForm::regex( $form_field_type, $options );
