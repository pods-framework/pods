<?php
global $post_ID;

wp_enqueue_script( 'pods-handlebars' );
wp_enqueue_script( 'jquery-ui-core' );
wp_enqueue_script( 'jquery-ui-sortable' );
wp_enqueue_script( 'thickbox' );
wp_enqueue_script( 'pods-attach' );

wp_enqueue_style( 'thickbox' );
wp_enqueue_style( 'pods-attach' );

$field_file = PodsForm::field_loader( 'file' );

$attributes = array();
$attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );

$css_id = $attributes[ 'id' ];

$uri_hash = wp_create_nonce( 'pods_uri_' . $_SERVER[ 'REQUEST_URI' ] );

$uid = @session_id();

if ( is_user_logged_in() )
    $uid = 'user_' . get_current_user_id();

$field_nonce = wp_create_nonce( 'pods_upload_' . ( !is_object( $pod ) ? '0' : $pod->pod_id ) . '_' . $uid . '_' . $uri_hash . '_' . $options[ 'id' ] );

$limit_file_type = pods_var( $form_field_type . '_type', $options, 'images' );

$title_editable = pods_var( $form_field_type . '_edit_title', $options, 0 );

if ( 'images' == $limit_file_type )
    $limit_types = 'jpg,png,gif';
elseif ( 'video' == $limit_file_type )
    $limit_types = 'mpg,mov,flv,mp4';
elseif ( 'audio' == $limit_file_type )
    $limit_types = 'mp3,m4a,wav,wma';
elseif ( 'text' == $limit_file_type )
    $limit_types = 'txt,rtx,csv,tsv';
elseif ( 'any' == $limit_file_type )
    $limit_types = '';
else
    $limit_types = pods_var( $form_field_type . '_allowed_extensions', $options, '' );

$limit_types = str_replace( ' ', '', $limit_types );

$tab = pods_var( $form_field_type . '_attachment_tab', $options, 'type', null, true );

if ( 'upload' == $tab )
    $tab = 'type';
elseif ( 'browse' == $tab )
    $tab = 'library';

$file_limit = 1;

if ( 'multi' == pods_var( $form_field_type . '_format_type', $options, 'single' ) )
    $file_limit = (int) pods_var( $form_field_type . '_limit', $options, 0 );

$data = array(
    'limit-types' => $limit_types,
    'limit-files' => $file_limit
);

$the_post_id = '';

if ( is_admin() && !empty( $post_ID ) )
    $the_post_id = '&amp;post_id=' . (int) $post_ID;

if ( empty( $value ) )
    $value = array();
else
    $value = (array) $value;
?>
<div<?php PodsForm::attributes( array( 'class' => $attributes[ 'class' ] ), $name, $form_field_type, $options ); ?>>
    <table class="form-table pods-metabox pods-form-ui-table-type-<?php echo $form_field_type; ?>" id="<?php echo $css_id; ?>"<?php PodsForm::data( $data, $name, $form_field_type, $options ); ?>>
        <tbody>
            <tr class="form-field">
                <td>
                    <ul class="pods-files pods-files-list"><?php // no extra space in ul or CSS:empty won't work
                        foreach ( $value as $val ) {
                            $attachment = get_post( $val );

                            if ( empty( $attachment ) )
                                continue;

                            $thumb = wp_get_attachment_image_src( $val, 'thumbnail', true );

                            $title = $attachment->post_title;

                            echo $field_file->markup( $attributes, $file_limit, $title_editable, $val, $thumb[ 0 ], $title );
                        }
                        ?></ul>

                    <a class="button pods-file-add" href="<?php echo admin_url() ?>media-upload.php?inlineId=pods_media_attachment<?php echo $the_post_id; ?>&amp;tab=<?php echo $tab; ?>&amp;TB_iframe=1&amp;width=640&amp;height=1500&pods_pod=<?php echo $pod->pod; ?>&pods_pod_id=<?php echo $pod->pod; ?>&pods_field=<?php echo $options[ 'name' ]; ?>&pods_field_id=<?php echo $options[ 'id' ]; ?>&pods_uri_hash=<?php echo $uri_hash; ?>&pods_field_nonce=<?php echo $field_nonce; ?>"><?php echo pods_var_raw( $form_field_type . '_add_button', $options, __( 'Add File', 'pods' ) ); ?></a>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script type="text/x-handlebars" id="<?php echo $css_id; ?>-handlebars">
    <?php echo $field_file->markup( $attributes, $file_limit, $title_editable ); ?>
</script>

<script type="text/javascript">
    jQuery( function ( $ ) {
        // init sortable
        $( '#<?php echo esc_js( $css_id ); ?> ul.pods-files' )
            .sortable( {
                containment : 'parent',
                axis : 'y',
                scrollSensitivity : 40,
                tolerance : 'pointer',
                opacity : 0.6
            } );

        // hook delete links
        $( '#<?php echo esc_js( $css_id ); ?>' ).on( 'click', 'li.pods-file-delete', function () {
            var podsfile = $( this ).parent().parent();
            podsfile.slideUp( function () {

                // check to see if this was the only entry
                if ( podsfile.parent().children().length == 1 ) { // 1 because we haven't removed our target yet
                    podsfile.parent().hide();
                }

                // remove the entry
                $( this ).remove();

            } );
        } );

        var maxFiles_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?> = <?php echo esc_js( $file_limit ); ?>;

        // hook the add link
        $( '#<?php echo esc_js( $css_id ); ?>' ).on( 'click', 'a.pods-file-add', function ( e ) {
            e.preventDefault();
            var trigger = $( this );
            var href = trigger.attr( 'href' ), width = $( window ).width(), H = $( window ).height(), W = ( 720 < width ) ? 720 : width;
            if ( !href ) {
                return;
            }
            href = href.replace( /&width=[0-9]+/g, '' );
            href = href.replace( /&height=[0-9]+/g, '' );
            trigger.attr( 'href', href + '&width=' + ( W - 80 ) + '&height=' + ( H - 85 ) );

            pods_file_context = trigger.parent().find( 'ul.pods-files' );
            pods_file_thickbox_modder = setInterval( function () {
                if ( pods_file_context )
                    pods_attachments( '<?php echo esc_js( $css_id ); ?>', maxFiles_<?php echo pods_clean_name( $attributes[ 'name' ] ); ?> );
            }, 500 );

            tb_show( 'Attach a file', e.target.href, false );
            return false;
        } );
    } );
</script>
