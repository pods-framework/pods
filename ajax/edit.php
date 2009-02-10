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

$name = pods_clean_name($name);

$dbtypes = array(
    'bool' => 'bool',
    'date' => 'datetime',
    'num' => 'decimal(9,2)',
    'txt' => 'varchar(128)',
    'file' => 'varchar(128)',
    'code' => 'mediumtext',
    'desc' => 'mediumtext'
);

/*
==================================================
Change a column's weight
==================================================
*/
if ('move' == $action)
{
    $result = pod_query("SELECT id FROM {$table_prefix}pod_fields WHERE datatype = $datatype ORDER BY weight");
    while ($row = mysql_fetch_assoc($result))
    {
        $fields[] = $row['id'];
    }

    $col_position = array_search($col, $fields);

    if ('down' == $dir)
    {
        $array_count = count($fields);
        if ($col_position < ($array_count - 1))
        {
            $tmp = $fields[$col_position + 1];
            $fields[$col_position + 1] = $col;
            $fields[$col_position] = $tmp;
        }
    }
    if ('up' == $dir)
    {
        if (0 < $col_position)
        {
            $tmp = $fields[$col_position - 1];
            $fields[$col_position - 1] = $col;
            $fields[$col_position] = $tmp;
        }
    }
    foreach ($fields as $key => $val)
    {
        $weight = ($key * 1);
        pod_query("UPDATE {$table_prefix}pod_fields SET weight = $weight WHERE id = $val LIMIT 1");
    }
}

/*
==================================================
Edit a column
==================================================
*/
elseif ('edit' == $action)
{
    if ('id' == $name || 'type' == $name)
    {
        die("Error: $name is not editable.");
    }

    $result = pod_query("SELECT id FROM {$table_prefix}pod_fields WHERE datatype = $datatype AND id != $field_id AND name = '$name' LIMIT 1");
    if (0 < mysql_num_rows($result))
    {
        die("Error: The $name column cannot be cloned.");
    }

    $sql = "SELECT name, coltype FROM {$table_prefix}pod_fields WHERE id = $field_id LIMIT 1";
    $result = pod_query($sql) or die(mysql_error());

    if (0 < mysql_num_rows($result))
    {
        $row = mysql_fetch_assoc($result);
        $old_coltype = $row['coltype'];
        $old_name = $row['name'];

        $dbtype = $dbtypes[$coltype];
        $pickval = ('pick' != $coltype || empty($pickval)) ? 'NULL' : "'$pickval'";
        $sister_field_id = ('pick' != $coltype || empty($sister_field_id)) ? 0 : "'$sister_field_id'";

        if ($coltype != $old_coltype && 'pick' == $coltype)
        {
            pod_query("ALTER TABLE {$table_prefix}pod_tbl_$dtname DROP COLUMN $old_name");
        }
        elseif ($coltype != $old_coltype && 'pick' == $old_coltype)
        {
            pod_query("ALTER TABLE {$table_prefix}pod_tbl_$dtname ADD COLUMN $name $dbtype", 'Cannot create column');
            pod_query("UPDATE {$table_prefix}pod_fields SET sister_field_id = NULL WHERE sister_field_id = $field_id");
            pod_query("DELETE FROM {$table_prefix}pod_rel WHERE field_id = $field_id");
        }
        elseif ('pick' != $coltype)
        {
            pod_query("ALTER TABLE {$table_prefix}pod_tbl_$dtname CHANGE $old_name $name $dbtype");
        }

        $sql = "
        UPDATE
            {$table_prefix}pod_fields
        SET
            name = '$name',
            label = '$label',
            comment = '$comment',
            coltype = '$coltype',
            pickval = $pickval,
            sister_field_id = $sister_field_id,
            required = '$required'
        WHERE
            id = $field_id
        LIMIT
            1
        ";
        pod_query($sql, 'Cannot edit column');
    }
}

/*
==================================================
Edit a page
==================================================
*/
elseif ('editpage' == $action)
{
    pod_query("UPDATE {$table_prefix}pod_pages SET title = '$page_title', phpcode = '$phpcode' WHERE id = $page_id LIMIT 1");
}

/*
==================================================
Edit a menu item
==================================================
*/
elseif ('editmenu' == $action)
{
    pod_query("UPDATE {$table_prefix}pod_menu SET uri = '$menu_uri', title = '$menu_title' WHERE id = $menu_id LIMIT 1");
}

/*
==================================================
Edit a widget
==================================================
*/
elseif ('editwidget' == $action)
{
    pod_query("UPDATE {$table_prefix}pod_widgets SET phpcode = '$phpcode' WHERE id = $widget_id LIMIT 1");
}

/*
==================================================
Edit roles
==================================================
*/
elseif ('editroles' == $action)
{
    $roles = array();
    foreach ($_POST as $key => $val)
    {
        if ('action' != $key && 'auth' != $key)
        {
            $tmp = empty($val) ? array() : explode(',', $val);
            $roles[$key] = $tmp;
        }
    }
    $roles = serialize($roles);
    delete_option('pods_roles');
    add_option('pods_roles', $roles);
}

/*
==================================================
Edit a pod
==================================================
*/
else
{
    $sql = "
    UPDATE
        {$table_prefix}pod_types
    SET
        label = '$label',
        is_toplevel = '$is_toplevel',
        list_filters = '$list_filters',
        tpl_detail = '$tpl_detail',
        tpl_list = '$tpl_list'
    WHERE
        id = $datatype
    LIMIT
        1
    ";
    pod_query($sql, 'Cannot change Pod details');
}

