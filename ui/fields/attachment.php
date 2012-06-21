<?php
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'thickbox' );
    wp_enqueue_script( 'handlebars', PODS_URL . 'ui/js/handlebars-1.0.0.beta.6.js' );
    wp_enqueue_script( 'pods-file-browser', PODS_URL . 'ui/js/pods-file-browser.js' );

    wp_enqueue_style( 'thickbox' );
    wp_enqueue_style( 'pods-file-browser', PODS_URL . 'ui/css/pods-file-browser.css' );

    $field_file = PodsForm::field_loader( 'file' );

    $attributes = array();
    $attributes[ 'value' ] = $value;
    $attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );

    $css_id = $attributes[ 'id' ];

    $file_limit = ( isset( $options[ 'file_limit' ] ) ? (int) $options[ 'file_limit' ] : 1 );

    $value = (array) $value;
?>
    <table class="form-table pods-metabox" id="<?php echo $css_id; ?>">
        <tbody>
            <tr class="form-field">
                <td>
                    <ul class="pods-files">
                        <?php
                            foreach ( $value as $val ) {
                                $thumb = wp_get_attachment_image_src( $val[ 'id' ], 'thumbnail', true );
                                echo $field_file->markup( $attributes, $file_limit, $val[ 'ID' ], $thumb[ 0 ], basename( $val[ 'guid' ] ) );
                            }
                        ?>
                    </ul>

                    <a class="button pods-file-add" href="media-upload.php?TB_iframe=1&amp;width=640&amp;height=1500">Add File</a>
                </td>
            </tr>
        </tbody>
    </table>

    <script type="text/x-handlebars" id="<?php echo $css_id; ?>-js-row">
        <?php echo $field_file->markup( $attributes ); ?>
    </script>