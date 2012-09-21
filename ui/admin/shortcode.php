<script type="text/javascript">
    jQuery( function ( $ ) {
        $( '#pods_insert_shortcode' ).click( function ( evt ) {
            var form = $( '#pods_shortcode_form_element' ),
                use_case = $( '#pods-use-case-selector' ).val(),
                pod_select = $( '#pod_select' ).val(),
                slug = $( '#pod_slug' ).val(),
                orderby = $( '#pod_orderby' ).val(),
                direction = $( '#pod_direction' ).val(),
                template = $( '#pod_template' ).val(),
                template_custom = $( '#pod_template_custom' ).val(),
                limit = $( '#pod_limit' ).val(),
                field = $( '#pod_field' ).val(),
                fields = $( '#pod_fields' ).val(),
                helper = $( '#pod_helper' ).val(),
                where = $( '#pod_where' ).val(),
                shortcode = '[pods';

            // Validate the form
            var errors = [];
            switch ( use_case ) {
                case 'single':
                    if ( !pod_select || !pod_select.length ) {
                        errors.push( "Pod" );
                    }
                    if ( !slug || !slug.length ) {
                        errors.push( "Slug or ID" );
                    }
                    if ( (!template || !template.length) && (!template_custom || !template_custom.length) ) {
                        errors.push( "Template" );
                    }
                    break;
                case 'list':
                    if ( !pod_select || !pod_select.length ) {
                        errors.push( "Pod" );
                    }
                    if ( (!template || !template.length) && (!template_custom || !template_custom.length) ) {
                        errors.push( "Template" );
                    }
                    break;
                case 'field':
                    if ( !pod_select || !pod_select.length ) {
                        errors.push( "Pod" );
                    }
                    if ( !slug || !slug.length ) {
                        errors.push( "ID or Slug" );
                    }
                    if ( !field || !field.length ) {
                        errors.push( "Field" );
                    }
                    break;
            }

            if ( errors.length ) {
                var error_msg = "The following fields are required:\n";
                error_msg += errors.join( "\n" );
                alert( error_msg );
                return false;
            }

            shortcode += ' name="' + pod_select + '"';
            if ( slug && slug.length ) {
                shortcode += 'slug="' + slug + '" ';
            }
            if ( orderby && orderby.length ) {
                if ( direction.length ) {
                    shortcode += ' orderby="' + orderby + ' ' + direction + '"';
                }
                else {
                    shortcode += ' orderby="' + orderby + ' ASC"';
                }
            }
            if ( template && template.length ) {
                shortcode += ' template="' + template + '"';
            }
            if ( limit && limit.length ) {
                shortcode += ' limit="' + limit + '"';
            }
            if ( field && field.length ) {
                shortcode += ' field="' + field + '"';
            }
            if ( helper && helper.length ) {
                shortcode += ' helper="' + helper + '"';
            }
            if ( where && where.length ) {
                shortcode += ' where="' + where + '"';
            }

            shortcode += ']';

            if ( template_custom && template_custom.length ) {
                console.log( template_custom );
                shortcode += '<br />' + template_custom.replace( /\n/g, '<br />' ) + '<br />[/pods]';
            }

            if ( (use_case == 'single' && window.pods_template_count == 0) || (use_case == 'list' && window.pods_template_count == 0) ) {
                alert( "No templates found!" );
                return false;
            }

            window.send_to_editor( shortcode );
            evt.preventDefault();

        } );
    } );
</script>

<style type="text/css">
    h3.popup-header {
        font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", sans-serif;
        font-weight: normal;
        color: #5a5a5a;
        font-size: 1.8em;
        background: url(<?php echo PODS_URL; ?>ui/images/icon32.png) top left no-repeat;
        padding: 8px 0 5px 36px;
        margin-top: 0;
    }

    div.pods-section, div.pods-select, div.pods-header {
        padding: 15px 15px 0 15px;
    }

    div.pods-section.hide {
        display: none;
    }

    .pods-section label {
        display: inline-block;
        width: 120px;
        font-weight: bold;
    }

    a#pods_insert_shortcode {
        color: white !important;
    }

    strong.red {
        color: red;
    }
