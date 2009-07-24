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

if (!empty($pickval) && is_string($pickval))
{
    $result = pod_query("SELECT id FROM @wp_pod_types WHERE name = '$pickval' LIMIT 1");
    if (0 < mysql_num_rows($result))
    {
        $sister_datatype = mysql_result($result, 0);

        $result = pod_query("SELECT name FROM @wp_pod_types WHERE id = $datatype LIMIT 1");
        if (0 < mysql_num_rows($result))
        {
            $datatype_name = mysql_result($result, 0);

            $result = pod_query("SELECT id, name FROM @wp_pod_fields WHERE datatype = $sister_datatype AND pickval = '$datatype_name'");
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

