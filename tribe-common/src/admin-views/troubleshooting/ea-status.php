<?php
/**
 * View: Troubleshooting - EA Status Section
 *
 * @since 4.15.2
 * @since 4.15.2 Only show if EA is there.
 *
 * @version 4.15.2
 */

if ( ! class_exists( 'Tribe__Events__Aggregator' ) ) {
	return;
}

$status_icons = [
	'success' => 'images/help/success-icon.svg',
	'warning' => 'images/help/warning-icon.svg',
	'error'   => 'images/help/error-icon.svg',
];

$show_third_party_accounts = ! is_network_admin();
?>
<h3 id="tribe-events-admin__ea-status" class="tribe-events-admin__troubleshooting-title tribe-events-admin__ea-status">
	<?php esc_html_e( 'Event Aggregator system status ', 'tribe-common' ); ?>
</h3>

<div class="tribe-events-admin__ea-status-table-wrapper">
	<table class="tribe-events-admin__ea-status-table">
		<?php
			// license key
			include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/troubleshooting/ea-status/license-key.php';
			// if EA is not active, bail out of the rest of this
			if ( $ea_active ) {
				// current usage
				include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/troubleshooting/ea-status/current-usage.php';
				// current status
				include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/troubleshooting/ea-status/current-status.php';
				// server connection
				include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/troubleshooting/ea-status/server-connection.php';
				// scheduler status
				include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/troubleshooting/ea-status/scheduler-status.php';

				if ( $show_third_party_accounts ) :
					// eventbrite
					include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/troubleshooting/ea-status/eventbrite.php';
					// meetup
					include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/troubleshooting/ea-status/meetup.php';
				endif;
			}
		?>
	</table>
</div>
