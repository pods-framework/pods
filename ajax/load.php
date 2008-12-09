<?php
// Include the MySQL connection
include(realpath('../../../../wp-config.php'));

// Get all Pod columns
if ($id = (int) $_GET['id'])
{
    $result = mysql_query("SELECT * FROM {$table_prefix}pod_types WHERE id = $id LIMIT 1");
    $module = mysql_fetch_assoc($result);

    $sql = "
        SELECT
            id, name, coltype, pickval, weight
        FROM
            {$table_prefix}pod_fields
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
    $result = mysql_query("SELECT name, label, coltype, pickval, sister_field_id, required FROM {$table_prefix}pod_fields WHERE id = $field_id LIMIT 1");
    $row = mysql_fetch_assoc($result);
    echo json_encode($row);
}

