<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) || !WP_UNINSTALL_PLUGIN || dirname( WP_UNINSTALL_PLUGIN ) != dirname( plugin_basename( __FILE__ ) ) ) {
	status_header( 404 );
	die();
}

global $wpdb;
$result = $wpdb->get_col("SHOW TABLES LIKE '{$wpdb->prefix}pods%'");
foreach ((array) $result as $table) {
    $wpdb->query("DROP TABLE `{$table}`");
}
$wpdb->query("DELETE FROM `{$wpdb->prefix}` WHERE `option_name` LIKE 'pods_%'");