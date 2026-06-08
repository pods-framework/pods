<?php
$attributes             = array();
$attributes['type']     = 'text';
$attributes['value']    = $value;
$attributes['tabindex'] = 2;
$attributes             = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );

if ( empty( $type ) ) {
	$type = pods_v( 'address_type', $options, 'address' );
}

$geo = array();

if ( isset( $value['geo'] ) && is_array( $value['geo'] ) ) {
	$geo = $value['geo'];
}

if ( 'text' === $type ) {
	if ( isset( $value['text'] ) ) {
		$value = $value['text'];
	}

	echo PodsForm::field( $name . '[text]', pods_sanitize( $value ), 'text', $options );
} elseif ( 'address' === $type ) {
	if ( isset( $value['address'] ) ) {
		$value = $value['address'];
	}
	?>
	<?php if ( pods_v( $form_field_type . '_address_line_1', $options ) ) : ?>
		<?php echo PodsForm::label( $name . '[address][line_1]', __( 'Address Line 1', 'pods' ) ); ?>
		<?php echo PodsForm::field( $name . '[address][line_1]', pods_v( 'line_1', $value ), 'text', $options ); ?>
	<?php endif; ?>

	<?php if ( pods_v( $form_field_type . '_address_line_2', $options ) ) : ?>
		<?php echo PodsForm::label( $name . '[address][line_2]', __( 'Address Line 2', 'pods' ) ); ?>
		<?php echo PodsForm::field( $name . '[address][line_2]', pods_v( 'line_2', $value ), 'text', $options ); ?>
	<?php endif; ?>

	<?php if ( pods_v( $form_field_type . '_address_city', $options ) ) : ?>
		<?php echo PodsForm::label( $name . '[address][city]', __( 'City', 'pods' ) ); ?>
		<?php echo PodsForm::field( $name . '[address][city]', pods_v( 'city', $value ), 'text', $options ); ?>
	<?php endif; ?>

	<?php if ( pods_v( $form_field_type . '_address_postal_code', $options ) ) : ?>
		<?php echo PodsForm::label( $name . '[address][postal_code]', __( 'ZIP / Postal Code', 'pods' ) ); ?>
		<?php echo PodsForm::field( $name . '[address][postal_code]', pods_v( 'postal_code', $value ), 'text', $options ); ?>
	<?php endif; ?>

	<?php if ( pods_v( $form_field_type . '_address_region', $options ) ) : ?>
		<?php echo PodsForm::label( $name . '[address][region]', __( 'State / Province', 'pods' ) ); ?>
		<?php if ( 'pick' === pods_v( $form_field_type . '_address_region_input', $options ) ) : ?>
			<?php echo PodsForm::field( $name . '[address][region]', pods_v( 'region', $value ), 'pick', array( 'pick_object' => 'us_state' ) ); ?>
		<?php else : ?>
			<?php echo PodsForm::field( $name . '[address][region]', pods_v( 'region', $value ), 'text', $options ); ?>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( pods_v( $form_field_type . '_address_country', $options ) ) : ?>
		<?php echo PodsForm::label( $name . '[address][country]', __( 'Country', 'pods' ) ); ?>
		<?php if ( 'pick' === pods_v( $form_field_type . '_address_country_input', $options ) ) : ?>
			<?php echo PodsForm::field( $name . '[address][country]', pods_v( 'country', $value ), 'pick', array( 'pick_object' => 'country' ) ); ?>
		<?php else : ?>
			<?php echo PodsForm::field( $name . '[address][country]', pods_v( 'country', $value ), 'text', $options ); ?>
		<?php endif; ?>
	<?php endif; ?>
	<?php
}

if ( pods_v( $form_field_type . '_address_geo', $options ) ) : ?>
	<?php echo PodsForm::label( $name . '[geo][lat]', __( 'Latitude', 'pods' ) ); ?>
	<?php echo PodsForm::field( $name . '[geo][lat]', pods_v( 'lat', $geo ), 'number', array( 'number_decimals' => 10, 'number_format' => '9999.99' ) ); ?>

	<?php echo PodsForm::label( $name . '[geo][lng]', __( 'Longitude', 'pods' ) ); ?>
	<?php echo PodsForm::field( $name . '[geo][lng]', pods_v( 'lng', $geo, pods_v( 'long', $geo ) ), 'number', array( 'number_decimals' => 10, 'number_format' => '9999.99' ) ); ?>
<?php endif;

PodsForm::regex( $form_field_type, $options );