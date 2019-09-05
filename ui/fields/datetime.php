<?php
/**
 * @var string $form_field_type
 * @var array  $options
 * @var        $value
 */

$use_time = ( 'time' === $form_field_type || 'datetime' === $form_field_type );
$use_date = ( 'date' === $form_field_type || 'datetime' === $form_field_type );

wp_enqueue_script( 'jquery-ui-datepicker' );
wp_enqueue_style( 'pods-styles' );

if ( $use_time ) {
	wp_enqueue_script( 'jquery-ui-timepicker' );
	wp_enqueue_style( 'jquery-ui-timepicker' );
}

PodsForm::field_method( $form_field_type, 'enqueue_jquery_ui_i18n' );

$attributes = array();

$html5 = false;
$type  = 'text';

if ( pods_v( $form_field_type . '_html5', $options, false ) ) {
	$html5 = true;
	$type  = $form_field_type;
}

$attributes['type']     = $type;
$attributes['tabindex'] = 2;

$format = PodsForm::field_method( $form_field_type, 'format_' . $form_field_type, $options );

$method = $form_field_type . 'picker';

$mysql_date_format = 'Y-m-d';
$mysql_time_format = 'H:i:s';

$args = array(
	'altField'         => '', // Done with JS.
	'altFieldTimeOnly' => false,
);

if ( $use_date ) {
	$args['dateFormat']  = PodsForm::field_method( $form_field_type, 'format_date', $options, true );
	$args['altFormat']   = PodsForm::field_method( $form_field_type, 'convert_format', $mysql_date_format );
	$args['changeMonth'] = true;
	$args['changeYear']  = true;
	$args['firstDay']    = (int) get_option( 'start_of_week', 0 );

	$year_range = pods_v( $form_field_type . '_year_range_custom', $options, '' );
	if ( $year_range ) {
		$args['yearRange'] = $year_range;
	}
}
if ( $use_time ) {
	$args['timeFormat']    = PodsForm::field_method( $form_field_type, 'format_time', $options, true );
	$args['altTimeFormat'] = PodsForm::field_method( $form_field_type, 'convert_format', $mysql_time_format );
	$args['ampm']          = ( false !== stripos( $args['timeFormat'], 'tt' ) );
}

$mysql_format = '';

switch ( $form_field_type ) {
	case 'datetime':
		$mysql_format = $mysql_date_format . ' ' . $mysql_time_format;

		$format_value = pods_v( $form_field_type . '_format', $options, 'mdy', true );

		if ( 'format' === pods_v( $form_field_type . '_type', $options, 'format', true ) && 'c' === $format_value ) {
			$args['ampm']       = false;
			$args['separator']  = 'T';
			$args['timeFormat'] = 'HH:mm:ssz';

			// $args[ 'showTimezone' ] = true;
			$timezone  = (int) get_option( 'gmt_offset' );
			$timezone *= 60;

			if ( 0 <= $timezone ) {
				$timezone = '+' . (string) $timezone;
			}

			$args['timezone'] = (string) $timezone;
		}

		break;
	case 'date':
		$mysql_format = $mysql_date_format;
		break;
	case 'time':
		$mysql_format = $mysql_time_format;
		break;
}

$date         = PodsForm::field_method( $form_field_type, 'createFromFormat', $format, (string) $value );
$date_default = PodsForm::field_method( $form_field_type, 'createFromFormat', $mysql_format, (string) $value );

$formatted_value = $value;
$mysql_value     = $value;

$empty_values = array(
	'',
	'0000-00-00',
	'0000-00-00 00:00:00',
	'00:00:00',
);

if (
	pods_v( $form_field_type . '_allow_empty', $options, true )
	&& in_array( $value, $empty_values, true )
) {
	$formatted_value = '';
	$value           = '';
} else {

	if ( false !== $date ) {
		$mysql_value = $date->format( $mysql_format );
	} elseif ( false !== $date_default ) {
		$mysql_value = $date_default->format( $mysql_format );
	} elseif ( ! empty( $value ) ) {
		$mysql_value = date_i18n( $mysql_format, strtotime( (string) $value ) );
	} else {
		$mysql_value = date_i18n( $mysql_format );
	}

	if ( $html5 ) {
		// HTML5 uses mysql date format.
		$value = $mysql_value;
	}
}

$args = apply_filters( 'pods_form_ui_field_' . $form_field_type . '_args', $args, $type, $options, $attributes, $name, $form_field_type );

$attributes['value'] = $value;

if ( $html5 && 'datetime' === $type ) {
	// Fix deprecated `datetime` input type.
	$type               = 'datetime-local';
	$attributes['type'] = 'datetime-local';
}

$attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );
?>
<input<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?> />

<script>
	jQuery( function () {
		var $container = jQuery( '<div>' ).appendTo( 'body' ).addClass( 'pods-compat-container' ),
			$element   = jQuery( 'input#<?php echo esc_js( $attributes['id'] ); ?>' ),
			beforeShow = {
				'beforeShow': function( textbox, instance) {
					jQuery( '#ui-datepicker-div' ).appendTo( $container );
				}
			},
			args = jQuery.extend( <?php echo json_encode( $args ); ?>, beforeShow );

		<?php
		if ( 'text' !== $type ) {
		?>
		// Test whether or not the browser supports date inputs
		function podsCheckHtml5 () {
			var input = document.createElement('input');
			input.setAttribute( 'type', '<?php echo $type; ?>' );

			var notADateValue = 'not-a-date';
			input.setAttribute('value', notADateValue);

			return (input.value !== notADateValue);
		}

		if ( ! podsCheckHtml5() ) {
			args = altField( args, $element );
			$element.val( '<?php echo esc_js( $formatted_value ); ?>' );
			$element.<?php echo esc_js( $method ); ?>( args );
		}
		<?php
		} else {
		?>
		args = altField( args, $element );
		$element.<?php echo esc_js( $method ); ?>( args );
		<?php
		}//end if
		?>
		function altField( args, el ) {
			var $el  = $( el ),
				$alt = $el.clone();

			$alt.attr( 'type', 'hidden' );
			$alt.val( '<?php echo esc_attr( $mysql_value ) ?>' );
			$el.after( $alt );

			$el.attr( 'name', $el.attr( 'name' ) + '__ui' );
			$el.attr( 'id', $el.attr( 'id' ) + '__ui' );

			args.altField = 'input#' + $alt.attr( 'id' );

			return args;
		};
	} );
</script>
