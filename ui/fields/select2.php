<?php
    wp_enqueue_style( 'pods-select2' );
wp_enqueue_script( 'pods-select2' );

    $attributes = array();
    $attributes['type'] = 'hidden';
    $attributes['value'] = $value;
    $attributes['data-field-type'] = 'select2';
    $attributes['style'] = 'width: 200px;';
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
            return item.title;
        }

        function <?php echo pods_clean_name( $attributes[ 'id' ] ); ?>_podsFormatSelection ( item ) {
            return item.title;
        }

        jQuery( '#<?php echo $attributes[ 'id' ]; ?>' ).select2( {
            placeholder : {
                title : 'Start Typing...',
                id : ''
            },
            minimumInputLength : 1,
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
        } );
    } );
</script>