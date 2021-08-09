<?php
/**
 * Promoter Auth View Template
 * The template for authorizing your site with Promoter.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/promoter/auth.php
 *
 * @package Tribe
 * @since   4.9
 * @version 4.9.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}
?>
<link href="https://fonts.googleapis.com/css?family=PT+Mono" rel="stylesheet">
<link rel="stylesheet" href="https://use.typekit.net/pha0nnp.css">
<link id="app" href="https://promoter.theeventscalendar.com/css/app.css" rel="stylesheet">
<style>
	.page--auth p {
		margin: 16px 0 30px;
	}
</style>

<div class="site-wrap">
	<main id="page-content" class="page page--push page--auth">
		<div class="row row--reduced">
			<div class="promoter-logo">
				<span class="a11y-visual-hide"><?php esc_html_e( 'Promoter', 'tribe-common' ); ?>/span>
			</div>

			<?php if ( ! $authorized ) : ?>
				<h1 class="headline__large">
					<?php esc_html_e( 'Promoter would like to sync with your site', 'tribe-common' ); ?>
				</h1>
			<?php endif; ?>

			<?php if ( ! $logged_in ) : ?>
				<p>
					<a href="<?php echo esc_url( wp_login_url( $_SERVER['REQUEST_URI'] ) ); ?>">
						<?php esc_html_e( 'Please log in to continue', 'tribe-common' ); ?>  &raquo;
					</a>
				</p>
			<?php elseif ( ! $admin ) : ?>
				<p>
					<?php esc_html_e( 'You do not have access to authenticate this site.', 'tribe-common' ); ?>
					<a href="<?php echo esc_url( wp_logout_url( $_SERVER['REQUEST_URI'] ) ); ?>">
						<?php esc_html_e( 'Please log out and log back in as an admin account', 'tribe-common' ); ?> &raquo;
					</a>
				</p>
			<?php else : ?>
				<p>
					<?php if ( $auth_error ) : ?>
						<?php esc_html_e( 'Sorry, unable to authenticate your site. Please contact Promoter support.', 'tribe-common' ); ?>
					<?php else : ?>
						<?php esc_html_e( 'Please authorize to continue onboarding.', 'tribe-common' ); ?>
					<?php endif; ?>
				</p>

				<form method="post">
					<input type="hidden" value="<?php echo esc_attr( $promoter_key ); ?>" name="promoter_key"/>
					<input type="hidden" value="<?php echo esc_attr( $license_key ); ?>" name="license_key"/>
					<input type="hidden" value="1" name="promoter_authenticate"/>
					<button class="btn btn--blue" type="submit"><?php esc_html_e( 'Authorize Promoter', 'tribe-common' ); ?></button>
				</form>
			<?php endif; ?>
		</div>
	</main>

	<footer class="site-footer site-footer--locked">
		<div class="row">
			<div class="site-footer__logo">
				<span class="a11y-visual-hide"><?php esc_html_e( 'Promoter', 'tribe-common' ); ?></span>
			</div>
			<p class="site-footer__meta">
				&copy;<?php echo esc_html( date_i18n( 'Y' ) ); ?>
				<?php esc_html_e( 'Promoter All rights reserved.', 'tribe-common' ); ?>
				<a href="https://promoter.theeventscalendar.com/privacy"><?php esc_html_e( 'Privacy', 'tribe-common' ); ?></a>
				<?php esc_html_e( 'and', 'tribe-common' ); ?>
				<a href="https://promoter.theeventscalendar.com/terms"><?php esc_html_e( 'Terms', 'tribe-common' ); ?></a>.
			</p>
		</div>
	</footer>
</div>
