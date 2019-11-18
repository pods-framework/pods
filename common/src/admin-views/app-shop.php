<div id="tribe-app-shop" class="wrap">

	<div class="header">
		<h1><?php esc_html_e( 'Events Add-Ons', 'tribe-common' ); ?></h1>
		<a class="button" href="https://theeventscalendar.com/?utm_campaign=in-app&utm_source=addonspage&utm_medium=top-banner" target="_blank"><?php esc_html_e( 'Browse All Add-Ons', 'tribe-common' ); ?></a>
	</div>
	<?php
	$all_products = array(
		'for-sale' => array(),
		'installed' => array(),
	);
	foreach ( $products as $product ) {
		if ( $product->is_installed ) {
			$all_products['installed'][] = $product;
		} else {
			$all_products['for-sale'][] = $product;
		}
	}

	$products = array();
	foreach ( $all_products as $type => $products ) {
		if ( empty( $products ) ) {
			continue;
		}

		$button_label = esc_html__( 'Buy This Add-On', 'tribe-common' );
		$button_class = 'button-primary';
		if ( 'installed' === $type ) {
			?><h1 class="tribe-installed-headline"><?php esc_html_e( 'Installed Add-Ons', 'tribe-common' ); ?></h1><?php
			$button_class = 'button-disabled';
			$button_label = '<span class="dashicons dashicons-yes"></span>' . esc_html__( 'Installed', 'tribe-common' );
		}

		?>
		<div class="content-wrapper">
			<div class="addon-grid">
				<?php

				$count = count( $products );

				switch ( $count ) {
					case 0:
					case 3:
					case 6:
						$wide_indexes = array();
						break;

					case 2:
						$wide_indexes = array( 0, 1 );
						break;

					case 5:
						$wide_indexes = array( 0, 4 );
						break;

					case 1:
					case 4:
					case 7:
					default:
						$wide_indexes = array( 0 );
				}

				foreach ( $products as $i => $product ) {
					?>
					<div class="tribe-addon<?php echo in_array( $i, $wide_indexes ) ? ' first' : ''; ?>">
						<div class="thumb">
							<a href="<?php echo esc_url( $product->link ); ?>" target="_blank"><img src="<?php echo esc_url( tribe_resource_url( $product->image, false, null, $main ) ); ?>" /></a>
						</div>
						<div class="caption">
							<h4><a href="<?php echo esc_url( $product->link ); ?>" target="_blank"><?php echo esc_html( $product->title ); ?></a></h4>

							<div class="description">
								<p><?php echo $product->description; ?></p>
								<?php
								if ( isset( $product->requires ) ) {
									?>
									<p><strong><?php esc_html_e( 'Requires:', 'tribe-common' );?></strong> <?php echo esc_html( $product->requires ); ?></p>
									<?php
								}
								?>
							</div>

							<a class="button <?php esc_attr_e( $button_class ); ?>" href="<?php echo esc_url( $product->link ); ?>"><?php echo $button_label; // escaped above ?></a>
						</div>
					</div>

					<?php
				}
				?>
			</div>
		</div>
		<?php
	}
	?>
</div>
