<?php
ob_start();
require_once(preg_replace("/wp-content.*/","wp-load.php",__FILE__));
require_once(preg_replace("/wp-content.*/","/wp-admin/includes/admin.php",__FILE__));
require_once(realpath(dirname(__FILE__) . '/init.php'));
ob_end_clean();

if ((!isset($_POST['_wpnonce']) || !pods_access('manage_settings') || false === wp_verify_nonce($_POST['_wpnonce'], 'pods-uninstall')) && !defined('WP_UNINSTALL_PLUGIN')) {
    die('Error: Access denied');
}

$result = pod_query("SHOW TABLES LIKE '@wp_pod%'");
if (0 < mysql_num_rows($result)) {
    while ($row = mysql_fetch_array($result)) {
        pod_query("DROP TABLE {$row[0]}");
    }
}
pod_query("DELETE FROM @wp_options WHERE option_name LIKE 'pods_%'");