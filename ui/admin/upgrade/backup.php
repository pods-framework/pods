<?php

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// phpcs:ignoreFile WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>
<p class="padded"><?php esc_html_e( 'We recommend that you back your database up, it can really save you in a bind or a really weird situation that you may not be expecting. Check out a few options we think are <em>great</em> below.', 'pods' ); ?></p>

<div id="pods-wizard-options">
	<div class="pods-wizard-option">
		<a href="http://ithemes.com/member/go.php?r=31250&i=l44" target="_blank" rel="noopener noreferrer">
			<img src="<?php echo esc_url( PODS_URL ); ?>ui/images/logo_backupbuddy.png" alt="Backup Buddy" />

			<p><?php esc_html_e( 'Receive 25% off', 'pods' ); ?></p>

			<p><?php esc_html_e( 'Coupon Code', 'pods' ); ?>: <strong>PODS25</strong></p>
		</a>

		<p>
			<em><?php esc_html_e( 'The all-in-one WordPress backup plugin to easily backup, restore, and migrate to any number of local or external locations.', 'pods' ); ?></em>
		</p>
	</div>
	<div class="pods-wizard-option">
		<a href="http://vaultpress.com/podsframework/" target="_blank" rel="noopener noreferrer">
			<img src="<?php echo esc_url( PODS_URL ); ?>ui/images/logo_vaultpress.png" alt="Vaultpress" />

			<p><?php esc_html_e( '1 free month', 'pods' ); ?></p>

			<p><strong><?php esc_html_e( 'Click to sign up', 'pods' ); ?></strong></p>
		</a>

		<p>
			<em><?php esc_html_e( 'A service that provides realtime continuous backups, restores, and security scanning.', 'pods' ); ?></em>
		</p>
	</div>
</div>
