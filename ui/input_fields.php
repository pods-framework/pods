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

        require_once(ABSPATH . '/wp-admin/includes/template.php');

        if (!function_exists('wp_editor') && (!isset($coltype_exists[$coltype]) || empty($coltype_exists[$coltype]))) {
            // New TinyMCE API by azaozz
            require_once( PODS_DIR . '/ui/wp-editor/wp-editor.php' );
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
    <?php global $wp_version; if (version_compare($wp_version, '3.3', '>=')) { ?>
        <script type="text/javascript" src="<?php echo WP_INC_URL . '/js/plupload/plupload.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo WP_INC_URL . '/js/plupload/plupload.html4.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo WP_INC_URL . '/js/plupload/plupload.html5.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo WP_INC_URL . '/js/plupload/plupload.flash.js'; ?>"></script>
        <script type="text/javascript" src="<?php echo WP_INC_URL . '/js/plupload/plupload.silverlight.js'; ?>"></script>
    <?php } else { ?>
        <script type="text/javascript" src="<?php echo WP_INC_URL . '/js/swfupload/swfupload.js'; ?>"></script>
    <?php } ?>
<?php
            }
            $button_height = (function_exists('is_super_admin') ? 23 : 24);
?>
    <script type="text/javascript">
        jQuery(function() {
    <?php global $wp_version; if (version_compare($wp_version, '3.3', '>=')) { ?>
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

        <?php } else { ?>

            swfu_<?php echo esc_attr($name); ?> = new SWFUpload({
                button_text: '<span class="button">Browse + Upload</span>',
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
                        server_data = eval('('+server_data.match( /\{(.*)\}/gi )+')');
                        jQuery("#"+file.id).html('<div class="btn dropme"></div> <a href="' + server_data.guid + '" target="_blank">' + server_data.post_title + '</a>');
                        jQuery("#"+file.id).attr("class", "success");
                        jQuery("#"+file.id).data("post-id", server_data.ID);
                    }
                },
                upload_complete_handler: function(file) {
                    this.startUpload();
                }
            });
        <?php } ?>
        });
    </script>
    <div class="plupload-container" id="plupload-container-<?php echo esc_attr($css_id); ?>">
        <input type="button" class="button" id="<?php echo esc_attr($css_id); ?>" value="Browse + Upload" style="cursor: pointer" />
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