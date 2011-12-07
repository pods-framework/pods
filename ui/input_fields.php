<?php
$cache = PodCache::instance();
$form_count = $cache->form_count;

global $coltype_exists;

$name = $field['name'];
$label = $field['label'];
$comment = $field['comment'];
$coltype = $field['coltype'];
$input_helper = $field['input_helper'];
$hidden = empty($field['hidden']) ? '' : ' hidden';
$value = is_array($this->data[$name]) ? $this->data[$name] : stripslashes($this->data[$name]);
$css_id = 'pods_form' . $form_count . '_' . $name;

// The first 3 CSS classes will be DEPRECATED in 2.0.0
$css_classes = "form $coltype $name pods_field pods_field_$name pods_coltype_$coltype";
if (1 > $field['multiple']) {
    $css_classes = str_replace(' pick ', ' pick1 ', $css_classes);
}

//pre-field hooks
do_action('pods_pre_input_field', $field, $css_id, $css_classes, $value, $this);
do_action("pods_pre_input_field_$name", $field, $css_id, $css_classes, $value, $this);
do_action("pods_pre_input_field_type_$coltype", $field, $css_id, $css_classes, $value, $this);
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
    $check_function = $function_or_file;
    if ((!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE) && (!defined('PODS_HELPER_FUNCTIONS') || !PODS_HELPER_FUNCTIONS))
        $check_function = false;
    $check_file = null;
    if ((!defined('PODS_STRICT_MODE') || !PODS_STRICT_MODE) && (!defined('PODS_HELPER_FILES') || !PODS_HELPER_FILES))
        $check_file = false;
    if (false !== $check_function && false !== $check_file)
        $function_or_file = pods_function_or_file($function_or_file, $check_function, 'helper', $check_file);
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

    if (false === $content && false !== $function_or_file && isset($function_or_file['function']))
        echo $function_or_file['function']($coltype, $field, $css_id, $css_classes, $value, $this);
    elseif (false === $content && false !== $function_or_file && isset($function_or_file['file']))
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
elseif ('bool' == $coltype) {
    $value = empty($value) ? '' : ' checked';
?>
    <input name="<?php echo esc_attr($name); ?>" type="checkbox" class="<?php echo esc_attr($css_classes); ?>" id="<?php echo esc_attr($css_id); ?>"<?php echo $value; ?> />
<?php
}

/*
==================================================
Date picker
==================================================
*/
elseif ('date' == $coltype) {
    if (!isset($coltype_exists[$coltype]) || empty($coltype_exists[$coltype])) {
?>
    <script type="text/javascript" src="<?php echo PODS_URL; ?>/ui/js/date_input.js"></script>
    <script type="text/javascript">
        jQuery(function() {
            jQuery(".pods_form input.date").date_input();
        });
    </script>
<?php
    }
    $value = empty($value) ? date("Y-m-d H:i:s") : $value;
?>
    <input name="<?php echo esc_attr($name); ?>" type="text" class="<?php echo esc_attr($css_classes); ?>" id="<?php echo esc_attr($css_id); ?>" value="<?php echo esc_attr($value); ?>" />
<?php
}

/*
==================================================
Standard text box
==================================================
*/
elseif ('num' == $coltype || 'txt' == $coltype || 'slug' == $coltype) {
?>
    <input name="<?php echo esc_attr($name); ?>" type="text" class="<?php echo esc_attr($css_classes); ?>" id="<?php echo esc_attr($css_id); ?>" value="<?php echo esc_attr($value); ?>" maxlength="<?php echo ($coltype=='num')?15:128; ?>" />
<?php
}

