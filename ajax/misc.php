<?php
// Include the MySQL connection
include(realpath('../../../../wp-config.php'));
include(realpath('../../../../wp-admin/includes/admin.php'));

foreach ($_POST as $key => $val)
{
    ${$key} = mysql_real_escape_string(stripslashes(trim($val)));
}

/*
==================================================
Upload a new file
==================================================
*/
if ('wp_handle_upload' == $action)
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
