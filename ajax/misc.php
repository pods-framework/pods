<?php
// Include the MySQL connection
require_once(realpath('../../../../wp-load.php'));
require_once(realpath('../../../../wp-admin/includes/admin.php'));

foreach ($_POST as $key => $val)
{
    ${$key} = mysql_real_escape_string(stripslashes(trim($val)));
}

/*
==================================================
Load file list
==================================================
*/
if ('browse_files' == $action)
{
    $search = empty($search) ? '' : "AND guid LIKE '%$search%'";

    $sql = "
    SELECT
        id, guid
    FROM
        @wp_posts
    WHERE
        post_type = 'attachment'
        $search
    ORDER BY
        guid ASC
    ";

    $result = pod_query($sql);

    if (0 < mysql_num_rows($result))
    {
        while ($row = mysql_fetch_assoc($result))
        {
            $guid = substr($row['guid'], strrpos($row['guid'], '/') + 1);
    ?>
        <div class="file_match" rel="<?php echo $row['id']; ?>"><?php echo $guid; ?></div>
    <?php
        }
    }
    else
    {
        echo 'Nothing found.';
    }
}

/*
==================================================
Upload a new file
==================================================
*/
elseif ('wp_handle_upload' == $action)
{
    $attachment_id = media_handle_upload('Filedata', 0);
    if (is_object($attachment_id))
    {
        $errors = array();
        foreach ($attachment_id->errors['upload_error'] as $error_code => $error_message)
        {
            $errors[] = $error_message;
        }
        echo 'Error: <div style="color:red">' . implode('</div><div>', $errors) . '</div>';
    }
    else
    {
        echo $attachment_id;
    }
}
