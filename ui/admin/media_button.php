<?php
/**
 * Pods Media Button
 */

/**
 * Add a button to the media buttons context
 */

$current_page = basename( $_SERVER[ 'PHP_SELF' ] );

function pods_media_button ( $context ) {
    $button = '<a href="#TB_inline?width=640&inlineId=pods_shortcode_form" class="thickbox" id="add_pod_button" title="Embed Pods"><img src="' . PODS_URL . 'ui/images/icon16.png" alt="Embed Pods" /></a>';
    $context .= $button;
    return $context;
}

if ( is_admin() ) {
    add_filter( 'media_buttons_context', 'pods_media_button' );
}

/**
 * Display the shortcode form
 */
function add_pods_mce_popup () {
?>
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
                    column = $( '#pod_column' ).val(),
                    columns = $( '#pod_columns' ).val(),
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
                    case 'column':
                        if ( !pod_select || !pod_select.length ) {
                            errors.push( "Pod" );
                        }
                        if ( !slug || !slug.length ) {
                            errors.push( "ID or Slug" );
                        }
                        if ( !column || !column.length ) {
                            errors.push( "Column" );
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
                if ( slug && slug.length )
                    shortcode += 'slug="' + slug + '" ';
                if ( orderby && orderby.length ) {
                    if ( direction.length ) {
                        shortcode += ' orderby="' + orderby + ' ' + direction + '"';
                    }
                    else {
                        shortcode += ' orderby="' + orderby + ' ASC"';
                    }
                }
                if ( template && template.length )
                    shortcode += ' template="' + template + '"';
                if ( limit && limit.length )
                    shortcode += ' limit="' + limit + '"';
                if ( column && column.length )
                    shortcode += ' field="' + column + '"';
                if ( helper && helper.length )
                    shortcode += ' helper="' + helper + '"';
                if ( where && where.length )
                    shortcode += ' where="' + where + '"';

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

            } );
        } );
    </script>
<?php
    require_once PODS_DIR . 'ui/admin/shortcode_form.php';
}

if ( is_admin() ) {
    add_action( 'admin_footer', 'add_pods_mce_popup' );
}