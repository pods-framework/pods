<?php
ob_start();
require_once(preg_replace("/wp-content.*/","wp-load.php",__FILE__));
require_once(preg_replace("/wp-content.*/","/wp-admin/includes/admin.php",__FILE__));
require_once(realpath(dirname(__FILE__) . '/init.php'));
ob_end_clean();

if ((!isset($_POST['_wpnonce']) || !pods_access('manage_settings') || false === wp_verify_nonce($_POST['_wpnonce'], 'pods-uninstall')) && !defined('WP_UNINSTALL_PLUGIN')) {
    pods_error('Error: Access denied - Cannot delete data');
}

global $wpdb;
$result = $wpdb->get_col("SHOW TABLES LIKE '{$wpdb->prefix}pod%'");
foreach ((array) $result as $table) {
    pods_query("DROP TABLE `{$table}`", false);
}
pods_query("DELETE FROM `@wp_options` WHERE `option_name` LIKE 'pods_%'", false);