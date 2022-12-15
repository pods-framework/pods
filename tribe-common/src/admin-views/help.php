<?php
/**
 * The template that displays the help page.
 */

$main = Tribe__Main::instance();

// Fetch the Help page Instance
$help = tribe( Tribe__Admin__Help_Page::class );

// get the products list
$products = tribe( 'plugins.api' )->get_products();

use \Tribe\Admin\Troubleshooting;

?>

<div class="tribe-events-admin-header tribe-events-admin-container">
	<?php
		tribe( Troubleshooting::class )->admin_notice( 'help' );
	?>
	<div class="tribe-events-admin-header__content-wrapper">

		<img
			class="tribe-events-admin-header__logo-word-mark"
			src="<?php echo esc_url( tribe_resource_url( 'images/logo/tec-brand.svg', false, null, $main ) ); ?>"
			alt="<?php esc_attr_e( 'The Events Calendar brand logo', 'tribe-common' ); ?>"
		/>

		<h2 class="tribe-events-admin-header__title"><?php esc_html_e( 'Help', 'tribe-common' ); ?></h2>
		<p class="tribe-events-admin-header__description"><?php esc_html_e( 'We\'re committed to helping make your calendar spectacular and have a wealth of resources available.', 'tribe-common' ); ?></p>

		<ul class="tribe-events-admin-tab-nav">
			<li class="selected" data-tab="tribe-calendar"><?php esc_html_e( 'Calendar', 'tribe-common' ); ?></li>
			<li data-tab="tribe-ticketing"><?php esc_html_e( 'Ticketing & RSVP', 'tribe-common' ); ?></li>
			<li data-tab="tribe-community"><?php esc_html_e( 'Community', 'tribe-common' ); ?></li>
		</ul>
	</div>
</div>

<div class="tribe-events-admin__line">
	&nbsp;
</div>

<div class="tribe-events-admin-content-wrapper tribe-events-admin-container">

	<?php
		// Calendar Tab
		include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/help-calendar.php';

		// Ticketing & RSVP Tab
		include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/help-ticketing.php';

		// Community Tab
		include_once Tribe__Main::instance()->plugin_path . 'src/admin-views/help-community.php';

		$troubleshooting_link = class_exists( 'Tribe__Events__Main' )
			? admin_url( 'edit.php?post_type=tribe_events&page=tec-troubleshooting' )
			: admin_url( 'admin.php?page=tec-tickets-troubleshooting' );
	?>

	<?php // shared footer area ?>
	<footer class="tribe-events-admin-cta">
		<img
			class="tribe-events-admin-cta__image"
			src="<?php echo esc_url( tribe_resource_url( 'images/help/troubleshooting.png', false, null, $main ) ); ?>"
			alt="<?php esc_attr_e( 'Graphic with an electrical plug and gears', 'tribe-common' ); ?>"
		/>

		<div class="tribe-events-admin-cta__content">
			<h2 class="tribe-events-admin-cta__content-title">
				<?php esc_html_e( 'Need additional support?', 'tribe-common' ); ?>
			</h2>

			<div class="tribe-events-admin-cta__content-description">
				<a href="<?php echo esc_url( $troubleshooting_link ); ?>">
					<?php esc_html_e( 'Visit Troubleshooting next', 'tribe-common' ); ?>
				</a>
			</div>
		</div>
	</footer>

	<img
		class="tribe-events-admin-footer-logo"
		src="<?php echo esc_url( tribe_resource_url( 'images/logo/tec-brand.svg', false, null, $main ) ); ?>"
		alt="<?php esc_attr_e( 'The Events Calendar brand logo', 'tribe-common' ); ?>"
	/>
</div>

<?php // this is inline jQuery / javascript for extra simplicity */ ?>
<script>
	jQuery( document ).ready( function($) {
		var current_tab = '#tribe-calendar';

		$( 'body' ).on( 'click', '.tribe-events-admin-tab-nav li', function() {
			var x = $( this );
			var tab = '#' + x.data( 'tab' );

			$( current_tab ).hide();
			$( '.tribe-events-admin-tab-nav li' ).removeClass( 'selected' );
			x.addClass( 'selected' );

			$( tab ).show();
			current_tab = tab;
		} );
	} );
</script>