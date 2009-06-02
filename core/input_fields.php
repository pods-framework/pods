<?php
$data = is_array($this->data[$name]) ? $this->data[$name] : stripslashes($this->data[$name]);
$hidden = empty($attr['hidden']) ? '' : ' hidden';
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
// Boolean checkbox
if ('bool' == $coltype)
{
    $data = empty($data) ? '' : ' checked';
?>
    <input type="checkbox" class="form bool <?php echo $name; ?>"<?php echo $data; ?> />
<?php
}
elseif ('date' == $coltype)
{
    $data = empty($data) ? date("Y-m-d H:i:s") : $data;
?>
    <input type="text" class="form date <?php echo $name; ?>" value="<?php echo $data; ?>" />
<?php
}
// File upload box
elseif ('file' == $coltype)
{
?>
    <input type="text" class="form file <?php echo $name; ?>" value="<?php echo $data; ?>" />
    <a href="javascript:;" onclick="active_file = '<?php echo $name; ?>'; jQuery('#dialog').jqmShow()">select</a> after
    <a href="<?php echo get_bloginfo('url'); ?>/wp-admin/media-upload.php" target="_blank">uploading</a>
<?php
}
// Standard text box
elseif ('num' == $coltype || 'txt' == $coltype || 'slug' == $coltype)
{
?>
    <input type="text" class="form <?php echo $coltype . ' ' . $name; ?>" value="<?php echo str_replace('"', '&quot;', $data); ?>" />
<?php
}
// Textarea box
elseif ('desc' == $coltype)
{
?>
    <textarea class="form desc <?php echo $name; ?>" id="desc-<?php echo $name; ?>"><?php echo $data; ?></textarea>
<?php
}
// Textarea box (without WYSIWYG)
elseif ('code' == $coltype)
{
?>
    <textarea class="form code <?php echo $name; ?>" id="code-<?php echo $name; ?>"><?php echo $data; ?></textarea>
<?php
}
else
{
    // Multi-select list
    if (1 == $attr['multiple'])
    {
?>
    <div class="form pick <?php echo $name; ?>">
<?php
        if (!empty($data))
        {
            foreach ($data as $key => $val)
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
    // Single-select list
    else
    {
?>
    <select class="form pick1 <?php echo $name; ?>">
        <option value="">-- Select one --</option>
<?php
        if (!empty($data))
        {
            foreach ($data as $key => $val)
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
