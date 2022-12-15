<?php
/**
 * View: Troubleshooting - Recent Template Changes
 *
 * @since 4.14.2
 *
 */

$template_changes = Tribe__Support__Template_Checker_Report::generate();
?>
<h3 class="tribe-events-admin__troubleshooting-title">
	<?php esc_html_e( 'Recent template changes', 'tribe-common' ); ?>
</h3>
<div class="tribe-events-admin__recent-template-changes">
	<?php echo $template_changes; ?>
</div>