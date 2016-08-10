<?php
$attributes             = array();
$attributes['type']     = 'text';
$attributes['value']    = $value;
$attributes['tabindex'] = 2;
$attributes             = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );
if ( isset( $value['geo'] ) ) {
	$value = $value['geo'];
}
?>
<?php echo PodsForm::label( $name . '[geo][lat]', __( 'Latitude', 'pods' ) ); ?>
<?php echo PodsForm::field( $name . '[geo][lat]', pods_v( 'lat', $value ), 'number', array( 'number_decimals' => 10, 'number_format' => '9999.99' ) ) ?>

<?php echo PodsForm::label( $name . '[geo][lng]', __( 'Longitude', 'pods' ) ); ?>
<?php echo PodsForm::field( $name . '[geo][lng]', pods_v( 'lng', $value ), 'number', array( 'number_decimals' => 10, 'number_format' => '9999.99' ) ) ?>
<?php
PodsForm::regex( PodsForm::$field_type, $options );