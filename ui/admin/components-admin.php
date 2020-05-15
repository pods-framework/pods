<div class="wrap pods-admin">
	<div id="icon-pods" class="icon32"><br /></div>
	<form action="" method="post" class="pods-submittable">
		<div class="pods-submittable-fields">
			<?php echo PodsForm::field( 'action', 'pods_admin_components', 'hidden' ); ?>
			<?php echo PodsForm::field( 'component', $component, 'hidden' ); ?>
			<?php echo PodsForm::field( 'method', 'settings', 'hidden' ); ?>
			<?php echo PodsForm::field( '_wpnonce', wp_create_nonce( 'pods-component-' . $component . '-settings' ), 'hidden' ); ?>

			<h2><?php _e( 'Settings', 'pods' ); ?>: <?php echo $component_label; ?></h2>

			<?php
			if ( isset( $_GET['do'] ) ) {
				pods_message( __( 'Settings saved successfully.', 'pods' ) );
			}
			?>

			<table class="form-table pods-manage-field">
				<?php
				$depends_on = false;

				foreach ( $options

				as $field_name => $field_option ) {
					$field_option = PodsForm::field_setup( $field_option, null, $field_option['type'] );

					$dep_options = PodsForm::dependencies( $field_option );
					$dep_classes = $dep_options['classes'];
					$dep_data    = $dep_options['data'];

					if ( ( ! empty( $depends_on ) || ! empty( $dep_classes ) ) && $depends_on != $dep_classes ) {
						if ( ! empty( $depends_on ) ) {
							?>
							</tbody>
							<?php
						}

						if ( ! empty( $dep_classes ) ) {
							?>
							<tbody class="pods-field-option-container <?php echo esc_attr( $dep_classes ); ?>" <?php PodsForm::data( $dep_data ); ?>>
					<?php
						}
					}

					if ( ! is_array( $field_option['group'] ) ) {
						$value = pods_v( $field_name, $settings, $field_option['default'] );
						?>
						<tr valign="top" class="pods-field-option" id="pods-setting-<?php echo esc_attr( $field_name ); ?>">
						<th>
							<?php echo PodsForm::label( 'pods_setting_' . $field_name, $field_option['label'], $field_option['help'], $field_option ); ?>
						</th>
						<td>
							<?php echo PodsForm::field( 'pods_setting_' . $field_name, $value, $field_option['type'], $field_option ); ?>
						</td>
						</tr>
						<?php
					} else {
						?>
						<tr valign="top" class="pods-field-option-group" id="pods-setting-<?php echo esc_attr( $field_name ); ?>">
						<th class="pods-field-option-group-label">
							<?php echo $field_option['label']; ?>
						</th>
						<td class="pods-pick-values pods-pick-checkbox">
							<ul>
								<?php
								foreach ( $field_option['group'] as $field_group_name => $field_group_option ) {
									$field_group_option = PodsForm::field_setup( $field_group_option, null, $field_group_option['type'] );

									if ( 'boolean' !== $field_group_option['type'] ) {
										continue;
									}

									$field_group_option['boolean_yes_label'] = $field_group_option['label'];

									$group_dep_options = PodsForm::dependencies( $field_group_option );
									$group_dep_classes = $group_dep_options['classes'];
									$group_dep_data    = $group_dep_options['data'];

									$value = pods_v( $field_group_name, $settings, $field_group_option['default'] );
									?>
									<li class="<?php echo esc_attr( $group_dep_classes ); ?>" <?php PodsForm::data( $group_dep_data ); ?>>
										<?php echo PodsForm::field( 'pods_setting_' . $field_group_name, $value, $field_group_option['type'], $field_group_option ); ?>
									</li>
									<?php
								}
								?>
								</ul>
								</td>
							</tr>
							<?php
					}//end if

					if ( false !== $depends_on || ! empty( $dep_classes ) ) {
						$depends_on = $dep_options;
					}
				}//end foreach

				if ( ! empty( $depends_on ) ) {
				?>
				</tbody>
			<?php
				}
			?>
			</table>

			<p class="submit">
				<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'pods' ); ?>">
				<img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
			</p>
		</div>
	</form>
</div>

<script type="text/javascript">
	jQuery( function ( $ ) {
		$( document ).Pods( 'validate' );
		$( document ).Pods( 'submit' );
		$( document ).Pods( 'dependency' );
		$( document ).Pods( 'confirm' );
		$( document ).Pods( 'exit_confirm' );
	} );

	var pods_admin_submit_callback = function ( id ) {
		document.location = '<?php echo pods_slash( pods_query_arg( array( 'do' => 'save' ) ) ); ?>';
	}
</script>
