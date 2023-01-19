<?php
/**
 * View: Troubleshooting - Support Call To Action
 *
 * @since 4.14.2
 *
 */
?>
<div class="tribe-events-admin-cta">
	<img
		class="tribe-events-admin-cta__image"
		src="<?php echo esc_url( tribe_resource_url( 'images/help/troubleshooting-support.png', false, null, $main ) ); ?>"
		alt="<?php esc_attr_e( 'Graphic with an electrical plug and gears', 'tribe-common' ); ?>"
	/>

	<div class="tribe-events-admin-cta__content tribe-events-admin__troubleshooting-cta">
		<div class="tribe-events-admin-cta__content-title">
			<?php esc_html_e( 'Get support from humans', 'tribe-common' ); ?>
		</div>

		<div class="tribe-events-admin-cta__content-subtitle">
			<?php esc_html_e( 'Included with our premium products', 'tribe-common' ); ?>
		</div>

		<div class="tribe-events-admin-cta__content-description">
			<a href="https://theeventscalendar.com/support/#contact" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Open a ticket', 'tribe-common' ); ?>
			</a>
		</div>
	</div>
</div>