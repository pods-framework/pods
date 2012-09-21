<?php
global $pods_cache, $coltype_exists, $pods_type_exists;
$pods_type_exists =& $coltype_exists;
/*$pods_cache = PodCache::instance();
$form_count = $pods_cache->form_count;*/
$form_count = 1;

if ( isset( $field[ 'options' ] ) ) {
    $options = $field[ 'options' ];
    unset( $field[ 'options' ] );

    $field = array_merge( $options, $field );
}

$name = $field['name'];
$label = $field['label'];
$comment = $field['description'];
$type = $coltype = $field['type'];
$oldcoltype = $field[ 'type' ];

if ( 'paragraph' == $coltype )
    $oldcoltype = 'code';
elseif ( 'wysiwyg' == $coltype )
    $oldcoltype = 'desc';
elseif ( 'number' == $coltype )
    $oldcoltype = 'num';
elseif ( 'text' == $coltype )
    $oldcoltype = 'txt';

$input_helper = pods_var_raw( 'input_helper', $field );
$hidden = (empty($field['hidden'])) ? '' : ' hidden';
$value = '';

if ( isset( $this->obj->row[ $name ] ) )
    $value = (is_array($this->obj->row[$name])) ? $this->obj->row[$name] : stripslashes($this->obj->row[$name]);

$css_id = 'pods_form' . $form_count . '_' . $name;

// The first 3 CSS classes will be DEPRECATED in 2.0.0
$css_classes = "form $oldcoltype $name pods_field pods_field_$name pods_coltype_$oldcoltype";
if ( 'single' == pods_var( 'pick_format_type', $field, 'single', null, true )) {
    $css_classes = str_replace(' pick ', ' pick1 ', $css_classes);
}

//pre-field hooks
do_action('pods_pre_input_field', $field, $css_id, $css_classes, $value, $this);
do_action("pods_pre_input_field_{$name}", $field, $css_id, $css_classes, $value, $this);
do_action("pods_pre_input_field_type_{$type}", $field, $css_id, $css_classes, $value, $this);
?>
    <div class="leftside <?php echo esc_attr($name . $hidden); ?>">
        <label for="<?php echo esc_attr($css_id); ?>"><?php echo $label; ?></label>
<?php
if (!empty($comment)) {
?>
        <div class="comment"><?php echo $comment; ?></div>
<?php
}
?>
    </div>
    <div class="rightside <?php echo esc_attr($name . $hidden); ?>">
<?php
/*
==================================================
Generate the input helper
==================================================
*/
if (!empty($input_helper)) {
    $function_or_file = $input_helper;
    $check_file = null;
    if ((!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE) && (!defined('PODS_HELPER_FILES') || !PODS_HELPER_FILES))
        $check_file = false;
    if (false !== $check_function && false !== $check_file)
        $function_or_file = pods_function_or_file($function_or_file, false, 'helper', $check_file);
    else
        $function_or_file = false;

    $content = false;
    if (!$function_or_file) {
        $api = new PodAPI();
        $params = array('name' => $input_helper, 'type' => 'input');
        if (!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE)
            $params = pods_sanitize($params);
        $content = $api->load_helper($params);
        if (false !== $content && 0 < strlen(trim($content['phpcode'])))
            $content = $content['phpcode'];
        else
            $content = false;
    }

    if (false === $content && false !== $function_or_file && isset($function_or_file['file']))
        locate_template($function_or_file['file'], true, true);
    elseif (false !== $content) {
        if (!defined('PODS_DISABLE_EVAL') || PODS_DISABLE_EVAL)
            eval("?>$content");
        else
            echo $content;
    }
}

/*
==================================================
Boolean checkbox
==================================================
*/
elseif ('boolean' == $type) {
    $value = (empty($value)) ? '' : ' checked';
?>
    <input name="<?php echo esc_attr($name); ?>" type="checkbox" class="<?php echo esc_attr($css_classes); ?>" id="<?php echo esc_attr($css_id); ?>"<?php echo $value; ?> />
<?php
}

/*
==================================================
Date picker
==================================================
*/
elseif ('datetime' == $type) {
    if (!isset($pods_type_exists[$type]) || empty($pods_type_exists[$type])) {
?>
    <script type="text/javascript" src="<?php echo PODS_URL; ?>deprecated/js/date_input.js"></script>
    <script type="text/javascript">
    jQuery(function() {
        jQuery(".pods_form input.date").date_input();
    });
    </script>
<?php
    }
    $value = (empty($value)) ? date("Y-m-d H:i:s") : $value;
?>
    <input name="<?php echo esc_attr($name); ?>" type="text" class="<?php echo esc_attr($css_classes); ?>" id="<?php echo esc_attr($css_id); ?>" value="<?php echo esc_attr($value); ?>" />
<?php
}

