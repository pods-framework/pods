<?php
$attributes             = array();
$attributes['type']     = 'text';
$attributes['value']    = $value;
$attributes['tabindex'] = 2;
$attributes             = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );

if ( $type == 'text' ) {

	if ( isset( $value['text'] ) ) {
		$value = $value['text'];
	}

	//echo PodsForm::label( $name . '[text]', __( 'Freeform Address', 'pods' ) );
	echo PodsForm::field( $name . '[text]', pods_sanitize( $value ), 'text', $options );

} elseif ( $type == 'address' ) {

	if ( isset( $value['address'] ) ) {
		$value = $value['address'];
	}
?>
	<?php if ( pods_v( $form_field_type . '_address_line_1', $options ) ): ?>
		<?php echo PodsForm::label( $name . '[address][line_1]', __( 'Address Line 1', 'pods' ) ) ?>
		<?php echo PodsForm::field( $name . '[address][line_1]', pods_v( 'line_1', $value ), 'text', $options ) ?>
	<?php endif; ?>

	<?php if ( pods_v( $form_field_type . '_address_line_2', $options ) ): ?>
		<?php echo PodsForm::label( $name . '[address][line_2]', __( 'Address Line 2', 'pods' ) ) ?>
		<?php echo PodsForm::field( $name . '[address][line_2]', pods_v( 'line_2', $value ), 'text', $options ) ?>
	<?php endif; ?>

	<?php if ( pods_v( $form_field_type . '_address_city', $options ) ): ?>
		<?php echo PodsForm::label( $name . '[address][city]', __( 'City', 'pods' ) ) ?>
		<?php echo PodsForm::field( $name . '[address][city]', pods_v( 'city', $value ), 'text', $options ) ?>
	<?php endif; ?>

	<?php if ( pods_v( $form_field_type . '_address_postal_code', $options ) ): ?>
		<?php echo PodsForm::label( $name . '[address][postal_code]', __( 'ZIP / Postal Code', 'pods' ) ) ?>
		<?php echo PodsForm::field( $name . '[address][postal_code]', pods_v( 'postal_code', $value ), 'text', $options ) ?>
	<?php endif; ?>

	<?php if ( pods_v( $form_field_type . '_address_region', $options ) ): ?>
		<?php echo PodsForm::label( $name . '[address][region]', __( 'State / Province', 'pods' ) ) ?>
		<?php if ( 'pick' == pods_v( $form_field_type . '_address_region_input', $options ) ): ?>
			<?php echo PodsForm::field( $name . '[address][region]', pods_v( 'region', $value ), 'pick', array( 'pick_object' => 'us_state' ) ) ?>
		<?php else: ?>
			<?php echo PodsForm::field( $name . '[address][region]', pods_v( 'region', $value ), 'text', $options ) ?>
		<?php endif ?>
	<?php endif; ?>

	<?php if ( pods_v( $form_field_type . '_address_country', $options ) ): ?>
		<?php echo PodsForm::label( $name . '[address][country]', __( 'Country', 'pods' ) ) ?>

		<?php if ( 'pick' == pods_v( $form_field_type . '_address_country_input', $options ) ): ?>
			<?php echo PodsForm::field( $name . '[address][country]', pods_v( 'country', $value ), 'pick', array( 'pick_object' => 'country' ) ) ?>
		<?php else: ?>
			<?php echo PodsForm::field( $name . '[address][country]', pods_v( 'country', $value ), 'text', $options ) ?>
		<?php endif ?>
	<?php endif; ?>
<?php
}
PodsForm::regex( $form_field_type, $options );