<?php
/**
 * @var $ui PodsUI
 */
?>
<div id="screen-meta">
	<?php
	$ui->do_hook( 'screen_meta_pre' );
	if ( 0 < strlen( $screen_html ) ) {
		?>
		<div id="screen-options-wrap" class="pods-hidden">
			<form id="adv-settings" action="" method="post">
				<?php
				echo $screen_html;
				$fields = array();
				foreach ( $ui->get_ui_page() as $page ) {
					if ( isset( $ui->fields[ $page ] ) && ! empty( $ui->fields[ $page ] ) ) {
						$fields = $ui->fields[ $page ];
					}
				}
				if ( ! empty( $fields ) || true === $ui->pagination ) {
					?>
					<h5><?php _e( 'Show on screen', 'pods' ); ?></h5>
					<?php
					if ( ! empty( $fields ) ) {
						?>
						<div class="metabox-prefs">
							<?php
							$ui->do_hook( 'screen_meta_screen_options' );
							foreach ( $fields as $field => $attributes ) {
								if ( false === $attributes['display'] || true === $attributes['hidden'] ) {
									continue;
								}
								?>
								<label for="<?php echo esc_attr( $field ); ?>-hide">
									<input class="hide-column-tog"
										   name="<?php echo esc_attr( $ui->get_unique_identifier() ); ?>_<?php echo esc_attr( $field ); ?>-hide"
										   type="checkbox" id="<?php echo esc_attr( $field ); ?>-hide"
										   value="<?php echo esc_attr( $field ); ?>"
										   checked="checked"><?php esc_html_e( $attributes['label'] ); ?>
								</label>
								<?php
							}
							?>
							<br class="clear">
						</div>
						<h5><?php _e( 'Show on screen', 'pods' ); ?></h5>
						<?php
					}//end if
					?>
					<div class="screen-options">
						<?php
						if ( true === $ui->pagination ) {
							?>
							<input type="text" class="screen-per-page" name="wp_screen_options[value]"
								   id="<?php echo esc_attr( $ui->get_unique_identifier() ); ?>_per_page" maxlength="3"
								   value="20">
							<label
									for="<?php echo esc_attr( $ui->get_unique_identifier() ); ?>_per_page"><?php esc_html_e( sprintf( __( '%s per page', 'pods' ), $ui->items ) ); ?></label>
							<?php
						}
						$ui->do_hook( 'screen_meta_screen_submit' );
						?>
						<input type="submit" name="screen-options-apply" id="screen-options-apply" class="button"
							   value="<?php esc_attr_e( 'Apply', 'pods' ); ?>">
						<input type="hidden" name="wp_screen_options[option]"
							   value="<?php echo esc_attr( $ui->get_unique_identifier() ); ?>_per_page">
						<?php wp_nonce_field( 'screen-options-nonce', 'screenoptionnonce', false ); ?>
					</div>
					<?php
				}//end if
				?>
			</form>
		</div>
		<?php
	}//end if
	if ( 0 < strlen( $help_html ) ) {
		?>
		<div id="contextual-help-wrap" class="pods-hidden">
			<div class="metabox-prefs">
				<?php echo $help_html; ?>
			</div>
		</div>
		<?php
	}
	?>
	<div id="screen-meta-links">
		<?php
		$ui->do_hook( 'screen_meta_links_pre' );
		if ( 0 < strlen( $help_html ) || 0 < strlen( $help_link ) ) {
			?>
			<div id="contextual-help-link-wrap" class="hide-if-no-js screen-meta-toggle">
				<?php
				if ( 0 < strlen( $help_link ) ) {
					?>
					<a href="<?php echo esc_url( $help_link ); ?>" class="show-settings">Help</a>
					<?php
				} else {
					?>
					<a href="#contextual-help" id="contextual-help-link" class="show-settings">Help</a>
					<?php
				}
				?>
			</div>
			<?php
		}
		if ( 0 < strlen( $screen_html ) || 0 < strlen( $screen_link ) ) {
			?>
			<div id="screen-options-link-wrap" class="hide-if-no-js screen-meta-toggle">
				<?php
				if ( 0 < strlen( $screen_link ) ) {
					?>
					<a href="<?php echo esc_url( $screen_link ); ?>" class="show-settings">Screen Options</a>
					<?php
				} else {
					?>
					<a href="#screen-options" id="show-settings-link" class="show-settings">Screen Options</a>
					<?php
				}
				?>
			</div>
			<?php
		}
		$ui->do_hook( 'screen_meta_links_post' );
		?>
	</div>
	<?php
	$ui->do_hook( 'screen_meta_post' );
	?>
</div>