/*
==================================================
Standard text box
==================================================
*/
elseif ('number' == $type || 'text' == $type || 'slug' == $type) {
?>
    <input name="<?php echo esc_attr($name); ?>" type="text" class="<?php echo esc_attr($css_classes); ?>" id="<?php echo esc_attr($css_id); ?>" value="<?php echo esc_attr($value); ?>" maxlength="<?php echo ($coltype=='num')?15:128; ?>" />
<?php
}

/*
==================================================
Textarea box
==================================================
*/
elseif ('wysiwyg' == $type) {
    if (is_admin()) {
        $type = 'desc_tinymce';

        require_once(ABSPATH . '/wp-admin/includes/template.php');

        if ( !function_exists( 'wp_editor' ) && ( !isset($pods_type_exists[$type]) || empty($pods_type_exists[$type] ) ) ) {
            // New TinyMCE API by azaozz
            require_once( PODS_DIR . 'ui/wp-editor/wp-editor.php' );
?>
    <style type="text/css" scoped="scoped">
        @import url("<?php echo PODS_URL; ?>deprecated/wp-editor/editor-buttons.css");
    </style>
<?php
        }
        $css_classes = str_replace("form desc {$name}", "form {$type} {$name}", $css_classes) . ' wp-editor-area';

        $media_bar = false;
        if (!(defined('PODS_DISABLE_FILE_UPLOAD') && true === PODS_DISABLE_FILE_UPLOAD)
                && !(defined('PODS_UPLOAD_REQUIRE_LOGIN') && is_bool(PODS_UPLOAD_REQUIRE_LOGIN) && true === PODS_UPLOAD_REQUIRE_LOGIN && !is_user_logged_in())
                && !(defined('PODS_UPLOAD_REQUIRE_LOGIN') && !is_bool(PODS_UPLOAD_REQUIRE_LOGIN) && (!is_user_logged_in() || !current_user_can(PODS_UPLOAD_REQUIRE_LOGIN)))) {
            $media_bar = true;
        }
        if (function_exists('wp_editor')) {
            wp_editor($value, $css_id, array('editor_class' => $css_classes, 'media_buttons' => $media_bar));
        }
        else {
            global $wp_editor;
            echo $wp_editor->editor($value, $css_id, array('editor_class' => $css_classes, 'media_buttons_context' => 'Upload/Insert ', 'textarea_rows' => 10), $media_bar);
        }
    }
    else {
        if (!isset($pods_type_exists[$type]) || empty($pods_type_exists[$type])) {
?>
    <script type="text/javascript" src="<?php echo PODS_URL; ?>deprecated/js/nicEdit.js"></script>
<?php
        }
?>
    <textarea name="<?php echo esc_attr($name); ?>" class="<?php echo esc_attr($css_classes); ?>" id="<?php echo esc_attr($css_id); ?>"><?php echo esc_textarea($value); ?></textarea>
<?php
    }
}

/*
==================================================
Textarea box (no WYSIWYG)
==================================================
*/
elseif ('paragraph' == $type) {
?>
    <textarea name="<?php echo esc_attr($name); ?>" class="<?php echo esc_attr($css_classes); ?>" id="<?php echo esc_attr($css_id); ?>"><?php echo esc_textarea($value); ?></textarea>
<?php
}

