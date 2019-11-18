<?php
/**
 * The Black Friday 2018 admin notice for when only TEC is active.
 *
 * @since 4.7.23
 *
 * @var string $mascot_url The local URL for the notice's mascot image.
 * @var int $end_time The Unix timestamp for the sale's end time.
 */
?>
<div class="tribe-marketing-notice tribe-bf-2018-tec">
	<div class="tribe-notice-icon">
		<img src="<?php echo esc_url( $mascot_url ); ?>" />
	</div>
	<div class="tribe-notice-content">
		<h3>Up to 30% Off!</h3>
		<p>Save big on Events Calendar PRO, Filter Bar, Community Events, and more during our huge Black Friday sale!</p>
		<p><a target="_blank" class="button button-primary" href="http://m.tri.be/1a8l">Shop Now</a> <em>(But hurry, because this offer ends on <abbr title="<?php echo esc_attr( date_i18n( 'r', $end_time ) ); ?>">Monday, November 26th</abbr>.)</em></p>
	</div>
</div>