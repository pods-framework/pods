<?php
if ( ! empty( $_POST ) ) {
	if ( isset( $_POST['clearcache'] ) ) {
		$api = pods_api();

		$api->cache_flush_pods();

		if ( defined( 'PODS_PRELOAD_CONFIG_AFTER_FLUSH' ) && PODS_PRELOAD_CONFIG_AFTER_FLUSH ) {
			$api->load_pods( array( 'bypass_cache' => true ) );
		}

		pods_redirect( pods_query_arg( array( 'pods_clearcache' => 1 ), array( 'page', 'tab' ) ) );
	}
} elseif ( 1 == pods_v_sanitized( 'pods_clearcache' ) ) {
	pods_message( 'Pods transients and cache have been cleared.' );
}
?>

	<h3><?php _e( 'Clear Pods Cache', 'pods' ); ?></h3>

	<p><?php esc_html_e( 'This tool will clear all of the transients/cache that are used by Pods.', 'pods' ); ?></p>

	<p class="submit">
		<input type="submit" class="button button-primary" name="clearcache" value="<?php esc_attr_e( 'Clear Pods Cache', 'pods' ); ?>" />
	</p>

	<hr />

	<h3><?php _e( 'Debug Information', 'pods' ); ?></h3>

<?php
global $wp_version, $wpdb;

$wp      = $wp_version;
$php     = phpversion();
$mysql   = $wpdb->db_version();
$plugins = array();

$all_plugins = get_plugins();

foreach ( $all_plugins as $plugin_file => $plugin_data ) {
	if ( is_plugin_active( $plugin_file ) ) {
		$plugins[ $plugin_data['Name'] ] = $plugin_data['Version'];
	}
}

$stylesheet = get_stylesheet();
$theme      = wp_get_theme( $stylesheet );
$theme_name = $theme->get( 'Name' );

$opcode_cache = array(
	'Apc'       => function_exists( 'apc_cache_info' ) ? 'Yes' : 'No',
	'Memcached' => class_exists( 'eaccelerator_put' ) ? 'Yes' : 'No',
	'OPcache'   => function_exists( 'opcache_get_status' ) ? 'Yes' : 'No',
	'Redis'     => class_exists( 'xcache_set' ) ? 'Yes' : 'No',
);

$object_cache = array(
	'APC'       => function_exists( 'apc_cache_info' ) ? 'Yes' : 'No',
	'APCu'      => function_exists( 'apcu_cache_info' ) ? 'Yes' : 'No',
	'Memcache'  => class_exists( 'Memcache' ) ? 'Yes' : 'No',
	'Memcached' => class_exists( 'Memcached' ) ? 'Yes' : 'No',
	'Redis'     => class_exists( 'Redis' ) ? 'Yes' : 'No',
);

$versions = array(
	'WordPress Version'             => $wp,
	'PHP Version'                   => $php,
	'MySQL Version'                 => $mysql,
	'Server Software'               => $_SERVER['SERVER_SOFTWARE'],
	'Your User Agent'               => $_SERVER['HTTP_USER_AGENT'],
	'Session Save Path'             => session_save_path(),
	'Session Save Path Exists'      => ( file_exists( session_save_path() ) ? 'Yes' : 'No' ),
	'Session Save Path Writeable'   => ( is_writable( session_save_path() ) ? 'Yes' : 'No' ),
	'Session Max Lifetime'          => ini_get( 'session.gc_maxlifetime' ),
	'Opcode Cache'                  => $opcode_cache,
	'Object Cache'                  => $object_cache,
	'WPDB Prefix'                   => $wpdb->prefix,
	'WP Multisite Mode'             => ( is_multisite() ? 'Yes' : 'No' ),
	'WP Memory Limit'               => WP_MEMORY_LIMIT,
	'Current Memory Usage'          => number_format_i18n( memory_get_usage() / 1024 / 1024, 3 ) . 'M',
	'Current Memory Usage (real)'   => number_format_i18n( memory_get_usage( true ) / 1024 / 1024, 3 ) . 'M',
	'Pods Network-Wide Activated'   => ( is_plugin_active_for_network( basename( PODS_DIR ) . '/init.php' ) ? 'Yes' : 'No' ),
	'Pods Install Location'         => PODS_DIR,
	'Pods Tableless Mode Activated' => ( ( pods_tableless() ) ? 'Yes' : 'No' ),
	'Pods Light Mode Activated'     => ( ( defined( 'PODS_LIGHT' ) && PODS_LIGHT ) ? 'Yes' : 'No' ),
	'Currently Active Theme'        => $theme_name,
	'Currently Active Plugins'      => $plugins,
);

foreach ( $versions as $what => $version ) {
	echo '<p><strong>' . esc_html( $what ) . '</strong>: ';

	if ( is_array( $version ) ) {
		echo '</p><ul class="ul-disc">';

		foreach ( $version as $what_v => $v ) {
			echo '<li><strong>' . esc_html( $what_v ) . '</strong>: ' . esc_html( $v ) . '</li>';
		}

		echo '</ul>';
	} else {
		echo esc_html( $version ) . '</p>';
	}
}
