<?php
$name = $field['name'];
$label = $field['label'];
$comment = $field['comment'];
$coltype = $field['coltype'];
$input_helper = $field['input_helper'];
$hidden = empty($field['hidden']) ? '' : ' hidden';
$value = is_array($this->data[$name]) ? $this->data[$name] : stripslashes($this->data[$name]);
?>
    <div class="leftside<?php echo $hidden; ?>">
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
    <div class="rightside<?php echo $hidden; ?>">
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
File upload
==================================================
*/
elseif ('file' == $coltype)
{
?>
    <input type="text" class="form file <?php echo $name; ?>" value="<?php echo $value; ?>" />
    <a href="javascript:;" onclick="active_file = '<?php echo $name; ?>'; jQuery('#dialog').jqmShow()">select</a> after
    <a href="<?php echo get_bloginfo('url'); ?>/wp-admin/media-upload.php" target="_blank">uploading</a>
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
    <textarea class="form code <?php echo $name; ?>" id="code-<?php echo $name; ?>"><?php echo $value; ?></textarea>
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
?>
    </div>
    <div class="clear"></div>
