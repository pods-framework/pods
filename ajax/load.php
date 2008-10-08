<?php
// Include the MySQL connection
include(realpath('../../../../wp-config.php'));

// Declare the $_GET variables
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
