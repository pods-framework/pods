<?php
global $coltype_exists;

$name = $field['name'];
$label = $field['label'];
$comment = $field['comment'];
$coltype = $field['coltype'];
$input_helper = $field['input_helper'];
$hidden = empty($field['hidden']) ? '' : ' hidden';
$value = is_array($this->data[$name]) ? $this->data[$name] : stripslashes($this->data[$name]);
?>
    <div class="leftside <?php echo $name . $hidden; ?>">
        <?php echo $label; ?>
<?php
if (!empty($comment))
{
?>
        <div class="comment"><?php echo $comment; ?></div>
<?php
}
?>
    </div>
    <div class="rightside <?php echo $name . $hidden; ?>">
<?php
/*
==================================================
Generate the input helper
==================================================
*/
if (!empty($input_helper))
{
    eval("?>$input_helper");
}

/*
==================================================
Boolean checkbox
==================================================
*/
elseif ('bool' == $coltype)
{
    $value = empty($value) ? '' : ' checked';
?>
    <input type="checkbox" class="form bool <?php echo $name; ?>"<?php echo $value; ?> />
<?php
}

/*
==================================================
Date picker
==================================================
*/
elseif ('date' == $coltype)
{
    $value = empty($value) ? date("Y-m-d H:i:s") : $value;
?>
    <input type="text" class="form date <?php echo $name; ?>" value="<?php echo $value; ?>" />
<?php
}

/*
==================================================
Standard text box
==================================================
*/
elseif ('num' == $coltype || 'txt' == $coltype || 'slug' == $coltype)
{
?>
    <input type="text" class="form <?php echo $coltype . ' ' . $name; ?>" value="<?php echo htmlspecialchars($value); ?>" />
<?php
}

/*
==================================================
Textarea box
==================================================
*/
elseif ('desc' == $coltype)
{
    if (false === isset($coltype_counter[$coltype]))
    {
?>
<script type="text/javascript" src="<?php echo PODS_URL; ?>/js/nicEdit.js"></script>
<?php
    }
?>
    <textarea class="form desc <?php echo $name; ?>" id="desc-<?php echo $name; ?>"><?php echo $value; ?></textarea>
<?php
}

/*
==================================================
Textarea box (no WYSIWYG)
==================================================
*/
elseif ('code' == $coltype)
{
?>
    <textarea class="form code <?php echo $name; ?>" id="code-<?php echo $name; ?>"><?php echo htmlentities($value); ?></textarea>
<?php
}

/*
==================================================
File upload
==================================================
*/
elseif ('file' == $coltype)
{
    require_once(realpath(ABSPATH . '/wp-admin/includes/template.php'));

    if (false === isset($coltype_exists[$coltype]))
    {
?>
<script type="text/javascript" src="<?php echo WP_INC_URL . '/js/swfupload/swfupload.js'; ?>"></script>
<?php
    }
?>
<script type="text/javascript">
jQuery(function() {
    swfu_<?php echo $name; ?> = new SWFUpload({
        button_text: '<span class="button">Select + Upload</span>',
        button_text_style: '.button { text-align:center; color:#464646; font-size:11px; font-family:"Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif; }',
        button_width: "132",
        button_height: "24",
        button_text_top_padding: 3,
        button_image_url: "<?php echo WP_INC_URL; ?>/images/upload.png",
        button_placeholder_id: "btn_<?php echo $name; ?>",
        button_cursor: SWFUpload.CURSOR.HAND,
        button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
        upload_url: "<?php echo PODS_URL; ?>/ajax/misc.php",
        flash_url: "<?php echo WP_INC_URL; ?>/js/swfupload/swfupload.swf",
        file_types: "*.*",
        file_size_limit: "<?php echo wp_max_upload_size(); ?>",
        post_params: {"action": "wp_handle_upload"},
        file_dialog_complete_handler: function(num_files, num_queued_files, total_queued_files) {
            this.startUpload();
        },
        file_queued_handler: function(file) {
            jQuery(".rightside.<?php echo $name; ?> .form").append('<div id="' + file.id + '">' + file.name + '<div class="pods-progress"><div class="pods-bar"></div></div></div>');
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
            else {
                jQuery("#"+file.id).prepend('<div class="btn dropme"></div>');
                jQuery("#"+file.id).attr("class", "success");
                jQuery("#"+file.id).attr("id", server_data);
            }
        },
        upload_complete_handler: function(file) {
            this.startUpload();
        }
    });
});
</script>
    <input type="button" id="btn_<?php echo $name; ?>" value="swfupload not loaded" />
    <input type="button" class="button" value="Browse Server" onclick="active_file = '<?php echo $name; ?>'; fileBrowser()" />
    <div class="form file <?php echo $name; ?>">
<?php
    // Retrieve uploaded files
    $field_id = $field['id'];
    $pod_id = $this->get_pod_id();
    $sql = "
    SELECT
        p.ID, p.guid
    FROM
        @wp_pod_rel r
    INNER JOIN
        @wp_posts p ON p.post_type = 'attachment' AND p.ID = r.tbl_row_id
    WHERE
        r.pod_id = '$pod_id' AND r.field_id = '$field_id'
    ";
    $result = pod_query($sql);
    while ($row = mysql_fetch_assoc($result))
    {
        $filepath = $row['guid'];
        $filename = substr($filepath, strrpos($filepath, '/') + 1);
?>
        <div id="<?php echo $row['ID']; ?>" class="success">
            <div class="btn dropme"></div><?php echo $filename; ?>
        </div>
<?php
    }
?>
    </div>
<?php
}

/*
==================================================
PICK select box
==================================================
*/
else
{
    // Multi-select
    if (1 == $field['multiple'])
    {
?>
    <div class="form pick <?php echo $name; ?>">
<?php
        if (!empty($value))
        {
            foreach ($value as $key => $val)
            {
                $active = empty($val['active']) ? '' : ' active';
?>
        <div class="option<?php echo $active; ?>" value="<?php echo $val['id']; ?>"><?php echo $val['name']; ?></div>
<?php
            }
        }
?>
    </div>
<?php
    }
    else
    {
?>
    <select class="form pick1 <?php echo $name; ?>">
        <option value="">-- Select one --</option>
<?php
        if (!empty($value))
        {
            foreach ($value as $key => $val)
            {
                $selected = empty($val['active']) ? '' : ' selected';
?>
        <option value="<?php echo $val['id']; ?>"<?php echo $selected; ?>><?php echo $val['name']; ?></option>
<?php
            }
        }
?>
    </select>
<?php
    }
}
$coltype_exists[$coltype] = true;
?>
    </div>
    <div class="clear<?php echo $hidden; ?>"></div>
