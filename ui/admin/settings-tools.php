<?php
    if ( !empty( $_POST ) ) {
        if ( isset( $_POST[ 'clearcache' ] ) ) {
			$api = pods_api();

            $api->cache_flush_pods();

			if ( defined( 'PODS_PRELOAD_CONFIG_AFTER_FLUSH' ) && PODS_PRELOAD_CONFIG_AFTER_FLUSH ) {
				$api->load_pods();
			}

		pods_redirect( pods_var_update( array( 'pods_clearcache' => 1 ), array( 'page', 'tab' ) ) );
	}
} elseif ( 1 == pods_v( 'pods_clearcache' ) ) {
	pods_message( 'Pods transients and cache have been cleared.' );
}

if ( PODS_GITHUB_UPDATE ) {
	?>

	<h3><?php _e( 'Force an update of this beta from GitHub', 'pods' ); ?></h3>

	<p><?php _e( 'This tool lets you update your Pods installation to the latest alpha/beta/release candidate, usually only when you\'ve been instructed to do so.', 'pods' ); ?></p>

	<?php
	$update = admin_url( 'update-core.php?pods_force_refresh=1' );

	if ( is_multisite() ) {
		$update = network_admin_url( 'update-core.php?pods_force_refresh=1' );
	}
	?>

	<p class="submit">
		<a href="<?php echo $update; ?>" class="button button-primary"><?php esc_html_e( 'Force Plugin Refresh/Update from GitHub', 'pods' ); ?></a>
	</p>

	<hr />

<?php } ?>

	<h3><?php _e( 'Clear Pods Cache', 'pods' ); ?></h3>

	<p><?php _e( 'This tool will clear all of the transients/cache that are used by Pods. ', 'pods' ); ?></p>

	<p class="submit">
		<input type="submit" class="button button-primary" name="clearcache" value="<?php esc_attr_e( 'Clear Pods Cache', 'pods' ); ?>" />
	</p>
