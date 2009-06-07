<?php
// Include the MySQL connection
include(realpath('../../../../wp-config.php'));

if ($_POST['auth'] != md5(AUTH_KEY))
{
    die('Error: Authentication failed');
}

/*
==================================================
Get all pod columns
==================================================
*/
if ($id = (int) $_POST['id'])
{
    $result = pod_query("SELECT * FROM @wp_pod_types WHERE id = $id LIMIT 1");
    $module = mysql_fetch_assoc($result);

    $sql = "
        SELECT
            id, name, coltype, pickval, required, weight
        FROM
            @wp_pod_fields
        WHERE
            datatype = $id
        ORDER BY
            weight
        ";

    $result = pod_query($sql);
    while ($row = mysql_fetch_assoc($result))
    {
        $fields[] = $row;
    }

    // Combine the fields into the $module array
    $module['fields'] = $fields;

    // Encode the array to JSON
    echo json_encode($module);
}

/*
==================================================
Get a menu item
==================================================
*/
elseif ($menu_id = (int) $_POST['menu_id'])
{
    $result = pod_query("SELECT uri, title FROM @wp_pod_menu WHERE id = $menu_id LIMIT 1");
    $row = mysql_fetch_assoc($result);
    echo json_encode($row);
}

/*
==================================================
Get a pod column
==================================================
*/
elseif ($field_id = (int) $_POST['col'])
{
    $result = pod_query("SELECT * FROM @wp_pod_fields WHERE id = $field_id LIMIT 1");
    $row = mysql_fetch_assoc($result);
    echo json_encode($row);
}

/*
==================================================
Get a pod template
==================================================
*/
elseif ($template_id = (int) $_POST['template_id'])
{
    $result = pod_query("SELECT * FROM @wp_pod_templates WHERE id = $template_id LIMIT 1");
    $row = mysql_fetch_assoc($result);
    echo json_encode($row);
}

/*
==================================================
Get a pod page
==================================================
*/
elseif ($page_id = (int) $_POST['page_id'])
{
    $result = pod_query("SELECT * FROM @wp_pod_pages WHERE id = $page_id LIMIT 1");
    $row = mysql_fetch_assoc($result);
    echo json_encode($row);
}

/*
==================================================
Get a helper
==================================================
*/
elseif ($helper_id = (int) $_POST['helper_id'])
{
    $result = pod_query("SELECT * FROM @wp_pod_helpers WHERE id = $helper_id LIMIT 1");
    $row = mysql_fetch_assoc($result);
    echo json_encode($row);
}

