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

$pick_limit = (int) pods_var( $form_field_type . '_limit', $options, 0 );

if ( 'multi' == pods_var( $form_field_type . '_format_type', $options ) && 1 != $pick_limit )
    wp_enqueue_script( 'jquery-ui-sortable' );

$options[ 'data' ] = (array) pods_var_raw( 'data', $options, array(), null, true );
?>
<div class="pods-select2">
    <input<?php PodsForm::attributes( $attributes, $name, $form_field_type, $options ); ?> />
</div>

<script type="text/javascript">
    jQuery( function ( $ ) {
        if ( 'undefined' == typeof ajaxurl ) {
            var ajaxurl = '<?php echo pods_slash( admin_url( 'admin-ajax.php' ) ); ?>';
        }

        function <?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>_podsFormatResult ( item ) {
            return item.text;
        }

        function <?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>_podsFormatSelection ( item ) {
            return item.text;
        }

        var <?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>_data = {<?php
                if ( !is_object( $pod ) || !empty( $options[ 'data' ] ) ) {
                    $data = array();

                    foreach ( $options[ 'data' ] as $item_id => $item ) {
                        $data[] = '\'' . esc_js( $item_id ) . '\' : {id : \'' . esc_js( $item_id ) . '\', text: \'' . str_replace( '&amp;', '&', esc_js( $item ) ) . '\'}';
                    }

                    echo implode( ",\n", $data );
                }
            ?>};

        var $element = $('#<?php echo esc_js( $attributes[ 'id' ] ); ?>' );

        $element.select2( {
			<?php
				if ( 1 == pods_v( $form_field_type . '_taggable', $options ) ) {
			?>
				tags : true,
				createSearchChoice : function( term, data ) {
					var $dropdown;

					// Get a reference to the dropdown container
					// the dropdown method is not available before v3.4.1
					try {
						$dropdown = $element.select2( 'dropdown' );
					}
					catch ( e ) {
						$dropdown = $( '.select2-drop-active' );
					}

					// Only show the dropdown if there is at least one unselected potential match
					$dropdown.hide();

					// Any potential matches?
					if ( !$.isEmptyObject( data ) ) {

						// If there are any unselected potential matches then we want to show the dropdown
						$.each( data, function( i, this_element ) {

							// Is this one unselected?
							// 'val' return will be an array of string ids
							if ( 0 > $element.select2( 'val' ).indexOf( this_element.id + '' ) ) {
								$dropdown.show();
								return false; // Break out of the each loop
							}
						} );
					}

					// Not an exact match for something existing?
					if ( 0 === $( data ).filter( function() { return this.text.localeCompare( term.trim() ) === 0; } ).length ) {
						return {
							// Simply use the new tag term as the id
							//we might want to append 'new' to all newly created term IDs for processing in PodsAPI.php
							id: term.trim(),
							text: term.trim()
						};
					}
				},
			<?php
				}
			?>

            width : 'resolve',

			<?php
				if ( 1 == (int) pods_v( $form_field_type . '_allow_html', $options ) ) {
			?>
				escapeMarkup : function (m) { return m; },
			<?php
				}
			?>

            initSelection : function ( element, callback ) {
                var data = [];

                jQuery( element.val().split( "," ) ).each( function () {
                    if ( 'undefined' != typeof <?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>_data[ this ] ) {
                        data.push( {
                            id : this,
                            text : <?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>_data[ this ].text
                        } );
                    }
                } );

                <?php
                    if ( 'multi' == pods_var( $form_field_type . '_format_type', $options ) && 1 != $pick_limit ) {
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

                if ( 'multi' == pods_var( $form_field_type . '_format_type', $options ) && 1 != $pick_limit ) {
            ?>
                placeholder : '<?php echo esc_js( __( 'Start Typing...', 'pods' ) ); ?>',
                multiple : true,
                maximumSelectionSize : <?php echo esc_js( (int) $pick_limit ); ?>,
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
                            $data_items[] = '{id : \'' . esc_js( $item_id ) . '\', text: \'' . str_replace( '&amp;', '&', esc_js( $item ) ) . '\'}';
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
                            _wpnonce : '<?php echo esc_js( $field_nonce ); ?>',
                            action : 'pods_relationship',
                            method : 'select2',
                            pod : '<?php echo esc_js( (int) $pod->pod_id ); ?>',
                            field : '<?php echo esc_js( (int) $options[ 'id' ] ); ?>',
                            uri : '<?php echo esc_js( $uri_hash ); ?>',
                            id : '<?php echo esc_js( (int) $id ); ?>',
                            query : term<?php
                                if ( did_action( 'wpml_loaded' ) && apply_filters( 'wpml_setting', true, 'auto_adjust_ids' ) ) {
                            ?>,
                                lang : '<?php echo esc_js( apply_filters( 'wpml_current_language', 'all' ) ); ?>'
                            <?php
                                }
                            ?>
                        };
                    },
                    results : function ( data, page ) {
                        return data;
                    }
                },
                formatResult : <?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>_podsFormatResult,
                formatSelection : <?php echo esc_js( pods_js_name( $attributes[ 'id' ] ) ); ?>_podsFormatSelection,
                minimumInputLength : 1
            <?php
                }
            ?>
        } );

        <?php if ( 'multi' == pods_var( $form_field_type . '_format_type', $options ) && 1 != $pick_limit ) { ?>
            $element.select2( 'container' ).find( 'ul.select2-choices' ).sortable( {
                containment: 'parent',
                start: function() { $element.select2( 'onSortStart' ); },
                update: function() { $element.select2( 'onSortEnd' ); }
            } );
        <?php } ?>
    } );
</script>
