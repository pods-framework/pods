<?php
// Include the MySQL connection
include(realpath('../../../../wp-config.php'));

foreach ($_GET as $key => $val)
{
    ${$key} = mysql_real_escape_string(stripslashes(trim($val)));
}

$dbtypes = array(
    'bool' => 'bool',
    'date' => 'datetime',
    'num' => 'decimal(9,2)',
    'txt' => 'varchar(128)',
    'file' => 'varchar(128)',
    'desc' => 'text'
);

if ('move' == $action)
{
    $result = mysql_query("SELECT id FROM wp_pod_fields WHERE datatype = $datatype ORDER BY weight");
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
        mysql_query("UPDATE wp_pod_fields SET weight = $weight WHERE id = $val LIMIT 1");
    }
}
elseif ('edit' == $action)
{
    if ('id' == $name || 'name' == $name || 'body' == $name)
    {
        die("Error: The $name column is not editable.");
    }

    $result = mysql_query("SELECT id FROM wp_pod_fields WHERE datatype = $datatype AND id != $field_id AND name = '$name' LIMIT 1");
    if (0 < mysql_num_rows($result))
    {
        die("Error: The $name column cannot be cloned.");
    }

    $sql = "SELECT name, coltype FROM wp_pod_fields WHERE id = $field_id LIMIT 1";
    $result = mysql_query($sql) or die(mysql_error());

    if (0 < mysql_num_rows($result))
    {
        $row = mysql_fetch_assoc($result);
        $old_coltype = $row['coltype'];
        $old_name = $row['name'];

        $dbtype = $dbtypes[$coltype];
        $pickval = ('pick' != $coltype || empty($pickval)) ? 'NULL' : "'$pickval'";
        $sister_field_id = ('pick' != $coltype || empty($sister_field_id)) ? 'NULL' : "'$sister_field_id'";

        if ($coltype != $old_coltype && 'pick' == $coltype)
        {
            // remove tbl_$dtname
            mysql_query("ALTER TABLE tbl_$dtname DROP COLUMN $field_name");
        }
        elseif ($coltype != $old_coltype && 'pick' == $old_coltype)
        {
            // create tbl_$dtname
            mysql_query("ALTER TABLE tbl_$dtname ADD COLUMN $name $dbtype") or die('Error: Could not create column!');
            mysql_query("ALTER TABLE wp_pod_fields SET sister_field_id = NULL WHERE sister_field_id = $field_id");
            mysql_query("DELETE FROM wp_pod_rel WHERE field_id = $field_id");
        }
        else
        {
            mysql_query("ALTER TABLE tbl_$dtname CHANGE $old_name $name $dbtype");
        }

        $sql = "
        UPDATE
            wp_pod_fields
        SET
            name = '$name',
            coltype = '$coltype',
            pickval = $pickval,
            sister_field_id = $sister_field_id
        WHERE
            id = $field_id
        LIMIT
            1
        ";
        mysql_query($sql) or die('Error: Problem editing the column.');
    }
}
else
{
    $sql = "
    UPDATE
        wp_pod_types
    SET
        description = '$desc',
        list_filters = '$list_filters',
        tpl_detail = '$tpl_detail',
        tpl_list = '$tpl_list'
    WHERE
        id = $datatype
    LIMIT
        1
    ";
    mysql_query($sql) or die('Error: Problem changing the pod description.');
}

