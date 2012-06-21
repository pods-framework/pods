<?php
wp_enqueue_script( 'jquery-ui-core' );
wp_enqueue_script( 'thickbox' );
wp_enqueue_script( 'handlebars', PODS_URL . 'ui/js/handlebars-1.0.0.beta.6.js' );
wp_enqueue_script( 'pods-file-browser', PODS_URL . 'ui/js/pods-file-browser.js' );

wp_enqueue_style( 'thickbox' );
wp_enqueue_style( 'pods-file-browser', PODS_URL . 'ui/css/pods-file-browser.css' );

$attributes = array();
$attributes[ 'value' ] = $value;
$attributes = PodsForm::merge_attributes( $attributes, $name, PodsForm::$field_type, $options );
$css_id = $attributes[ 'id' ];
$existing_files = array();
?>
    <table class="form-table pods-metabox">
        <tbody>
            <tr class="form-field">
                <th scope="row" valign="top">
                    <label class="pods-form-ui-label-pods-meta-files">Files</label>
                </th>
                <td>
                    <ul class="pods-files"><?php
                        if( is_array( $existing_files ) && !empty( $existing_files ) )
                        {
                            foreach( $existing_files as $existing_file )
                            {
                                pfb_file_entry_markup( $existing_file['id'], $existing_file['icon'], $existing_file['name'] );
                            }
                        }
                    ?></ul>
                    <a class="button pods-file-add" href="media-upload.php?TB_iframe=1&amp;width=640&amp;height=1500">Add File</a>
                </td>
            </tr>
        </tbody>
    </table>
