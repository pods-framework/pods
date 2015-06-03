<?php
/**
 * @package  Pods
 * @category Admin
 */

if ( ! empty( $_POST ) ) {
	if ( isset( $_POST[ 'clearcache' ] ) ) {
		$api = pods_api();

		$api->cache_flush_pods();

		if ( defined( 'PODS_PRELOAD_CONFIG_AFTER_FLUSH' ) && PODS_PRELOAD_CONFIG_AFTER_FLUSH ) {
			$api->load_pods();
		}

		pods_redirect( pods_query_arg( array( 'pods_clearcache' => 1 ), array( 'page', 'tab' ) ) );
	}
} elseif ( 1 == pods_v( 'pods_clearcache' ) ) {
	pods_message( 'Pods transients and cache have been cleared.' );
}
?>

<h3><?php _e( 'Clear Pods Cache', 'pods' ); ?></h3>

<p><?php _e( 'This tool will clear all of the transients/cache that are used by Pods. ', 'pods' ); ?></p>

<p class="submit">
	<input type="submit" class="button button-primary" name="clearcache" value="<?php esc_attr_e( 'Clear Pods Cache', 'pods' ); ?>" />
</p>
