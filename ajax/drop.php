<?php
// Include the MySQL connection
include(realpath('../../../../wp-config.php'));

// Delete a single column
if ($field_id = (int) $_GET['col'])
{
    $sql = "SELECT name FROM wp_pod_fields WHERE id = $field_id LIMIT 1";
    $result = mysql_query($sql) or die(mysql_error());
    $row = mysql_fetch_assoc($result);
    $field_name = $row['field_name'];

    mysql_query("ALTER TABLE tbl_$dtname DROP COLUMN $field_name");
    mysql_query("ALTER TABLE wp_pod_fields SET sister_field_id = NULL WHERE sister_field_id = $field_id");
    mysql_query("DELETE FROM wp_pod_fields WHERE id = $field_id LIMIT 1");
    mysql_query("DELETE FROM wp_pod_rel WHERE field_id = $field_id");
}

// Delete an entire datatype
elseif ($datatype_id = (int) $_GET['pod'])
{
    $fields = '0';
    mysql_query("DELETE FROM wp_pod_types WHERE id = $datatype_id LIMIT 1");
    $result = mysql_query("SELECT id FROM wp_pod_fields WHERE datatype = $datatype_id");
    while ($row = mysql_fetch_assoc($result))
    {
        $fields .= ', ' . $row['id'];
    }
    mysql_query("ALTER TABLE wp_pod_fields SET sister_field_id = NULL WHERE sister_field_id IN ($fields)");
    mysql_query("DELETE FROM wp_pod_fields WHERE datatype = $datatype_id");
    mysql_query("DELETE FROM wp_pod_rel WHERE field_id IN ($fields)");
    mysql_query("DROP TABLE tbl_$dtname");
}

