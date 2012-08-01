<?php
    $date_format = array(
        'mdy' => 'mm/dd/yy',
        'dmy' => 'dd/mm/yy',
        'dmy_dash' => 'dd-mm-yy',
        'dmy_dot' => 'dd.mm.yy',
        'ymd_slash' => 'yy/mm/dd',
        'ymd_dash' => 'yy-mm-dd',
        'ymd_dot' => 'yy.mm.dd'
    );
    $time_format = array(
        'h_mm_A' => 'h:mm TT',
        'hh_mm_A' => 'hh:mm TT',
        'h_mma' => 'h:mmtt',
        'hh_mma' => 'hh:mmtt',
        'h_mm' => 'h:mm',
        'hh_mm' => 'hh:mm'
    );

    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('jquery-ui-timepicker');
    wp_enqueue_style('jquery-ui');
    wp_enqueue_style('jquery-ui-timepicker');

    $attributes = array();

    $type = 'date';

    if ( isset( $options[ 'date_format_type' ] ) )
        $type = $options[ 'date_format_type' ];

    $attributes[ 'type' ] = $type;

    $args = array(
        'timeFormat' => $time_format[ $options[ 'date_time_format' ] ],
        'dateFormat' => $date_format[ $options[ 'date_format' ] ]
    );

    $format = PodsForm::field_method( 'date', 'format', $options );

    if ( 'datetime' == $type ) {
        $method = 'datetimepicker';

        $args = array(
            'timeFormat' => $time_format[ $options[ 'date_time_format' ] ],
            'dateFormat' => $date_format[ $options[ 'date_format' ] ]
        );

        if ( false !== stripos( $args[ 'timeFormat' ], 'tt' ) )
            $args[ 'ampm' ] = true;

        $html5_format = 'Y-m-d\TH:i:s';
    }
    elseif ( 'date' == $type ) {
        $method = 'datepicker';

        $args = array(
            'dateFormat' => $date_format[ $options[ 'date_format' ] ]
        );

        $html5_format = 'Y-m-d';
    }
    elseif ( 'time' == $type ) {
        $method = 'timepicker';

        $args = array(
            'timeFormat' => $time_format[ $options[ 'date_time_format' ] ]
        );

        if ( false !== stripos( $args[ 'timeFormat' ], 'tt' ) )
            $args[ 'ampm' ] = true;

        $html5_format = '\TH:i:s';
    }

    $date = DateTime::createFromFormat( $format, (string) $value );
    $date_default = DateTime::createFromFormat( 'Y-m-d H:i:s', (string) $value );

    $formatted_date = $value;

    if ( false !== $date )
        $value = $date->format( $html5_format );
    elseif ( false !== $date_default )
        $value = $date_default->format( $html5_format );
    elseif ( !empty( $value ) )
        $value = date_i18n( $html5_format, strtotime( (string) $value ) );
    else
        $value = date_i18n( $html5_format );

    $args = apply_filters( 'pods_form_ui_field_date_args', $args, $type, $options, $attributes, $name, PodsForm::$field_type );

    $attributes[ 'value' ] = $value;

    $attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );
?>
<input<?php PodsForm::attributes( $attributes, $name, PodsForm::$field_type, $options ); ?> />
<script>
    jQuery( function () {
        var <?php echo pods_clean_name( $attributes[ 'id' ] ); ?>_args = <?php echo json_encode($args); ?>;

        if ( 'undefined' == typeof pods_test_date_field_<?php echo $type; ?> ) {
        // Test whether or not the browser supports date inputs
            function pods_test_date_field_<?php echo $type; ?> () {
                var input = jQuery( '<input/>', {
                    'type' : '<?php echo $type; ?>',
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

        if ( !pods_test_date_field_<?php echo $type; ?>() ) {
            jQuery( 'input#<?php echo $attributes[ 'id' ]; ?>' ).val( '<?php echo $formatted_date; ?>' );
            jQuery( 'input#<?php echo $attributes[ 'id' ]; ?>' ).<?php echo $method; ?>( <?php echo pods_clean_name( $attributes[ 'id' ] ); ?>_args );
        }
    } );
</script>