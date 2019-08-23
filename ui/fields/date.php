<?php
/**
 * @var string $form_field_type
 * @var array  $options
 * @var        $value
 */

wp_enqueue_script( 'jquery-ui-datepicker' );
wp_enqueue_style( 'pods-styles' );

PodsForm::field_method( 'date', 'enqueue_jquery_ui_i18n' );

$attributes = array();

$type = 'text';

if ( 1 == pods_var( $form_field_type . '_html5', $options ) ) {
	$type = $form_field_type;
}

$attributes['type']     = $type;
$attributes['tabindex'] = 2;

$format = PodsForm::field_method( 'date', 'format_date', $options );

$method = 'datepicker';

$mysql_format = 'Y-m-d';

$args = array(
	// Get selected JS date format.
	'dateFormat'  => PodsForm::field_method( 'date', 'format_date', $options, true ),
	'altFormat'   => PodsForm::field_method( 'date', 'convert_format', $mysql_format ),
	'altField'    => '', // Done after merging attributes.
	'changeMonth' => true,
	'changeYear'  => true,
	'firstDay'    => (int) get_option( 'start_of_week', 0 ),
);

$date         = PodsForm::field_method( 'date', 'createFromFormat', $format, (string) $value );
$date_default = PodsForm::field_method( 'date', 'createFromFormat', 'Y-m-d', (string) $value );

$formatted_value = $value;
$mysql_value     = $value;

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

	if ( 'text' !== $type ) {
		// HTML5 uses mysql date format.
		$value = $mysql_value;
	}
}

$args = apply_filters( 'pods_form_ui_field_date_args', $args, $type, $options, $attributes, $name, $form_field_type );

$attributes['value'] = $mysql_value;

$attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );

$ui_attributes = $attributes;
$ui_attributes['value'] = $value;
$ui_attributes['name']  = $name . '__ui';
$ui_attributes['id']   .= '__ui';

$attributes['type']  = 'hidden';

$args['altField'] = 'input#' . esc_js( $attributes['id'] );
?>
<input<?php PodsForm::attributes( $ui_attributes, $name . '__ui', $form_field_type, $options ); ?> />
<input<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?> />

<script>
	jQuery( function () {
		var $container = jQuery( '<div>' ).appendTo( 'body' ).addClass( 'pods-compat-container' );
		var $element   = jQuery( 'input#<?php echo esc_js( $attributes['id'] ); ?>__ui' );
		var beforeShow = {
			'beforeShow': function( textbox, instance) {
				jQuery( '#ui-datepicker-div' ).appendTo( $container );
			}
		};

		var <?php echo esc_js( pods_js_name( $attributes['id'] ) ); ?>_args = jQuery.extend( <?php echo json_encode( $args ); ?>, beforeShow );

		<?php
		if ( 'text' !== $type ) {
		?>
		if ( 'undefined' == typeof pods_test_date_field_<?php echo esc_js( $type ); ?> ) {
			// Test whether or not the browser supports date inputs
			function pods_test_date_field_<?php echo esc_js( $type ); ?> () {
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

		if ( !pods_test_date_field_<?php echo esc_js( $type ); ?>() ) {
			$element.val( '<?php echo esc_js( $formatted_date ); ?>' );
			$element.<?php echo esc_js( $method ); ?>( <?php echo esc_js( pods_js_name( $attributes['id'] ) ); ?>_args );
		}
		<?php
		} else {
		?>
		$element.<?php echo esc_js( $method ); ?>( <?php echo esc_js( pods_js_name( $attributes['id'] ) ); ?>_args );
		<?php
		}//end if
		?>
	} );
</script>
