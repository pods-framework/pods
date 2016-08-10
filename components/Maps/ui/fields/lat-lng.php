<?php
$attributes             = array();
$attributes['type']     = 'text';
$attributes['value']    = $value;
$attributes['tabindex'] = 2;
$attributes             = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );
?>
<?php echo PodsForm::label( $name . '[lat]', __( 'Latitude', 'pods' ) ); ?>
<?php echo PodsForm::field( $name . '[lat]', pods_v( 'lat', $value ), 'number', array( 'number_decimals' => 10, 'number_format' => '9999.99' ) ) ?>

<?php echo PodsForm::label( $name . '[lng]', __( 'Longitude', 'pods' ) ); ?>
<?php echo PodsForm::field( $name . '[lng]', pods_v( 'lng', $value ), 'number', array( 'number_decimals' => 10, 'number_format' => '9999.99' ) ) ?>
<?php
PodsForm::regex( PodsForm::$field_type, $options );