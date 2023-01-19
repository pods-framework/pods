<?php
// $main, $products, $bundles, $extensions must be defined before loading this file

$all_products = [
	'for-sale' => [],
	'installed' => [],
];
foreach ( $products as $product ) {
	if ( $product->is_installed ) {
		$all_products['installed'][] = $product;
	} else {
		$all_products['for-sale'][] = $product;
	}
}

use \Tribe\Admin\Troubleshooting;
?>

<div id="tribe-app-shop">

	<?php 
		tribe( Troubleshooting::class )->admin_notice( 'app-shop' );
	?>

	<div class="tribe-header">
		<div class="content-wrapper">
			<div class="logo-word-mark">
				<img
					src="<?php echo esc_url( tribe_resource_url( 'images/logo/tec-brand.svg', false, null, $main ) ); ?>"
					alt="<?php esc_attr_e( 'The Events Calendar brand logo', 'tribe-common' ); ?>"
				/>
			</div>

			<ul>
				<li class="selected" data-tab="tribe-all-solutions"><?php esc_html_e( 'All Solutions', 'tribe-common' ); ?></li>
				<li data-tab="tribe-bundles"><?php esc_html_e( 'Save with Bundles', 'tribe-common' ); ?></li>
				<li data-tab="tribe-extensions"><?php esc_html_e( 'Extensions', 'tribe-common' ); ?></li>
				<li data-tab="tribe-stellar"><?php esc_html_e( 'Stellar Discounts', 'tribe-common' ); ?></li>
			</ul>
		</div>
	</div>

	<div id="tribe-all-solutions" class="tribe-content">
		<img
			class="tribe-events-admin-graphic"
			src="<?php echo esc_url( tribe_resource_url( 'images/header/all-solutions.jpg', false, null, $main ) ); ?>"
			alt="<?php esc_attr_e( 'Shapes and lines for visual interest', 'tribe-common' ); ?>"
		/>
		<div class="content-wrapper">
			<div class="addon-grid">
				<?php foreach ( $all_products as $status => $some_products ) : ?>
					<?php if ( 'for-sale' == $status ) :?>
						<h2><?php esc_html_e( 'One calendar. Countless ways to make it your own.', 'tribe-common' ); ?></h2>
						<p><?php esc_html_e( 'Calendars, ticketing, and powerful WordPress tools to manage your events from start to finish.', 'tribe-common' ); ?></p>
					<?php else: ?>
						<h2 class="already-installed"><?php esc_html_e( 'Already Installed', 'tribe-common' ); ?></h2>
					<?php endif; ?>

					<?php foreach ( $some_products as $product ) : ?>
						<div class="tribe-addon">
							<div class="headline">
								<img src="<?php echo esc_url( tribe_resource_url( $product->logo, false, null, $main ) ); ?>" alt="<?php esc_attr_e( 'TEC Logo', 'tribe-common' ); ?>" />
								<h3 <?php echo ( 'installed' == $status || $product->free ) ? 'class="has-pill"' : ''; ?>><a href="<?php echo esc_url( $product->link ); ?>" target="_blank"><?php echo esc_html( $product->title ); ?></a></h3>

								<?php if ( 'installed' == $status ) : ?>
									<span class="pill active"><?php esc_html_e( 'Active', 'tribe-common' ); ?></span>
								<?php elseif ( $product->free ) : ?>
									<span class="pill free"><?php esc_html_e( 'FREE', 'tribe-common' ); ?></span>
								<?php endif; ?>

							</div>
							<div class="promo-image">
								<a href="<?php echo esc_url( $product->link ); ?>" target="_blank"><img src="<?php echo esc_url( tribe_resource_url( $product->image, false, null, $main ) ); ?>" /></a>
							</div>

							<div class="description">
								<p><?php echo esc_html( $product->description ); ?></p>
							</div>

							<ul class="features">
								<?php foreach ( $product->features as $feature ) : ?>
									<li>
										<span class="check">
											<svg fill="none" height="12" viewBox="0 0 16 12" width="16" xmlns="http://www.w3.org/2000/svg"><path clip-rule="evenodd" d="m13.7357.374803-8.40784 8.402337-3.06361-3.06158c-.52424-.506-1.357557-.49877-1.872924.01626s-.522608 1.34779-.016275 1.87169l4.008209 4.00559c.52173.5212 1.36747.5212 1.8892 0l9.35244-9.34634c.5064-.5239.4991-1.356665-.0162-1.871692-.5154-.515027-1.3487-.522264-1.873-.016265z" fill="#3d54ff" fill-rule="evenodd"/></svg>
										</span><span class="feature-text">
											<?php echo esc_html( $feature ); ?>
										</span>
									</li>
								<?php endforeach; ?>
							</ul>

							<?php if ( 'installed' == $status ) : ?>
								<a class="button" href="https://evnt.is/1aiz"><?php esc_html_e( 'Manage', 'tribe-common' ); ?></a>
							<?php else : ?>
								<a class="button" href="<?php echo esc_url( $product->link ); ?>"><?php esc_html_e( 'Learn More', 'tribe-common' ); ?></a>
							<?php endif; ?>

						</div>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<div id="tribe-bundles" class="tribe-content">
		<img
			class="tribe-events-admin-graphic"
			src="<?php echo esc_url( tribe_resource_url( 'images/header/bundles.jpg', false, null, $main ) ); ?>"
			alt="<?php esc_attr_e( 'Shapes and lines for visual interest', 'tribe-common' ); ?>"
		/>
		<div class="content-wrapper">
			<div class="addon-grid">
				<h2><?php esc_html_e( 'The plugins you need at one discounted price', 'tribe-common' ); ?></h2>
				<p><?php esc_html_e( 'We\'ve packaged our most popular plugins into bundles jam-packed with value.', 'tribe-common' ); ?></p>
				<?php foreach ( $bundles as $bundle ) : ?>
					<div class="tribe-bundle">
						<div class="details">
							<div class="headline">
								<img src="<?php echo esc_url( tribe_resource_url( $bundle->logo, false, null, $main ) ); ?>" alt="<?php esc_attr_e( 'TEC Logo', 'tribe-common' ); ?>" />
								<h3><a href="<?php echo esc_url( $bundle->link ); ?>" target="_blank"><?php echo esc_html( $bundle->title ); ?></a></h3>
							</div>

							<p><?php echo esc_html( $bundle->description ); ?></p>

							<div class="cta wide">
								<a class="button" href="<?php echo esc_url( $bundle->link ); ?>"><?php esc_html_e( 'Save With A Bundle', 'tribe-common' ); ?></a>
								<span class="discount"><?php echo esc_html( $bundle->discount ); ?></span>
							</div>
						</div>

						<div class="includes">
							<h4><?php esc_html_e( 'Includes', 'tribe-common' ); ?></h4>
							<ul>
								<?php foreach ( $bundle->includes as $i => $product_key ) : ?>
									<?php
									// get $product object
									$product = $products[ $product_key ];
									?>

									<li>
										<img src="<?php echo esc_url( tribe_resource_url( $product->logo, false, null, $main ) ); ?>" alt="<?php esc_attr_e( 'TEC Logo', 'tribe-common' ); ?>" />
										<span><?php echo esc_html( $product->title ); ?></span>
									</li>

									<?php if ( $i == 4 ) : // if there are 5 products included, then we need 2 lists ?>
										</ul><ul class="second">
									<?php endif; ?>
								<?php endforeach; ?>
							</ul>
						</div>

						<div class="cta narrow">
							<a class="button" href="<?php echo esc_url( $bundle->link ); ?>"><?php esc_html_e( 'Save With A Bundle', 'tribe-common' ); ?></a>
							<span class="discount"><?php echo esc_html( $bundle->discount ); ?></span>
						</div>

					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<div id="tribe-extensions" class="tribe-content">
		<img
			class="tribe-events-admin-graphic"
			src="<?php echo esc_url( tribe_resource_url( 'images/header/extensions.jpg', false, null, $main ) ); ?>"
			alt="<?php esc_attr_e( 'Shapes and lines for visual interest', 'tribe-common' ); ?>"
		/>
		<div class="content-wrapper">
			<div class="addon-grid">
				<h2><?php esc_html_e( 'Free extensions to power up your plugins', 'tribe-common' ); ?></h2>
				<p><?php esc_html_e( 'Extensions are quick solutions our team came up with to solve specific issues you may need. (Note - extensions are not covered by our support team.)', 'tribe-common' ); ?></p>
				<?php foreach ( $extensions as $extension ) : ?>
					<div class="tribe-addon">
						<div class="headline">
							<h3 class="has-pill"><a href="<?php echo esc_url( $extension->link ); ?>" target="_blank"><?php echo esc_html( $extension->title ); ?></a></h3>
							<span class="pill free"><?php esc_html_e( 'FREE', 'tribe-common' ); ?></span>
						</div>

						<div class="promo-image">
							<a href="<?php echo esc_url( $extension->link ); ?>" target="_blank"><img src="<?php echo esc_url( tribe_resource_url( $extension->image, false, null, $main ) ); ?>" /></a>
						</div>

						<p><?php echo esc_html( $extension->description ); ?></p>

						<a class="button" href="<?php echo esc_url( $extension->link ); ?>"><?php esc_html_e( 'Download', 'tribe-common' ); ?></a>
					</div>
				<?php endforeach; ?>

				<a class="button secondary" href="https://evnt.is/1ajd"><?php esc_html_e( 'Browse Extensions', 'tribe-common' ); ?></a>

			</div>

		</div>
	</div>

	<div id="tribe-stellar" class="tribe-content" style="display: none;">
		<img
			class="tribe-events-admin-graphic"
			src="<?php echo esc_url( tribe_resource_url( 'images/header/all-solutions.jpg', false, null, $main ) ); ?>"
			alt="<?php esc_attr_e( 'Shapes and lines for visual interest', 'tribe-common' ); ?>"
		/>
		<div class="content-wrapper">
			<div class="stellar-logo">
				<img src="<?php echo esc_url( tribe_resource_url( 'images/shop/stellar-logo.png', false, null, $main ) ); ?>" />
			</div>

			<div class="stellar-discounts-description">
				<p><?php esc_html_e( 'Stellar is a collective of WordPress innovators, and home to WordPress products done right.', 'tribe-common' ); ?></p>
			</div>

			<div class="stellar-discounts-coupon-callout">
				<p><?php printf( wp_kses( __( '$25 towards any Stellar product using code <u>%s</u>', 'tribe-common' ), ['u' => []] ), 'Stellar25' );?></p>
			</div>

			<div class="stellar-brand-grid addon-grid">
				<?php foreach ( $stellar_brands as $stellar_brand ) : ?>
					<div class="stellar-brand">
						<div class="stellar-brand__image">
							<a href="<?php echo esc_url( $stellar_brand->link ); ?>" target="_blank"><img src="<?php echo esc_url( tribe_resource_url( $stellar_brand->image, false, null, $main ) ); ?>" /></a>
						</div>

						<div class="stellar-brand__logo">
							<img src="<?php echo esc_url( tribe_resource_url( $stellar_brand->logo, false, null, $main ) ); ?>" />
						</div>

						<div class="stellar-brand__content">
							<h3 class="stellar-brand__title"><?php echo esc_html( $stellar_brand->title ); ?></h3>
							<p class="stellar-brand__description"><?php echo esc_html( $stellar_brand->description ); ?></p>
						</div>						

						<a class="button stellar-brand__link" href="<?php echo esc_url( $stellar_brand->link ); ?>" target="_blank" rel="noopener noreferrer">
							<?php echo esc_html( $stellar_brand->linktext ); ?>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

</div>

<?php // this is inline jQuery / javascript for extra simplicity */ ?>
<script type="text/javascript">
	jQuery( document ).ready( function($) {
		var current_tab = "#tribe-all-solutions";
		$( 'body' ).on( "click", ".tribe-header li", function() {
			var tab = "#" + $( this ).data( "tab" );
			$( current_tab ).hide();
			$( '.tribe-header li' ).removeClass( "selected" );
			$( this ).addClass( "selected" );

			$( tab ).show();
			current_tab = tab;
		} );
	} );
</script>
