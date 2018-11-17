<?php
/**
 * @var string $form_field_type
 * @var array  $options
 * @var        $value
 */

wp_enqueue_script( 'jquery-ui-datepicker' );
wp_enqueue_script( 'jquery-ui-timepicker' );
wp_enqueue_style( 'pods-styles' );
wp_enqueue_style( 'jquery-ui-timepicker' );

PodsForm::field_method( 'time', 'enqueue_jquery_ui_i18n' );

$attributes = array();

$type = 'text';

if ( 1 == pods_var( $form_field_type . '_html5', $options ) ) {
	$type = $form_field_type;
}

$attributes['type']     = $type;
$attributes['tabindex'] = 2;

$format = PodsForm::field_method( 'time', 'format_time', $options );

$method = 'timepicker';

$args = array(
	'ampm'       => false,
	// Get selected JS time format.
	'timeFormat' => PodsForm::field_method( 'time', 'format_time', $options, true ),
);

if ( false !== stripos( $args['timeFormat'], 'tt' ) ) {
	$args['ampm'] = true;
}

$html5_format = 'H:i:s';

$date         = PodsForm::field_method( 'time', 'createFromFormat', $format, (string) $value );
$date_default = PodsForm::field_method( 'time', 'createFromFormat', 'H:i:s', (string) $value );

$formatted_date = $value;

if ( 1 == pods_var( $form_field_type . '_allow_empty', $options, 1 ) && in_array(
	$value, array(
		'',
		'0000-00-00',
		'0000-00-00 00:00:00',
		'00:00:00',
	), true
) ) {
	$formatted_date = '';
	$value          = '';
} elseif ( 'text' !== $type ) {
	$formatted_date = $value;

	if ( false !== $date ) {
		$value = $date->format( $html5_format );
	} elseif ( false !== $date_default ) {
		$value = $date_default->format( $html5_format );
	} elseif ( ! empty( $value ) ) {
		$value = date_i18n( $html5_format, strtotime( (string) $value ) );
	} else {
		$value = date_i18n( $html5_format );
	}
}

$args = apply_filters( 'pods_form_ui_field_time_args', $args, $type, $options, $attributes, $name, $form_field_type );

$attributes['value'] = $value;

$attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );

?>
<input<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?> />

<script>
	jQuery( function () {
		var $container = jQuery( '<div>' ).appendTo( 'body' ).addClass( 'pods-compat-container' );
		var beforeShow = {
			'beforeShow': function( textbox, instance) {
				jQuery( '#ui-datepicker-div' ).appendTo( $container );
			}
		};
		var <?php echo esc_js( pods_js_name( $attributes['id'] ) ); ?>_args = jQuery.extend( <?php echo json_encode( $args ); ?>, beforeShow );

		<?php
		if ( 'text' !== $type ) {
		?>
		if ( 'undefined' == typeof pods_test_time_field_<?php echo esc_js( $type ); ?> ) {
			// Test whether or not the browser supports date inputs
			function pods_test_time_field_<?php echo esc_js( $type ); ?> () {
				var input = jQuery( '<input/>', {
					'type' : '<?php echo esc_js( $type ); ?>', css : {
						position : 'absolute', display : 'none'
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

		if ( !pods_test_time_field_<?php echo esc_js( $type ); ?>() ) {
			jQuery( 'input#<?php echo esc_js( $attributes['id'] ); ?>' ).val( '<?php echo esc_js( $formatted_date ); ?>' );
			jQuery( 'input#<?php echo esc_js( $attributes['id'] ); ?>' ).<?php echo esc_js( $method ); ?>( <?php echo esc_js( pods_js_name( $attributes['id'] ) ); ?>_args );
		}
		<?php
		} else {
		?>
		jQuery( 'input#<?php echo esc_js( $attributes['id'] ); ?>' ).<?php echo esc_js( $method ); ?>( <?php echo esc_js( pods_js_name( $attributes['id'] ) ); ?>_args );
		<?php
		}//end if
		?>
	} );
</script>
