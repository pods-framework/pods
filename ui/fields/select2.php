<?php
wp_enqueue_style( 'pods-select2' );
wp_enqueue_script( 'pods-select2' );

if ( is_array( $value ) )
    $value = implode( ',', $value );

$attributes = array();
$attributes[ 'type' ] = 'hidden';
$attributes[ 'value' ] = $value;
$attributes[ 'data-field-type' ] = 'select2';
$attributes[ 'tabindex' ] = 2;
$attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );
$attributes[ 'class' ] .= ' pods-form-ui-field-type-select2';

$uri_hash = wp_create_nonce( 'pods_uri_' . $_SERVER[ 'REQUEST_URI' ] );

$uid = @session_id();

if ( is_user_logged_in() )
    $uid = 'user_' . get_current_user_id();

$field_nonce = wp_create_nonce( 'pods_relationship_' . ( !is_object( $pod ) ? '0' : $pod->pod_id ) . '_' . $uid . '_' . $uri_hash . '_' . $options[ 'id' ] );

$pick_limit = (int) pods_var( 'pick_limit', $options, 0 );

if ( 'multi' == pods_var( 'pick_format_type', $options ) && 1 != $pick_limit )
    wp_enqueue_script( 'jquery-ui-sortable' );

$options[ 'data' ] = (array) pods_var_raw( 'data', $options, array(), null, true );
?>
<div class="pods-select2">
    <input<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?> />
</div>

<script type="text/javascript">
    jQuery( function ( $ ) {
        if ( 'undefined' == typeof ajaxurl ) {
            var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
        }

        function <?php echo pods_clean_name( $attributes[ 'id' ] ); ?>_podsFormatResult ( item ) {
            return item.text;
        }

        function <?php echo pods_clean_name( $attributes[ 'id' ] ); ?>_podsFormatSelection ( item ) {
            return item.text;
        }

        var <?php echo pods_clean_name( $attributes[ 'id' ] ); ?>_data = {<?php
                if ( !is_object( $pod ) || !empty( $options[ 'data' ] ) ) {
                    $data = array();

                    foreach ( $options[ 'data' ] as $item_id => $item ) {
                        $data[] = '\'' . esc_js( $item_id ) . '\' : {id : \'' . esc_js( $item_id ) . '\', text: \'' . esc_js( $item ) . '\'}';
                    }

                    echo implode( ",\n", $data );
                }
            ?>};

        var $element = $('#<?php echo $attributes[ 'id' ] ?>' );

        $element.select2( {
            width : 'resolve',
            initSelection : function ( element, callback ) {
                var data = [];

                jQuery( element.val().split( "," ) ).each( function () {
                    if ( 'undefined' != typeof <?php echo pods_clean_name( $attributes[ 'id' ] ); ?>_data[ this ] ) {
                        data.push( {
                            id : this,
                            text : <?php echo pods_clean_name( $attributes[ 'id' ] ); ?>_data[ this ].text
                        } );
                    }
                } );

                <?php
                    if ( 'multi' == pods_var( 'pick_format_type', $options ) && 1 != $pick_limit ) {
                ?>
                    callback( data );
                <?php
                    }
                    else {
                ?>
                    if ( 0 < data.length )
                        callback( data[ 0 ] );
                <?php
                    }
                ?>
            },
            <?php
               if ( 1 != (int) pods_var( 'required', $options ) ) {
            ?>
                allowClear : true,
            <?php
               }

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

                if ( !is_object( $pod ) || !empty( $options[ 'data' ] ) ) {
            ?>
                data : [
                    <?php
                        $data_items = array();

                        foreach ( $options[ 'data' ] as $item_id => $item ) {
                            $data_items[] = '{id : \'' . esc_js( $item_id ) . '\', text: \'' . esc_js( $item ) . '\'}';
                        }

                        echo implode( ",\n", $data_items );
                    ?>
                ],
            <?php
                }

                if ( empty( $options[ 'data' ] ) || ( isset( $ajax ) && $ajax ) ) {
            ?>
                ajax : {
                    url : ajaxurl + '?pods_ajax=1',
                    type : 'POST',
                    dataType : 'json',
                    data : function ( term, page ) {
                        return {
                            _wpnonce : '<?php echo $field_nonce; ?>',
                            action : 'pods_relationship',
                            method : 'select2',
                            pod : '<?php echo (int) $pod->pod_id; ?>',
                            field : '<?php echo (int) $options[ 'id' ]; ?>',
                            uri : '<?php echo $uri_hash; ?>',
                            id : '<?php echo (int) $id; ?>',
                            query : term<?php
                                global $sitepress, $icl_adjust_id_url_filter_off;

                                if ( is_object( $sitepress ) && !$icl_adjust_id_url_filter_off ) {
                            ?>,
                                lang : '<?php echo ICL_LANGUAGE_CODE; ?>'
                            <?php
                                }
                            ?>
                        };
                    },
                    results : function ( data, page ) {
                        return data;
                    }
                },
                formatResult : <?php echo pods_clean_name( $attributes[ 'id' ] ); ?>_podsFormatResult,
                formatSelection : <?php echo pods_clean_name( $attributes[ 'id' ] ); ?>_podsFormatSelection,
                minimumInputLength : 1
            <?php
                }
            ?>
        } );

        <?php if ( 'multi' == pods_var( 'pick_format_type', $options ) && 1 != $pick_limit ) { ?>
            $element.select2( 'container' ).find( 'ul.select2-choices' ).sortable( {
                containment: 'parent',
                start: function() { $element.select2( 'onSortStart' ); },
                update: function() { $element.select2( 'onSortEnd' ); }
            } );
        <?php } ?>
    } );
</script>