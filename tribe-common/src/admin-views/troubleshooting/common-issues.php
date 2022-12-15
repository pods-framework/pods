<?php
/**
 * View: Troubleshooting - Common Issues
 *
 * @since 4.14.2
 *
 */

use \Tribe\Admin\Troubleshooting;
$common_issues = tribe( Troubleshooting::class )->get_common_issues();
?>
<div class="tribe-events-admin-section-header">
	<h3>
		<?php esc_html_e( 'Common Problems', 'tribe-common' ); ?>
	</h3>
</div>

<div class="tribe-events-admin-faq tribe-events-admin-4col-grid">
	<?php foreach ( $common_issues as $common_issue ) : ?>
		<div class="tribe-events-admin-faq-card">
			<div class="tribe-events-admin-faq-card__icon">
				<img
					src="<?php echo esc_url( tribe_resource_url( 'images/icons/faq.png', false, null, $main ) ); ?>"
					alt="<?php esc_attr_e( 'lightbulb icon', 'tribe-common' ); ?>"
				/>
			</div>
			<div class="tribe-events-admin-faq-card__content">
				<div class="tribe-events-admin-faq__question">
					<?php echo esc_html( $common_issue['issue'] ); ?>
				</div>
				<div class="tribe-events-admin-faq__answer">
					<?php
						$label = '<a href="' . esc_url( $common_issue['link'] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $common_issue['link_label'] ) . '</a>';
						echo sprintf( $common_issue['solution'], $label );
					?>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>