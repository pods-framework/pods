<?php
// Include the MySQL connection
include(realpath('../../../../wp-config.php'));

foreach ($_GET as $key => $val)
{
    ${$key} = mysql_real_escape_string(stripslashes(trim($val)));
}

if (!empty($pickval) && is_string($pickval))
{
    $result = mysql_query("SELECT id FROM {$table_prefix}pod_types WHERE name = '$pickval' LIMIT 1");
    if (0 < mysql_num_rows($result))
    {
        $row = mysql_fetch_assoc($result);
        $sister_datatype = $row['id'];

        $result = mysql_query("SELECT name FROM {$table_prefix}pod_types WHERE id = $datatype LIMIT 1");
        if (0 < mysql_num_rows($result))
        {
            $row = mysql_fetch_assoc($result);
            $datatype_name = $row['name'];

            $result = mysql_query("SELECT id, name FROM {$table_prefix}pod_fields WHERE datatype = $sister_datatype AND pickval = '$datatype_name'");
            if (0 < mysql_num_rows($result))
            {
                while ($row = mysql_fetch_assoc($result))
                {
                    $sister_fields[] = $row;
                }
                die(json_encode($sister_fields));
            }
        }
    }
}

