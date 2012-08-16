<?php
    wp_enqueue_style( 'pods-select2' );
    wp_enqueue_script( 'pods-select2' );

    $attributes = array();
    $attributes['type'] = 'hidden';
    $attributes['value'] = $value;
    $attributes['data-field-type'] = 'select2';
    $attributes['style'] = 'max-width: 80%;';
    $attributes = PodsForm::merge_attributes($attributes, $name, PodsForm::$field_type, $options);
?>
<input<?php PodsForm::attributes($attributes, $name, PodsForm::$field_type, $options); ?> />

<script type="text/javascript">
    jQuery( function () {
        if ( typeof pods_ajaxurl === "undefined" ) {
            var pods_ajaxurl = "<?php echo admin_url( 'admin-ajax.php?pods_ajax=1' ); ?>";
        }

        if ( typeof pods_select2_nonce === "undefined" ) {
            var pods_select2_nonce = "<?php echo wp_create_nonce( 'pods-select2_ajax' ); ?>";
        }

        function <?php echo pods_clean_name( $attributes[ 'id' ] ); ?>_podsFormatResult ( item ) {
            return item.text;
        }

        function <?php echo pods_clean_name( $attributes[ 'id' ] ); ?>_podsFormatSelection ( item ) {
            return item.text;
        }

        jQuery( '#<?php echo $attributes[ 'id' ]; ?>' ).select2( {
            minimumInputLength : 1,
            <?php
               if ( 1 != (int) pods_var( 'required', $options ) ) {
            ?>
                allowClear : true,
            <?php
               }
            ?>
            <?php
                $pick_limit = (int) pods_var( 'pick_limit', $options, 0 );

                if ( 'multi' == pods_var( 'pick_format_type', $options ) && 1 != $pick_limit ) {
            ?>
                placeholder : '<?php echo esc_js( __( 'Start Typing...', 'pods' ) ); ?>',
                multiple : true,
                maximumSelectionSize : <?php echo (int) $pick_limit; ?>,
            <?php
                }
                else {
            ?>
                placeholder : '<?php echo esc_js( __( 'Start Typing...', 'pods' ) ); ?>',
            <?php
                }

                if ( !empty( $options[ 'data' ] ) ) {
            ?>
                data : [
                    <?php
                        $data_items = array();

                        foreach ( $options[ 'data' ] as $item_id => $item ) {
                            $data_items[] = '{id : \'' . esc_js( $item_id ) . '\', text: \'' . esc_js( $item ) . '\'}';
                        }

                        echo implode( ",\n", $data_items );
                    ?>
                ]
            <?php
                }
                else {
            ?>
                ajax : {
                    url : pods_ajaxurl,
                    type : 'POST',
                    dataType : 'json',
                    data : function ( term, page ) {
                        return {
                            _wpnonce : pods_select2_nonce,
                            action : 'pods_admin',
                            method : 'select2_ajax',
                            query : term
                        };
                    },
                    results : function ( data, page ) {
                        return data;
                    }
                },
                formatResult : <?php echo pods_clean_name( $attributes[ 'id' ] ); ?>_podsFormatResult,
                formatSelection : <?php echo pods_clean_name( $attributes[ 'id' ] ); ?>_podsFormatSelection
            <?php
                }
            ?>
        } );
    } );
</script>