/*
==================================================
Textarea box
==================================================
*/
elseif ('desc' == $coltype) {
    if (is_admin()) {
        $coltype = 'desc_tinymce';

        // New TinyMCE API by azaozz
        require_once(PODS_DIR . '/ui/wp-editor/wp-editor.php');
        require_once(ABSPATH . '/wp-admin/includes/template.php');

        if (!function_exists('wp_editor') && (!isset($coltype_exists[$coltype]) || empty($coltype_exists[$coltype]))) {
?>
    <style type="text/css" scoped="scoped">
        @import url("<?php echo PODS_URL; ?>/ui/wp-editor/editor-buttons.css");
    </style>
<?php
        }
        $css_classes = str_replace("form desc {$name}", "form {$coltype} {$name}", $css_classes) . ' wp-editor-area';

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
        if (!isset($coltype_exists[$coltype]) || empty($coltype_exists[$coltype])) {
?>
    <script type="text/javascript" src="<?php echo PODS_URL; ?>/ui/js/nicEdit.js"></script>
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
elseif ('code' == $coltype) {
?>
    <textarea name="<?php echo esc_attr($name); ?>" class="<?php echo esc_attr($css_classes); ?>" id="<?php echo esc_attr($css_id); ?>"><?php echo esc_textarea($value); ?></textarea>
<?php
}

/*
==================================================
File upload
==================================================
*/
elseif ('file' == $coltype) {
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

            if (!isset($coltype_exists[$coltype]) || empty($coltype_exists[$coltype])) {
?>
    <script type="text/javascript" src="<?php echo WP_INC_URL . '/js/swfupload/swfupload.js'; ?>"></script>
<?php
            }
            $button_height = (function_exists('is_super_admin') ? 23 : 24);
?>
    <script type="text/javascript">
        jQuery(function() {
            swfu_<?php echo esc_attr($name); ?> = new SWFUpload({
                button_text: '<span class="button">Select + Upload</span>',
                button_text_style: '.button { text-align:center; color:#464646; font-size:11px; font-family:"Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif; }',
                button_width: "132",
                button_height: "<?php echo $button_height; ?>",
                button_text_top_padding: 3,
                button_image_url: "<?php echo WP_INC_URL; ?>/images/upload.png",
                button_placeholder_id: "<?php echo esc_attr($css_id); ?>",
                button_cursor: SWFUpload.CURSOR.HAND,
                button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
                upload_url: "<?php echo PODS_URL; ?>/ui/ajax/misc.php",
                flash_url: "<?php echo WP_INC_URL; ?>/js/swfupload/swfupload.swf",
                file_types: "*.*",
                file_size_limit: "<?php echo esc_attr(wp_max_upload_size()); ?>",
                post_params: {"action": "wp_handle_upload_advanced", "_wpnonce": "<?php echo wp_create_nonce('pods-wp_handle_upload_advanced'); ?>", "auth_cookie": "<?php echo (is_ssl() ? esc_attr($_COOKIE[SECURE_AUTH_COOKIE]) : esc_attr($_COOKIE[AUTH_COOKIE])); ?>", "logged_in_cookie": "<?php echo esc_attr($_COOKIE[LOGGED_IN_COOKIE]); ?>"},
                file_dialog_complete_handler: function(num_files, num_queued_files, total_queued_files) {
                    this.startUpload();
                },
                file_queued_handler: function(file) {
                    jQuery(".rightside.<?php echo esc_attr($name); ?> .form").append('<div id="' + file.id + '">' + file.name + '<div class="pods-progress"><div class="pods-bar"></div></div></div>');
                },
                upload_progress_handler: function(file, bytes_complete, bytes_total) {
                    var percent = Math.ceil(100 * (bytes_complete / bytes_total));
                    jQuery("#"+file.id+" .pods-bar").css("width", percent + "%");
                },
                upload_success_handler: function(file, server_data, response) {
                    jQuery("#"+file.id+" .pods-progress").remove();

                    if ("Error" == server_data.substr(0, 5)) {
                        server_data = server_data.substr(7);
                        jQuery("#"+file.id).append(server_data);
                    }
                    else if ("<e>" == server_data.substr(0, 3)) {
                        jQuery("#"+file.id).append(server_data);
                    }
                    else {
                        server_data = eval('('+server_data+')');
                        jQuery("#"+file.id).html('<div class="btn dropme"></div> <a href="' + server_data.guid + '" target="_blank">' + server_data.post_title + '</a>');
                        jQuery("#"+file.id).attr("class", "success");
                        jQuery("#"+file.id).attr("id", server_data.ID);
                    }
                },
                upload_complete_handler: function(file) {
                    this.startUpload();
                }
            });
        });
    </script>
    <input type="button" id="<?php echo esc_attr($css_id); ?>" value="swfupload not loaded" />
<?php
        }
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
        $pod_id = (int) $this->get_pod_id();
        $sql = "
        SELECT
            p.ID, p.guid
        FROM
            @wp_pod_rel r
        INNER JOIN
            @wp_posts p ON p.post_type = 'attachment' AND p.ID = r.tbl_row_id
        WHERE
            r.field_id = {$field_id} AND r.pod_id = {$pod_id}
        ";
        $result = pod_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $filepath = $row['guid'];
            $filename = substr($filepath, strrpos($filepath, '/') + 1);
?>
        <div id="<?php echo esc_attr($row['ID']); ?>" class="success">
            <div class="btn dropme"></div> <a href="<?php echo esc_attr($row['guid']); ?>" target="_blank"><?php echo esc_html($filename); ?></a>
        </div>
<?php
        }
?>
    </div>
<?php
    }
}

/*
==================================================
Multi-select PICK
==================================================
*/
elseif ('pick' == $coltype && 0 < $field['multiple']) {
?>
    <div class="<?php echo esc_attr($css_classes); ?>" id="<?php echo esc_attr($css_id); ?>">
<?php
        if (!empty($value)) {
            foreach ($value as $key => $val) {
                $active = empty($val['active']) ? '' : ' active';
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
elseif ('pick' == $coltype) {
?>
    <select name="<?php echo esc_attr($name); ?>" class="<?php echo esc_attr($css_classes); ?>" id="<?php echo esc_attr($css_id); ?>">
        <option value="">-- Select one --</option>
<?php
        if (!empty($value)) {
            foreach ($value as $key => $val) {
                $selected = empty($val['active']) ? '' : ' selected';
?>
        <option value="<?php echo esc_attr($val['id']); ?>"<?php echo $selected; ?>><?php echo esc_attr($val['name']); ?></option>
<?php
            }
        }
?>
    </select>
<?php
}
do_action("pods_input_field_type_$coltype", $field, $css_id, $css_classes, $value, $this);
$coltype_exists[$coltype] = true;
?>
    </div>
    <div class="clear<?php echo esc_attr($hidden); ?>" id="spacer_<?php echo esc_attr($name); ?>"></div>
<?php
//post-field hooks
do_action('pods_post_input_field', $field, $css_id, $css_classes, $this);
do_action("pods_post_input_field_$name", $field, $css_id, $css_classes, $this);
do_action("pods_post_input_field_type_$coltype", $field, $css_id, $css_classes, $value, $this);