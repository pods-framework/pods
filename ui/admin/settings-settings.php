<p><?php _e( 'The following are settings provided for advanced site configurations.', 'pods' ); ?></p>

<?php
wp_enqueue_script( 'pods' );
wp_enqueue_style( 'pods-form' );

$settings = get_option( 'pods_settings' );

if ( empty( $settings ) ) {
	$settings = array();
}

/**
 * Allow filtering the list of fields for the settings page.
 *
 * @since TBD
 *
 * @param array $fields List of fields for the settings page.
 */
$fields = apply_filters( 'pods_admin_settings_fields', array() );

$nonce = wp_create_nonce( 'pods_settings_form' );

if ( isset( $_POST['_pods_nonce'] ) && wp_verify_nonce( $_POST['_pods_nonce'], 'pods_settings_form' ) ) {
	$action = __( 'saved', 'pods' );

	$params = pods_unslash( (array) $_POST );

	foreach ( $fields as $key => $field ) {
		if ( ! isset( $field['name'] ) ) {
			$field['name'] = $key;
		}

		$value = '';

		if ( isset( $params[ 'pods_field_' . $field['name'] ] ) ) {
			$value = $params[ 'pods_field_' . $field['name'] ];
		} elseif ( 'boolean' === $field['type'] ) {
			$value = '0';
		}

		$settings[ $field['name'] ] = $value;
	}

	update_option( 'pods_settings', $settings, 'yes' );

	$saved = true;

	$message = sprintf( __( '<strong>Success!</strong> %1$s %2$s successfully.', 'pods' ), __( 'Settings', 'pods' ), $action );
	$error   = sprintf( __( '<strong>Error:</strong> %1$s %2$s successfully.', 'pods' ), __( 'Settings', 'pods' ), $action );

	if ( $saved ) {
		pods_message( $message );
	} else {
		pods_message( $error, 'error' );
	}
}

$do = 'save';
?>

<form action="" method="post" class="pods-submittable pods-form pods-form-settings">
	<div class="pods-submittable-fields pods-dependency">
		<?php echo PodsForm::field( 'do', $do, 'hidden' ); ?>
		<?php echo PodsForm::field( '_pods_nonce', $nonce, 'hidden' ); ?>

		<?php
		foreach ( $fields as $key => $field ) {
			if ( ! isset( $field['name'] ) ) {
				$field['name'] = $key;
			}

			if ( 'hidden' !== $field['type'] ) {
				continue;
			}

			echo PodsForm::field( 'pods_field_' . $field['name'], pods_v( $field['name'], $settings ), 'hidden' );
		}
		?>
		<table class="form-table pods-manage-field">
			<?php
			$depends_on = false;

			foreach ( $fields as $key => $field ) {
				if ( 'hidden' === $field['type'] ) {
					continue;
				}

				if ( ! isset( $field['name'] ) ) {
					$field['name'] = $key;
				}

				if ( ! isset( $field['id'] ) ) {
					$field['id'] = $field['name'];
				}

				if ( ! isset( $field['description'] ) ) {
					$field['description'] = '';
				}

				$dep_options = PodsForm::dependencies( $field );
				$dep_classes = $dep_options['classes'];
				$dep_data    = $dep_options['data'];

				if ( ( ! empty( $depends_on ) || ! empty( $dep_classes ) ) && $depends_on !== $dep_classes ) {
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
			?>
			<tr valign="top" class="pods-field-option pods-field <?php echo esc_attr( 'pods-form-ui-row-type-' . $field['type'] . ' pods-form-ui-row-name-' . PodsForm::clean( $field['name'], true ) ); ?>">
				<?php if ( 'heading' === $field['type'] ) : ?>
					<td colspan="2">
						<h2><?php echo esc_html( $field['label'] ); ?></h2>
						<?php echo PodsForm::comment( 'pods_field_' . $field['name'], $field['description'], $field ); ?>
					</td>
				<?php elseif ( 'html' === $field['type'] ) : ?>
					<td colspan="2">
						<?php echo PodsForm::field( 'pods_field_' . $field['name'], pods_v( $field['name'], $settings ), $field['type'], $field ); ?>
					</td>
				<?php else : ?>
					<th>
						<?php echo PodsForm::label( 'pods_field_' . $field['name'], $field['label'], $field['help'], $field ); ?>
					</th>
					<td>
						<?php echo PodsForm::field( 'pods_field_' . $field['name'], pods_v( $field['name'], $settings ), $field['type'], $field ); ?>
						<?php echo PodsForm::comment( 'pods_field_' . $field['name'], $field['description'], $field ); ?>
					</td>
				<?php endif; ?>
			</tr>
			<?php
			if ( false !== $depends_on || ! empty( $dep_classes ) ) {
				$depends_on = $dep_classes;
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
			<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Settings', 'pods' ); ?>">
			<img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
		</p>
	</div>
</form>

<script type="text/javascript">
	jQuery( function ( $ ) {
		$( document ).Pods( 'validate' );
		$( document ).Pods( 'dependency', true );
	} );
</script>