</style>

<script type="text/javascript">
    jQuery( function ( $ ) {
        var $useCaseSelector = $( '#pods-use-case-selector' ),
            $form = $( '#pods_shortcode_form_element' ),
            $podSelector = $( '#pod_select' ),
            pods_ajaxurl = "<?php echo admin_url( 'admin-ajax.php?pods_ajax=1' ); ?>",
            nonce = "<?php echo wp_create_nonce( 'pods-load_pod' ); ?>";

        $useCaseSelector.change( function ( evt ) {
            var val = $( this ).val();

            $( '.pods-section' ).addClass( 'hide' );

            switch ( val ) {
                case 'single':
                    $( '#pod_select, #pod_slug, #pod_template, #pod_template_custom, #pods_insert_shortcode' ).each( function () {
                        $( this ).closest( '.pods-section' ).removeClass( 'hide' );
                    } )
                    break;
                case 'list':
                    $( '#pod_select, #pod_limit, #pod_orderby, #pod_direction, #pod_where, #pod_template, #pod_template_custom, #pods_insert_shortcode' ).each( function () {
                        $( this ).closest( '.pods-section' ).removeClass( 'hide' );
                    } )
                    break;
                case 'field':
                    $( '#pod_select, #pod_slug, #pod_helper, #pod_field, #pods_insert_shortcode' ).each( function () {
                        $( this ).closest( '.pods-section' ).removeClass( 'hide' );
                    } )
                    break;
                case 'form':
                    $( '#pod_select, #pod_slug, #pod_fields, #pods_insert_shortcode' ).each( function () {
                        $( this ).closest( '.pods-section' ).removeClass( 'hide' );
                    } )
                    break;
            }
        } );

        $( '#pod_select' ).change( function () {
            var pod = $( this ).val();
            var jax = $.ajax( pods_ajaxurl, {
                type : 'POST',
                dataType : 'json',
                data : {
                    action : 'pods_admin',
                    method : 'load_pod',
                    name : pod,
                    _wpnonce : nonce
                }
            } );
            jax.success( function ( json ) {
                var $orderby = $( '#pod_orderby' ),
                    $field = $( '#pod_field' );

                $orderby.find( 'option' ).remove();
                $orderby.append( '<option value=""></option>' );

                $field.find( 'option' ).remove();
                $field.append( '<option value=""></option>' );

                $.each( json.fields, function ( key, val ) {
                    $orderby.append( '<option value="' + val.name + '">' + val.label + '</option>' );
                    $field.append( '<option value="' + val.name + '">' + val.label + '</option>' );
                } );
            } );
        } );

        $( '#pod_select' ).trigger( 'change' );
    } );
</script>


