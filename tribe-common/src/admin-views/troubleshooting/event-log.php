<?php
/**
 * View: Troubleshooting - Event Logs
 *
 * @since 4.14.2
 *
 */

$error_log = tribe( Tribe__Log__Admin::class )->display_log();
?>
<div class="tribe-events-admin__troubleshooting-event-log-wrapper">
	<h3 class="tribe-events-admin__troubleshooting-title tribe-events-admin__recent-log">
		<?php esc_html_e( 'Event log', 'tribe-common' ); ?>
	</h3>
	<div class="tribe-events-admin__recent-log-filters-select-wrapper">
		<?php echo $error_log ?>
	</div>
</div>