<?php
// Include the MySQL connection
include(realpath('../../../../wp-config.php'));

foreach ($_GET as $key => $val)
{
    ${$key} = mysql_real_escape_string(stripslashes(trim($val)));
}

// Delete a single column
if ($field_id = (int) $_GET['col'])
{
    $sql = "SELECT name FROM {$table_prefix}pod_fields WHERE id = $field_id LIMIT 1";
    $result = mysql_query($sql) or die(mysql_error());
    $row = mysql_fetch_assoc($result);
    $field_name = $row['field_name'];

    mysql_query("ALTER TABLE {$table_prefix}tbl_$dtname DROP COLUMN $field_name");
    mysql_query("ALTER TABLE {$table_prefix}pod_fields SET sister_field_id = NULL WHERE sister_field_id = $field_id");
    mysql_query("DELETE FROM {$table_prefix}pod_fields WHERE id = $field_id LIMIT 1");
    mysql_query("DELETE FROM {$table_prefix}pod_rel WHERE field_id = $field_id");
}

// Delete a single page
elseif ($page_id = (int) $_GET['page'])
{
    mysql_query("DELETE FROM {$table_prefix}pod_pages WHERE id = $page_id LIMIT 1");
}

// Delete a single content item
elseif ($post_id = (int) $_GET['post_id'])
{
    $sql = "
    SELECT
        t.name, p.row_id
    FROM
        {$table_prefix}pod p
    INNER JOIN
        {$table_prefix}pod_types t ON t.id = p.datatype
    WHERE
        p.post_id = $post_id
    LIMIT
        1
    ";
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);

    mysql_query("DELETE FROM {$table_prefix}tbl_$row[0] WHERE id = $row[1] LIMIT 1");
    mysql_query("UPDATE {$table_prefix}pod_rel SET sister_post_id = NULL WHERE sister_post_id = $post_id");
    mysql_query("DELETE FROM {$table_prefix}pod WHERE post_id = $post_id LIMIT 1");
    mysql_query("DELETE FROM {$table_prefix}posts WHERE ID = $post_id LIMIT 1");
    mysql_query("DELETE FROM {$table_prefix}pod_rel WHERE post_id = $post_id");
}

// Delete a single widget
elseif ($widget_id = (int) $_GET['widget'])
{
    mysql_query("DELETE FROM {$table_prefix}pod_widgets WHERE id = $widget_id LIMIT 1");
}

// Delete an entire datatype
elseif ($datatype_id = (int) $_GET['pod'])
{
    $fields = '0';
    mysql_query("DELETE FROM {$table_prefix}pod_types WHERE id = $datatype_id LIMIT 1");
    $result = mysql_query("SELECT id FROM {$table_prefix}pod_fields WHERE datatype = $datatype_id");
    while ($row = mysql_fetch_assoc($result))
    {
        $fields .= ', ' . $row['id'];
    }
    mysql_query("ALTER TABLE {$table_prefix}pod_fields SET sister_field_id = NULL WHERE sister_field_id IN ($fields)");
    mysql_query("DELETE FROM {$table_prefix}pod_fields WHERE datatype = $datatype_id");
    mysql_query("DELETE FROM {$table_prefix}pod_rel WHERE field_id IN ($fields)");
    mysql_query("DROP TABLE {$table_prefix}tbl_$dtname");
}

