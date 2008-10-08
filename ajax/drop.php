<?php
// Include the MySQL connection
include(realpath('../../../../wp-config.php'));

// Delete a single column
if ($field_id = (int) $_GET['col'])
{
    $sql = "
    SELECT
        f.id AS field_id, f.name AS field_name, t.name AS module_name
    FROM
        wp_pod_fields f
    INNER JOIN
        wp_pod_types t ON t.id = f.datatype
    WHERE
        f.id = $field_id
    LIMIT
        1
    ";
    $result = mysql_query($sql) or die(mysql_error());
    $row = mysql_fetch_assoc($result);
    $field_id = $row['field_id'];
    $field_name = $row['field_name'];
    $module_name = $row['module_name'];

    mysql_query("ALTER TABLE tbl_$module_name DROP COLUMN $field_name");
    mysql_query("ALTER TABLE wp_pod_fields SET sister_field_id = NULL WHERE sister_field_id = $field_id");
    mysql_query("DELETE FROM wp_pod_fields WHERE id = $field_id LIMIT 1");
    mysql_query("DELETE FROM wp_pod_rel WHERE field_id = $field_id");
}

// Delete an entire datatype
elseif ($datatype_id = (int) $_GET['pod'])
{
    $result = mysql_query("SELECT name FROM wp_pod_types WHERE id = $datatype_id LIMIT 1");
    $row = mysql_fetch_assoc($result);
    $datatype_name = $row['name'];

    mysql_query("DELETE FROM wp_pod_types WHERE id = $datatype_id LIMIT 1");
    $result = mysql_query("SELECT id FROM wp_pod_fields WHERE datatype = $datatype_id");
    $fields = '0';
    while ($row = mysql_fetch_assoc($result))
    {
        $fields .= ', ' . $row['id'];
    }
    mysql_query("ALTER TABLE wp_pod_fields SET sister_field_id = NULL WHERE sister_field_id IN ($fields)");
    mysql_query("DELETE FROM wp_pod_fields WHERE datatype = $datatype_id");
    mysql_query("DELETE FROM wp_pod_rel WHERE field_id IN ($fields)");
    mysql_query("DROP TABLE tbl_$datatype_name");
}

