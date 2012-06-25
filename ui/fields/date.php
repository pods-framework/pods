<?php
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('jquery-ui-timepicker');
    wp_enqueue_style('jquery-ui');
    wp_enqueue_style('jquery-ui-timepicker');

    $attributes = array();
    $type = 'date';
    if ( isset( $options[ 'date_format_type' ] ) )
        $type = $options[ 'date_format_type' ];
    $attributes[ 'type' ] = $type;
    $attributes[ 'value' ] = $value;
    $attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );

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
?>
<input<?php PodsForm::attributes( $attributes, $name, PodsForm::$field_type, $options ); ?> />
<script>
    jQuery( function () {
        <?php
            $args = array(
                'timeFormat' => $time_format[ $options[ 'date_time_format' ] ],
                'dateFormat' => $date_format[ $options[ 'date_format' ] ]
            );
            if ( 'datetime' == $type ) {
                $method = 'datetimepicker';
                $args = array(
                    'timeFormat' => $time_format[ $options[ 'date_time_format' ] ],
                    'dateFormat' => $date_format[ $options[ 'date_format' ] ]
                );
            }
            elseif ( 'date' == $type ) {
                $method = 'datepicker';
                $args = array(
                    'dateFormat' => $date_format[ $options[ 'date_format' ] ]
                );
            }
            elseif ( 'time' == $type ) {
                $method = 'timepicker';
                $args = array(
                    'timeFormat' => $time_format[ $options[ 'date_time_format' ] ]
                );
            }

            $args = apply_filters( 'pods_form_ui_field_date_args', $args, $type, $options, $attributes, $name, PodsForm::$field_type );
        ?>
        var args = <?php echo json_encode($args); ?>;
        jQuery( 'input#<?php echo $attributes[ 'id' ]; ?>' ).<?php echo $method; ?>( args );
    } );
</script>
