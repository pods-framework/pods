<?php
/**
 * View: Troubleshooting - System information
 *
 * @since 4.14.2
 *
 */

$support     = Tribe__Support::getInstance();
$system_info = $support->formattedSupportStats();
$optin_key = ! empty( get_option( $support::$option_key ) );
?>
<div class="tribe-events-admin__system-information">
	<div class="tribe-events-admin__system-information-content">
		<h3 class="tribe-events-admin__troubleshooting-title">
			<?php esc_html_e( 'System Information', 'tribe-common' ); ?>
		</h3>
		<p class="tribe-events-admin__troubleshooting-description">
			<?php esc_html_e( 'Please opt-in below to automatically share your system information with our support team. This will allow us to assist you faster if you post in our help desk.', 'tribe-common' ); ?>
		</p>
		<div class="tribe-events-admin__system-information-select">
			<input name="tribe_auto_sysinfo_opt_in" id="tribe_auto_sysinfo_opt_in" type="checkbox" value="optin" <?php checked( $optin_key ); ?> />
			<label for="tribe_auto_sysinfo_opt_in">
				<?php esc_html_e( 'Yes, automatically share my system information with The Events Calendar support team*', 'tribe-common' ); ?>
			</label>
		</div>
		<small>
			<?php esc_html_e( '* Your system information will only be used by The Events Calendar support team. All information is stored securely. We do not share this information with any third parties.', 'tribe-common' ); ?>
		</small>
		<p class="tribe-sysinfo-optin-msg"></p>
	</div>

	<div class="tribe-events-admin__system-information-widget">
		<?php echo $system_info; ?>
	</div>

	<div class="tribe-events-admin__system-information-widget-copy">
		<button data-clipboard-action="copy" class="system-info-copy-btn" data-clipboard-target=".support-stats" >
			<?php esc_attr_e( 'Copy to clipboard', 'tribe-common' ); ?>
		</button>
	</div>
</div>
