<?php
// Include the MySQL connection
include(realpath('../../../../wp-config.php'));

foreach ($_GET as $key => $val)
{
    ${$key} = mysql_real_escape_string(stripslashes(trim($val)));
}

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
elseif ('rename' == $action)
{
    $sql = "
    SELECT
        f.name as field_name, f.coltype, t.name AS module_name
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

    if (0 < mysql_num_rows($result))
    {
        $row = mysql_fetch_assoc($result);
        $field_name = $row['field_name'];
        $module_name = $row['module_name'];
        $coltype = $row['coltype'];

        if ($name != $field_name)
        {
            mysql_query("UPDATE wp_pod_fields SET name = '$name' WHERE id = $field_id LIMIT 1");

            if ('pick' != $coltype)
            {
                $dbtypes = array(
                    'bool' => 'bool',
                    'date' => 'datetime',
                    'num' => 'decimal(9,2)',
                    'txt' => 'varchar(128)',
                    'file' => 'varchar(128)',
                    'desc' => 'text'
                );
                $dbtype = $dbtypes[$coltype];
                mysql_query("ALTER TABLE tbl_$module_name CHANGE $field_name $name $dbtype");
            }
        }
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

