<?php
/**
 * Template for end of year sale promo.
 *
 * @since 4.14.9
 * @var string $background_image - the url of the background image to use
 * @var string $branding_logo - the url of the TEC branding logo
 * @var string $button_link - the url the button links to
 */
?>

<div class="end-of-year-sale-promo">
	<div class="end-of-year-sale-promo__branding">
		<img
			src="<?php echo esc_url( $branding_logo ); ?>"
			alt="<?php echo esc_attr__( 'The Events Calendar brand logo', 'tribe-common' ); ?>"
			class="end-of-year-sale-promo__branding-image"
		/>
	</div>
	<div class="end-of-year-sale-promo__promo" style="background-image: url('<?php echo $background_image; ?>')">
		<div class="end-of-year-sale-promo__content">
			<p class="end-of-year-sale-promo__text">
				<?php _e( 'End of Year Sale!<br/>Save 30% on<br/> all our plugins.<br/>Offer expires soon!', 'tribe-common' ); ?>
			</p>
			<a
				href="<?php echo esc_url( $button_link ); ?>"
				class="button end-of-year-sale-promo__button"
				rel="noreferrer noopener"
				target="_blank"
			>
				<?php echo esc_html__( 'Save now', 'tribe-common' ); ?>
			</a>
		</div>
	</div>
</div>