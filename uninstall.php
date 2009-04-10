<?php
// Include the MySQL connection
include(realpath('../../../wp-config.php'));

if ($_POST['auth'] != md5(AUTH_KEY))
{
    die('Error: Authentication failed');
}

$result = pod_query("SHOW TABLES LIKE '@wp_pod%'");
if (0 < mysql_num_rows($result))
{
    while ($row = mysql_fetch_array($result))
    {
        pod_query("DROP TABLE $row[0]");

        // Remember all pod tables
        if ($pos = strpos($row[0], 'pod_tbl_'))
        {
            $dtnames[] = substr($row[0], $pos + 8);
        }
    }
}
pod_query("DELETE FROM @wp_options WHERE option_name = 'pods_version'");

