<?php
/**
 * The end of year sale admin notice.
 *
 * @since 4.14.9
 *
 * @var string $icon_url The local URL for the notice's image.
 * @var string $cta_url The URL for the sale page.
 */
?>
<div class="tribe-marketing-notice">
	<div class="tribe-marketing-notice__icon">
		<img src="<?php echo esc_url( $icon_url ); ?>"/>
	</div>
	<div class="tribe-marketing-notice__content">
		<h3><?php echo esc_html__( 'End of year savings!', 'tribe-common' ); ?></h3>
		<p>
			<?php echo esc_html__( 'Get 30% off on all plugins from now through December 31.', 'tribe-common' ); ?>
			<span class="tribe-marketing-notice__cta">
				<a
					href="<?php echo esc_url( $cta_url ); ?>"
					rel="noreferrer noopener"
					target="_blank"
				>
					<?php echo esc_html__( 'Shop now', 'tribe-common' ); ?>
				</a>
			</span>
		</p>
	</div>
</div>