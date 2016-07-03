<?php
$attributes             = array();
$attributes['type']     = 'text';
$attributes['value']    = $value;
$attributes['tabindex'] = 2;
$attributes             = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );
?>
<?php echo PodsForm::label( $name . '[lat]', 'Latitude' ); ?>
<?php echo PodsForm::field( $name . '[lat]', pods_v( 'lat', $value ), 'number' ) ?>

<?php echo PodsForm::label( $name . '[long]', 'Longitude' ); ?>
<?php echo PodsForm::field( $name . '[long]', pods_v( 'long', $value ), 'number' ) ?>
<?php
PodsForm::regex( PodsForm::$field_type, $options );