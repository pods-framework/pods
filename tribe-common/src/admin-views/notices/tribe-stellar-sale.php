<?php
/**
 * The Stellar Sale admin notice.
 *
 * @since 4.14.2
 *
 * @var string $icon_url The local URL for the notice's image.
 * @var string $cta_url The short URL for black friday.
 */
?>
<div class="tribe-marketing-notice">
	<div class="tribe-marketing-notice__content">
		<div class="tribe-marketing-notice__icon">
			<?php include \Tribe__Main::instance()->plugin_path . 'src/views/v2/components/icons/stellar-icon.php'; ?>
		</div>
		<?php include \Tribe__Main::instance()->plugin_path . 'src/resources/images/marketing/stellar-sale-text.svg'; ?>
		<h3>
			<?php
			/* Translators: %1$s formatted date. */
			echo sprintf(
				__( '<b>40%% off</b> all WordPress solutions through %1$s.', 'tribe-common' ),
				esc_html( $end_date->format( 'F j' ) )
			); ?>
		</h3>
		<p>
			<span class="tribe-marketing-notice__cta"><a target="_blank" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html_x( 'Shop now', 'Shop now link text', 'tribe-common' ) ?></a></span>
		</p>
	</div>
</div>
