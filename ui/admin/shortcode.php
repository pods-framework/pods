<script type="text/javascript">
    jQuery( function ( $ ) {
        $( '#pods_insert_shortcode' ).click( function ( evt ) {
            var form = $( '#pods_shortcode_form_element' ),
                use_case = $( '#pods-use-case-selector' ).val(),
                pod_select = $( '#pod_select' ).val(),
                slug = $( '#pod_slug' ).val(),
                orderby = $( '#pod_orderby' ).val(),
                limit = $( '#pod_limit' ).val(),
                where = $( '#pod_where' ).val(),
                <?php if ( class_exists( 'Pods_Templates' ) ) { ?>
                    template = $( '#pod_template' ).val(),
                <?php } else { ?>
                    template = '',
                <?php } ?>
                template_custom = $( '#pod_template_custom' ).val(),
                field = $( '#pod_field' ).val(),
                fields = $( '#pod_fields' ).val(),
                label = $( '#pod_label' ).val(),
                thank_you = $( '#pod_thank_you' ).val(),
                shortcode = '[pods',
                pods_shortcode_first = true;

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
                case 'form':
                    if ( !pod_select || !pod_select.length ) {
                        errors.push( "Pod" );
                    }
                    break;
            }

            if ( errors.length ) {
                var error_msg = "The following fields are required:\n";
                error_msg += errors.join( "\n" );
                alert( error_msg );
                return false;
            }

            // Slash and burn
            pod_select = ( pod_select + '' ).replace( /\\"/g, '\\$&' ).replace( /\u0000/g, '\\0' );
            slug = ( slug + '' ).replace( /\\"/g, '\\$&' ).replace( /\u0000/g, '\\0' );
            orderby = ( orderby + '' ).replace( /\\"/g, '\\$&' ).replace( /\u0000/g, '\\0' );
            limit = ( limit + '' ).replace( /\\"/g, '\\$&' ).replace( /\u0000/g, '\\0' );
            where = ( where + '' ).replace( /\\"/g, '\\$&' ).replace( /\u0000/g, '\\0' );
            template = ( template + '' ).replace( /\\"/g, '\\$&' ).replace( /\u0000/g, '\\0' );
            field = ( field + '' ).replace( /\\"/g, '\\$&' ).replace( /\u0000/g, '\\0' );
            fields = ( fields + '' ).replace( /\\"/g, '\\$&' ).replace( /\u0000/g, '\\0' );
            label = ( label + '' ).replace( /\\"/g, '\\$&' ).replace( /\u0000/g, '\\0' );
            thank_you = ( thank_you + '' ).replace( /\\"/g, '\\$&' ).replace( /\u0000/g, '\\0' );

            shortcode += ' name="' + pod_select + '"';

            if ( slug.length )
                shortcode += ' slug="' + slug + '"';

            if ( orderby.length )
                shortcode += ' orderby="' + orderby + '"';

            if ( limit.length )
                shortcode += ' limit="' + limit + '"';

            if ( where.length )
                shortcode += ' where="' + where + '"';

            <?php if ( class_exists( 'Pods_Templates' ) ) { ?>
                if ( template.length )
                    shortcode += ' template="' + template + '"';
            <?php } ?>

            if ( field.length )
                shortcode += ' field="' + field + '"';

            if ( fields.length || label.length || thank_you.length )
                shortcode += ' form="1"';

            if ( fields.length )
                shortcode += ' fields="' + fields + '"';

            if ( label.length )
                shortcode += ' label="' + label + '"';

            if ( thank_you.length )
                shortcode += ' thank-you="' + thank_you + '"';

            shortcode += ']';

            if ( template_custom && template_custom.length )
                shortcode += '<br />' + template_custom.replace( /\n/g, '<br />' ) + '<br />[/pods]';

            window.send_to_editor( shortcode );
            evt.preventDefault();

        } );
        var $useCaseSelector = $( '#pods-use-case-selector' ),
                $form = $( '#pods_shortcode_form_element' ),
                $podSelector = $( '#pod_select' ),
                pods_ajaxurl = "<?php echo admin_url( 'admin-ajax.php?pods_ajax=1' ); ?>",
                nonce = "<?php echo wp_create_nonce( 'pods-shortcode_load_fields' ); ?>";

        $useCaseSelector.change(function ( evt ) {
            var val = $( this ).val();

            $( '.pods-section' ).addClass( 'hide' );

            switch ( val ) {
                case 'single':
                    $( '#pod_select, #pod_slug, #pod_template, #pod_template_custom, #pods_insert_shortcode' ).each( function () {
                        $( this ).closest( '.pods-section' ).removeClass( 'hide' );
                    } )
                    break;
                case 'list':
                    $( '#pod_select, #pod_limit, #pod_orderby, #pod_where, #pod_template, #pod_template_custom, #pods_insert_shortcode' ).each( function () {
                        $( this ).closest( '.pods-section' ).removeClass( 'hide' );
                    } )
                    break;
                case 'field':
                    $( '#pod_select, #pod_slug, #pod_field, #pods_insert_shortcode' ).each( function () {
                        $( this ).closest( '.pods-section' ).removeClass( 'hide' );
                    } )
                    break;
                case 'form':
                    $( '#pod_select, #pod_slug, #pod_fields, #pod_label, #pod_thank_you, #pods_insert_shortcode' ).each( function () {
                        $( this ).closest( '.pods-section' ).removeClass( 'hide' );
                    } )
                    break;
            }

            // Fix for TB ajaxContent not picking up the height on the first open
            if ( pods_shortcode_first ) {
                $( '#TB_ajaxContent' ).css( { height: '91%' } );
                pods_shortcode_first = false;
            }
        } );
    } );
</script>

<style type="text/css">
    .pods-shortcode h3.popup-header {
        font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", sans-serif;
        font-weight: normal;
        color: #5a5a5a;
        font-size: 1.8em;
        background: url(<?php echo PODS_URL; ?>ui/images/icon32.png) top left no-repeat;
        padding: 8px 0 5px 36px;
        margin-top: 0;
    }

    .pods-shortcode div.pods-section, div.pods-select, div.pods-header {
        padding: 15px 15px 0 15px;
    }

    .pods-shortcode div.pods-section.hide {
        display: none;
    }

    .pods-shortcode .pods-section label {
        display: inline-block;
        width: 120px;
        font-weight: bold;
    }

    a#pods_insert_shortcode {
        color: white !important;
    }

    .pods-shortcode strong.red {
        color: red;
    }

    /* Thickbox Inline content fix */
    #TB_ajaxContent {
        width: auto !important;
        height: auto !important;
    }
</style>

<div id="pods_shortcode_form" style="display: none;">
    <div class="wrap pods-shortcode">
        <div>
            <div class="pods-header">
                <h3 class="popup-header"><?php _e( 'Pods &raquo; Embed', 'pods' ); ?></h3>
            </div>

            <form id="pods_shortcode_form_element">
                <div class="pods-select">
                    <label for="pods-use-case-selector"><?php _e( 'What would you like to do?', 'pods' ); ?></label>

                    <select id="pods-use-case-selector">
                        <option value="single"><?php _e( 'Display a single Pod item', 'pods' ); ?></option>
                        <option value="list" SELECTED><?php _e( 'List multiple Pod items', 'pods' ); ?></option>
                        <option value="field"><?php _e( 'Display a field from a single Pod item', 'pods' ); ?></option>
                        <option value="form"><?php _e( 'Display a form for creating and editing Pod items', 'pods' ); ?></option>
                    </select>
                </div>

                <div class="pods-section">
                    <?php
                        $api = pods_api();
                        $all_pods = $api->load_pods();
                        $pod_count = count( $all_pods );
                    ?>
                    <label for="pod_select"><?php _e( 'Choose a Pod', 'pods' ); ?></label>

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

                <?php if ( class_exists( 'Pods_Templates' ) ) { ?>
                    <div class="pods-section">
                        <?php
                            $templates = $api->load_templates();
                            $template_count = count( $templates );
                        ?>
                        <label for="pod_template"><?php _e( 'Template', 'pods' ); ?></label>

                        <select id="pod_template" name="pod_template">
                            <option value="" SELECTED>- <?php _e( 'Custom Template', 'pods' ); ?> -</option>

                            <?php foreach ( $templates as $tmpl ) { ?>
                                <option value="<?php echo $tmpl[ 'name' ]; ?>">
                                    <?php echo $tmpl[ 'name' ]; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                <?php } ?>

                <div class="pods-section">
                    <label for="pod_template_custom"><?php _e( 'Custom Template', 'pods' ); ?></label>

                    <textarea name="pod_template_custom" id="pod_template_custom" cols="10" rows="10" class="widefat"></textarea>
                </div>

                <div class="pods-section hide">
                    <label for="pod_slug"><?php _e( 'ID or Slug', 'pods' ); ?></label>

                    <input type="text" id="pod_slug" name="pod_slug" />
                </div>

                <div class="pods-section">
                    <label for="pod_limit"><?php _e( 'Limit', 'pods' ); ?></label>

                    <input type="text" id="pod_limit" name="pod_limit" />
                </div>

                <div class="pods-section">
                    <label for="pod_orderby"><?php _e( 'Order By', 'pods' ); ?></label>

                    <input type="text" id="pod_orderby" name="pod_orderby" />
                </div>

                <div class="pods-section">
                    <label for="pod_where"><?php _e( 'Where', 'pods' ); ?></label>

                    <input type="text" name="pod_where" id="pod_where" />
                </div>

                <div class="pods-section hide">
                    <label for="pod_field"><?php _e( 'Field', 'pods' ); ?></label>

                    <input type="text" name="pod_field" id="pod_field" />
                </div>

                <div class="pods-section hide">
                    <label for="pod_fields"><?php _e( 'Fields (comma-separated)', 'pods' ); ?></label>

                    <input type="text" id="pod_fields" name="pod_fields" />
                </div>

                <div class="pods-section hide">
                    <label for="pod_label"><?php _e( 'Submit Label', 'pods' ); ?></label>

                    <input type="text" id="pod_label" name="pod_label" />
                </div>

                <div class="pods-section hide">
                    <label for="pod_thank_you"><?php _e( 'Thank You URL upon submission', 'pods' ); ?></label>

                    <input type="text" id="pod_thank_you" name="pod_thank_you" />
                </div>

                <div class="pods-section" style="text-align: right;">
                    <a class="button-primary" id="pods_insert_shortcode" href="#"><?php _e( 'Insert', 'pods' ); ?></a>
                </div>
            </form>
        </div>
    </div>
</div>