/*
==================================================
File upload
==================================================
*/
elseif ('file' == $type) {
    if (((defined('PODS_DISABLE_FILE_UPLOAD') && true === PODS_DISABLE_FILE_UPLOAD)
                || (defined('PODS_UPLOAD_REQUIRE_LOGIN') && is_bool(PODS_UPLOAD_REQUIRE_LOGIN) && true === PODS_UPLOAD_REQUIRE_LOGIN && !is_user_logged_in())
                || (defined('PODS_UPLOAD_REQUIRE_LOGIN') && !is_bool(PODS_UPLOAD_REQUIRE_LOGIN) && (!is_user_logged_in() || !current_user_can(PODS_UPLOAD_REQUIRE_LOGIN))))
            && ((defined('PODS_DISABLE_FILE_BROWSER') && true === PODS_DISABLE_FILE_BROWSER)
                || (defined('PODS_FILES_REQUIRE_LOGIN') && is_bool(PODS_FILES_REQUIRE_LOGIN) && true === PODS_FILES_REQUIRE_LOGIN && !is_user_logged_in())
                || (defined('PODS_FILES_REQUIRE_LOGIN') && !is_bool(PODS_FILES_REQUIRE_LOGIN) && (!is_user_logged_in() || !current_user_can(PODS_FILES_REQUIRE_LOGIN))))) {
?>
            <p>You do not have access to upload / browse files. Contact your website admin to resolve.</p>
<?php
    }
    else {
        if (!(defined('PODS_DISABLE_FILE_UPLOAD') && true === PODS_DISABLE_FILE_UPLOAD)
                && !(defined('PODS_UPLOAD_REQUIRE_LOGIN') && is_bool(PODS_UPLOAD_REQUIRE_LOGIN) && true === PODS_UPLOAD_REQUIRE_LOGIN && !is_user_logged_in())
                && !(defined('PODS_UPLOAD_REQUIRE_LOGIN') && !is_bool(PODS_UPLOAD_REQUIRE_LOGIN) && (!is_user_logged_in() || !current_user_can(PODS_UPLOAD_REQUIRE_LOGIN)))) {
            require_once(realpath(ABSPATH . '/wp-admin/includes/template.php'));

            if (!isset($pods_type_exists[$type]) || empty($pods_type_exists[$type])) {
?>
    <script type="text/javascript" src="<?php echo WP_INC_URL . '/js/plupload/plupload.js'; ?>"></script>
    <script type="text/javascript" src="<?php echo WP_INC_URL . '/js/plupload/plupload.html4.js'; ?>"></script>
    <script type="text/javascript" src="<?php echo WP_INC_URL . '/js/plupload/plupload.html5.js'; ?>"></script>
    <script type="text/javascript" src="<?php echo WP_INC_URL . '/js/plupload/plupload.flash.js'; ?>"></script>
    <script type="text/javascript" src="<?php echo WP_INC_URL . '/js/plupload/plupload.silverlight.js'; ?>"></script>
<?php
            }
            $button_height = (function_exists('is_super_admin') ? 23 : 24);
?>
    <script type="text/javascript">
        jQuery(function() {
            plup_<?php echo esc_attr($name); ?> = new plupload.Uploader({
                runtimes: 'html5,flash,silverlight,html4',
                browse_button: '<?php echo esc_attr($css_id); ?>',
                container: 'plupload-container-<?php echo esc_attr($css_id); ?>',
                file_data_name: 'Filedata',
                max_file_size: '<?php echo wp_max_upload_size(); ?>b',
                url: '<?php echo PODS_URL; ?>/ui/ajax/misc.php',
                flash_swf_url: '<?php echo includes_url('js/plupload/plupload.flash.swf'); ?>',
                silverlight_xap_url: '<?php echo includes_url('js/plupload/plupload.silverlight.xap'); ?>',
                multipart: true,
                urlstresm_upload: true,
                multipart_params: {
                    "_wpnonce": "<?php echo wp_create_nonce('pods-wp_handle_upload_advanced'); ?>",
                    "action": "wp_handle_upload_advanced",
                    "auth_cookie": "<?php echo (is_ssl() ? esc_attr($_COOKIE[SECURE_AUTH_COOKIE]) : esc_attr($_COOKIE[AUTH_COOKIE])); ?>",
                    "logged_in_cookie": "<?php echo esc_attr($_COOKIE[LOGGED_IN_COOKIE]); ?>"
                }
            });
            plup_<?php echo esc_attr($name); ?>.init();

            // Plupload Init Event Handler
            plup_<?php echo esc_attr($name); ?>.bind('Init', function(up, params) {

            });

            // Plupload FilesAdded Event Handler
            plup_<?php echo esc_attr($name); ?>.bind('FilesAdded', function(up, files) {
                // Hide any existing files (for use in single/limited field configuration)
                // jQuery('.pods_field_<?php echo $name; ?> .success').hide();

                jQuery.each(files, function(index, file) {
                    jQuery(".rightside.<?php echo esc_attr($name); ?> .form").append('<div id="' + file.id + '">' + file.name + '<div class="pods-progress"><div class="pods-bar"></div></div></div>');
                });

                up.refresh();
                up.start();
            });

            // Plupload UploadProgress Event Handler
            plup_<?php echo esc_attr($name); ?>.bind('UploadProgress', function(up, file) {
                jQuery('#' + file.id + ' .pods-bar').css('width', file.percent + '%');
            });

            // Plupload FileUploaded Event Handler
            <?php $queue_limit = 1; ?>
            plup_<?php echo esc_attr($name); ?>.bind('FileUploaded', function(up, file, resp) {
                var file_div = jQuery('#' + file.id);
                var queue_limit = <?php echo $queue_limit; ?>;
                file_div.find('.pods-progress').remove();

                if ("Error" == resp.response.substr(0, 5)) {
                    var response = resp.response.substr(7);
                    file_div.append(response);
                } else if ("<e>" == resp.response.substr(0, 3)) {
                    var response = resp.response;
                    file_div.append(resp.response);
                } else {
                    var response = eval( '(' +resp.response.match( /\{(.*)\}/gi ) + ')' );
                    file_div.html('<div class="btn dropme"></div><a href="' + response.guid + '" target="_blank">' + response.post_title + '</a>');
                    file_div.attr('class', 'success');
                    file_div.data('post-id', response.ID);
                }

                /**
                 * Field limit
                jQuery.fn.reverse = [].reverse;
                var files = jQuery('.pods_field_<?php echo $name; ?> .success'), file_count = files.size();
                files.reverse().each(function(idx, elem) {
                    if (idx + 1 > queue_limit) {
                        jQuery(elem).remove();
                    }
                });
                */

            });
        } );
    </script>
<?php
        if (!(defined('PODS_DISABLE_FILE_BROWSER') && true === PODS_DISABLE_FILE_BROWSER)
                && !(defined('PODS_FILES_REQUIRE_LOGIN') && is_bool(PODS_FILES_REQUIRE_LOGIN) && true === PODS_FILES_REQUIRE_LOGIN && !is_user_logged_in())
                && !(defined('PODS_FILES_REQUIRE_LOGIN') && !is_bool(PODS_FILES_REQUIRE_LOGIN) && (!is_user_logged_in() || !current_user_can(PODS_FILES_REQUIRE_LOGIN)))) {
?>
    <input type="button" class="button" value="Browse Server" onclick="active_file = '<?php echo esc_attr($name); ?>'; fileBrowser();" />
<?php
        }
?>
    <div class="<?php echo esc_attr($css_classes); ?>">
<?php
        // Retrieve uploaded files
        $field_id = (int) $field['id'];
        $files = $this->get_field( $field[ 'name' ] );
        if ( !empty( $files ) && isset( $files[ 'ID' ] ) )
            $files = array( $files );

        if ( !empty( $files ) ) {
            foreach ( $files as $file ) {
                $filepath = $file[ 'guid' ];
                $filename = substr($filepath, strrpos($filepath, '/') + 1);
?>
        <div data-post-id="<?php echo (int) $file[ 'ID' ]; ?>" class="success">
            <div class="btn dropme"></div>
            <a href="<?php echo esc_attr( $file[ 'guid' ] ); ?>" target="_blank"><?php echo esc_html( $filename ); ?></a>
        </div>
<?php
            }
        }
?>
        </div>
    </div>
<?php
    }
    }
}

