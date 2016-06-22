<script type="text/javascript">
    var pods_shortcode_first = true;

    jQuery( function ( $ ) {
        $( '#pods_insert_shortcode' ).on( 'click', function ( e ) {
            var form = $( '#pods_shortcode_form_element' ),
                use_case = $( '#pods_use_case_selector' ).val(),
                pod_select = $( '#pod_select' ).val(),
                slug = $( '#pod_slug' ).val(),
                orderby = $( '#pod_orderby' ).val(),
                limit = $( '#pod_limit' ).val(),
                where = $( '#pod_where' ).val(),
                template = '',
                pods_page = '',
                template_custom = $( '#pod_template_custom' ).val(),
                field = $( '#pod_field' ).val(),
                fields = $( '#pod_fields' ).val(),
                label = $( '#pod_label' ).val(),
                thank_you = $( '#pod_thank_you' ).val(),
                view = $( '#pod_view' ).val(),
                cache_mode = $( '#pod_cache_mode' ).val(),
                expires = $( '#pod_expires' ).val();
                template = $( '#pod_template' ).val();

            <?php if ( class_exists( 'Pods_Pages' ) ) { ?>
                pods_page = $( '#pods_page' ).val();
            <?php } ?>

            // Slash and burn
            pod_select = ( pod_select + '' ).replace( /\\"/g, '\\$&' ).replace( /\u0000/g, '\\0' );
            slug = ( slug + '' ).replace( /\\"/g, '\\$&' ).replace( /\u0000/g, '\\0' );
            orderby = ( orderby + '' ).replace( /\\"/g, '\\$&' ).replace( /\u0000/g, '\\0' );
            limit = ( limit + '' ).replace( /\\"/g, '\\$&' ).replace( /\u0000/g, '\\0' );
            where = ( where + '' ).replace( /\\"/g, '\\$&' ).replace( /\u0000/g, '\\0' );
            template = ( template + '' ).replace( /\\"/g, '\\$&' ).replace( /\u0000/g, '\\0' );
            pods_page = ( pods_page + '' ).replace( /\\"/g, '\\$&' ).replace( /\u0000/g, '\\0' );
            field = ( field + '' ).replace( /\\"/g, '\\$&' ).replace( /\u0000/g, '\\0' );
            fields = ( fields + '' ).replace( /\\"/g, '\\$&' ).replace( /\u0000/g, '\\0' );
            label = ( label + '' ).replace( /\\"/g, '\\$&' ).replace( /\u0000/g, '\\0' );
            thank_you = ( thank_you + '' ).replace( /\\"/g, '\\$&' ).replace( /\u0000/g, '\\0' );
            view = ( view + '' ).replace( /\\"/g, '\\$&' ).replace( /\u0000/g, '\\0' );
            cache_mode = ( cache_mode + '' ).replace( /\\"/g, '\\$&' ).replace( /\u0000/g, '\\0' );
            expires = ( expires + '' ).replace( /\\"/g, '\\$&' ).replace( /\u0000/g, '\\0' );

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
                    if ( ( !template || !template.length ) && ( !template_custom || !template_custom.length) ) {
                        errors.push( "Template" );
                    }
                    break;

                case 'list':
                    if ( !pod_select || !pod_select.length ) {
                        errors.push( "Pod" );
                    }
                    if ( ( !template || !template.length ) && ( !template_custom || !template_custom.length) ) {
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

                case 'field-current':
                    if ( !field || !field.length ) {
                        errors.push( "Field" );
                    }
                    break;

                case 'form':
                    if ( !pod_select || !pod_select.length ) {
                        errors.push( "Pod" );
                    }
                    break;

                case 'view':
                    if ( !view || !view.length ) {
                        errors.push( "File to include" );
                    }
                    break;

                case 'page':
                    if ( !pods_page || !pods_page.length ) {
                        errors.push( "Pod Page" );
                    }
                    break;
            }

            if ( errors.length ) {
                alert( "The following fields are required:\n" + errors.join( "\n" ) );

                e.preventDefault();

                return false;
            }

            var shortcode = '[pods';

            if ( 'single' == use_case ) {
                if ( pod_select.length )
                    shortcode += ' name="' + pod_select + '"';

                if ( slug.length )
                    shortcode += ' slug="' + slug + '"';

                if ( template.length )
                    shortcode += ' template="' + template + '"';
            }
            else if ( 'list' == use_case ) {
                if ( pod_select.length )
                    shortcode += ' name="' + pod_select + '"';

                if ( orderby.length )
                    shortcode += ' orderby="' + orderby + '"';

                if ( limit.length )
                    shortcode += ' limit="' + limit + '"';

                if ( where.length )
                    shortcode += ' where="' + where + '"';

                if ( template.length )
                    shortcode += ' template="' + template + '"';

            }
            else if ( 'field' == use_case ) {
                if ( pod_select.length )
                    shortcode += ' name="' + pod_select + '"';

                if ( slug.length )
                    shortcode += ' slug="' + slug + '"';

                if ( field.length )
                    shortcode += ' field="' + field + '"';

            }
            else if ( 'field-current' == use_case ) {
                if ( field.length )
                    shortcode += ' field="' + field + '"';

            }
            else if ( 'form' == use_case ) {
                if ( pod_select.length )
                    shortcode += ' name="' + pod_select + '"';

                if ( slug.length )
                    shortcode += ' slug="' + slug + '"';

                if ( fields.length || label.length || thank_you.length )
                    shortcode += ' form="1"';

                if ( fields.length )
                    shortcode += ' fields="' + fields + '"';

                if ( label.length )
                    shortcode += ' label="' + label + '"';

                if ( thank_you.length )
                    shortcode += ' thank_you="' + thank_you + '"';

            }
            else if ( 'view' == use_case ) {
                if ( view.length )
                    shortcode += ' view="' + view + '"';

                if ( cache_mode.length && 'none' != cache_mode ) {
                    shortcode += ' cache_mode="' + cache_mode + '"';

                    if ( expires.length )
                        shortcode += ' expires="' + expires + '"';
                }
            }
            else if ( 'page' == use_case ) {
                if ( pods_page.length )
                    shortcode += ' pods_page="' + pods_page + '"';
            }

            shortcode += ']';

            if ( ( 'single' == use_case || 'list' == use_case ) && template_custom && template_custom.length )
                shortcode += '<br />' + template_custom.replace( /\n/g, '<br />' ) + '<br />[/pods]';

            window.send_to_editor( shortcode );

            e.preventDefault();
        } );

        $( '#pod_cache_mode' ).on( 'change', function () {
            var $this = $( this );

            if ( 'none' == $this.val() ) {
                $( this ).closest( '.pods-section' ).addClass( 'hide' );
            }
            else {
                $( this ).closest( '.pods-section' ).removeClass( 'hide' );
            }
        } );

        var $useCaseSelector = $( '#pods_use_case_selector' );

        $useCaseSelector.on( 'change', function () {
            var val = $( this ).val();

            $( '.pods-section' ).addClass( 'hide' );

            switch ( val ) {
                case 'single':
                    $( '#pod_select, #pod_slug, #pod_template, #pod_template_custom, #pods_insert_shortcode' ).each( function () {
                        $( this ).closest( '.pods-section' ).removeClass( 'hide' );
                    } )
                    break;

                case 'list':
                    $( '#pod_select, #pod_limit, #pod_orderby, #pod_where, #pod_template, #pod_template_custom, #pod_cache_mode, #pod_expires, #pods_insert_shortcode' ).each( function () {
                        $( this ).closest( '.pods-section' ).removeClass( 'hide' );
                    } )
                    break;

                case 'field':
                    $( '#pod_select, #pod_slug, #pod_field, #pods_insert_shortcode' ).each( function () {
                        $( this ).closest( '.pods-section' ).removeClass( 'hide' );
                    } )
                    break;

                case 'field-current':
                    $( '#pod_field, #pods_insert_shortcode' ).each( function () {
                        $( this ).closest( '.pods-section' ).removeClass( 'hide' );
                    } )
                    break;

                case 'form':
                    $( '#pod_select, #pod_slug, #pod_fields, #pod_label, #pod_thank_you, #pods_insert_shortcode' ).each( function () {
                        $( this ).closest( '.pods-section' ).removeClass( 'hide' );
                    } )
                    break;

                case 'view':
                    $( '#pod_view, #pod_cache_mode, #pod_expires, #pods_insert_shortcode' ).each( function () {
                        $( this ).closest( '.pods-section' ).removeClass( 'hide' );
                    } )
                    break;

                <?php if ( class_exists( 'Pods_Pages' ) ) { ?>
                    case 'page':
                        $( '#pods_page, #pods_insert_shortcode' ).each( function () {
                            $( this ).closest( '.pods-section' ).removeClass( 'hide' );
                        } )
                        break;
                <?php } ?>
            }

            // Fix for TB ajaxContent not picking up the height on the first open
            if ( pods_shortcode_first ) {
                $( '#TB_ajaxContent' ).css( { width: 'auto', height: '91%' } );

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
        background: url(<?php echo esc_url( PODS_URL ); ?>ui/images/icon32.png) top left no-repeat;
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
</style>

<div id="pods_shortcode_form" style="display: none;">
    <div class="wrap pods-shortcode">
        <div>
            <div class="pods-header">
                <h3 class="popup-header"><?php _e( 'Pods &raquo; Embed', 'pods' ); ?></h3>
            </div>

            <form id="pods_shortcode_form_element">
                <div class="pods-select">
                    <label for="pods_use_case_selector"><?php _e( 'What would you like to do?', 'pods' ); ?></label>

                    <select id="pods_use_case_selector">
                        <option value="single"><?php _e( 'Display a single Pod item', 'pods' ); ?></option>
                        <option value="list"><?php _e( 'List multiple Pod items', 'pods' ); ?></option>
                        <option value="field"><?php _e( 'Display a field from a single Pod item', 'pods' ); ?></option>
                        <option value="field-current" SELECTED><?php _e( 'Display a field from this item', 'pods' ); ?></option>
                        <option value="form"><?php _e( 'Display a form for creating and editing Pod items', 'pods' ); ?></option>
                        <option value="view"><?php _e( 'Include a file from a theme, with caching options', 'pods' ); ?></option>
                        <?php if ( class_exists( 'Pods_Pages' ) ) { ?>
                            <option value="page"><?php _e( 'Embed content from a Pods Page', 'pods' ); ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="pods-section hide">
                    <?php
                        $api = pods_api();
                        $all_pods = $api->load_pods( array( 'names' => true ) );
                        $pod_count = count( $all_pods );
                    ?>
                    <label for="pod_select"><?php _e( 'Choose a Pod', 'pods' ); ?></label>

                    <?php if ( $pod_count > 0 ) { ?>
                        <select id="pod_select" name="pod_select">
                            <?php foreach ( $all_pods as $pod_name => $pod_label ) { ?>
                                <option value="<?php echo esc_attr( $pod_name ); ?>">
                                    <?php echo esc_html( $pod_label . ' (' . $pod_name . ')' ); ?>
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
                    <div class="pods-section hide">
                        <?php
                        $templates = $api->load_templates();
                        $template_count = count( $templates );
                        ?>
                        <label for="pod_template"><?php _e( 'Template', 'pods' ); ?></label>

                        <select id="pod_template" name="pod_template">
                            <option value="" SELECTED>- <?php _e( 'Custom Template', 'pods' ); ?> -</option>

                            <?php foreach ( $templates as $tmpl ) { ?>
                                <option value="<?php echo esc_attr( $tmpl[ 'name' ] ); ?>">
                                    <?php echo esc_html( $tmpl[ 'name' ] ); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                <?php
                    }
                    else {
                ?>
                    <div class="pods-section hide">
                        <label for="pod_template"><?php _e( 'Template', 'pods' ); ?></label>

                        <input type="text" id="pod_template" name="pod_template" />
                    </div>
                <?php
                    }
                ?>

                <div class="pods-section hide">
                    <label for="pod_template_custom"><?php _e( 'Custom Template', 'pods' ); ?></label>

                    <textarea name="pod_template_custom" id="pod_template_custom" cols="10" rows="10" class="widefat"></textarea>
                </div>

                <?php if ( class_exists( 'Pods_Pages' ) ) { ?>
                    <div class="pods-section hide">
                        <?php
                        $pages = $api->load_pages();
                        $page_count = count( $pages );
                        ?>
                        <label for="pods_page"><?php _e( 'Pods Page', 'pods' ); ?></label>

                        <select id="pods_page" name="pods_page">
                            <?php foreach ( $pages as $page ) { ?>
                                <option value="<?php echo esc_attr( $page[ 'name' ] ); ?>">
                                    <?php echo esc_html( $page[ 'name' ] ); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                <?php } ?>

                <div class="pods-section hide">
                    <label for="pod_slug"><?php _e( 'ID or Slug', 'pods' ); ?></label>

                    <input type="text" id="pod_slug" name="pod_slug" />
                </div>

                <div class="pods-section hide">
                    <label for="pod_limit"><?php _e( 'Limit', 'pods' ); ?></label>

                    <input type="text" id="pod_limit" name="pod_limit" />
                </div>

                <div class="pods-section hide">
                    <label for="pod_orderby"><?php _e( 'Order By', 'pods' ); ?></label>

                    <input type="text" id="pod_orderby" name="pod_orderby" />
                </div>

                <div class="pods-section hide">
                    <label for="pod_where"><?php _e( 'Where', 'pods' ); ?></label>

                    <input type="text" name="pod_where" id="pod_where" />
                </div>

                <div class="pods-section">
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

                <div class="pods-section hide">
                    <label for="pod_view"><?php _e( 'File to include', 'pods' ); ?></label>

                    <input type="text" name="pod_view" id="pod_view" />
                </div>

                <div class="pods-section hide">
                    <label for="pod_cache_mode"><?php _e( 'Cache Type', 'pods' ); ?></label>

                    <?php
                        $cache_modes = array(
                            'none' => __( 'Disable Caching', 'pods' ),
                            'cache' => __( 'Object Cache', 'pods' ),
                            'transient' => __( 'Transient', 'pods' ),
                            'site-transient' => __( 'Site Transient', 'pods' )
                        );

                        $default_cache_mode = apply_filters( 'pods_shortcode_default_cache_mode', 'none' );
                    ?>
                    <select id="pod_cache_mode" name="pod_cache_mode">
                        <?php foreach ( $cache_modes as $cache_mode_option => $cache_mode_label ): ?>
                            <option value="<?php echo esc_attr( $cache_mode_option ); ?>"<?php selected( $default_cache_mode, $cache_mode_option ); ?>>
                                <?php echo esc_html( $cache_mode_label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="pods-section hide">
                    <label for="pod_expires"><?php _e( 'Cache Expiration (in seconds)', 'pods' ); ?></label>

                    <input type="text" name="pod_expires" id="pod_expires" value="<?php echo ( 60 * 5 ); ?>" />
                </div>

                <div class="pods-section" style="text-align: right;">
                    <a class="button-primary" id="pods_insert_shortcode" href="#insert-shortcode"><?php _e( 'Insert', 'pods' ); ?></a>
                </div>
            </form>
        </div>
    </div>
</div>