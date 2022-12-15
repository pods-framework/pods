<?php
/**
 * The Black Friday admin notice.
 *
 * @since 4.12.14
 *
 * @var string $icon_url The local URL for the notice's image.
 * @var string $cta_url The short URL for black friday.
 * @var string $end_date - the end date of the sale.
 */
?>
<div class="tribe-marketing-notice">
	<div class="tribe-marketing-notice__icon">
		<img src="<?php echo esc_url( $icon_url ); ?>"/>
	</div>
	<div class="tribe-marketing-notice__content">
		<h3><?php echo esc_html__( 'Save 40% on every single plugin.', 'tribe-common' ); ?></h3>
		<p>
			<?php printf( esc_html__( 'Black Friday Sale now through %s.', 'tribe-common' ), $end_date ); ?>
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