<?php
ob_start();
require_once(preg_replace("/wp-content.*/","wp-load.php",__FILE__));
require_once(realpath(dirname(__FILE__) . '/init.php'));
ob_end_clean();

if (!pods_access('manage_settings') || !defined('WP_UNINSTALL_PLUGIN')) {
    pods_error('Error: Access denied - Cannot delete data');
}

global $wpdb;
$result = $wpdb->get_col("SHOW TABLES LIKE '{$wpdb->prefix}pod%'");
foreach ((array) $result as $table) {
    pods_query("DROP TABLE `{$table}`");
}
delete_option('pods_version');
delete_option('pods_roles');