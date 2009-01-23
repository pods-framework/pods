<?php
// Include the MySQL connection
include(realpath('../../../../wp-config.php'));

if ($_POST['auth'] != md5(AUTH_KEY))
{
    die('Error: Authentication failed');
}

// Get all Pod columns
if ($id = (int) $_POST['id'])
{
    $result = pod_query("SELECT * FROM {$table_prefix}pod_types WHERE id = $id LIMIT 1");
    $module = mysql_fetch_assoc($result);

    $sql = "
        SELECT
            id, name, coltype, pickval, required, weight
        FROM
            {$table_prefix}pod_fields
        WHERE
            datatype = " . $module['id'] . "
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
// Get a single Pod column
elseif ($field_id = (int) $_POST['col'])
{
    $result = pod_query("SELECT name, label, comment, coltype, pickval, sister_field_id, required FROM {$table_prefix}pod_fields WHERE id = $field_id LIMIT 1");
    $row = mysql_fetch_assoc($result);
    echo json_encode($row);
}