<div id="pods_shortcode_form" style="display: none;">
    <div class="wrap">
        <div>
            <div class="pods-header">
                <h3 class="popup-header"><?php _e( 'Pods &raquo; Embed', 'pods' ); ?></h3>
            </div>

            <form id="pods_shortcode_form_element">
                <div class="pods-select">
                    <label for="pods-use-case-selector"><?php _e( 'What would you like to do?', 'pods' ); ?></label> <select id="pods-use-case-selector">
                    <option value="">---</option>
                    <option value="single"><?php _e( 'Display a single Pod item', 'pods' ); ?></option>
                    <option value="list"><?php _e( 'List multiple Pod items', 'pods' ); ?></option>
                    <option value="field"><?php _e( 'Display a field from a single Pod item', 'pods' ); ?></option>
                </select>
                </div>
                <div class="pods-section hide">
                    <?php
                    $api = pods_api();
                    $all_pods = $api->load_pods();
                    $pod_count = count( $all_pods );
                    ?>
                    <label for="pod_select">Choose a Pod</label>
                    <?php if ( $pod_count > 0 ) { ?>
                    <select id="pod_select" name="pod_select">
                        <?php foreach ( $all_pods as $pod ) { ?>
                        <option value="<?php echo $pod[ 'name' ]; ?>">
                            <?php echo $pod[ 'label' ]; ?>
                        </option>
                        <?php } ?>
                    </select>
                    <?php
                }
                else {
                    ?>
                    <strong class="red" id="pod_select"><?php _e( 'None Found', 'pods' ); ?></strong>
                    <?php } ?>
                </div>
                <div class="pods-section hide">
                    <?php
                    $templates = $api->load_templates();
                    $template_count = count( $templates );
                    ?>
                    <label for="pod_template">Template</label> <select id="pod_template" name="pod_template">
                    <option value="">Custom Template</option>
                    <?php foreach ( $templates as $tmpl ) { ?>
                    <option value="<?php echo $tmpl[ 'name' ]; ?>">
                        <?php echo $tmpl[ 'name' ]; ?>
                    </option>
                    <?php } ?>
                </select>
                </div>
                <div class="pods-section hide">
                    <label for="pod_template_custom"> <?php _e( 'Custom Template', 'pods' ); ?> </label> <textarea name="pod_template_custom" id="pod_template_custom" cols="10" rows="10" class="widefat"></textarea>
                </div>
                <div class="pods-section hide">
                    <label for="pod_slug"><?php _e( 'ID or Slug', 'pods' ); ?></label>
                    <input type="text" id="pod_slug" name="pod_slug" />
                </div>
                <div class="pods-section hide">
                    <label for="pod_limit"><?php _e( 'Limit', 'pods' ); ?></label>
                    <input type="text" id="pod_limit" name="pod_limit" />
                </div>
                <div class="pods-section hide">
                    <label for="pod_orderby"><?php _e( 'Order By', 'pods' ); ?></label> <select name="pod_orderby" id="pod_orderby"> </select>
                </div>
                <div class="pods-section hide">
                    <label for="pod_direction"><?php _e( 'Order Direction', 'pods' ); ?></label> <select id="pod_direction" name="pod_direction">
                    <option value="ASC">
                        <?php _e( 'Ascending', 'pods' ); ?>
                    </option>
                    <option value="DESC">
                        <?php _e( 'Descending', 'pods' ); ?>
                    </option>
                </select>
                </div>
                <div class="pods-section hide">
                    <label for="pod_where"><?php _e( 'Where', 'pods' ); ?></label>
                    <input type="text" name="pod_where" id="pod_where" />
                </div>
                <div class="pods-section hide">
                    <label for="pod_field"><?php _e( 'Field', 'pods' ); ?></label> <select id="pod_field" name="pod_field"> </select>
                </div>
                <div class="pods-section hide">
                    <label for="pod_fields"><?php _e( 'Fields (comma-separated)', 'pods' ); ?></label>
                    <input type="text" id="pod_fields" name="pod_fields" />
                </div>
                <div class="pods-section hide">
                    <?php
                    $helpers = $api->load_helpers( array( 'options' => array( 'helper_type' => 'display' ) ) );
                    $helper_count = count( $helpers );
                    ?>
                    <label for="pod_helper"><?php _e( 'Helper', 'pods' ); ?></label> <select id="pod_helper" name="pod_helper">
                    <option value="">- <?php _e( 'Helper', 'pods' ); ?> -</option>
                    <?php foreach ( $helpers as $helper ) { ?>
                    <option value="<?php echo $helper[ 'name' ]; ?>">
                        <?php echo $helper[ 'name' ]; ?>
                    </option>
                    <?php } ?>
                </select>
                </div>
                <div class="pods-section hide" style="text-align: right;">
                    <a class="button-primary" id="pods_insert_shortcode" href="#"><?php _e( 'Insert', 'pods' ); ?></a>
                </div>
            </form>
        </div>
    </div>
</div>
