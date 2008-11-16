<?php
// Include the MySQL connection
include(realpath('../../../../wp-config.php'));

// Get all Pod columns
if ($id = (int) $_GET['id'])
{
    $result = mysql_query("SELECT * FROM wp_pod_types WHERE id = $id LIMIT 1");
    $module = mysql_fetch_assoc($result);

    $sql = "
        SELECT
            id, name, coltype, pickval, weight
        FROM
            wp_pod_fields
        WHERE
            datatype = " . $module['id'] . "
        ORDER BY
            weight
        ";

    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result))
    {
        $fields[] = $row;
    }

    // Combine the fields into the $module array
    $module['fields'] = $fields;

    // Encode the array to JSON
    echo json_encode($module);
}
// Get a single Pod columnelseif ($field_id = (int) $_GET['col'])
{
    $result = mysql_query("SELECT name, label, coltype, pickval, sister_field_id, required FROM wp_pod_fields WHERE id = $field_id LIMIT 1");
    $row = mysql_fetch_assoc($result);
    echo json_encode($row);
}
// Get a custom page
elseif ($page_id = (int) $_GET['page'])
{
    $result = mysql_query("SELECT phpcode FROM wp_pod_pages WHERE id = $page_id LIMIT 1");
    $row = mysql_fetch_assoc($result);
    echo $row['phpcode'];
}

