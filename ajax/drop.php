<?php
// Include the MySQL connection
include(realpath('../../../../wp-config.php'));

if ($_POST['auth'] != md5(AUTH_KEY))
{
    die('Error: Authentication failed');
}

foreach ($_POST as $key => $val)
{
    ${$key} = mysql_real_escape_string(stripslashes(trim($val)));
}

/*
==================================================
Drop a single column
==================================================
*/
if ($field_id = (int) $_POST['col'])
{
    $result = pod_query("SELECT name, coltype FROM {$table_prefix}pod_fields WHERE id = $field_id LIMIT 1");
    $row = mysql_fetch_assoc($result);
    $field_name = $row['name'];

    if ('pick' != $row['coltype'])
    {
        pod_query("ALTER TABLE {$table_prefix}pod_tbl_$dtname DROP COLUMN $field_name");
    }

    pod_query("UPDATE {$table_prefix}pod_fields SET sister_field_id = NULL WHERE sister_field_id = $field_id");
    pod_query("DELETE FROM {$table_prefix}pod_fields WHERE id = $field_id LIMIT 1");
    pod_query("DELETE FROM {$table_prefix}pod_rel WHERE field_id = $field_id");
}

/*
==================================================
Drop a single page
==================================================
*/
elseif ($page_id = (int) $_POST['page'])
{
    pod_query("DELETE FROM {$table_prefix}pod_pages WHERE id = $page_id LIMIT 1");
}

/*
==================================================
Drop a menu item and all children
==================================================
*/
elseif ($menu_id = (int) $_POST['menu_id'])
{
    $result = pod_query("SELECT lft, rgt, (rgt - lft + 1) AS width FROM {$table_prefix}pod_menu WHERE id = $menu_id LIMIT 1");
    $row = mysql_fetch_assoc($result);
    $lft = $row['lft'];
    $rgt = $row['rgt'];
    $width = $row['width'];

    pod_query("DELETE from {$table_prefix}pod_menu WHERE lft BETWEEN $lft AND $rgt");
    pod_query("UPDATE {$table_prefix}pod_menu SET rgt = rgt - $width WHERE rgt > $rgt");
    pod_query("UPDATE {$table_prefix}pod_menu SET lft = lft - $width WHERE lft > $rgt");
}

/*
==================================================
Drop a single content item
==================================================
*/
elseif ($pod_id = (int) $_POST['pod_id'])
{
    $sql = "
    SELECT
        p.tbl_row_id, t.name
    FROM
        {$table_prefix}pod p
    INNER JOIN
        {$table_prefix}pod_types t ON t.id = p.datatype
    WHERE
        p.id = $pod_id
    LIMIT
        1
    ";
    $result = pod_query($sql);
    $row = mysql_fetch_assoc($result);
    $dtname = $row['name'];
    $tbl_row_id = $row['tbl_row_id'];

    pod_query("DELETE FROM {$table_prefix}pod_tbl_$dtname WHERE id = $tbl_row_id LIMIT 1");
    pod_query("UPDATE {$table_prefix}pod_rel SET sister_pod_id = NULL WHERE sister_pod_id = $pod_id");
    pod_query("DELETE FROM {$table_prefix}pod WHERE id = $pod_id LIMIT 1");
    pod_query("DELETE FROM {$table_prefix}pod_rel WHERE pod_id = $pod_id");
}

/*
==================================================
Drop a single helper
==================================================
*/
elseif ($helper_id = (int) $_POST['helper'])
{
    pod_query("DELETE FROM {$table_prefix}pod_helpers WHERE id = $helper_id LIMIT 1");
}

/*
==================================================
Drop an entire datatype
==================================================
*/
elseif ($datatype_id = (int) $_POST['pod'])
{
    $fields = '0';
    pod_query("DELETE FROM {$table_prefix}pod_types WHERE id = $datatype_id LIMIT 1");
    $result = pod_query("SELECT id FROM {$table_prefix}pod_fields WHERE datatype = $datatype_id");
    while ($row = mysql_fetch_assoc($result))
    {
        $fields .= ', ' . $row['id'];
    }

    pod_query("UPDATE {$table_prefix}pod_fields SET sister_field_id = NULL WHERE sister_field_id IN ($fields)");
    pod_query("DELETE FROM {$table_prefix}pod_fields WHERE datatype = $datatype_id");
    pod_query("DELETE FROM {$table_prefix}pod_rel WHERE field_id IN ($fields)");
    pod_query("DELETE FROM {$table_prefix}pod WHERE datatype = $datatype_id");
    pod_query("DROP TABLE {$table_prefix}pod_tbl_$dtname");
}

