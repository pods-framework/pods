<div class="wrap pods-admin pods-admin--flex">
	<div class="pods-admin__content-container">
		<form action="" method="post" class="pods-submittable pods-form pods-form-settings">

			<div id="icon-pods" class="icon32"><br /></div>

			<?php
			$default = 'settings';

			$tabs = [
				'settings' => __( 'Settings', 'pods' ),
				'tools'    => __( 'Tools', 'pods' ),
				'reset'    => __( 'Cleanup &amp; Reset', 'pods' ),
			];

			/**
			 * Allow filtering of settings page tabs.
			 *
			 * @since 2.8.0
			 *
			 * @param array $tabs List of settings page tabs.
			 */
			$tabs = apply_filters( 'pods_admin_settings_tabs', $tabs );

			$current_tab = pods_v( 'tab', 'get', $default, true );

			if ( ! isset( $tabs[ $current_tab ] ) ) {
				$current_tab = $default;
			}
			?>

			<h2 class="nav-tab-wrapper">
				<?php
				foreach ( $tabs as $tab => $label ) {
					$class = '';

					if ( $tab === $current_tab ) {
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

			/**
			 * Allow customizations on tab page before output.
			 *
			 * @since 2.8.0
			 */
			do_action( 'pods_admin_settings_page_pre_' . $current_tab );

			pods_view( PODS_DIR . 'ui/admin/settings-' . sanitize_title( $current_tab ) . '.php' );

			/**
			 * Allow customizations on tab page after output.
			 *
			 * @since 2.8.0
			 */
			do_action( 'pods_admin_settings_page_post_' . $current_tab );
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
