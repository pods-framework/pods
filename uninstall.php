<?php
// Include the MySQL connection
require_once(realpath(dirname(__FILE__) . '/../../../wp-load.php'));
require_once(realpath(dirname(__FILE__) . '/init.php'));

if (!pods_access('manage_settings'))
{
    die('Error: Access denied');
}

$result = pod_query("SHOW TABLES LIKE '@wp_pod%'");
if (0 < mysql_num_rows($result))
{
    while ($row = mysql_fetch_array($result))
    {
        pod_query("DROP TABLE $row[0]");
    }
}
pod_query("DELETE FROM @wp_options WHERE option_name IN ('pods_version', 'pods_roles')");