/*
==================================================
Multi-select PICK
==================================================
*/
elseif ('pick' == $type && 'multi' == pods_var( 'pick_format_type', $field, 'single', null, true ) ) {
?>
    <div class="<?php echo esc_attr($css_classes); ?>" id="<?php echo esc_attr($css_id); ?>">
<?php
        if (!empty($value)) {
            foreach ($value as $key => $val) {
                $active = (empty($val['active'])) ? '' : ' active';
?>
        <div class="option<?php echo $active; ?>" data-value="<?php echo esc_attr($val['id']); ?>"><?php echo esc_attr($val['name']); ?></div>
<?php
            }
        }
?>
    </div>
<?php
}
/*
==================================================
Single-select PICK
==================================================
*/
elseif ('pick' == $type && 'single' == pods_var( 'pick_format_type', $field, 'single', null, true ) ) {
?>
    <select name="<?php echo esc_attr($name); ?>" class="<?php echo esc_attr($css_classes); ?>" id="<?php echo esc_attr($css_id); ?>">
        <option value="">-- Select one --</option>
<?php
        if (!empty($value)) {
            foreach ($value as $key => $val) {
                $selected = (empty($val['active'])) ? '' : ' selected';
?>
        <option value="<?php echo esc_attr($val['id']); ?>"<?php echo $selected; ?>><?php echo esc_attr($val['name']); ?></option>
<?php
            }
        }
?>
    </select>
<?php
}
do_action("pods_input_field_type_{$type}", $field, $css_id, $css_classes, $value, $this);
$pods_type_exists[$type] = true;
?>
    </div>
    <div class="clear<?php echo esc_attr($hidden); ?>" id="spacer_<?php echo esc_attr($name); ?>"></div>
<?php
//post-field hooks
do_action('pods_post_input_field', $field, $css_id, $css_classes, $this);
do_action("pods_post_input_field_{$name}", $field, $css_id, $css_classes, $this);
do_action("pods_post_input_field_type_{$type}", $field, $css_id, $css_classes, $value, $this);