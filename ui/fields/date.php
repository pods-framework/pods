<?php
/**
 * @var string $form_field_type
 * @var array $options
 * @var $value
 */

	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_style( 'pods-styles' );

	PodsForm::field_method( 'date', 'enqueue_jquery_ui_i18n' );

	$attributes = array();

	$type = 'text';

	if ( 1 == pods_var( $form_field_type . '_html5', $options ) )
		$type = $form_field_type;

	$attributes[ 'type' ] = $type;
	$attributes[ 'tabindex' ] = 2;

	$format = PodsForm::field_method( 'date', 'format', $options );

	$method = 'datepicker';

	$args = array(
		// Get selected JS date format.
		'dateFormat' => PodsForm::field_method( 'date', 'format', $options, true ),
		'changeMonth' => true,
		'changeYear' => true,
		'firstDay' => (int) get_option( 'start_of_week', 0 ),
	);

	$html5_format = 'Y-m-d';

	$date = PodsForm::field_method( 'date', 'createFromFormat', $format, (string) $value );
	$date_default = PodsForm::field_method( 'date', 'createFromFormat', 'Y-m-d', (string) $value );

	$formatted_date = $value;

	if ( 1 == pods_var( $form_field_type . '_allow_empty', $options, 1 ) && in_array( $value, array( '', '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) )
		$formatted_date = $value = '';
	elseif ( 'text' != $type ) {
		$formatted_date = $value;

		if ( false !== $date )
			$value = $date->format( $html5_format );
		elseif ( false !== $date_default )
			$value = $date_default->format( $html5_format );
		elseif ( !empty( $value ) )
			$value = date_i18n( $html5_format, strtotime( (string) $value ) );
		else
			$value = date_i18n( $html5_format );
	}

	$args = apply_filters( 'pods_form_ui_field_date_args', $args, $type, $options, $attributes, $name, $form_field_type );

	$attributes[ 'value' ] = $value;

	$attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );
?>
<input<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?> />

<script>
	jQuery( function () {
		var <?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>_args = <?php echo json_encode( $args ); ?>;

		<?php
			if ( 'text' != $type ) {
		?>
			if ( 'undefined' == typeof pods_test_date_field_<?php echo esc_js( $type ); ?> ) {
				// Test whether or not the browser supports date inputs
				function pods_test_date_field_<?php echo esc_js( $type ); ?> () {
					var input = jQuery( '<input/>', {
						'type' : '<?php echo esc_js( $type ); ?>',
						css : {
							position : 'absolute',
							display : 'none'
						}
					} );

					jQuery( 'body' ).append( input );

					var bool = input.prop( 'type' ) !== 'text';

					if ( bool ) {
						var smile = ":)";
						input.val( smile );

						return (input.val() != smile);
					}
				}
			}

			if ( !pods_test_date_field_<?php echo esc_js( $type ); ?>() ) {
				jQuery( 'input#<?php echo esc_js( $attributes[ 'id' ] ); ?>' ).val( '<?php echo esc_js( $formatted_date ); ?>' );
				jQuery( 'input#<?php echo esc_js( $attributes[ 'id' ] ); ?>' ).<?php echo esc_js( $method ); ?>( <?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>_args );
			}
		<?php
			}
			else {
		?>
			jQuery( 'input#<?php echo esc_js( $attributes[ 'id' ] ); ?>' ).<?php echo esc_js( $method ); ?>( <?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>_args );
		<?php
			}
		?>
	} );
</script>
