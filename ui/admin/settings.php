<div class="wrap pods-admin pods-admin--flex">
	<div class="pods-admin__content-container">
		<form action="" method="post" class="pods-submittable pods-form pods-form-settings">

			<div id="icon-pods" class="icon32"><br /></div>

			<?php
			$default = 'tools';

			$tabs = [
				'tools'    => __( 'Tools', 'pods' ),
				'settings' => __( 'Settings', 'pods' ),
				'reset'    => __( 'Cleanup &amp; Reset', 'pods' ),
			];

			/**
			 * Allow filtering of settings page tabs.
			 *
			 * @since TBD
			 *
			 * @param array $tabs List of settings page tabs.
			 */
			$tabs = apply_filters( 'pods_admin_settings_tabs', $tabs );
			?>

			<h2 class="nav-tab-wrapper">
				<?php
				foreach ( $tabs as $tab => $label ) {
					$class = '';

					if ( $tab === pods_v( 'tab', 'get', $default ) ) {
						$class = ' nav-tab-active';

						$label = 'Pods ' . $label;
					}

					$url = pods_query_arg( [ 'tab' => $tab ], [ 'page' ] );
					?>
					<a href="<?php echo esc_url( $url ); ?>" class="nav-tab<?php echo esc_attr( $class ); ?>">
						<?php echo $label; ?>
					</a>
					<?php
				}
				?>
			</h2>
			<img src="<?php echo esc_url( PODS_URL ); ?>ui/images/pods-logo-notext-rgb-transparent.png" class="pods-leaf-watermark-right" />

			<?php
			wp_nonce_field( 'pods-settings' );

			$tab = pods_v_sanitized( 'tab', 'get', $default );
			$tab = sanitize_title( $tab );

			$supported = [
				'settings',
				'tools',
				'reset',
			];

			if ( in_array( $tab, $supported, true ) ) {
				pods_view( PODS_DIR . 'ui/admin/settings-' . $tab . '.php' );
			}

			/**
			 * Allow customizations on tab page.
			 *
			 * @since TBD
			 */
			do_action( 'pods_admin_settings_page_' . $tab );
			?>
		</form>
	</div>

	<?php
	/**
	 * Allow additional output after the container area of the Pods settings screen.
	 *
	 * @since 2.7.17
	 */
	do_action( 'pods_admin_after_settings' );
	?>
</div>
