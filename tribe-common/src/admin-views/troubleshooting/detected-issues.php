<?php
/**
 * View: Troubleshooting - Detected Issues
 *
 * @since 4.14.2
 *
 */
use \Tribe\Admin\Troubleshooting;
$issues_found = tribe( Troubleshooting::class )->get_issues_found();

if ( tribe( Troubleshooting::class )->is_any_issue_active() ) : //checks is there are any active issues before printing ?>
	<div class="tribe-events-admin-section-header">
		<h3>
			<?php esc_html_e( 'We’ve detected the following issues', 'tribe-common' ); ?>
		</h3>
	</div>

	<?php // toggles to appear here ?>
	<?php foreach ( $issues_found as $issue ) : ?>
		<?php
			// yoda conditioning
			if ( false === $issue['active'] ) {
				continue;
			}
		?>
		<div class="tribe-events-admin__issues-found-card">
			<div class="tribe-events-admin__issues-found-card-title">
				<img
					src="<?php echo esc_url( tribe_resource_url( 'images/help/warning-icon.svg', false, null, $main ) ); ?>"
					alt="<?php esc_attr_e( 'warning-icon', 'tribe-common' ); ?>"
				/>
				<h3>
					<i></i>
					<span>
						<?php echo esc_html( $issue['title'] ); ?>
					</span>
				</h3>
			</div>
			<div class="tribe-events-admin__issues-found-card-description">
				<p>
					<?php echo esc_html( $issue['description'] ); ?>
				</p>
				<div class="tribe-events-admin__issues-found-card-description-actions">
					<a href="<?php echo esc_url( $issue['more_info'] ); ?>" target="_blank" rel="noopener noreferrer">
						<?php esc_attr_e( 'Learn more', 'tribe-common' ); ?>
					</a>
					<a href="<?php echo esc_url( $issue['fix'] ); ?>">
						<?php echo esc_html( $issue['resolve_text'] ); ?>
					</a>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
<?php endif; ?>